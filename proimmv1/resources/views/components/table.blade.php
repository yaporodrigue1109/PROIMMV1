@props([
    'title' => null,
    'collection' => null,
    'emptyMessage' => 'Aucune donnée trouvée',
    'colspan' => 1,
    'searchId' => null,
    'searchPlaceholder' => 'Rechercher...',
])

<div class="table-workspace">
    <div class="card">

        @if($title || isset($actions))
            <div class="table-toolbar">
                <div>
                    @if($title)
                        <h3>{{ $title }}</h3>
                    @endif
                </div>

                @if(isset($actions) || $searchId)
                    <div class="table-toolbar-actions">
                        {{ $actions ?? '' }}

                        @if($searchId)
                            <label class="search-field" for="{{ $searchId }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                          clip-rule="evenodd"/>
                                </svg>
                                <input id="{{ $searchId }}" type="search" placeholder="{{ $searchPlaceholder }}">
                            </label>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <div class="table-shell u-table-flush">
            <table {{ $attributes->merge(['class' => 'data-table responsive-table u-table-fit']) }}>
                <thead>
                <tr>
                    {{ $thead }}
                </tr>
                </thead>

                <tbody>
                @php
                    $hasItems = false;

                    if ($collection) {
                        if (is_array($collection)) {
                            $hasItems = count($collection) > 0;
                        } elseif (is_object($collection) && method_exists($collection, 'count')) {
                            $hasItems = $collection->count() > 0;
                        } elseif (is_countable($collection)) {
                            $hasItems = count($collection) > 0;
                        }
                    }
                @endphp

                @if($hasItems)
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="{{ $colspan }}" class="text-center text-muted">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        @php
            $hasPages = false;

            if (is_object($collection) && method_exists($collection, 'hasPages')) {
                $hasPages = $collection->hasPages();
            }
        @endphp

        @if($hasPages)
            <div class="pagination-wrap u-p-sm">
                {{ $collection->links() }}
            </div>
        @endif

    </div>
</div>