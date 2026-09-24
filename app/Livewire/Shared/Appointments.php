<?php

namespace App\Livewire\Shared;

use App\Models\Appointment;
use App\Models\ClinicNotification;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.clinic')]
#[Title('Appointments')]
class Appointments extends Component
{
    use WithPagination;

    public string $status = '';
    public string $date = '';

    public function mount(): void
    {
        // Nurses mostly care about today's queue.
        if (auth()->user()->hasRole(User::NURSE)) {
            $this->date = today()->toDateString();
        }
    }

    public function updated($name): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['date', 'status']);
    }

    public function cancel(int $id): void
    {
        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id);
        $this->authorize('cancel', $appointment);

        $appointment->update(['status' => Appointment::CANCELLED]);

        $actor = auth()->user();
        $when = $appointment->appointment_date->format('M j').' '.$appointment->time_label;
        if ($actor->hasRole(User::PATIENT)) {
            ClinicNotification::send($appointment->doctor_id, 'system', 'Appointment cancelled',
                "{$actor->name} cancelled the appointment on {$when}.");
        } else {
            ClinicNotification::send($appointment->patient->user_id, 'system', 'Appointment cancelled',
                "Your appointment on {$when} was cancelled by the clinic.");
        }

        session()->flash('status', 'Appointment cancelled.');
    }

    public function render()
    {
        $u = auth()->user();

        $appointments = Appointment::with(['patient.user', 'doctor'])
            ->when($u->hasRole(User::PATIENT), fn ($q) => $q->where('patient_id', $u->patient->id))
            ->when($u->hasRole(User::DOCTOR), fn ($q) => $q->where('doctor_id', $u->id))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->date, fn ($q) => $q->whereDate('appointment_date', $this->date))
            ->orderByDesc('appointment_date')->orderBy('appointment_time')
            ->paginate(10);

        return view('livewire.shared.appointments', compact('appointments'));
    }
}
