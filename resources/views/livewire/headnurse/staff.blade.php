<div class="max-w-4xl">
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">{{ ucfirst($label) }}s</h1>

    <form wire:submit="create" class="card mb-6 grid gap-4 sm:grid-cols-4">
        <x-field label="Full name" error="name"><input wire:model="name" class="input"></x-field>
        <x-field label="Email" error="email"><input type="email" wire:model="email" class="input"></x-field>
        <x-field label="Temporary password" error="password"><input type="password" wire:model="password" class="input"></x-field>
        <div class="flex items-end"><button class="btn btn-primary w-full">Add {{ $label }}</button></div>
    </form>

    <div class="card">
        <table class="tbl">
            <thead><tr><th>Name</th><th>Email</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($staff as $s)
                <tr wire:key="staff-{{ $s->id }}">
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->email }}</td>
                    <td>{{ $s->is_active ? 'Active' : 'Deactivated' }}</td>
                    <td class="text-right">
                        <button wire:click="toggle({{ $s->id }})" class="btn btn-ghost">{{ $s->is_active ? 'Deactivate' : 'Reactivate' }}</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-slate-500">No {{ $label }}s yet. Add the first one above.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
