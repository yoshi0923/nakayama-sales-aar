<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AarRecord extends Model
{
    use SoftDeletes;

    protected $table = 'aar_records';

    protected $fillable = [
        'opportunity_id', 'user_id', 'process_step', 'activity_date',
        'result', 'q1_goal', 'q2_result', 'q3_cause', 'q4_action',
        'is_draft', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'is_draft'      => 'boolean',
            'submitted_at'  => 'datetime',
            'process_step'  => 'integer',
        ];
    }

    // ── Relations ──────────────────────────────────────────────
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followupAction(): HasOne
    {
        return $this->hasOne(FollowupAction::class);
    }

    // ── Helpers ────────────────────────────────────────────────
    public function getProcessLabelAttribute(): string
    {
        return match($this->process_step) {
            1 => 'ヒアリング',
            2 => '提案からフォローアップ',
            3 => '受注してから売上まで',
            default => '不明',
        };
    }

    public function getResultLabelAttribute(): string
    {
        return match($this->result) {
            'success' => '成功',
            'failure' => '失敗',
            'ongoing' => '継続中',
            default   => '未選択',
        };
    }
}
