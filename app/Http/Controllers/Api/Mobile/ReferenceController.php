<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferenceController extends Controller
{
    public function regions(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('regions')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function cities(Request $request): JsonResponse
    {
        $data = $request->validate([
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
        ]);

        $cities = DB::table('villes')
            ->when(
                $data['region_id'] ?? null,
                fn ($query, $regionId) => $query->where('region_id', $regionId),
            )
            ->orderBy('name')
            ->get(['id', 'region_id', 'name']);

        return response()->json(['data' => $cities]);
    }

    public function genders(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('genres')
                ->orderBy('name')
                ->get(['id', 'name', 'abreviation']),
        ]);
    }
}
