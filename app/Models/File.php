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
        'physical_location_id', // NEW: Reference to the Office/Location
        'physical_path',        // NEW: The specific drawer or room string
        'fullname',
        'description',
        'attachments',
        'priority',
        'completed',
    ];

    protected $casts = [
        'attachments' => 'array',
        'completed' => 'boolean',
    ];

    /**
     * Get the category/list this file belongs to.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(FileList::class, 'list_id');
    }

    /**
     * NEW: Get the physical location (Office) where this file is stored.
     */
    public function physical_location(): BelongsTo
    {
        return $this->belongsTo(PhysicalLocation::class, 'physical_location_id');
    }
}
