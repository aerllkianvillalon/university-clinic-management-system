<?php

namespace App\Livewire\Doctor;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.clinic')]
#[Title('My Availability')]
class Availability extends Component
{
    public const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public $day_of_week = 1;
    public string $start_time = '09:00';
    public string $end_time = '12:00';
    public $slot_duration_minutes = 30;

    public function add(): void
    {
        $data = $this->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration_minutes' => 'required|integer|in:10,15,20,30,45,60',
        ]);

        $overlap = auth()->user()->availability()
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'].':00')
            ->where('end_time', '>', $data['start_time'].':00')
            ->exists();

        if ($overlap) {
            $this->addError('start_time', 'This overlaps another block on the same day.');

            return;
        }

        auth()->user()->availability()->create($data);
        session()->flash('status', 'Availability added.');
    }

    public function remove(int $id): void
    {
        // Scoped to the logged-in doctor, so nobody can delete someone else's block.
        auth()->user()->availability()->whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.doctor.availability', [
            'blocks' => auth()->user()->availability()->orderBy('day_of_week')->orderBy('start_time')->get(),
        ]);
    }
}
