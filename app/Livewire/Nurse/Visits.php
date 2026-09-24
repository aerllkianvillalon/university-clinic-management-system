<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Visit;
use App\Models\VitalSign;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.clinic')]
#[Title('Visits & Vital Signs')]
class Visits extends Component
{
    public $visitId = null;
    public string $temperature = '';
    public string $bp_systolic = '';
    public string $bp_diastolic = '';
    public string $heart_rate = '';
    public string $respiratory_rate = '';
    public string $height = '';
    public string $weight = '';
    public string $notes = '';

    public function checkIn(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $this->authorize('checkIn', $appointment);

        if (! $appointment->appointment_date->isToday()) {
            $this->addError('checkin', 'Only today\'s appointments can be checked in.');

            return;
        }

        $visit = DB::transaction(function () use ($appointment) {
            $appointment->update(['status' => Appointment::CHECKED_IN]);

            return Visit::firstOrCreate(
                ['appointment_id' => $appointment->id],
                ['nurse_id' => auth()->id(), 'visit_date' => today()]
            );
        });

        $this->openVitals($visit->id);
    }

    public function openVitals(int $visitId): void
    {
        $visit = Visit::with('vitalSigns')->findOrFail($visitId);
        $this->authorize('update', $visit);

        $this->resetValidation();
        $v = $visit->vitalSigns;
        $this->visitId = $visit->id;
        $this->fill([
            'temperature' => (string) ($v->temperature ?? ''),
            'bp_systolic' => (string) ($v->bp_systolic ?? ''),
            'bp_diastolic' => (string) ($v->bp_diastolic ?? ''),
            'heart_rate' => (string) ($v->heart_rate ?? ''),
            'respiratory_rate' => (string) ($v->respiratory_rate ?? ''),
            'height' => (string) ($v->height ?? ''),
            'weight' => (string) ($v->weight ?? ''),
            'notes' => (string) $visit->notes,
        ]);
    }

    public function saveVitals(): void
    {
        $visit = Visit::findOrFail($this->visitId);
        $this->authorize('update', $visit);

        $data = $this->validate([
            'temperature' => 'required|numeric|between:30,45',
            'bp_systolic' => 'required|integer|between:50,260',
            'bp_diastolic' => 'required|integer|between:30,160|lt:bp_systolic',
            'heart_rate' => 'required|integer|between:20,250',
            'respiratory_rate' => 'required|integer|between:4,80',
            'height' => 'nullable|numeric|between:30,250',
            'weight' => 'nullable|numeric|between:2,400',
            'notes' => 'nullable|string|max:1000',
        ]);

        VitalSign::updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'temperature' => $data['temperature'],
                'bp_systolic' => $data['bp_systolic'],
                'bp_diastolic' => $data['bp_diastolic'],
                'heart_rate' => $data['heart_rate'],
                'respiratory_rate' => $data['respiratory_rate'],
                'height' => $data['height'] ?: null,
                'weight' => $data['weight'] ?: null,
            ]
        );
        $visit->update(['notes' => $data['notes']]);

        $this->reset('visitId');
        session()->flash('status', 'Vital signs saved. The patient is ready for the doctor.');
    }

    public function cancelForm(): void
    {
        $this->reset('visitId');
        $this->resetValidation();
    }

    public function render()
    {
        $u = auth()->user();

        $waiting = Appointment::with(['patient.user', 'doctor'])
            ->whereDate('appointment_date', today())->where('status', Appointment::SCHEDULED)
            ->orderBy('appointment_time')->get();

        $visits = Visit::with(['appointment.patient.user', 'appointment.doctor', 'vitalSigns'])
            ->whereDate('visit_date', today())
            ->when($u->hasRole(User::NURSE), fn ($q) => $q->where('nurse_id', $u->id))
            ->latest()->get();

        return view('livewire.nurse.visits', compact('waiting', 'visits'));
    }
}
