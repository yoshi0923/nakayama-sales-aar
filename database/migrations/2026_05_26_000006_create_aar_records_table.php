<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aar_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('process_step')->comment('1=ヒアリング 2=提案・フォロー 3=受注〜売上');
            $table->date('activity_date');
            $table->enum('result', ['success', 'failure', 'ongoing'])->nullable();
            $table->text('q1_goal')->nullable()->comment('何を達成しようとしたか');
            $table->text('q2_result')->nullable()->comment('実際に何が起きたか');
            $table->text('q3_cause')->nullable()->comment('なぜ差異が生じたか');
            $table->text('q4_action')->nullable()->comment('次回どう改善するか');
            $table->boolean('is_draft')->default(true);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['opportunity_id', 'process_step']);
            $table->index(['user_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aar_records');
    }
};
