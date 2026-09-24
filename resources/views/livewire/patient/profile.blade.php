<div class="max-w-2xl">
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">My Profile</h1>

    <form wire:submit="save" class="card space-y-4">
        <x-field label="Full name" error="name"><input wire:model="name" class="input"></x-field>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field label="Student ID" error="student_id"><input wire:model="student_id" class="input"></x-field>
            <x-field label="Date of birth" error="date_of_birth"><input type="date" wire:model="date_of_birth" class="input"></x-field>
            <x-field label="Sex" error="sex">
                <select wire:model="sex" class="input"><option value="">Select…</option><option value="male">Male</option><option value="female">Female</option></select>
            </x-field>
            <x-field label="Contact number" error="contact_number"><input wire:model="contact_number" class="input"></x-field>
        </div>
        <x-field label="Address" error="address"><input wire:model="address" class="input"></x-field>
        <x-field label="Medical history (allergies, chronic conditions, current medication)" error="medical_history">
            <textarea wire:model="medical_history" rows="4" class="input"></textarea>
        </x-field>

        <div class="rounded-lg bg-slate-50 p-4 text-sm">
            <label class="flex items-start gap-2">
                <input type="checkbox" wire:model="consent" class="mt-1">
                <span>I consent to the University Clinic collecting and processing my personal and health information
                    for care and clinic operations, in accordance with the Data Privacy Act of 2012 (RA 10173). Only
                    authorized clinic staff can view it, and every access is logged.</span>
            </label>
            @error('consent') <p class="mt-1 text-xs text-red-600">You need to give consent to use the clinic system.</p> @enderror
        </div>

        <div class="text-right"><button class="btn btn-primary">Save profile</button></div>
    </form>
</div>
