<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $schema = Capsule::schema();

        // Customers
        if (!$schema->hasTable('customers')) {
            $schema->create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->nullable(); // Unique? Ideally yes, but maybe legacy allows null/dupes? Default to unique for clean data.
                $table->string('phone')->nullable();
                $table->string('company_name')->nullable();
                $table->text('notes')->nullable();
                $table->date('birth_date')->nullable();
                $table->string('gender')->nullable();
                $table->string('emergency_contact_name')->nullable();
                $table->string('emergency_contact_phone')->nullable();
                $table->string('emergency_contact_relationship')->nullable();
                $table->text('medical_notes')->nullable();
                $table->boolean('marketing_opt_in')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Customer Addresses
        if (!$schema->hasTable('customer_addresses')) {
            $schema->create('customer_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('address_type')->default('home');
                $table->string('address_line1');
                $table->string('address_line2')->nullable();
                $table->string('city');
                $table->string('state');
                $table->string('postal_code');
                $table->string('country')->default('US');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        // Customer Phones
        if (!$schema->hasTable('customer_phones')) {
            $schema->create('customer_phones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('phone_type')->default('mobile');
                $table->string('phone_number');
                $table->string('extension')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('can_sms')->default(false);
                $table->boolean('can_call')->default(true);
                $table->string('label')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Customer Emails
        if (!$schema->hasTable('customer_emails')) {
            $schema->create('customer_emails', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('email_type')->default('personal');
                $table->string('email');
                $table->boolean('is_default')->default(false);
                $table->boolean('can_market')->default(false);
                $table->string('label')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Customer Contacts
        if (!$schema->hasTable('customer_contacts')) {
            $schema->create('customer_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('contact_type')->default('emergency');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('relationship')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->boolean('is_primary_emergency')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Certification Agencies
        if (!$schema->hasTable('certification_agencies')) {
            $schema->create('certification_agencies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('abbreviation')->nullable();
                $table->timestamps();
            });
        }

        // Customer Certifications
        if (!$schema->hasTable('customer_certifications')) {
            $schema->create('customer_certifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('certification_agency_id')->nullable()->constrained('certification_agencies')->nullOnDelete();
                $table->string('certification_level');
                $table->string('certification_number')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('expiration_date')->nullable();
                $table->string('instructor_name')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Customer Equipment
        if (!$schema->hasTable('customer_equipment')) {
            $schema->create('customer_equipment', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('manufacturer')->nullable();
                $table->string('model')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('size')->nullable();
                $table->string('material')->nullable();
                $table->date('last_vip_date')->nullable();
                $table->date('last_hydro_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }
};
