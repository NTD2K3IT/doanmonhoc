<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'action',
        'reference_id',
        'title',
        'description',
        'created_at',
    ];
}