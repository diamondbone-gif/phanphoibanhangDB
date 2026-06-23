<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNoteTemplate extends Model
{
    protected $table = 'customer_note_templates';

    protected $fillable = [
        'content',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
