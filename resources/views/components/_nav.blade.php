{{--<div class="mr-5"><x-app-logo /></div>--}}
{{--<flux:navbar.item :href="route('home')" :current="request()->routeIs('home')" wire:navigate>--}}
{{--    {{ __('Home') }}--}}
{{--</flux:navbar.item> <div class="hidden lg:block">|</div>--}}
{{--<flux:navbar.item :href="route('creative-direction')" :current="request()->routeIs('creative-direction')" wire:navigate>--}}
{{--    {{ __('Creative Direction') }}--}}
{{--</flux:navbar.item> <div class="hidden lg:block">|</div>--}}
{{--<flux:navbar.item :href="route('stage-choreo')" :current="request()->routeIs('stage-choreo')" wire:navigate>--}}
{{--    {{ __('Stage Choreo') }}--}}
{{--</flux:navbar.item> <div class="hidden lg:block">|</div>--}}
{{--<flux:navbar.item :href="route('teaching')" :current="request()->routeIs('teaching')" wire:navigate>--}}
{{--    {{ __('Teaching') }}--}}
{{--</flux:navbar.item> <div class="hidden lg:block">|</div>--}}
{{--<flux:navbar.item :href="route('dancing')" :current="request()->routeIs('dancing')" wire:navigate>--}}
{{--    {{ __('Dancing') }}--}}
{{--</flux:navbar.item> <div class="hidden lg:block">|</div>--}}
{{--<flux:navbar.item :href="route('bio')" :current="request()->routeIs('bio')" wire:navigate>--}}
{{--    {{ __('Bio') }}--}}
{{--</flux:navbar.item>--}}
<div class="mr-5"><x-app-logo /></div>
<flux:navbar.item
    :href="route('home')"
    :current="request()->routeIs('home')"
    wire:navigate
    class="navlink rounded-md px-3 py-1.5 transition-colors
             text-white !opacity-100
             hover:bg-zinc-800/70
             active:bg-zinc-800/70
             focus-visible:bg-zinc-800/60
             data-[current=true]:bg-zinc-800/80 data-[current=true]:font-semibold
             [--tw-ring-offset-shadow:0_0] [--tw-ring-shadow:0_0]">
    {{ __('Home') }}
</flux:navbar.item>

<div class="hidden lg:block text-zinc-500">|</div>

<flux:navbar.item
    :href="route('creative-direction')"
    :current="request()->routeIs('creative-direction')"
    wire:navigate
    class="navlink rounded-md px-3 py-1.5 transition-colors
             text-white !opacity-100
             hover:bg-zinc-800/70
             active:bg-zinc-800/70
             focus-visible:bg-zinc-800/60
             data-[current=true]:bg-zinc-800/80 data-[current=true]:font-semibold
             [--tw-ring-offset-shadow:0_0] [--tw-ring-shadow:0_0]">
    {{ __('Creative Direction') }}
</flux:navbar.item>

<div class="hidden lg:block text-zinc-500">|</div>

<flux:navbar.item
    :href="route('stage-choreo')"
    :current="request()->routeIs('stage-choreo')"
    wire:navigate
    class="navlink rounded-md px-3 py-1.5 transition-colors
             text-white !opacity-100
             hover:bg-zinc-800/70
             active:bg-zinc-800/70
             focus-visible:bg-zinc-800/60
             data-[current=true]:bg-zinc-800/80 data-[current=true]:font-semibold
             [--tw-ring-offset-shadow:0_0] [--tw-ring-shadow:0_0]">
    {{ __('Stage Choreo') }}
</flux:navbar.item>

<div class="hidden lg:block text-zinc-500">|</div>

<flux:navbar.item
    :href="route('teaching')"
    :current="request()->routeIs('teaching')"
    wire:navigate
    class="navlink rounded-md px-3 py-1.5 transition-colors
             text-white !opacity-100
             hover:bg-zinc-800/70
             active:bg-zinc-800/70
             focus-visible:bg-zinc-800/60
             data-[current=true]:bg-zinc-800/80 data-[current=true]:font-semibold
             [--tw-ring-offset-shadow:0_0] [--tw-ring-shadow:0_0]">
    {{ __('Teaching') }}
</flux:navbar.item>

<div class="hidden lg:block text-zinc-500">|</div>

<flux:navbar.item
    :href="route('dancing')"
    :current="request()->routeIs('dancing')"
    wire:navigate
    class="navlink rounded-md px-3 py-1.5 transition-colors
             text-white !opacity-100
             hover:bg-zinc-800/70
             active:bg-zinc-800/70
             focus-visible:bg-zinc-800/60
             data-[current=true]:bg-zinc-800/80 data-[current=true]:font-semibold
             [--tw-ring-offset-shadow:0_0] [--tw-ring-shadow:0_0]">
    {{ __('Dancing') }}
</flux:navbar.item>

<div class="hidden lg:block text-zinc-500">|</div>

<flux:navbar.item
    :href="route('bio')"
    :current="request()->routeIs('bio')"
    wire:navigate
    class="navlink rounded-md px-3 py-1.5 transition-colors
             text-white !opacity-100
             hover:bg-zinc-800/70
             active:bg-zinc-800/70
             focus-visible:bg-zinc-800/60
             data-[current=true]:bg-zinc-800/80 data-[current=true]:font-semibold
             [--tw-ring-offset-shadow:0_0] [--tw-ring-shadow:0_0]">
    {{ __('Bio') }}
</flux:navbar.item>
