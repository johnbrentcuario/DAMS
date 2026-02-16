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
        'priority',
        'completed',
        'completed_requirements'
    ];
    protected $casts = [
        'completed' => 'boolean',
        'completed_requirements' => 'array',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(FileList::class, 'list_id');
    }
}
