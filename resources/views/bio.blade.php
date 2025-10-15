<x-layouts.app title="Bio">



    <div class="mx-auto">
        <div class="columns-1 md:columns-2 lg:columns-3 gap-4 masonry">
            @forelse ($headshots as $h)
            <div class="mb-4 break-inside-avoid">
                <div>
                    <div class="relative mb-1 overflow-hidden">
                        <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile" style="background-image:url('/images/chrome.jpg')"></div>
                        <img class="relative z-10 block w-full h-auto p-1 border border-zinc-200 dark:border-zinc-700" src="{{ asset($h->path) }}" loading="lazy" decoding="async" />
                    </div>
                </div>
            </div>
            @empty
                <div class="rounded-lg border p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                    Nothing here yet.
                </div>
            @endforelse
{{--            <div class="mb-4 break-inside-avoid">--}}
{{--                <div>--}}
{{--                    <div class="relative mb-1 overflow-hidden">--}}
{{--                        <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile" style="background-image:url('/images/chrome.jpg')"></div>--}}
{{--                        <img class="relative z-10 block w-full h-auto p-1 border border-zinc-200 dark:border-zinc-700" src="/images/headshot-2.png" loading="lazy" decoding="async" />--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>

        <div class="mt-5 mb-5">
            <p>
                <span class="uppercase font-black" style="font-size: 30px;">Isabella Lown</span> {!! nl2br(e(optional($bio)->content)) !!}
            </p>
        </div>
{{--        <div class="mx-auto max-w-3xl p-4 prose prose-zinc dark:prose-invert">--}}
{{--            {!! nl2br(e(optional($bio)->content)) !!}--}}
{{--        </div>--}}
    </div>

</x-layouts.app>


