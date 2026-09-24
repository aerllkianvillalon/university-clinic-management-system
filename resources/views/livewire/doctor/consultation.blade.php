<div>
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">Consultations</h1>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 font-semibold">Ready to see</h2>
            <div class="space-y-2">
                @forelse ($queue as $a)
                    <button wire:click="select({{ $a->id }})" wire:key="q-{{ $a->id }}"
                        class="w-full rounded-lg border px-3 py-2 text-left text-sm hover:bg-slate-50 {{ $appointmentId == $a->id ? 'border-teal-700 bg-teal-50' : 'border-slate-200' }}">
                        <span class="block font-medium">{{ $a->patient->user->name }}</span>
                        <span class="text-slate-500">{{ $a->appointment_date->format('M j') }} · {{ $a->time_label }} · {{ $a->reason }}</span>
                    </button>
                @empty
                    <p class="text-sm text-slate-500">No patients have been checked in by the nurse yet.</p>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-2">
            @if ($selected)
                @php $vs = $selected->visit?->vitalSigns; @endphp
                <div class="card mb-4">
                    <h2 class="font-semibold">{{ $selected->patient->user->name }}</h2>
                    <p class="text-sm text-slate-500">Reason: {{ $selected->reason }}</p>
                    @if ($vs)
                        <p class="mt-3 text-sm">Temp {{ $vs->temperature }} °C · BP {{ $vs->bp_systolic }}/{{ $vs->bp_diastolic }} · HR {{ $vs->heart_rate }} · RR {{ $vs->respiratory_rate }}
                            @if ($vs->weight) · {{ $vs->weight }} kg @endif @if ($vs->height) · {{ $vs->height }} cm @endif</p>
                        @foreach ($vs->abnormalFlags() as $flag)
                            <span class="mr-1 rounded bg-red-100 px-1.5 py-0.5 text-xs text-red-700">Check {{ strtolower($flag) }}</span>
                        @endforeach
                    @else
                        <p class="mt-3 text-sm text-slate-500">No vital signs recorded for this visit.</p>
                    @endif
                    @if ($selected->patient->medical_history)
                        <p class="mt-3 text-sm"><span class="font-medium">History:</span> {{ $selected->patient->medical_history }}</p>
                    @endif
                    @if ($history->isNotEmpty())
                        <p class="mt-3 text-sm font-medium">Previous diagnoses</p>
                        <ul class="list-disc pl-5 text-sm text-slate-600">
                            @foreach ($history as $h) <li>{{ $h->created_at->format('M j, Y') }} — {{ $h->diagnosis }}</li> @endforeach
                        </ul>
                    @endif
                </div>

                <form wire:submit="save" class="card space-y-4">
                    <x-field label="Diagnosis" error="diagnosis"><input wire:model="diagnosis" class="input"></x-field>
                    <x-field label="Treatment" error="treatment"><textarea wire:model="treatment" rows="4" class="input"></textarea></x-field>
                    <x-field label="Notes" error="notes"><textarea wire:model="notes" rows="3" class="input"></textarea></x-field>
                    <div class="text-right"><button class="btn btn-primary">Save & complete visit</button></div>
                </form>
            @else
                <div class="card text-sm text-slate-500">Choose a patient from the list to write their diagnosis and treatment.</div>
            @endif
        </div>
    </div>
</div>
