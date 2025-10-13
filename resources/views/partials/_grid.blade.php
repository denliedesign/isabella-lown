{{-- resources/views/portfolio/_grid.blade.php --}}
@props(['items'])

<div class="columns-1 md:columns-2 lg:columns-3 gap-4 masonry">
    @forelse ($items as $item)
        {{-- Each item MUST be an immediate child of the columns container, and must have break-inside-avoid --}}
        <div class="mb-4 break-inside-avoid">
            @php
                $isEmbed = ($item->type === 'embed') && !empty($item->embed_html);
                $html    = $isEmbed ? $item->embed_html : '';

                // Detect Instagram from the embed HTML
                $isInsta = $isEmbed && \Illuminate\Support\Str::contains(
                    $html,
                    ['instagram.com', 'class="instagram-media"']
                );

                // Make width/height fluid if present
                $fluidHtml = $isEmbed
                    ? str_replace(
                        ['width="560"', 'height="315"', "width='560'", "height='315'"],
                        ['width="100%"','height="100%"','width="100%"','height="100%"'],
                        $html
                    )
                    : '';
            @endphp

            @if ($item->type === 'image' && $item->path)
                <div>
                    <div class="relative mb-1 overflow-hidden">
                        <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile"
                             style="background-image:url('/images/chrome.jpg')"></div>

                        <img class="relative z-10 block w-full h-auto p-1 border border-zinc-200 dark:border-zinc-700"
                             src="{{ asset($item->path) }}"
                             alt="{{ $item->title ?? basename($item->path) }}"
                             loading="lazy" decoding="async" />
                    </div>

                    @if ($item->title)
                        <div class="relative z-10 my-1 px-1 text-md font-black uppercase">{{ $item->title }}</div>
                    @endif
                </div>
            @elseif ($item->type === 'video' && $item->path)
                <div>
                    <div class="relative mb-1 overflow-hidden">
                        <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile"
                             style="background-image:url('/images/chrome.jpg')"></div>

                        <div class="relative z-10 p-1 border border-zinc-200 dark:border-zinc-700">
                            <div class="w-full flex justify-center">
                                <div class="relative">
                                    <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile"></div>
                                    <video
                                        class="relative z-10 w-full h-full"
                                        src="{{ asset($item->path) }}"
                                        @if(!empty($item->poster_path)) poster="{{ asset($item->poster_path) }}" @endif
                                        preload="metadata"
                                        controls
                                        playsinline
                                    ></video>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($item->title)
                        <div class="relative z-10 my-1 px-1 text-md font-black uppercase">{{ $item->title }}</div>
                    @endif
                </div>
            @elseif ($isEmbed && $isInsta)
                {{-- Instagram (portrait-ish) --}}
                <div>
                    <div class="relative mb-1 overflow-hidden">
                        <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile"
                             style="background-image:url('/images/chrome.jpg')"></div>

                        <div class="relative z-10 p-1 border border-zinc-200 dark:border-zinc-700">
                            <div class="w-full flex justify-center">
                                <div class="relative" style="width:min(420px,100%); aspect-ratio: 9 / 16;">
                                    <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile"></div>
                                    <div class="relative z-10 w-full h-full">
                                        {!! $fluidHtml !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($item->title)
                        <div class="relative z-10 my-1 px-1 text-md font-black uppercase">{{ $item->title }}</div>
                    @endif
                </div>

                @once
                <script async defer src="https://www.instagram.com/embed.js"></script>
                @endonce

            @elseif ($isEmbed)
                {{-- Default (e.g., YouTube 16:9) --}}
                <div>
                    <div class="relative mb-1 overflow-hidden">
                        <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile"
                             style="background-image:url('/images/chrome.jpg')"></div>

                        <div class="relative z-10 p-1 border border-zinc-200 dark:border-zinc-700">
                            <div class="w-full flex justify-center">
                                <div class="relative" style="width:min(560px,100%); aspect-ratio: 16 / 9;">
                                    <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile"></div>
                                    <div class="relative z-10 w-full h-full">
                                        {!! $fluidHtml !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($item->title)
                        <div class="relative z-10 my-1 px-1 text-md font-black uppercase">{{ $item->title }}</div>
                    @endif
                </div>
            @endif
        </div> {{-- /item card --}}
    @empty
        <div class="rounded-lg border p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            Nothing here yet.
        </div>
    @endforelse
</div>
