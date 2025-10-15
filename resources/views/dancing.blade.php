<x-layouts.app title="Isabella Lown | Dancing">

    @php
        $all = \App\Models\Media::where('tag','dancing')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $groups = [
          'hip-hop'      => $all->where('style','hip-hop'),
          'contemporary' => $all->where('style','contemporary'),
          'fusion'       => $all->where('style','fusion'),
          'uncategorized'=> $all->filter(fn($m) => empty($m->style)),
        ];

        $labels = [
          'hip-hop'      => 'Hip Hop',
          'contemporary' => 'Contemporary',
          'fusion'       => 'Fusion / Jazz Funk',
          'uncategorized'=> 'Other',
        ];
    @endphp

    <div class="mx-auto max-w-6xl p-4">
        @foreach (['hip-hop','contemporary','fusion','uncategorized'] as $key)
            @if ($groups[$key]->isNotEmpty())
                <h2 class="mb-3 mt-8 text-xl uppercase font-thin">{{ $labels[$key] }}</h2>
                @include('partials._grid', ['items' => $groups[$key]])
            @endif
        @endforeach
    </div>

</x-layouts.app>
