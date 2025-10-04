<x-layouts.app.header :title="$title ?? null">
    <flux:main container>
        {{ $slot }}
        @include('components._footer')
    </flux:main>
</x-layouts.app.header>
