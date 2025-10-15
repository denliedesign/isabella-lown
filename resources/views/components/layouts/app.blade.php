<x-layouts.app.header :title="$title ?? null">
    <flux:main container>
        <div class="lg:hidden flex justify-center" style="width: 100% !important;"><x-app-logo /></div>
        <h1 class="text-center lg:hidden font-semibold uppercase underline mb-3" style="font-size: 25px;">@if($title != "Isabella Lown"){{ $title }}@endif</h1>
        {{ $slot }}
        @include('components._footer')
    </flux:main>
</x-layouts.app.header>
