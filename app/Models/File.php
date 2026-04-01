<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'fullname',
        'description',
        'attachments', // <--- Add this!
        'priority',    // Keep if you need for sorting
        'completed',   // Keep if you need for status
    ];

    protected $casts = [
        // This ensures the JSON in the DB becomes a clean PHP array
        'attachments' => 'array',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(FileList::class, 'list_id');
    }
}
