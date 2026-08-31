<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\WebController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogController extends Controller
{
    public function __construct(private readonly WebController $catalog)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'mode' => ['nullable', 'in:vente,location'],
            'property_type' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $allProperties = collect($this->catalog->catalogProperties());
        $propertyTypes = $allProperties
            ->pluck('property_type')
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $properties = $allProperties
            ->when($filters['mode'] ?? null, fn ($items, $mode) =>
                $items->where('mode', $mode)
            )
            ->when($filters['property_type'] ?? null, fn ($items, $type) =>
                $items->filter(fn (array $property) =>
                    Str::lower((string) ($property['property_type'] ?? '')) === Str::lower(trim($type))
                )
            )
            ->when($filters['location'] ?? null, fn ($items, $location) =>
                $items->filter(fn (array $property) =>
                    Str::contains(
                        Str::lower((string) ($property['address'] ?? '')),
                        Str::lower(trim($location))
                    )
                )
            )
            ->values();

        $perPage = (int) ($filters['per_page'] ?? 10);
        $currentPage = (int) ($filters['page'] ?? 1);
        $page = new LengthAwarePaginator(
            $properties->forPage($currentPage, $perPage)->values(),
            $properties->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json(array_merge($page->toArray(), [
            'property_types' => $propertyTypes,
        ]));
    }

    public function show(string $listing): JsonResponse
    {
        $property = $this->catalog->catalogProperty($listing);

        abort_unless($property, 404, 'Ce bien est indisponible.');

        return response()->json([
            'data' => collect($property)->except('reference')->all(),
        ]);
    }
}
