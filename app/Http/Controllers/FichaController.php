<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveFichaRequest;
use App\Models\Curso;
use App\Models\Ficha;
use App\Models\Team;
use Dompdf\Dompdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FichaController extends Controller
{
    /**
     * Display a listing of the enrolment records of the current team.
     */
    public function index(Request $request): Response
    {
        $team = $this->team($request);
        $filters = $this->filters($request);

        $fichas = Ficha::query()
            ->with('curso')
            ->forTeam($team)
            ->forCurso($filters['curso_id'])
            ->search($filters['search'])
            ->latestFirst()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Ficha $ficha): array => $this->serialize($ficha));

        return Inertia::render('Fichas/Index', [
            'fichas' => $fichas,
            'cursos' => $this->cursos($team),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new enrolment record.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Ficha::class);

        return Inertia::render('Fichas/Create', [
            'cursos' => $this->cursos($this->team($request)),
            'defaults' => [
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i'),
            ],
        ]);
    }

    /**
     * Store a newly created enrolment record.
     */
    public function store(SaveFichaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Ficha::class);

        $ficha = $this->team($request)->fichas()->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Ficha registrada correctamente.',
        ]);

        return to_route('fichas.index');
    }

    /**
     * Show the form for editing the given enrolment record.
     */
    public function edit(Request $request): Response
    {
        $model = $this->findFicha($request);

        Gate::authorize('update', $model);

        return Inertia::render('Fichas/Edit', [
            'ficha' => $this->serialize($model),
            'cursos' => $this->cursos($this->team($request)),
        ]);
    }

    /**
     * Update the given enrolment record.
     */
    public function update(SaveFichaRequest $request): RedirectResponse
    {
        $model = $this->findFicha($request);

        Gate::authorize('update', $model);

        $model->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Ficha actualizada correctamente.',
        ]);

        return to_route('fichas.index');
    }

    /**
     * Remove the given enrolment record.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $model = $this->findFicha($request);

        Gate::authorize('delete', $model);

        $model->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Ficha eliminada correctamente.',
        ]);

        return back();
    }

    /**
     * Download the given enrolment record as a PDF report.
     */
    public function pdf(Request $request): StreamedResponse
    {
        $model = $this->findFicha($request);

        Gate::authorize('view', $model);

        $pdf = app(Dompdf::class);
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHtml(
            view('fichas.pdf', [
                'ficha' => $model,
                'team' => $this->team($request),
                'hora' => $this->hora($model),
                'generatedAt' => now(),
                'sello' => $this->sello(),
            ])->render()
        );
        $pdf->render();

        $filename = 'ficha-'.Str::slug($model->estudiante).'.pdf';
        $content = $pdf->output();

        return response()->streamDownload(
            function () use ($content): void {
                echo $content;
            },
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Resolve the current team from the request.
     */
    protected function team(Request $request): Team
    {
        return $request->user()->currentTeam;
    }

    /**
     * Find the enrolment record of the current request, limited to the current team.
     *
     * The identifier is read from the route instead of the method signature
     * because the team slug also occupies a route parameter position.
     */
    protected function findFicha(Request $request): Ficha
    {
        $ficha = Ficha::forTeam($this->team($request))
            ->with('curso')
            ->whereKey($request->route('ficha'))
            ->first();

        abort_if($ficha === null, 404);

        return $ficha;
    }

    /**
     * Get the list filters coming from the query string.
     *
     * @return array{search: string, curso_id: int|null}
     */
    protected function filters(Request $request): array
    {
        return [
            'search' => $request->string('search')->trim()->value(),
            'curso_id' => $request->integer('curso_id') ?: null,
        ];
    }

    /**
     * Get the courses of the team formatted for select inputs.
     *
     * @return array<int, array{id: int, curso: string}>
     */
    protected function cursos(Team $team): array
    {
        return Curso::forTeam($team)
            ->ordered()
            ->get(['id', 'curso'])
            ->map(fn (Curso $curso): array => [
                'id' => $curso->id,
                'curso' => $curso->curso,
            ])
            ->all();
    }

    /**
     * Format an enrolment record for the frontend.
     *
     * @return array{id: int, estudiante: string, representante: string, cedula_estudiante: string, cedula_representante: string, telefono: string|null, causa_salida: string|null, fecha: string, hora: string|null, curso_id: int, curso: string|null}
     */
    protected function serialize(Ficha $ficha): array
    {
        return [
            'id' => $ficha->id,
            'estudiante' => $ficha->estudiante,
            'representante' => $ficha->representante,
            'cedula_estudiante' => $ficha->cedula_estudiante,
            'cedula_representante' => $ficha->cedula_representante,
            'telefono' => $ficha->telefono,
            'causa_salida' => $ficha->causa_salida,
            'fecha' => $ficha->fecha->toDateString(),
            'hora' => $this->hora($ficha),
            'curso_id' => $ficha->curso_id,
            'curso' => $ficha->curso->curso,
        ];
    }

    /**
     * Get the exit time of the record as a HH:MM string.
     */
    protected function hora(Ficha $ficha): ?string
    {
        return $ficha->hora !== null
            ? Carbon::parse($ficha->hora)->format('H:i')
            : null;
    }

    /**
     * Get the institutional seal as an inline data URI.
     *
     * Remote resources are disabled on the PDF renderer, so the image is
     * embedded in the report markup instead of being referenced by URL.
     */
    protected function sello(): ?string
    {
        $path = public_path('images/sello.jpeg');

        if (! is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        return $contents === false
            ? null
            : 'data:image/jpeg;base64,'.base64_encode($contents);
    }
}
