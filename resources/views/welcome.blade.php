<x-layouts.app :title="__('Isabella Lown')">

    @php
        // Fast, simple query—no publish filter since you removed it
        $items = \App\Models\Media::orderBy('sort_order')->orderByDesc('id')->get();
    @endphp

    <div class="mx-auto">
        @include('partials._grid', ['items' => $items])
    </div>

</x-layouts.app>


