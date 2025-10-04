<x-layouts.app>

    @php
        // Fast, simple query—no publish filter since you removed it
        $items = \App\Models\Media::where('tag','stage-choreo')
                  ->orderBy('sort_order')
                  ->orderByDesc('id')
                  ->get();
    @endphp

    <div class="mx-auto max-w-6xl p-4">
        @include('partials._grid', ['items' => $items])
    </div>

</x-layouts.app>
