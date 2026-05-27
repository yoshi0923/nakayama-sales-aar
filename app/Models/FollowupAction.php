<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowupAction extends Model
{
    protected $fillable = [
        'aar_record_id', 'opportunity_id', 'user_id',
        'action_description', 'scheduled_date',
        'completed_at', 'status', 'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_at'   => 'datetime',
            'notified_at'    => 'datetime',
        ];
    }

    public function aarRecord(): BelongsTo
    {
        return $this->belongsTo(AarRecord::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
