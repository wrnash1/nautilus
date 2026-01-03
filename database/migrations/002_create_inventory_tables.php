<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $schema = Capsule::schema();

        // Categories
        if (!$schema->hasTable('categories')) {
            $schema->create('categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Vendors
        if (!$schema->hasTable('vendors')) {
            $schema->create('vendors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('website')->nullable();
                $table->string('address_line1')->nullable();
                $table->string('address_line2')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country')->default('US');
                $table->string('payment_terms')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Products
        if (!$schema->hasTable('products')) {
            $schema->create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->string('name');
                $table->string('sku')->unique()->nullable();
                $table->string('barcode')->nullable();
                $table->string('qr_code')->nullable();
                $table->text('description')->nullable();
                $table->decimal('cost_price', 10, 2)->default(0);
                $table->decimal('retail_price', 10, 2)->default(0);
                $table->decimal('weight', 8, 2)->nullable();
                $table->string('weight_unit')->default('lb');
                $table->string('dimensions')->nullable();
                $table->string('color')->nullable();
                $table->string('material')->nullable();
                $table->string('manufacturer')->nullable();
                $table->text('warranty_info')->nullable();
                $table->string('location_in_store')->nullable();
                $table->text('supplier_info')->nullable();
                $table->date('expiration_date')->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->integer('low_stock_threshold')->default(5);
                $table->boolean('track_inventory')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Product Images
        if (!$schema->hasTable('product_images')) {
            $schema->create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('image_path');
                $table->boolean('is_primary')->default(false);
                $table->string('alt_text')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Inventory Transactions
        if (!$schema->hasTable('inventory_transactions')) {
            $schema->create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('transaction_type'); // e.g., 'purchase', 'sale', 'adjustment'
                $table->integer('quantity_change');
                $table->text('notes')->nullable();
                $table->string('reference_id')->nullable(); // e.g., Order ID or Supplier Invoice
                $table->timestamps();
            });
        }
    }
};
