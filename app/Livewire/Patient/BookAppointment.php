<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\ClinicNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.clinic')]
#[Title('Book Appointment')]
class BookAppointment extends Component
{
    public $doctorId = '';
    public string $date = '';
    public string $time = '';
    public string $reason = '';

    public function mount()
    {
        // Data-privacy consent must exist before we collect any health information.
        if (! auth()->user()->patient->consent_at) {
            session()->flash('status', 'Please complete your profile and give consent before booking.');

            return $this->redirectRoute('patient.profile');
        }
    }

    public function updatedDoctorId(): void
    {
        $this->time = '';
    }

    public function updatedDate(): void
    {
        $this->time = '';
    }

    #[Computed]
    public function slots(): array
    {
        if (! $this->doctorId || ! $this->date || $this->date < today()->toDateString()) {
            return [];
        }

        return Appointment::availableSlots((int) $this->doctorId, $this->date);
    }

    public function book()
    {
        $data = $this->validate([
            'doctorId' => ['required', Rule::exists('users', 'id')->where('role', User::DOCTOR)->where('is_active', true)],
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:255',
        ]);

        $patient = auth()->user()->patient;
        $appointment = null;

        DB::transaction(function () use ($data, $patient, &$appointment) {
            // Re-check inside a transaction with a row lock so two patients can't grab the same slot.
            $taken = Appointment::where('doctor_id', $data['doctorId'])
                ->whereDate('appointment_date', $data['date'])
                ->where('appointment_time', $data['time'].':00')
                ->where('status', '!=', Appointment::CANCELLED)
                ->lockForUpdate()->exists();

            if ($taken || ! in_array($data['time'], Appointment::availableSlots((int) $data['doctorId'], $data['date']), true)) {
                return;
            }

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $data['doctorId'],
                'appointment_date' => $data['date'],
                'appointment_time' => $data['time'].':00',
                'reason' => $data['reason'],
                'status' => Appointment::SCHEDULED,
            ]);
        });

        if (! $appointment) {
            $this->time = '';
            $this->addError('time', 'That slot was just taken. Please choose another time.');

            return;
        }

        ClinicNotification::send((int) $data['doctorId'], 'system', 'New appointment',
            auth()->user()->name.' booked '.$appointment->appointment_date->format('M j').' at '.$appointment->time_label.'.');

        session()->flash('status', 'Appointment booked for '.$appointment->appointment_date->format('M j, Y').' at '.$appointment->time_label.'.');

        return $this->redirectRoute('appointments');
    }

    public function render()
    {
        return view('livewire.patient.book-appointment', [
            'doctors' => User::where('role', User::DOCTOR)->where('is_active', true)
                ->whereHas('availability')->orderBy('name')->get(),
        ]);
    }
}
