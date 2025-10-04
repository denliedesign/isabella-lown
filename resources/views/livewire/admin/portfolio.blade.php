<?php

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

use function Livewire\Volt\{ state, rules, uses };

uses([WithFileUploads::class]);

state([
    'items' => fn () => Media::orderBy('sort_order')->orderByDesc('id')->get(),
    'upload' => null,         // temporary file (image or video)
    'type' => 'image',      // image|video
    'title' => '',
    'tag' => 'all',
    'embed_html' => '', // when type = embed
]);

rules([
    'type' => 'required|in:image,embed',
    'title' => 'nullable|string|max:255',
    'tag' => 'required|string|max:50',
    'upload' => 'nullable|file|max:102400',  // only used when type=image
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

    if ($this->type === 'image') {
        $path = $this->upload ? $this->upload->store('portfolio', 'public') : null;
        if (!$path) {
            $this->addError('upload', 'Please choose an image file.');
            return;
        }
        $path = "storage/{$path}";
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
        'embed_html' => $this->type === 'embed' ? $this->embed_html : null,
        'title' => $this->title ?: null,
        'sort_order' => $nextOrder,
        'created_by' => auth()->id(),
        'tag' => $this->tag,
    ]);

    $this->reset(['upload','title','type','embed_html']);
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

    $m->delete();
    $this->items = Media::orderBy('sort_order')->orderByDesc('id')->get();
};

$reorder = function (array $orderedIds) {
    $this->authorize('viewAny', Media::class);
    foreach ($orderedIds as $i => $id) {
        Media::whereKey($id)->update(['sort_order' => $i + 1]);
    }
    $this->items = Media::orderBy('sort_order')->orderByDesc('id')->get();
};

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
            <option class="text-black" value="film-choreo">Film Choreo</option>
            <option class="text-black" value="stage-choreo">Stage Choreo</option>
            <option class="text-black" value="dancing">Dancing</option>
            <option class="text-black" value="teaching">Teaching</option>
        </select>

        {{-- List / Reorder --}}
        <div id="look-grid"
            x-data="{
                order: @entangle('items').defer,
                draggingId: null,
                start(e,id){ this.draggingId=id; e.dataTransfer.effectAllowed='move' },
                over(e){ e.preventDefault() },
                drop(e,id){
                    e.preventDefault();
                    if(this.draggingId===null||this.draggingId===id) return;
                    const ids = this.order.map(i=>i.id);
                    const from = ids.indexOf(this.draggingId);
                    const to = ids.indexOf(id);
                    ids.splice(to,0, ids.splice(from,1)[0]);
                    $wire.reorder(ids);
                    this.draggingId=null;
                }
            }"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
        >
            @foreach($items as $m)
                <div id="look-card"
                    draggable="true"
                    @dragstart="start($event, {{ $m->id }})"
                    @dragover="over($event)"
                    @drop="drop($event, {{ $m->id }})"
                    class="group relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700"
                >
                    <div class="aspect-video bg-zinc-100 dark:bg-zinc-900">
                        @if ($m->type === 'image' && $m->path)
                            <img class="h-full w-full object-cover" src="{{ asset($m->path) }}" alt="{{ $m->title ?? '' }}">
                        @elseif ($m->type === 'embed' && $m->embed_html)
                            <div class="h-full w-full">
                                {!! $m->embed_html !!}
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-3 px-2 pt-2">
                        <div class="min-w-0">
                            <div class="truncate font-medium">{{ $m->title ?? basename($m->path) }}</div>
                            <div class="text-xs text-zinc-500">{{ $m->tag }} · {{ $m->type }}</div>
                        </div>

                        @can('update', $m)
                            <div class="flex shrink-0 items-center gap-2">
                                <button
                                    type="button"
                                    class="rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs text-red-700"
                                    wire:click="$call('delete', {{ $m->id }})"
                                >Delete</button>
                            </div>
                        @endcan
                    </div>
                </div>
            @endforeach

    </div>
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
                            <option value="dancing">Dancing</option>
                            <option value="teaching">Teaching</option>
                            <option value="film-choreo">Film Choreo</option>
                            <option value="stage-choreo">Stage Choreo</option>
                        </select>
                        @error('tag') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    {{-- Type --}}
                    <label class="block text-sm">
                        <span class="text-zinc-700 dark:text-zinc-200">Type</span>
                        <select class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                                wire:model.live="type">
                            <option value="image">Image (upload)</option>
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
                            <span class="text-zinc-700 dark:text-zinc-200">Image file</span>
                            <input type="file"
                                   class="mt-1 w-full rounded-lg border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 file:me-3 file:rounded-md file:border-0 file:bg-zinc-700 file:px-3 file:py-2"
                                   wire:model="upload">
                            @error('upload') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
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
