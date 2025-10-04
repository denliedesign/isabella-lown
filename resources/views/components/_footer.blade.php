<div class="mt-5 pt-5 flex justify-center" style="font-size: 15px; text-align: center; color: #D8D8D8;">
    <div class="flex align-center"><strong>contact</strong>&nbsp; isabellalown@gmail.com / anais@blocagency.com</div>
    <div class="mx-3">&nbsp;</div><div class="flex align-center"><a href="https://www.youtube.com/@isabellalown4176" target="_blank"><ion-icon style="font-size: 22px; color: #D8D8D8;" name="logo-youtube"></ion-icon></a></div>
        <div class="mx-3">&nbsp;</div><div class="flex align-center"><a href="https://www.instagram.com/isabellalown" target="_blank"><ion-icon style="font-size: 22px; color: #D8D8D8;" name="logo-instagram"></ion-icon></a></div>
</div>
<div class="mt-3 flex justify-center" style="font-size: 11px; text-align: center; color: #4D4D4D;">
    @guest
        <a href="{{ route('login') }}" class="hover:underline">Admin</a>
    @endguest

    @auth
        @can('create', \App\Models\Media::class)
            <a href="{{ route('admin.portfolio') }}" class="me-3 hover:underline">Admin</a>
        @else
            <a href="{{ route('login') }}" class="me-3 hover:underline">Account</a>
        @endcan
        <div class="mx-3">&middot;</div>
        {{-- POST logout (looks like a link) --}}
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="hover:underline text-red-600">Logout</button>
        </form>
    @endauth
</div>


