<?php

use App\Models\Bio;

use function Livewire\Volt\{ state, rules };

state([
    'content' => fn() => optional(Bio::first())->content ?? '',
]);

rules([
    'content' => 'nullable|string|max:20000',
]);

$save = function () {
    $this->authorize('create', \App\Models\Media::class); // reusing MediaPolicy
    $this->validate();

    $bio = \App\Models\Bio::first() ?? new \App\Models\Bio();
    $bio->content   = $this->content;
    $bio->save();

    session()->flash('status', 'Saved!');   // 👈 feedback
    return back();                          // 👈 full refresh so message appears
};

?>


<div class="mx-auto max-w-3xl p-4">
    @include('.components._controls')
    <h1 class="mb-4 text-xl font-semibold">Edit Bio</h1>

    @can('create', \App\Models\Media::class)
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
            <label class="block text-sm mb-2">Bio Content</label>
        @if (session('status'))
            <div class="mb-3 rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <textarea
                class="w-full min-h-[240px] rounded-md border px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                wire:model.defer="content"
            ></textarea>

            <div class="mt-3 flex justify-end">
                <button
                    type="button"
                    class="rounded-md bg-zinc-900 px-4 py-2 text-sm text-white disabled:opacity-60"
                    wire:click="$call('save')"
                    wire:loading.attr="disabled"
                    wire:target="$call('save')"
                >
                    <span wire:loading.remove wire:target="$call('save')">Save</span>
                    <span wire:loading wire:target="$call('save')">Saving…</span>
                </button>
            </div>
        </div>
    @else
        <div class="rounded-lg border p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            You don’t have permission to edit the bio.
        </div>
    @endcan
</div>
