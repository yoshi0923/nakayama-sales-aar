<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('opportunity_no')->unique()->comment('案件番号（自動採番）');
            $table->string('title');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contact_name')->nullable()->comment('取引先担当者名');
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('opportunity_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('estimated_amount', 15, 2)->nullable()->comment('見込金額');
            $table->decimal('confirmed_amount', 15, 2)->nullable()->comment('確定受注金額');
            $table->tinyInteger('current_process')->default(1)->comment('現在のプロセスステップ 1-3');
            $table->enum('status', ['active', 'won', 'lost', 'hold'])->default('active');
            $table->smallInteger('fiscal_year')->nullable()->comment('対象年度');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
