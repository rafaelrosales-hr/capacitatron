<?php

use App\Models\Course;
use App\Models\SubjectArea;
use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.login');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('navigation menu can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeVolt('layout.navigation');
});

test('dashboard shows the number of courses the authenticated user has taken', function () {
    $user = User::factory()->create();
    $subjectArea = SubjectArea::create([
        'key' => 1,
        'description' => 'Tecnología',
    ]);

    $course = Course::create([
        'name' => 'Curso de prueba',
        'duration' => 4,
        'start_course' => now(),
        'end_course' => now()->addDay(),
        'agent_type' => 'INTERNO',
        'rfc_agent' => 'RFC123',
        'format' => 'ONLINE',
        'training_objetive' => 'Objetivo de prueba',
        'institution' => 'Institución de prueba',
        'teacher' => 'Docente de prueba',
        'is_active' => true,
        'subject_area_key' => $subjectArea->key,
    ]);

    $user->courses()->attach($course->id, [
        'attended' => true,
        'completed_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeInOrder(['Cursos tomados', '1']);
});

test('dashboard calendar highlights days with scheduled courses', function () {
    $user = User::factory()->create();
    $subjectArea = SubjectArea::create([
        'key' => 2,
        'description' => 'Desarrollo',
    ]);

    Course::create([
        'name' => 'Curso del calendario',
        'duration' => 2,
        'start_course' => now()->startOfDay(),
        'end_course' => now()->startOfDay()->addDay(),
        'agent_type' => 'INTERNO',
        'rfc_agent' => 'RFC456',
        'format' => 'PRESCENCIAL',
        'training_objetive' => 'Objetivo del calendario',
        'institution' => 'Institución del calendario',
        'teacher' => 'Profesor prueba',
        'is_active' => true,
        'subject_area_key' => $subjectArea->key,
    ]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSee('bg-cyan-500/30');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('layout.navigation');

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
