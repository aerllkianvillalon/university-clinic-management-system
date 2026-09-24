<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('patient')->index()->after('password');
            $t->boolean('is_active')->default(true)->after('role');
        });

        Schema::create('patients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('student_id', 30)->nullable()->unique();
            $t->date('date_of_birth')->nullable();
            $t->string('sex', 10)->nullable();
            $t->string('contact_number', 30)->nullable();
            $t->string('address')->nullable();
            $t->text('medical_history')->nullable();
            $t->timestamp('consent_at')->nullable(); // RA 10173 consent
            $t->timestamps();
        });

        Schema::create('doctor_availability', function (Blueprint $t) {
            $t->id();
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $t->unsignedTinyInteger('day_of_week'); // 0 = Sunday ... 6 = Saturday
            $t->time('start_time');
            $t->time('end_time');
            $t->unsignedSmallInteger('slot_duration_minutes')->default(30);
            $t->timestamps();
        });

        Schema::create('appointments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $t->foreignId('doctor_id')->constrained('users');
            $t->date('appointment_date');
            $t->time('appointment_time');
            $t->string('reason');
            $t->string('status', 20)->default('scheduled'); // scheduled|checked_in|completed|cancelled
            $t->timestamps();
            $t->index(['doctor_id', 'appointment_date', 'appointment_time']);
        });

        Schema::create('visits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();
            $t->foreignId('nurse_id')->constrained('users');
            $t->date('visit_date');
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('vital_signs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('visit_id')->unique()->constrained()->cascadeOnDelete();
            $t->decimal('temperature', 4, 1);
            $t->unsignedSmallInteger('bp_systolic');
            $t->unsignedSmallInteger('bp_diastolic');
            $t->unsignedSmallInteger('heart_rate');
            $t->unsignedSmallInteger('respiratory_rate');
            $t->decimal('height', 5, 1)->nullable();
            $t->decimal('weight', 5, 1)->nullable();
            $t->timestamps();
        });

        Schema::create('medical_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $t->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $t->foreignId('doctor_id')->constrained('users');
            $t->string('diagnosis');
            $t->text('treatment');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes(); // records are voided, never destroyed
        });

        // Named clinic_notifications to avoid clashing with Laravel's built-in `notifications` table.
        Schema::create('clinic_notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type', 30); // appointment_reminder | record_updated | system
            $t->string('title');
            $t->text('message');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });

        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $t->foreignId('uploaded_by')->constrained('users');
            $t->string('file_path'); // private disk only
            $t->string('original_name');
            $t->string('file_type', 100);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['documents', 'clinic_notifications', 'medical_records', 'vital_signs', 'visits', 'appointments', 'doctor_availability', 'patients'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'is_active']));
    }
};
