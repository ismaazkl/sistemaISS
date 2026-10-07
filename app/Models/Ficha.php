<?php

namespace App\Models;

use Database\Factories\FichaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $curso_id
 * @property int $team_id
 * @property string $estudiante
 * @property string $representante
 * @property string $cedula_estudiante
 * @property string $cedula_representante
 * @property string|null $telefono
 * @property Carbon $fecha
 * @property string|null $hora
 * @property string|null $causa_salida
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team $team
 * @property-read Curso $curso
 */
#[Fillable(['estudiante', 'representante', 'cedula_estudiante', 'cedula_representante', 'telefono', 'causa_salida', 'curso_id', 'team_id', 'fecha', 'hora'])]
class Ficha extends Model
{
    /** @use HasFactory<FichaFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'fichas';

    /**
     * Get the course the student is enrolled in.
     *
     * @return BelongsTo<Curso, $this>
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Get the team that owns this record.
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope the query to a single team.
     *
     * @param  Builder<Ficha>  $query
     * @return Builder<Ficha>
     */
    public function scopeForTeam(Builder $query, Team|int $team): Builder
    {
        return $query->where('team_id', $team instanceof Team ? $team->getKey() : $team);
    }

    /**
     * Scope the query to a single course.
     *
     * @param  Builder<Ficha>  $query
     * @return Builder<Ficha>
     */
    public function scopeForCurso(Builder $query, ?int $cursoId): Builder
    {
        return $query->when($cursoId, fn (Builder $query) => $query->where('curso_id', $cursoId));
    }

    /**
     * Scope the query to records matching the given search term.
     *
     * @param  Builder<Ficha>  $query
     * @return Builder<Ficha>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        return $query->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
            $query->where('estudiante', 'like', '%'.$search.'%')
                ->orWhere('representante', 'like', '%'.$search.'%')
                ->orWhere('cedula_estudiante', 'like', '%'.$search.'%');
        }));
    }

    /**
     * Order the query by the most recent enrolment first.
     *
     * @param  Builder<Ficha>  $query
     * @return Builder<Ficha>
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('fecha')->orderByDesc('id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}
