<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Ficha;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $team = $user->currentTeam;

        $email = strtolower($user->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        return Inertia::render('dashboard', [
            'pendingInvitations' => $pendingInvitations,
            'stats' => [
                'totalFichas' => Ficha::forTeam($team)->count(),
                'totalCursos' => Curso::forTeam($team)->count(),
                'fichasDelMes' => Ficha::forTeam($team)
                    ->whereYear('fecha', now()->year)
                    ->whereMonth('fecha', now()->month)
                    ->count(),
            ],
            'fichasPorDia' => Ficha::query()
                ->with('curso')
                ->forTeam($team)
                ->latestFirst()
                ->limit(100)
                ->get()
                ->groupBy(fn (Ficha $ficha): string => $ficha->fecha->toDateString())
                ->take(7)
                ->map(fn (Collection $fichas, string $fecha): array => [
                    'fecha' => $fecha,
                    'total' => $fichas->count(),
                    'fichas' => $fichas
                        ->map(fn (Ficha $ficha): array => [
                            'id' => $ficha->id,
                            'estudiante' => $ficha->estudiante,
                            'curso' => $ficha->curso->curso,
                            'hora' => $ficha->hora !== null
                                ? Carbon::parse($ficha->hora)->format('H:i')
                                : null,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values(),
        ]);
    }
}
