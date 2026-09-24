<div>
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">Medical Records</h1>

    <div class="mb-4 flex flex-wrap items-center gap-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search patient or diagnosis" class="input max-w-sm">
        @if (auth()->user()->role === 'head_nurse')
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="showVoided"> Include voided records</label>
        @endif
    </div>

    <div class="card overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($records as $r)
                <tr wire:key="rec-{{ $r->id }}">
                    <td>{{ $r->created_at->format('M j, Y') }}</td>
                    <td>{{ $r->patient->user->name }}</td>
                    <td>Dr. {{ $r->doctor->name }}</td>
                    <td>{{ $r->diagnosis }}</td>
                    <td>@if ($r->trashed()) <x-badge status="voided" /> @else <x-badge status="completed" /> @endif</td>
                    <td class="space-x-1 text-right">
                        @can('view', $r) <button wire:click="view({{ $r->id }})" class="btn btn-ghost">View</button> @endcan
                        @can('update', $r) <button wire:click="edit({{ $r->id }})" class="btn btn-ghost">Edit</button> @endcan
                        @if (! $r->trashed())
                            @can('delete', $r) <button wire:click="void({{ $r->id }})" wire:confirm="Void this record? It stays in the audit history." class="btn btn-danger">Void</button> @endcan
                        @else
                            @can('restore', $r) <button wire:click="restore({{ $r->id }})" class="btn btn-ghost">Restore</button> @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-slate-500">No medical records to show.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $records->links() }}</div>
    <p class="mt-4 text-xs text-slate-500">Medical records are never permanently deleted. Incorrect records are voided to preserve the clinic's audit history.</p>

    @if ($viewing)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4">
            <div class="card max-h-[90vh] w-full max-w-xl overflow-y-auto">
                <h2 class="text-lg font-semibold">{{ $viewing->patient->user->name }}
                    @if ($viewing->trashed()) <x-badge status="voided" /> @endif
                </h2>
                <p class="text-sm text-slate-500">{{ $viewing->created_at->format('M j, Y g:i A') }} · Dr. {{ $viewing->doctor->name }}</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="font-medium">Diagnosis</dt><dd>{{ $viewing->diagnosis }}</dd></div>
                    <div><dt class="font-medium">Treatment</dt><dd class="whitespace-pre-line">{{ $viewing->treatment }}</dd></div>
                    @if ($viewing->notes)<div><dt class="font-medium">Notes</dt><dd class="whitespace-pre-line">{{ $viewing->notes }}</dd></div>@endif
                    @if ($v = $viewing->visit?->vitalSigns)
                        <div>
                            <dt class="font-medium">Vital signs</dt>
                            <dd>Temp {{ $v->temperature }} °C · BP {{ $v->bp_systolic }}/{{ $v->bp_diastolic }} · HR {{ $v->heart_rate }} · RR {{ $v->respiratory_rate }}
                                @if ($v->weight) · {{ $v->weight }} kg @endif @if ($v->height) · {{ $v->height }} cm @endif</dd>
                        </div>
                    @endif
                </dl>
                <div class="mt-5 text-right"><button wire:click="close" class="btn btn-ghost">Close</button></div>
            </div>
        </div>
    @endif

    @if ($editingId)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4">
            <form wire:submit="save" class="card w-full max-w-xl space-y-4">
                <h2 class="text-lg font-semibold">Edit medical record</h2>
                <x-field label="Diagnosis" error="diagnosis"><input wire:model="diagnosis" class="input"></x-field>
                <x-field label="Treatment" error="treatment"><textarea wire:model="treatment" rows="4" class="input"></textarea></x-field>
                <x-field label="Notes" error="notes"><textarea wire:model="notes" rows="3" class="input"></textarea></x-field>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="close" class="btn btn-ghost">Cancel</button>
                    <button class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    @endif
</div>
