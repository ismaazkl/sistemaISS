<?php

use App\Models\Curso;
use App\Models\Ficha;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function fichaData(array $overrides = []): array
{
    return array_merge([
        'estudiante' => 'Juan Pérez',
        'representante' => 'María Pérez',
        'cedula_estudiante' => '1712345678',
        'cedula_representante' => '0912345678',
        'telefono' => '0991234567',
        'causa_salida' => 'Traslado de domicilio',
        'fecha' => '2026-01-15',
        'hora' => '08:30',
    ], $overrides);
}

test('guests are redirected to the login page', function () {
    $this->get(route('fichas.index', ['current_team' => 'inexistente']))
        ->assertRedirect(route('login'));
});

test('users can view the fichas of their team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    Ficha::factory()->for($team)->create(['estudiante' => 'Ana Ramírez']);
    Ficha::factory()->create(['estudiante' => 'Ficha Ajena']);

    $this->actingAs($user)
        ->get(route('fichas.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Fichas/Index')
            ->has('fichas.data', 1)
            ->where('fichas.data.0.estudiante', 'Ana Ramírez')
        );
});

test('fichas can be filtered by search term', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    Ficha::factory()->for($team)->create([
        'estudiante' => 'Ana Ramírez',
        'cedula_estudiante' => '1711111111',
    ]);
    Ficha::factory()->for($team)->create([
        'estudiante' => 'Luis Torres',
        'cedula_estudiante' => '1722222222',
    ]);

    $this->actingAs($user)
        ->get(route('fichas.index', ['search' => 'torres']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('fichas.data', 1)
            ->where('fichas.data.0.estudiante', 'Luis Torres')
        );

    $this->actingAs($user)
        ->get(route('fichas.index', ['search' => '1711111111']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('fichas.data', 1)
            ->where('fichas.data.0.estudiante', 'Ana Ramírez')
        );
});

test('fichas can be filtered by course', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $cursoA = Curso::factory()->for($team)->create(['curso' => '1ERO EGB A']);
    $cursoB = Curso::factory()->for($team)->create(['curso' => '2DO EGB A']);

    Ficha::factory()->for($team)->for($cursoB, 'curso')->create([
        'estudiante' => 'Ana Ramírez',
    ]);

    $this->actingAs($user)
        ->get(route('fichas.index', ['curso_id' => $cursoA->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('fichas.data', 0)
        );

    $this->actingAs($user)
        ->get(route('fichas.index', ['curso_id' => $cursoB->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('fichas.data', 1)
            ->where('fichas.data.0.curso', '2DO EGB A')
        );
});

test('users can open the create form', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    Curso::factory()->for($team)->create(['curso' => '1ERO EGB A']);

    $this->actingAs($user)
        ->get(route('fichas.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Fichas/Create')
            ->has('cursos', 1)
            ->where('cursos.0.curso', '1ERO EGB A')
            ->where('defaults.hora', now()->format('H:i'))
        );
});

test('the create form only offers courses of the current team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    Curso::factory()->for($team)->create(['curso' => '1ERO EGB A']);
    Curso::factory()->create(['curso' => 'CURSO AJENO']);

    $this->actingAs($user)
        ->get(route('fichas.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('cursos', 1)
            ->missing('cursos.1')
        );
});

test('users can store a ficha', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData(['curso_id' => $curso->id]))
        ->assertRedirect(route('fichas.index'))
        ->assertSessionHasNoErrors();

    $ficha = Ficha::sole();

    expect($ficha->team_id)->toBe($team->id)
        ->and($ficha->curso_id)->toBe($curso->id)
        ->and($ficha->estudiante)->toBe('Juan Pérez')
        ->and($ficha->causa_salida)->toBe('Traslado de domicilio')
        ->and($ficha->hora)->toStartWith('08:30');
});

test('the exit cause and the time are optional', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            'causa_salida' => null,
            'hora' => null,
        ]))
        ->assertSessionHasNoErrors();

    expect(Ficha::sole()->causa_salida)->toBeNull()
        ->and(Ficha::sole()->hora)->toBeNull();
});

test('the time must use the HH:MM format', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            'hora' => '8:30 am',
        ]))
        ->assertSessionHasErrors(['hora' => 'La hora debe tener el formato HH:MM.']);

    expect(Ficha::count())->toBe(0);
});

test('the ficha belongs to the team from the url and ignores any submitted team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $otherTeam = Team::factory()->create();
    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            'team_id' => $otherTeam->id,
        ]))
        ->assertRedirect(route('fichas.index'));

    expect(Ficha::sole()->team_id)->toBe($team->id);
});

test('the ficha fields are required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), [])
        ->assertSessionHasErrors([
            'estudiante',
            'representante',
            'cedula_estudiante',
            'cedula_representante',
            'fecha',
            'curso_id',
        ]);

    expect(Ficha::count())->toBe(0);
});

test('the cedulas must have ten digits', function ($field, $message) {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            $field => '12345',
        ]))
        ->assertSessionHasErrors([$field => $message]);

    expect(Ficha::count())->toBe(0);
})->with([
    ['cedula_estudiante', 'La cédula del estudiante debe tener 10 dígitos.'],
    ['cedula_representante', 'La cédula del representante debe tener 10 dígitos.'],
]);

test('the student cedula must be unique within the team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    Ficha::factory()->for($team)->create([
        'cedula_estudiante' => '1712345678',
    ]);

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            'cedula_estudiante' => '1712345678',
        ]))
        ->assertSessionHasErrors([
            'cedula_estudiante' => 'Ya existe una ficha con esta cédula.',
        ]);

    expect(Ficha::count())->toBe(1);
});

test('the student cedula can be reused in a different team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    Ficha::factory()->create(['cedula_estudiante' => '1712345678']);

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            'cedula_estudiante' => '1712345678',
        ]))
        ->assertSessionHasNoErrors();

    expect(Ficha::count())->toBe(2);
});

test('the phone number accepts digits only', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            'telefono' => 'abc-123',
        ]))
        ->assertSessionHasErrors([
            'telefono' => 'El teléfono debe contener entre 7 y 15 dígitos.',
        ]);

    expect(Ficha::count())->toBe(0);
});

test('the phone number is optional', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            'telefono' => null,
        ]))
        ->assertSessionHasNoErrors();

    expect(Ficha::sole()->telefono)->toBeNull();
});

test('the enrolment date cannot be in the future', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData([
            'curso_id' => $curso->id,
            'fecha' => now()->addWeek()->toDateString(),
        ]))
        ->assertSessionHasErrors(['fecha' => 'La fecha no puede ser futura.']);

    expect(Ficha::count())->toBe(0);
});

test('the course must belong to the current team', function () {
    $user = User::factory()->create();

    $cursoAjeno = Curso::factory()->create();

    $this->actingAs($user)
        ->post(route('fichas.store'), fichaData(['curso_id' => $cursoAjeno->id]))
        ->assertSessionHasErrors([
            'curso_id' => 'El curso seleccionado no es válido.',
        ]);

    expect(Ficha::count())->toBe(0);
});

test('users can open the edit form of their team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $ficha = Ficha::factory()->for($team)->create([
        'causa_salida' => 'Traslado de domicilio',
        'hora' => '08:30',
    ]);

    $this->actingAs($user)
        ->get(route('fichas.edit', $ficha))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Fichas/Edit')
            ->where('ficha.id', $ficha->id)
            ->where('ficha.hora', '08:30')
            ->where('ficha.causa_salida', 'Traslado de domicilio')
        );
});

test('users can update a ficha of their team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $ficha = Ficha::factory()->for($team)->create(['telefono' => '0991234567']);
    $curso = Curso::factory()->for($team)->create();

    $this->actingAs($user)
        ->patch(route('fichas.update', $ficha), fichaData([
            'curso_id' => $curso->id,
            'cedula_estudiante' => '1755555555',
        ]))
        ->assertRedirect(route('fichas.index'))
        ->assertSessionHasNoErrors();

    expect($ficha->fresh())
        ->estudiante->toBe('Juan Pérez')
        ->cedula_estudiante->toBe('1755555555')
        ->curso_id->toBe($curso->id);
});

test('the student cedula can be kept unchanged when updating', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $ficha = Ficha::factory()->for($team)->create(['cedula_estudiante' => '1712345678']);

    $this->actingAs($user)
        ->patch(route('fichas.update', $ficha), fichaData([
            'curso_id' => $ficha->curso_id,
            'cedula_estudiante' => '1712345678',
            'estudiante' => 'Nombre Actualizado',
        ]))
        ->assertSessionHasNoErrors();

    expect($ficha->fresh()->estudiante)->toBe('Nombre Actualizado');
});

test('users can delete a ficha of their team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $ficha = Ficha::factory()->for($team)->create();

    $this->actingAs($user)
        ->from(route('fichas.index'))
        ->delete(route('fichas.destroy', $ficha))
        ->assertRedirect(route('fichas.index'));

    expect($ficha->fresh())->toBeNull();
});

test('users cannot read a ficha of another team', function () {
    $user = User::factory()->create();
    $ficha = Ficha::factory()->create();

    $this->actingAs($user)
        ->get(route('fichas.edit', $ficha))
        ->assertNotFound();
});

test('users cannot update a ficha of another team', function () {
    $user = User::factory()->create();
    $ficha = Ficha::factory()->create();

    // The payload is valid for the user's own team, so the request only fails
    // because the record belongs to a different one.
    $cursoPropio = Curso::factory()->for($user->currentTeam)->create();

    $this->actingAs($user)
        ->patch(route('fichas.update', $ficha), fichaData([
            'curso_id' => $cursoPropio->id,
        ]))
        ->assertNotFound();

    expect($ficha->fresh()->estudiante)->not->toBe('Juan Pérez');
});

test('users cannot delete a ficha of another team', function () {
    $user = User::factory()->create();
    $ficha = Ficha::factory()->create();

    $this->actingAs($user)
        ->delete(route('fichas.destroy', $ficha))
        ->assertNotFound();

    expect($ficha->fresh())->not->toBeNull();
});

test('users can download the pdf report of a ficha of their team', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $ficha = Ficha::factory()->for($team)->create([
        'estudiante' => 'Juan Pérez',
        'causa_salida' => 'Traslado de domicilio',
        'hora' => '08:30',
    ]);

    $response = $this->actingAs($user)
        ->get(route('fichas.pdf', $ficha))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('ficha-juan-perez.pdf');

    expect($response->streamedContent())->toStartWith('%PDF');
});

test('users cannot download the pdf report of a ficha of another team', function () {
    $user = User::factory()->create();
    $ficha = Ficha::factory()->create();

    $this->actingAs($user)
        ->get(route('fichas.pdf', $ficha))
        ->assertNotFound();
});

test('the pdf report embeds the institutional seal on the top left corner', function () {
    $user = User::factory()->create();
    $ficha = Ficha::factory()->for($user->currentTeam)->create();

    $markup = view('fichas.pdf', [
        'ficha' => $ficha->load('curso'),
        'team' => $user->currentTeam,
        'hora' => '08:30',
        'generatedAt' => now(),
        'sello' => 'data:image/jpeg;base64,'.base64_encode('seal-bytes'),
    ])->render();

    expect($markup)
        ->toContain('class="sello"')
        ->toContain('src="data:image/jpeg;base64,'.base64_encode('seal-bytes').'"')
        ->toContain('position: absolute');

    $this->actingAs($user)
        ->get(route('fichas.pdf', $ficha))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('the pdf report omits the seal when the image is not readable', function () {
    $user = User::factory()->create();
    $ficha = Ficha::factory()->for($user->currentTeam)->create();

    $markup = view('fichas.pdf', [
        'ficha' => $ficha->load('curso'),
        'team' => $user->currentTeam,
        'hora' => '08:30',
        'generatedAt' => now(),
        'sello' => null,
    ])->render();

    expect($markup)->not->toContain('class="sello"');
});
