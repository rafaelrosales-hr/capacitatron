<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'description', 'is_active'])]
class Ocupation extends Model
{
    protected $primaryKey = 'key';
    public $timestamps = false;
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }
}
