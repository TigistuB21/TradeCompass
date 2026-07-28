<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Feedback extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'feedback';

    protected $fillable = [
        'trader_id',
        'analyst_id',
        'trade_id',
        'type',
        'content',
        'ai_suggestions',
        'status',
        'submitted_at',
        'locked_at',
        'strengths',
        'weaknesses',
        'recommendations',
        'confidence_rating',
    ];

    protected $casts = [
        'ai_suggestions' => 'array',
        'submitted_at' => 'datetime',
        'locked_at' => 'datetime',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'recommendations' => 'array',
        'confidence_rating' => 'integer',
    ];

    /**
     * Relationships
     */
    public function trader()
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * Scopes
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted')->orWhere('status', 'locked');
    }

    public function scopeEditable($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'draft')
              ->orWhere(function ($q2) {
                  $q2->where('status', 'submitted')
                     ->where('submitted_at', '>=', now()->subHours(24));
              });
        });
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    /**
     * Helper methods
     */
    public function isEditable(): bool
    {
        if ($this->status === 'draft') {
            return true;
        }

        if ($this->status === 'locked') {
            return false;
        }

        if ($this->status === 'submitted' && $this->submitted_at) {
            return $this->submitted_at->addHours(24)->isFuture();
        }

        return false;
    }

    public function lock(): void
    {
        if (!$this->isEditable() && $this->status === 'submitted') {
            $this->status = 'locked';
            $this->locked_at = now();
            $this->save();
        }
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->isEditable() && $this->analyst_id === $user->id;
    }

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['content', 'status', 'submitted_at', 'locked_at'])
            ->logOnlyDirty();
    }
}
