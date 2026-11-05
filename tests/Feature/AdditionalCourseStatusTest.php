<?php

use App\Models\AdditionalCourse;
use App\Models\User;

it('permite actualizar el estado de una solicitud de curso adicional', function () {
    $user = User::factory()->create();

    $course = AdditionalCourse::create([
        'name' => 'Curso de prueba',
        'duration' => 20,
        'institution' => 'Institución de prueba',
        'has_certification' => true,
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $course->update(['status' => 'approved']);

    expect($course->fresh()->status)->toBe('approved');
});
