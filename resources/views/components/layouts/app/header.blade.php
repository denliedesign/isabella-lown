<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-950" id="top">
        <flux:header class="bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950">
            <flux:sidebar.toggle inset="left" class="lg:hidden me-3" icon="bars-2" />

            <flux:navbar class="-mb-px max-lg:hidden mx-auto">
               @include('components._nav')
            </flux:navbar>
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar stashable sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
            <flux:navbar class="flex flex-col items-start justify-start gap-4 p-4 text-left">
                @include('components._nav')
            </flux:navbar>
        </flux:sidebar>

        {{ $slot }}

        {{-- In your base layout, near the end of <body> --}}
        <div id="toTopWrap"
             class="fixed right-4 bottom-4 z-[100] pointer-events-auto lg:right-6 lg:bottom-6">
            <flux:button
                id="toTopBtn"
                icon="arrow-up"
                variant="ghost"
                aria-label="Back to top"
                class="rounded-full shadow-lg hidden" />
        </div>

        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

        @fluxScripts
        @livewireScripts
    </body>
</html>
