<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $key
 * @property string $description
 * @property boolean $is_active
 * @property string $surname
 */

#[Fillable(['key', 'description', 'is_active'])]
class SubjectArea extends Model
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
