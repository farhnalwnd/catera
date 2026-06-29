<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class access_logs extends Model
{
    protected $table = 'catera.access_logs';

    protected $fillable = [
        'authorizeds_id',
        'uuid',
        'group',
        'status',
        'scanned_at',
    ];

    protected function casts()
    {
        return [
            'scanned_at' => 'date',
        ];
    }
}
