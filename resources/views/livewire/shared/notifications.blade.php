<div class="max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Notifications</h1>
        <button wire:click="markAllRead" class="btn btn-ghost">Mark all as read</button>
    </div>

    <div class="space-y-3">
        @forelse ($items as $n)
            <div class="card flex items-start justify-between gap-4 {{ $n->read_at ? 'opacity-60' : 'border-teal-300' }}">
                <div>
                    <p class="font-medium">{{ $n->title }}
                        <span class="ml-2 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">{{ str_replace('_', ' ', $n->type) }}</span>
                    </p>
                    <p class="text-sm text-slate-600">{{ $n->message }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
                </div>
                @unless ($n->read_at)
                    <button wire:click="markRead({{ $n->id }})" class="btn btn-ghost shrink-0">Mark read</button>
                @endunless
            </div>
        @empty
            <p class="text-slate-500">You're all caught up.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
