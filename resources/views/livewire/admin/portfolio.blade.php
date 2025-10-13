<?php

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

use function Livewire\Volt\{ state, rules, uses };

uses([WithFileUploads::class]);

state([
    'items' => fn () => Media::orderBy('sort_order')->orderByDesc('id')->get(),
    'upload' => null,         // temporary file (image or video)
    'poster_upload' => null,   // optional poster when type = video
    'type' => 'image',      // image|video|embed
    'title' => '',
    'tag' => 'all',
    'style' => null,
    'embed_html' => '', // when type = embed
]);

rules([
    'type' => 'required|in:image,video,embed',
    'title' => 'nullable|string|max:255',
    'tag' => 'required|string|max:50',
    'style' => 'nullable|in:hip-hop,contemporary,fusion', // fusion = "fusion / jazz funk"
    'upload' => 'nullable|file',
    'poster_upload' => 'nullable|image|max:10240', // up to ~10MB for posters
    'embed_html' => 'nullable|string',       // used when type=embed
]);

$parseYoutubeId = function (string $url): ?string {
    // handles youtu.be/ID, youtube.com/watch?v=ID, /embed/ID, etc.
    $patterns = [
        '~youtu\.be/([^\?\&\#]+)~',
        '~youtube\.com/embed/([^\?\&\#]+)~',
        '~youtube\.com/watch\?v=([^\&\#]+)~',
        '~youtube\.com/shorts/([^\?\&\#]+)~',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $url, $m)) return $m[1];
    }
    return null;
};

$save = function () {
    $this->authorize('create', \App\Models\Media::class);
    $this->validate();

    $path = null;
    $posterPath = null;

       if ($this->type === 'image') {
               // stricter validation for images
               $this->validate(['upload' => 'required|image|max:102400']); // ~100MB
               $stored = $this->upload->store('portfolio', 'public');
               $path   = "storage/{$stored}";

           } elseif ($this->type === 'video') {
               // video files: mp4 / webm / ogg — increase limit if you need
               $this->validate(['upload' => 'required|mimetypes:video/*|max:512000']); // ~500MB
               $stored = $this->upload->store('portfolio', 'public');
               $path   = "storage/{$stored}";

           // optional poster
           if ($this->poster_upload) {
               $pStored    = $this->poster_upload->store('portfolio_posters', 'public');
               $posterPath = "storage/{$pStored}";
           } else {
               $posterPath = null; // no poster
           }

           } else { // embed
        if (trim((string)$this->embed_html) === '') {
            $this->addError('embed_html', 'Please paste the YouTube embed iframe.');
            return;
        }
    }

    $nextOrder = (int) \App\Models\Media::max('sort_order') + 1;

    \App\Models\Media::create([
        'type' => $this->type,              // image | embed
        'path' => $path,                    // null for embed
        'poster_path' => $posterPath,
        'embed_html' => $this->type === 'embed' ? $this->embed_html : null,
        'title' => $this->title ?: null,
        'sort_order' => $nextOrder,
        'created_by' => auth()->id(),
        'tag'   => $this->tag,
        'style' => $this->tag === 'dancing' ? $this->style : null,
    ]);

    $this->reset(['upload','poster_upload','title','type','embed_html','style']);
    $this->tag = $this->tag ?: 'all';
    $this->items = \App\Models\Media::orderBy('sort_order')->orderByDesc('id')->get();
    $this->dispatch('close', name: 'upload-media'); // tell Flux to close this modal
    return redirect()->route('admin.portfolio');

};

$delete = function (int $id) {
    $m = Media::findOrFail($id);
    $this->authorize('delete', $m);

    if (str_starts_with($m->path, 'storage/portfolio/')) {
        $old = str_replace('storage/', '', $m->path);
        Storage::disk('public')->delete($old);
    }

    if ($m->poster_path && str_starts_with($m->poster_path, 'storage/portfolio_posters/')) {
        $pOld = str_replace('storage/', '', $m->poster_path);
        Storage::disk('public')->delete($pOld);
    }

    $m->delete();
    $this->items = Media::orderBy('sort_order')->orderByDesc('id')->get();
};

$reorder = function (array $orderedIds) {
    $this->authorize('viewAny', \App\Models\Media::class);

    // Keep it safe & consistent
    \Illuminate\Support\Facades\DB::transaction(function () use ($orderedIds) {
        foreach ($orderedIds as $i => $id) {
            \App\Models\Media::whereKey($id)->update(['sort_order' => $i + 1]);
        }
    });

    // Reload the current listing
    $this->items = \App\Models\Media::orderBy('sort_order')->orderByDesc('id')->get();
};

//$reorder = function (array $orderedIds) {
//    $this->authorize('viewAny', Media::class);
//    foreach ($orderedIds as $i => $id) {
//        Media::whereKey($id)->update(['sort_order' => $i + 1]);
//    }
//    $this->items = Media::orderBy('sort_order')->orderByDesc('id')->get();
//};

$reloadItems = function (string $section = 'all') {
    $q = Media::orderBy('sort_order')->orderByDesc('id');
    if ($section !== 'all') $q->where('tag', $section);
    $this->items = $q->get();
};

?><div id="onediv">
    <div class="mx-auto max-w-5xl p-4">
        @include('.components._controls')
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Portfolio Media</h1>

            @can('create', \App\Models\Media::class)
                <flux:modal.trigger name="upload-media">
                    <flux:button icon="plus">Add Media</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>

        <select class="rounded-lg border px-2 py-1 mb-3 text-sm"
                wire:change="$set('items', [])"
                x-on:change="$wire.$call('reloadItems', $event.target.value)">
            <option class="text-black" value="all">All</option>
            <option class="text-black" value="home">Home</option>
            <option class="text-black" value="creative-direction">Creative Direction</option>
            <option class="text-black" value="stage-choreo">Stage Choreo</option>
            <option class="text-black" value="dancing">Dancing</option>
            <option class="text-black" value="teaching">Teaching</option>
        </select>

        <!-- UI sortable list -->
        <div
            x-data="{
      ids: @js($items->pluck('id')->values()),   // <-- IDs only, never undefined
      draggingId: null,
      start(e, id) {
        this.draggingId = id;
        e.dataTransfer.effectAllowed = 'move';
      },
      over(e) { e.preventDefault(); },
      drop(e, id) {
        e.preventDefault();
        if (this.draggingId === null || this.draggingId === id) return;

        // local reorder
        const ids = [...this.ids];
        const from = ids.indexOf(this.draggingId);
        const to   = ids.indexOf(id);
        if (from === -1 || to === -1) return;
        ids.splice(to, 0, ids.splice(from, 1)[0]);
        this.ids = ids;

        // persist to server
        $wire.reorder(ids);
        this.draggingId = null;
      }
  }"
            class="space-y-3"
        >
            @foreach ($items as $m)
                <div
                    data-id="{{ $m->id }}"               {{-- not strictly needed but handy for debugging --}}
                draggable="true"
                    @dragstart="start($event, {{ $m->id }})"
                    @dragover="over($event)"
                    @drop="drop($event, {{ $m->id }})"
                    class="flex items-center gap-3 rounded-xl border border-zinc-200 dark:border-zinc-700 p-2 bg-white dark:bg-zinc-900"
                >
                    <div class="cursor-grab select-none text-zinc-500" title="Drag">&#x2630;</div>

                    <div class="size-16 shrink-0 rounded overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800">
                        @if ($m->type === 'image' && $m->path)
                            <img src="{{ asset($m->path) }}" alt="" class="h-full w-full object-cover">
                        @elseif ($m->type === 'video' && $m->path)
                            <video src="{{ asset($m->path) }}" class="h-full w-full object-cover" preload="metadata" muted></video>
                        @elseif ($m->type === 'embed')
                            <div class="flex h-full w-full items-center justify-center text-[10px] uppercase text-zinc-500">Embed</div>
                        @endif
                    </div>

                    <div class="min-w-0 grow">
                        <div class="truncate font-medium">{{ $m->title ?: basename($m->path ?? 'embed') }}</div>
                        <div class="text-xs text-zinc-500">{{ $m->tag }}@if($m->style) · {{ $m->style }}@endif · {{ $m->type }}</div>
                    </div>

                    <button type="button"
                            class="rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs text-red-700"
                            wire:click="$call('delete', {{ $m->id }})">
                        Delete
                    </button>
                </div>
            @endforeach
        </div>

        <!-- end sortable UI list -->

    @can('create', \App\Models\Media::class)
        {{-- Modal --}}
            <flux:modal name="upload-media" class="md:w-96">

            <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                <div class="space-y-4 p-4 sm:p-6">

                    <h3 class="text-lg font-semibold">Add Media</h3>

                    {{-- Tag / Section --}}
                    <label class="block text-sm">
                        <span class="text-zinc-700 dark:text-zinc-200">Tag / Section</span>
                        <select class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                                wire:model.live="tag">
                            <option>Please Select</option>
                            <option value="home">Home</option>
                            <option value="creative-direction">Creative Direction</option>
                            <option value="stage-choreo">Stage Choreo</option>
                            <option value="teaching">Teaching</option>
                            <option value="dancing">Dancing</option>
                        </select>
                        @error('tag') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    {{-- Style (only when tag = dancing) --}}
                    @if ($tag === 'dancing')
                        <label class="block text-sm">
                            <span class="text-zinc-700 dark:text-zinc-200">Style</span>
                            <select class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                                    wire:model.defer="style">
                                <option value="">(Choose a style)</option>
                                <option value="hip-hop">Hip Hop</option>
                                <option value="contemporary">Contemporary</option>
                                <option value="fusion">Fusion / Jazz Funk</option>
                            </select>
                            @error('style') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                    @endif

                    {{-- Type --}}
                    <label class="block text-sm">
                        <span class="text-zinc-700 dark:text-zinc-200">Type</span>
                        <select class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                                wire:model.live="type">
                            <option value="image">Image (upload)</option>
                            <option value="video">Video (upload)</option>
                            <option value="embed">YouTube (paste iframe)</option>
                        </select>
                        @error('type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    {{-- Conditional input: File OR Embed HTML --}}
                    @if ($type === 'embed')
                        <label class="block text-sm">
                            <span class="text-zinc-700 dark:text-zinc-200">YouTube Embed HTML</span>
                            <textarea rows="5"
                                      class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                                      wire:model.defer="embed_html"
                                      placeholder='<iframe src="https://www.youtube.com/embed/…"></iframe>'></textarea>
                            @error('embed_html') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                    @else
                        <label class="block text-sm">
  <span class="text-zinc-700 dark:text-zinc-200">
    {{ $type === 'video' ? 'Video file' : 'Image file' }}
  </span>

                            <div x-data="{ isUploading:false, progress:0 }"
                                 x-on:livewire-upload-start="isUploading=true; progress=0"
                                 x-on:livewire-upload-finish="isUploading=false"
                                 x-on:livewire-upload-error="isUploading=false"
                                 x-on:livewire-upload-progress="progress=$event.detail.progress">

                                <input
                                    type="file"
                                    wire:key="media-upload-{{ $type }}"
                                    accept="{{ $type === 'video' ? 'video/*' : 'image/*' }}"
                                    class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 file:me-3 file:rounded-md file:border file:border-zinc-100 file:px-3 file:py-2"
                                    wire:model="upload"
                                >

                                {{-- progress bar --}}
                                <div x-show="isUploading" class="mt-2 h-2 rounded bg-zinc-200/70 overflow-hidden">
                                    <div class="h-2 bg-emerald-500 transition-all" :style="'width:'+progress+'%;'"></div>
                                </div>
                                <div x-show="isUploading" class="mt-1 text-xs text-zinc-600" x-text="progress + '%'"></div>

                                {{-- optional cancel (just clears the temp file prop) --}}
                                <button x-show="isUploading" type="button"
                                        class="mt-2 rounded border px-2 py-1 text-xs"
                                        wire:click="$set('upload', null)">
                                    Cancel
                                </button>
                            </div>

                            @error('upload') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>


                        {{-- Poster (optional) — no progress bar --}}
                        @if ($type === 'video')
                            <label class="mt-3 block text-sm">
                                <span class="text-zinc-700 dark:text-zinc-200">Poster image (optional)</span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 file:me-3 file:rounded-md file:border file:border-zinc-100 file:px-3 file:py-2"
                                    wire:model="poster_upload"
                                >
                                @error('poster_upload') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>
                        @endif
                    @endif

                    {{-- Title --}}
                    <label class="block text-sm">
                        <span class="text-zinc-700 dark:text-zinc-200">Title (optional)</span>
                        <input type="text"
                               class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                               wire:model.defer="title">
                        @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    {{-- Actions --}}
                    <div class="mt-2 flex justify-end gap-2">
                        <flux:modal.close>
                            <button type="button" class="rounded-lg border px-4 py-2 text-sm">Cancel</button>
                        </flux:modal.close>

                        <button type="button"
                                  class="rounded-lg bg-zinc-900 px-4 py-2 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900"
                                wire:click="$call('save')">Upload</button>

                    </div>
                </div>
            </div>
        </flux:modal>
        @endcan
    </div>
</div>
