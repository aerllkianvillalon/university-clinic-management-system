<?php

namespace App\Livewire\Patient;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.clinic')]
#[Title('My Profile')]
class Profile extends Component
{
    public string $name = '';
    public string $student_id = '';
    public string $date_of_birth = '';
    public string $sex = '';
    public string $contact_number = '';
    public string $address = '';
    public string $medical_history = '';
    public bool $consent = false;

    public function mount(): void
    {
        $user = auth()->user();
        $p = $user->patient;

        $this->name = $user->name;
        $this->student_id = (string) $p->student_id;
        $this->date_of_birth = $p->date_of_birth?->toDateString() ?? '';
        $this->sex = (string) $p->sex;
        $this->contact_number = (string) $p->contact_number;
        $this->address = (string) $p->address;
        $this->medical_history = (string) $p->medical_history;
        $this->consent = (bool) $p->consent_at;
    }

    public function save(): void
    {
        $user = auth()->user();
        $p = $user->patient;

        $data = $this->validate([
            'name' => 'required|string|max:255',
            'student_id' => ['nullable', 'string', 'max:30', Rule::unique('patients', 'student_id')->ignore($p->id)],
            'date_of_birth' => 'nullable|date|before:today',
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'contact_number' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string|max:2000',
            'consent' => 'accepted',
        ]);

        $user->update(['name' => $data['name']]);
        $p->update([
            'student_id' => $data['student_id'] ?: null,
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'sex' => $data['sex'] ?: null,
            'contact_number' => $data['contact_number'],
            'address' => $data['address'],
            'medical_history' => $data['medical_history'],
            'consent_at' => $p->consent_at ?? now(),
        ]);

        session()->flash('status', 'Profile saved.');
    }

    public function render()
    {
        return view('livewire.patient.profile');
    }
}
