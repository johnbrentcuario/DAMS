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
        'physical_location_id',
        'physical_path',
        'fullname',
        'description',
        'attachments',
        'priority',
        'completed',
        'separation_mode_id',
        'effectivity_date',
    ];

    protected $casts = [
        'attachments'      => 'array',
        'completed'        => 'boolean',
        'effectivity_date' => 'date:Y-m-d',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(FileList::class, 'list_id');
    }

    public function physical_location(): BelongsTo
    {
        return $this->belongsTo(PhysicalLocation::class, 'physical_location_id');
    }

    public function separationMode(): BelongsTo
    {
        return $this->belongsTo(SeparationMode::class);
    }
}
