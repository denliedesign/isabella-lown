<x-layouts.app>

    @php
        // Fast, simple query—no publish filter since you removed it
        $items = \App\Models\Media::where('tag','creative-direction')
                  ->orderBy('sort_order')
                  ->orderByDesc('id')
                  ->get();
    @endphp

    <div class="mx-auto max-w-6xl p-4">
        <div class="text-center mb-5 pb-5">
            <em>All content below was directed, choreographed, and edited by Isabella Lown.</em>
        </div>
        @include('partials._grid', ['items' => $items])
    </div>

</x-layouts.app>
