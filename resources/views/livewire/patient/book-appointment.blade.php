<div class="max-w-2xl">
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">Book an Appointment</h1>

    <form wire:submit="book" class="card space-y-4">
        <x-field label="Doctor" error="doctorId">
            <select wire:model.live="doctorId" class="input">
                <option value="">Select a doctor…</option>
                @foreach ($doctors as $d) <option value="{{ $d->id }}">Dr. {{ $d->name }}</option> @endforeach
            </select>
        </x-field>

        <x-field label="Date" error="date">
            <input type="date" wire:model.live="date" min="{{ today()->toDateString() }}" class="input">
        </x-field>

        @if ($doctorId && $date)
            <x-field label="Available times" error="time">
                <div class="flex flex-wrap gap-2">
                    @forelse ($this->slots as $slot)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="time" value="{{ $slot }}" class="peer sr-only">
                            <span class="block rounded-lg border border-slate-300 px-3 py-1.5 text-sm peer-checked:border-teal-700 peer-checked:bg-teal-700 peer-checked:text-white peer-focus-visible:ring-2">
                                {{ \Carbon\Carbon::createFromFormat('H:i', $slot)->format('g:i A') }}
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">This doctor has no open times on that date. Try another day.</p>
                    @endforelse
                </div>
            </x-field>
        @endif

        <x-field label="Reason for visit" error="reason"><input wire:model="reason" class="input" placeholder="e.g. Fever and cough for 2 days"></x-field>

        <div class="text-right"><button class="btn btn-primary">Book appointment</button></div>
    </form>
</div>
