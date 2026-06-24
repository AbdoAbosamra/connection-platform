<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only audit trail of admin moderation actions, for accountability.
 */
class ModerationLog extends Model
{
    use HasFactory;

    public const ACTIONS = [
        'warning', 'suspension', 'reinstatement', 'dismissal', 'content_removed', 'note',
    ];

    protected $fillable = [
        'moderator_id', 'user_id', 'report_id', 'action', 'notes', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
