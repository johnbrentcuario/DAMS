<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileList extends Model
{
    use HasFactory;
    protected $table = 'lists';

    protected $fillable = [
        'name',
        'color',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'list_id');
    }
}
