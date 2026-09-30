<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasUuids;

    protected $fillable = [
        'subject',
        'message',
        'target',
        'created_by',
        'sent_count',
    ];
}
