<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'opportunity_no', 'title', 'client_id', 'contact_name',
        'area_id', 'opportunity_type_id', 'assigned_user_id', 'department_id',
        'estimated_amount', 'confirmed_amount', 'current_process',
        'status', 'fiscal_year', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_amount'  => 'decimal:2',
            'confirmed_amount'  => 'decimal:2',
            'current_process'   => 'integer',
            'fiscal_year'       => 'integer',
        ];
    }

    // ── Relations ──────────────────────────────────────────────
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function opportunityType(): BelongsTo
    {
        return $this->belongsTo(OpportunityType::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function aarRecords(): HasMany
    {
        return $this->hasMany(AarRecord::class)->orderBy('process_step')->orderBy('activity_date');
    }

    public function followupActions(): HasMany
    {
        return $this->hasMany(FollowupAction::class);
    }

    // ── Helpers ────────────────────────────────────────────────
    /** 年度（3月〜翌2月）を取得 */
    public static function getFiscalYear(\Carbon\Carbon $date): int
    {
        return $date->month >= 3 ? $date->year : $date->year - 1;
    }

    /** 次の案件番号を採番 */
    public static function generateOpportunityNo(): string
    {
        $max = static::max('id') ?? 0;
        return 'OPP-' . str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }
}
