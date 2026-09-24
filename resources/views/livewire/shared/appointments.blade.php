<div>
    <x-flash />
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Appointments</h1>
        @if (auth()->user()->role === 'patient')
            <a href="{{ route('patient.book') }}" class="btn btn-primary">Book appointment</a>
        @endif
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <select wire:model.live="status" class="input w-44">
            <option value="">All statuses</option>
            @foreach (['scheduled', 'checked_in', 'completed', 'cancelled'] as $s)
                <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <input type="date" wire:model.live="date" class="input w-44">
        @if ($date || $status)
            <button wire:click="clearFilters" class="btn btn-ghost">Clear filters</button>
        @endif
        <button wire:click="$set('date', '{{ today()->toDateString() }}')" class="btn btn-ghost">Today</button>
    </div>

    <div class="card overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Reason</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($appointments as $a)
                <tr wire:key="appt-{{ $a->id }}">
                    <td>{{ $a->appointment_date->format('M j, Y') }}</td>
                    <td>{{ $a->time_label }}</td>
                    <td>{{ $a->patient->user->name }}</td>
                    <td>Dr. {{ $a->doctor->name }}</td>
                    <td>{{ $a->reason }}</td>
                    <td><x-badge :status="$a->status" /></td>
                    <td class="text-right">
                        @can('cancel', $a)
                            <button wire:click="cancel({{ $a->id }})" wire:confirm="Cancel this appointment?" class="btn btn-ghost">Cancel</button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-slate-500">No appointments found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $appointments->links() }}</div>
</div>
