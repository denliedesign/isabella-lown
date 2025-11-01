@php
    $pageTitle = $title ?? 'Isabella Lown';
    $displayTitle = str($pageTitle)->contains('Isabella Lown |')
        ? str($pageTitle)->after('Isabella Lown | ')->trim()
        : $pageTitle;
@endphp

<x-layouts.app.header :title="$pageTitle">
    <flux:main container>
        <div class="lg:hidden flex justify-center" style="width: 100% !important;">
            <x-app-logo />
        </div>
        <h1 class="text-center lg:hidden font-semibold underline uppercase mb-3" style="font-size: 25px;">
            {{ $displayTitle }}
        </h1>
        {{ $slot }}
        @include('components._footer')
    </flux:main>
</x-layouts.app.header>

