<div class="max-w-3xl">
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">My Documents</h1>

    <form wire:submit="upload" class="card mb-6 space-y-3">
        <x-field label="Upload a medical document (PDF, JPG or PNG, up to 5 MB)" error="file">
            <input type="file" wire:model="file" class="input">
        </x-field>
        <div wire:loading wire:target="file" class="text-sm text-slate-500">Uploading…</div>
        <div class="text-right"><button class="btn btn-primary" wire:loading.attr="disabled" wire:target="file">Upload document</button></div>
    </form>

    <div class="card">
        <table class="tbl">
            <thead><tr><th>File</th><th>Uploaded</th><th></th></tr></thead>
            <tbody>
            @forelse ($documents as $d)
                <tr wire:key="doc-{{ $d->id }}">
                    <td>{{ $d->original_name }}</td>
                    <td>{{ $d->created_at->format('M j, Y') }}</td>
                    <td class="space-x-1 text-right">
                        {{-- Signed link expires in 5 minutes and is still checked against DocumentPolicy --}}
                        <a href="{{ URL::temporarySignedRoute('documents.download', now()->addMinutes(5), ['document' => $d->id]) }}" class="btn btn-ghost">Download</a>
                        @can('delete', $d)
                            <button wire:click="remove({{ $d->id }})" wire:confirm="Remove this document?" class="btn btn-ghost">Remove</button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-slate-500">No documents uploaded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
