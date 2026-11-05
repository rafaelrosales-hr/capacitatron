<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Database\Factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Fillable(['curp', 'name', 'surname', 'second_surname', 'state', 'municipality', 'ocupation', 'education_level', 'major_title', 'institution', 'workplace', 'area', 'major', 'major_description', 'email', 'employee_number', 'username', 'google_id', 'password', 'is_active', 'ocupation_key'])]

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación con la Ocupación
     */
    public function ocupation(): BelongsTo
    {
        return $this->belongsTo(Ocupation::class, 'ocupation_key', 'key');
    }

    /**
     * Relación con los cursos que este usuario ha tomado
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'courses_has_users')
            ->using(CourseHasUser::class)
            ->withPivot(['attended', 'attended_rh', 'final_grade', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * Obtiene los cursos adicionales registrados por el usuario.
     */
    public function additionalCourses(): HasMany
    {
        return $this->hasMany(AdditionalCourse::class);
    }

    /**
     * Obtiene el total de cursos que este usuario ha tomado.
     */
    public function coursesTakenCount(): int
    {
        return $this->courses()->count();
    }

    /**
     * Obtiene el total de cursos faltantes para este usuario.
     */
    public function coursesMissingCount(): int
    {
        return max(0, Course::query()->count() - $this->coursesTakenCount());
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1) . Str::substr($initials, -1)
            : $initials;
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeNewThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }
}
