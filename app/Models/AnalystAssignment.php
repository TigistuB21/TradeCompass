<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalystAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'analyst_id',
        'trader_id',
        'assigned_by',
        'status',
        'start_date',
        'current_focus_area',
    ];

    /**
     * Relationships
     */
    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function trader()
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
