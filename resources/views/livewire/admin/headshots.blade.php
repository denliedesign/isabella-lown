<?php

use App\Models\Headshot;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

use function Livewire\Volt\{ state, rules, uses };

uses([WithFileUploads::class]);

state([
    // pull newest first so new uploads appear at the top
    'items'  => fn () => Headshot::latest()->get(),
    'upload' => null,        // Livewire temporary uploaded file
    'fileId' => fn () => uniqid('file_', true), // used to force-reset the <input type="file">
]);

rules([
    'upload' => 'required|image|max:10240', // 10MB
]);

$save = function () {
    $this->authorize('create', \App\Models\Media::class); // reuse MediaPolicy

    // Guard in case user clicks during upload
    if ($this->upload === null) {
        $this->addError('upload', 'Please choose an image first.');
        return;
    }

    $this->validate();

    // Store and persist
    $stored = $this->upload->store('headshots', 'public'); // storage/app/public/headshots/...
    $path   = "storage/{$stored}";

    Headshot::create([
        'path' => $path,
    ]);

    // Reset the file input and temp upload so the next selection is clean
    $this->reset('upload');
    $this->fileId = uniqid('file_', true);

    // Refresh items so the new image appears immediately
    $this->items = Headshot::latest()->get();

    // Flash success (no redirect)
    session()->flash('status', 'Uploaded!');
};

$delete = function (int $id) {
    $this->authorize('create', \App\Models\Media::class);

    $h = Headshot::findOrFail($id);

    if (str_starts_with($h->path, 'storage/headshots/')) {
        $relative = str_replace('storage/', '', $h->path); // headshots/...
        Storage::disk('public')->delete($relative);
    }

    $h->delete();

    // Refresh items so the deletion reflects immediately
    $this->items = Headshot::latest()->get();

    session()->flash('status', 'Deleted.');
};
?>
<div class="mx-auto max-w-5xl p-4">
    @include('.components._controls')
    <h1 class="mb-4 text-xl font-semibold">Headshots (Admin)</h1>

    @if (session('status'))
        <div class="mb-3 rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @can('create', \App\Models\Media::class)
        {{-- Use a real form so submit waits for Livewire --}}
        <form wire:submit.prevent="$call('save')" class="mb-6 flex items-center gap-3"
              x-data>
            <input
                :key="$wire.fileId"  {{-- force a new DOM node after each save --}}
            key="{{ $fileId }}"  {{-- SSR key hint --}}
                type="file"
                class="rounded-md border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                wire:model="upload"
                wire:key="uploader-{{ $fileId }}"
                accept="image/*">

            <button type="submit"
                    class="rounded-md bg-zinc-900 px-4 py-2 text-sm text-white disabled:opacity-60"
                    {{-- disable while file is uploading or action is running --}}
                    wire:loading.attr="disabled"
                    wire:target="upload,save">
                <span wire:loading.remove wire:target="upload,save">Upload</span>
                <span wire:loading wire:target="upload,save">Processing…</span>
            </button>

            {{-- show progress while the file is uploading --}}
            <div class="text-sm text-zinc-600" wire:loading wire:target="upload">
                Uploading file…
            </div>

            @error('upload')
            <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
        </form>
    @endcan

    {{-- Simple grid --}}
    <div class="columns-1 md:columns-2 lg:columns-3 gap-4">
        @forelse ($items as $h)
            <div class="mb-4 break-inside-avoid">
                <div class="border border-zinc-800">
                    <div class="relative overflow-hidden">
                        <div class="absolute inset-0 z-0 bg-cover bg-center bg-tile"
                             style="background-image:url('/images/chrome.jpg')"></div>
                        <img class="relative z-10 block w-full h-auto p-1 border border-zinc-200 dark:border-zinc-700"
                             src="{{ asset($h->path) }}" alt="Headshot" loading="lazy" decoding="async" />
                    </div>

                    @can('create', \App\Models\Media::class)
                        <div class="flex justify-end p-2">
                            <button type="button"
                                    class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-xs text-red-700"
                                    wire:click="$call('delete', {{ $h->id }})"
                                    wire:loading.attr="disabled" wire:target="delete">
                                Delete
                            </button>
                        </div>
                    @endcan
                </div>
            </div>
        @empty
            <div class="rounded-lg border p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                No headshots yet.
            </div>
        @endforelse
    </div>
</div>
