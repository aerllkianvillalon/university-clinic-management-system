<div>
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">Visits & Vital Signs</h1>
    @error('checkin') <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div> @enderror

    <div class="card mb-6">
        <h2 class="mb-3 font-semibold">Waiting for check-in (today)</h2>
        <table class="tbl">
            <thead><tr><th>Time</th><th>Patient</th><th>Doctor</th><th>Reason</th><th></th></tr></thead>
            <tbody>
            @forelse ($waiting as $a)
                <tr wire:key="wait-{{ $a->id }}">
                    <td>{{ $a->time_label }}</td>
                    <td>{{ $a->patient->user->name }}</td>
                    <td>Dr. {{ $a->doctor->name }}</td>
                    <td>{{ $a->reason }}</td>
                    <td class="text-right"><button wire:click="checkIn({{ $a->id }})" class="btn btn-primary">Check in & record vitals</button></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-slate-500">No one is waiting to be checked in.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2 class="mb-3 font-semibold">Today's visits</h2>
        <table class="tbl">
            <thead><tr><th>Patient</th><th>Doctor</th><th>Vital signs</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($visits as $v)
                <tr wire:key="visit-{{ $v->id }}">
                    <td>{{ $v->appointment->patient->user->name }}</td>
                    <td>Dr. {{ $v->appointment->doctor->name }}</td>
                    <td>
                        @if ($vs = $v->vitalSigns)
                            {{ $vs->temperature }} °C · {{ $vs->bp_systolic }}/{{ $vs->bp_diastolic }} · HR {{ $vs->heart_rate }} · RR {{ $vs->respiratory_rate }}
                            @foreach ($vs->abnormalFlags() as $flag)
                                <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-xs text-red-700">{{ $flag }}</span>
                            @endforeach
                        @else <span class="text-slate-400">Not recorded</span> @endif
                    </td>
                    <td><x-badge :status="$v->appointment->status" /></td>
                    <td class="text-right">
                        @can('update', $v)
                            @if ($v->appointment->status === 'checked_in')
                                <button wire:click="openVitals({{ $v->id }})" class="btn btn-ghost">{{ $v->vitalSigns ? 'Edit vitals' : 'Record vitals' }}</button>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-slate-500">No visits yet today.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($visitId)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4">
            <form wire:submit="saveVitals" class="card max-h-[90vh] w-full max-w-xl space-y-4 overflow-y-auto">
                <h2 class="text-lg font-semibold">Vital signs</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Temperature (°C)" error="temperature"><input wire:model="temperature" inputmode="decimal" class="input"></x-field>
                    <x-field label="Heart rate (bpm)" error="heart_rate"><input wire:model="heart_rate" inputmode="numeric" class="input"></x-field>
                    <x-field label="BP systolic (mmHg)" error="bp_systolic"><input wire:model="bp_systolic" inputmode="numeric" class="input"></x-field>
                    <x-field label="BP diastolic (mmHg)" error="bp_diastolic"><input wire:model="bp_diastolic" inputmode="numeric" class="input"></x-field>
                    <x-field label="Respiratory rate (per min)" error="respiratory_rate"><input wire:model="respiratory_rate" inputmode="numeric" class="input"></x-field>
                    <x-field label="Weight (kg)" error="weight"><input wire:model="weight" inputmode="decimal" class="input"></x-field>
                    <x-field label="Height (cm)" error="height"><input wire:model="height" inputmode="decimal" class="input"></x-field>
                </div>
                <x-field label="Visit notes" error="notes"><textarea wire:model="notes" rows="3" class="input"></textarea></x-field>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="cancelForm" class="btn btn-ghost">Cancel</button>
                    <button class="btn btn-primary">Save vital signs</button>
                </div>
            </form>
        </div>
    @endif
</div>
