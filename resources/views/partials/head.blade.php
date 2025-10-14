<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? 'Isabella Lown' }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Encode+Sans+Expanded:wght@100;200;300;400;500;600;700;800;900&display=swap');
</style>

<style>html,body{background:#a4a4d9!important}</style>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
@livewireStyles
