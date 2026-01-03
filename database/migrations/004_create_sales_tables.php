<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $schema = Capsule::schema();

        // Orders
        if (!$schema->hasTable('orders')) {
            $schema->create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('order_number')->unique();
                $table->string('status')->default('pending'); // pending, completed, cancelled
                $table->decimal('total', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Order Items
        if (!$schema->hasTable('order_items')) {
            $schema->create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Transactions (POS)
        if (!$schema->hasTable('transactions')) {
            $schema->create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('transaction_number')->unique();
                $table->string('transaction_type')->default('sale'); // sale, refund, quote, layaway
                $table->string('status')->default('completed'); // completed, pending, voided, refunded
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->string('payment_method')->nullable(); // Helper
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Transaction Items
        if (!$schema->hasTable('transaction_items')) {
            $schema->create('transaction_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('item_name');
                $table->string('item_sku')->nullable();
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('total', 10, 2);
                $table->timestamps();
            });
        }

        // Payments
        if (!$schema->hasTable('payments')) {
            $schema->create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
                $table->string('payment_method'); // cash, credit_card, etc.
                $table->decimal('amount', 10, 2);
                $table->string('status')->default('completed');
                $table->string('reference_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }
};
