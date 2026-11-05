<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use App\Models\CourseHasUser;

use Illuminate\Support\Carbon;

#[Fillable(['name', 'duration', 'start_time', 'start_course', 'end_course', 'year', 'agent_type', 'rfc_agent', 'format', 'training_objetive', 'institution', 'teacher', 'is_active', 'subject_area_key', 'course_link',])]
class Course extends Model
{
    /**
     * Incluye automáticamente el campo al convertir el modelo a Array o JSON
     */
    protected $appends = ['year'];

    protected function casts(): array
    {
        return [
            'duration' => 'float',
            'start_time' => 'datetime',
            'start_course' => 'date',
            'year' => 'integer',
            'end_course' => 'date',
            'is_active' => 'boolean',
            'subject_area_key' => 'integer',
        ];
    }

    /**
     * Obtiene únicamente el año del curso desde 'start_course'
     */
    protected function year(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->start_course?->format('Y'),
        );
    }

    /**
     * Obtiene la ocupación asociada al curso.
     */
    public function subjetArea()
    {
        return $this->belongsTo(SubjectArea::class, 'subject_area_key', 'key');
    }

    /**
     * Relación con los usuarios que han tomado este curso
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'courses_has_users')
            ->using(CourseHasUser::class)
            ->withPivot(['attended', 'attended_rh', 'final_grade', 'completed_at'])
            ->withTimestamps();
    }
    /**
     * Determina si el curso está activo basándose en la fecha de finalización.
     */
    protected static function booted(): void
    {
        static::retrieved(function (Course $course) {
            if ($course->is_active && $course->end_course && $course->end_course->isPast() && !$course->end_course->isToday()) {
                $course->timestamps = false;
                $course->updateQuietly([
                    'is_active' => false,
                ]);
            }
        });
    }
}
