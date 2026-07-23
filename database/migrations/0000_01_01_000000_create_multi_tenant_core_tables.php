<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('max_computers')->default(1);
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_additional')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('plan_modules', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->primary(['plan_id', 'module_id']);
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('company_name');
            $table->string('trade_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('plan_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('logo_path')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('plan_modules');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('plans');
    }
};
