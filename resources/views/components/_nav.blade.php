<div class="mr-5"><x-app-logo /></div>
<flux:navbar.item :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
    {{ __('Home') }}
</flux:navbar.item> <div class="hidden lg:block">|</div>
<flux:navbar.item :href="route('creative-direction')" :current="request()->routeIs('creative-direction')" wire:navigate>
    {{ __('Creative Direction') }}
</flux:navbar.item> <div class="hidden lg:block">|</div>
<flux:navbar.item :href="route('stage-choreo')" :current="request()->routeIs('stage-choreo')" wire:navigate>
    {{ __('Stage Choreo') }}
</flux:navbar.item> <div class="hidden lg:block">|</div>
<flux:navbar.item :href="route('teaching')" :current="request()->routeIs('teaching')" wire:navigate>
    {{ __('Teaching') }}
</flux:navbar.item> <div class="hidden lg:block">|</div>
<flux:navbar.item :href="route('dancing')" :current="request()->routeIs('dancing')" wire:navigate>
    {{ __('Dancing') }}
</flux:navbar.item> <div class="hidden lg:block">|</div>
<flux:navbar.item :href="route('bio')" :current="request()->routeIs('bio')" wire:navigate>
    {{ __('Bio') }}
</flux:navbar.item>
