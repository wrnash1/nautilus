<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $schema = Capsule::schema();

        // System Settings
        if (!$schema->hasTable('system_settings')) {
            $schema->create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('setting_key')->unique();
                $table->text('setting_value')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // Cash Drawers
        if (!$schema->hasTable('cash_drawers')) {
            $schema->create('cash_drawers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('location')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Cash Drawer Sessions
        if (!$schema->hasTable('cash_drawer_sessions')) {
            $schema->create('cash_drawer_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('drawer_id')->constrained('cash_drawers')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('start_cash', 10, 2);
                $table->decimal('end_cash', 10, 2)->nullable();
                $table->decimal('expected_cash', 10, 2)->nullable();
                $table->timestamp('opened_at')->useCurrent();
                $table->timestamp('closed_at')->nullable();
                $table->string('status')->default('open'); // open, closed
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Cash Drawer Transactions
        if (!$schema->hasTable('cash_drawer_transactions')) {
            $schema->create('cash_drawer_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('cash_drawer_sessions')->cascadeOnDelete();
                $table->string('transaction_type'); // sale, refund, pay_in, pay_out
                $table->decimal('amount', 10, 2);
                $table->string('payment_method')->default('cash');
                $table->string('description')->nullable();
                $table->string('reference_type')->nullable(); // transaction, user, etc.
                $table->string('reference_id')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }
};
