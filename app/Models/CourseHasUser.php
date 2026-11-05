<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

#[Fillable(['course_id', 'user_id', 'attended', 'attended_rh', 'final_grade', 'completed_at'])]
class CourseHasUser extends Pivot
{

    protected $casts = [
        'completed_at' => 'datetime',
        'attended' => 'boolean',
        'attended_rh' => 'boolean',
    ];
    protected $table = 'courses_has_users';
    public $timestamps = true;

    /**
     * Relación inversa para acceder al usuario desde el pivote 
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación inversa para acceder al curso desde el pivote
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
