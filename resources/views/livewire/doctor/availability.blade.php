<div class="max-w-3xl">
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">My Availability</h1>

    <form wire:submit="add" class="card mb-6 grid gap-4 sm:grid-cols-5">
        <x-field label="Day" error="day_of_week">
            <select wire:model="day_of_week" class="input">
                @foreach (\App\Livewire\Doctor\Availability::DAYS as $i => $d) <option value="{{ $i }}">{{ $d }}</option> @endforeach
            </select>
        </x-field>
        <x-field label="From" error="start_time"><input type="time" wire:model="start_time" class="input"></x-field>
        <x-field label="To" error="end_time"><input type="time" wire:model="end_time" class="input"></x-field>
        <x-field label="Slot length" error="slot_duration_minutes">
            <select wire:model="slot_duration_minutes" class="input">
                @foreach ([10, 15, 20, 30, 45, 60] as $m) <option value="{{ $m }}">{{ $m }} min</option> @endforeach
            </select>
        </x-field>
        <div class="flex items-end"><button class="btn btn-primary w-full">Add block</button></div>
    </form>

    <div class="card">
        <table class="tbl">
            <thead><tr><th>Day</th><th>Hours</th><th>Slot length</th><th></th></tr></thead>
            <tbody>
            @forelse ($blocks as $b)
                <tr wire:key="blk-{{ $b->id }}">
                    <td>{{ \App\Livewire\Doctor\Availability::DAYS[$b->day_of_week] }}</td>
                    <td>{{ \Carbon\Carbon::parse($b->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($b->end_time)->format('g:i A') }}</td>
                    <td>{{ $b->slot_duration_minutes }} min</td>
                    <td class="text-right"><button wire:click="remove({{ $b->id }})" wire:confirm="Remove this block?" class="btn btn-ghost">Remove</button></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-slate-500">You haven't set any hours yet. Patients can only book times you add here.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
