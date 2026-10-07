<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Ficha;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ficha>
 */
class FichaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'estudiante' => fake()->name(),
            'representante' => fake()->name(),
            'cedula_estudiante' => fake()->unique()->numerify('##########'),
            'cedula_representante' => fake()->numerify('##########'),
            'telefono' => fake()->numerify('09########'),
            'causa_salida' => fake()->randomElement([
                'Traslado de domicilio',
                'Cambio de institución',
                'Fin del año lectivo',
                'Motivo económico',
            ]),
            'fecha' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'hora' => fake()->dateTime()->format('H:i'),
        ];
    }

    /**
     * Configure the model factory.
     *
     * The course is resolved after the attributes are set so that the record
     * always belongs to the same team as its course.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Ficha $ficha): void {
            $curso = $ficha->curso_id
                ? Curso::find($ficha->curso_id)
                : Curso::factory()->create([
                    'team_id' => $ficha->team_id ?? Team::factory()->create()->id,
                ]);

            $ficha->curso_id = $curso->id;
            $ficha->team_id = $curso->team_id;
        });
    }

    /**
     * Indicate that the record has no phone number.
     */
    public function withoutPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'telefono' => null,
        ]);
    }
}
