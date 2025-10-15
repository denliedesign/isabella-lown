<x-layouts.app.header :title="$title ?? null">
    <flux:main container>
        <div class="lg:hidden flex justify-center" style="width: 100% !important;"><x-app-logo /></div>
        <h1 class="text-center lg:hidden font-semibold underline uppercase mb-3" style="font-size: 25px;">{{ str($title)->after('Isabella Lown | ')->trim() }}</h1>
        {{ $slot }}
        @include('components._footer')
    </flux:main>
</x-layouts.app.header>
