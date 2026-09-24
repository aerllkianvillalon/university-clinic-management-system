<div class="max-w-4xl">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <h1 class="text-2xl font-semibold">Reports</h1>
        <div class="flex flex-wrap items-end gap-3 print:hidden">
            <x-field label="From" error="from"><input type="date" wire:model.live="from" class="input"></x-field>
            <x-field label="To" error="to"><input type="date" wire:model.live="to" class="input"></x-field>
            <button onclick="window.print()" class="btn btn-ghost">Print</button>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat label="Appointments" :value="$byStatus->sum()" />
        <x-stat label="Visits handled" :value="$visits" />
        <x-stat label="Visits with abnormal vitals" :value="$abnormal" />
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="card">
            <h2 class="mb-2 font-semibold">By status</h2>
            <ul class="space-y-1 text-sm">
                @forelse ($byStatus as $status => $total)
                    <li class="flex justify-between"><span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span><span>{{ $total }}</span></li>
                @empty <li class="text-slate-500">No appointments in this period.</li> @endforelse
            </ul>
        </div>
        <div class="card">
            <h2 class="mb-2 font-semibold">By doctor</h2>
            <ul class="space-y-1 text-sm">
                @forelse ($byDoctor as $row)
                    <li class="flex justify-between"><span>Dr. {{ $row->doctor->name }}</span><span>{{ $row->total }}</span></li>
                @empty <li class="text-slate-500">No data.</li> @endforelse
            </ul>
        </div>
        <div class="card">
            <h2 class="mb-2 font-semibold">Top diagnoses</h2>
            <ul class="space-y-1 text-sm">
                @forelse ($topDiagnoses as $row)
                    <li class="flex justify-between"><span class="capitalize">{{ $row->diagnosis }}</span><span>{{ $row->total }}</span></li>
                @empty <li class="text-slate-500">No records in this period.</li> @endforelse
            </ul>
        </div>
    </div>
</div>
