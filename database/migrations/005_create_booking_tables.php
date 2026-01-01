<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $schema = Capsule::schema();

        // Courses
        if (!$schema->hasTable('courses')) {
            $schema->create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('course_code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->integer('duration_days')->default(1);
                $table->integer('max_students')->default(6);
                $table->integer('min_students')->default(1);
                $table->text('prerequisites')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Course Schedules (Optional but good practice to separate definition from instance - implies 'dates')
        // For now, simple enrollment usually links to a schedule.
        // Assuming 'course_schedules' or similar if necessary, but TransactionService linked to 'schedule_id'.
        // Let's assume there is a course_schedules table.
        if (!$schema->hasTable('course_schedules')) {
            $schema->create('course_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->string('location')->nullable();
                $table->integer('max_students')->nullable(); // Override
                $table->integer('current_enrollment')->default(0);
                $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
                $table->timestamps();
            });
        }

        // Course Enrollments
        if (!$schema->hasTable('course_enrollments')) {
            $schema->create('course_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_schedule_id')->nullable()->constrained('course_schedules')->nullOnDelete(); // Or course_id if rolling
                $table->foreignId('student_id')->constrained('customers')->cascadeOnDelete();
                $table->string('status')->default('enrolled');
                $table->date('enrollment_date');
                $table->date('completion_date')->nullable();
                $table->boolean('certification_issued')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Trips
        if (!$schema->hasTable('trips')) {
            $schema->create('trips', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('destination')->nullable();
                $table->decimal('price', 10, 2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Trip Schedules
        if (!$schema->hasTable('trip_schedules')) {
            $schema->create('trip_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
                $table->date('departure_date');
                $table->date('return_date')->nullable();
                $table->integer('max_participants');
                $table->integer('current_bookings')->default(0);
                $table->string('status')->default('scheduled');
                $table->timestamps();
            });
        }

        // Trip Bookings
        if (!$schema->hasTable('trip_bookings')) {
            $schema->create('trip_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('trip_schedule_id')->constrained('trip_schedules')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('status')->default('booked');
                $table->date('booking_date');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Rental Equipment
        if (!$schema->hasTable('rental_equipment')) {
            $schema->create('rental_equipment', function (Blueprint $table) {
                $table->id();
                $table->string('equipment_code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('daily_rate', 10, 2);
                $table->string('status')->default('available'); // available, rented, maintenance, lost
                $table->string('size')->nullable();
                $table->string('serial_number')->nullable();
                $table->timestamps();
            });
        }
    }
};
