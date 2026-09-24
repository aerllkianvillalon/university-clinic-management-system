<?php

namespace Database\Seeders;

use App\Models\DoctorAvailability;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $make = fn (string $name, string $email, string $role) => User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password', 'role' => $role, 'email_verified_at' => now()]
        );

        $make('Helen Head Nurse', 'headnurse@clinic.test', User::HEAD_NURSE);
        $make('Nina Nurse', 'nurse@clinic.test', User::NURSE);
        $make('Nico Nurse', 'nurse2@clinic.test', User::NURSE);
        $doctor = $make('Dana Doctor', 'doctor@clinic.test', User::DOCTOR);
        $patient = $make('Paolo Patient', 'patient@clinic.test', User::PATIENT);

        $patient->patient->update([
            'student_id' => '2024-00001', 'sex' => 'male', 'date_of_birth' => '2004-05-14',
            'contact_number' => '09171234567', 'address' => 'Lapu-Lapu City',
            'consent_at' => now(),
        ]);

        // Monday–Friday, 9–12 and 1–4, 30-minute slots
        foreach (range(1, 5) as $day) {
            foreach ([['09:00', '12:00'], ['13:00', '16:00']] as [$start, $end]) {
                DoctorAvailability::firstOrCreate([
                    'doctor_id' => $doctor->id, 'day_of_week' => $day, 'start_time' => $start,
                ], ['end_time' => $end, 'slot_duration_minutes' => 30]);
            }
        }
    }
}
