<?php

use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;

function userWithRole(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

function recordFor(User $patientUser, User $doctor, User $nurse): MedicalRecord
{
    $appointment = Appointment::create([
        'patient_id' => $patientUser->patient->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => today(),
        'appointment_time' => '09:00:00',
        'reason' => 'Check-up',
        'status' => 'completed',
    ]);
    $visit = Visit::create(['appointment_id' => $appointment->id, 'nurse_id' => $nurse->id, 'visit_date' => today()]);

    return MedicalRecord::create([
        'patient_id' => $patientUser->patient->id,
        'visit_id' => $visit->id,
        'doctor_id' => $doctor->id,
        'diagnosis' => 'Common cold',
        'treatment' => 'Rest and fluids',
    ]);
}

it('stops a patient from viewing another patient\'s record', function () {
    $owner = userWithRole('patient');
    $other = userWithRole('patient');
    $record = recordFor($owner, userWithRole('doctor'), userWithRole('nurse'));

    expect($owner->can('view', $record))->toBeTrue()
        ->and($other->can('view', $record))->toBeFalse();
});

it('only lets a nurse see records from visits she handled', function () {
    $handled = userWithRole('nurse');
    $stranger = userWithRole('nurse');
    $record = recordFor(userWithRole('patient'), userWithRole('doctor'), $handled);

    expect($handled->can('view', $record))->toBeTrue()
        ->and($stranger->can('view', $record))->toBeFalse();
});

it('only lets the head nurse void or restore any record', function () {
    $doctor = userWithRole('doctor');
    $record = recordFor(userWithRole('patient'), $doctor, userWithRole('nurse'));

    expect(userWithRole('head_nurse')->can('delete', $record))->toBeTrue()
        ->and($doctor->can('delete', $record))->toBeTrue()          // author
        ->and(userWithRole('doctor')->can('delete', $record))->toBeFalse() // different doctor
        ->and($doctor->can('restore', $record))->toBeFalse();
});

it('soft-deletes instead of destroying a medical record', function () {
    $record = recordFor(userWithRole('patient'), userWithRole('doctor'), userWithRole('nurse'));
    $record->delete();

    expect(MedicalRecord::find($record->id))->toBeNull()
        ->and(MedicalRecord::withTrashed()->find($record->id))->not->toBeNull();
});

it('blocks patients from head-nurse and staff pages', function () {
    $patient = userWithRole('patient');

    $this->actingAs($patient)->get(route('headnurse.audit'))->assertForbidden();
    $this->actingAs($patient)->get(route('headnurse.reports'))->assertForbidden();
    $this->actingAs($patient)->get(route('patients'))->assertForbidden();
    $this->actingAs($patient)->get(route('doctor.availability'))->assertForbidden();
});

it('does not offer a slot that is already booked', function () {
    Carbon::setTestNow(Carbon::parse('next monday 06:00'));

    $doctor = userWithRole('doctor');
    $patient = userWithRole('patient');
    DoctorAvailability::create([
        'doctor_id' => $doctor->id, 'day_of_week' => 1,
        'start_time' => '09:00', 'end_time' => '10:00', 'slot_duration_minutes' => 30,
    ]);

    expect(Appointment::availableSlots($doctor->id, today()))->toBe(['09:00', '09:30']);

    Appointment::create([
        'patient_id' => $patient->patient->id, 'doctor_id' => $doctor->id,
        'appointment_date' => today(), 'appointment_time' => '09:00:00',
        'reason' => 'Fever', 'status' => 'scheduled',
    ]);

    expect(Appointment::availableSlots($doctor->id, today()))->toBe(['09:30']);

    Carbon::setTestNow();
});
