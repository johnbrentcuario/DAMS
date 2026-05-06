<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhysicalLocation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'storage_paths'];

    protected $casts = [
        'storage_paths' => 'array',
    ];

    public function files()
    {
        return $this->hasMany(File::class, 'physical_location_id');
    }
}
