<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followup_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aar_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('action_description');
            $table->date('scheduled_date');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'scheduled_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followup_actions');
    }
};
