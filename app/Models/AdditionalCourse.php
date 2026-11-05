<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'duration', 'institution', 'has_certification', 'url_course', 'certificate_path', 'user_id', 'status', 'price',])]
class AdditionalCourse extends Model
{
    protected function casts(): array
    {
        return [
            'duration' => 'float',
            'has_certification' => 'boolean',
            'user_id' => 'integer',
            'status' => 'string',
        ];
    }

    /**
     * Obtiene el usuario dueño de este curso adicional.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
