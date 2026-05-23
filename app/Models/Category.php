<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
    ];

    /**
     * @return HasMany<MasterClass, $this>
     */
    public function masterClasses(): HasMany
    {
        return $this->hasMany(MasterClass::class);
    }
}
