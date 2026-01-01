<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $schema = Capsule::schema();

        // Roles
        if (!$schema->hasTable('roles')) {
            $schema->create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // Permissions
        if (!$schema->hasTable('permissions')) {
            $schema->create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('permission_code')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // Role Permissions
        if (!$schema->hasTable('role_permissions')) {
            $schema->create('role_permissions', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
        }

        // Users
        if (!$schema->hasTable('users')) {
            $schema->create('users', function (Blueprint $table) {
                $table->id();
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('password_hash');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->timestamps();
            });
        }

        // User Roles
        if (!$schema->hasTable('user_roles')) {
            $schema->create('user_roles', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->primary(['user_id', 'role_id']);
            });
        }
    }
};
