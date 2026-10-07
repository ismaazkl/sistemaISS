<?php

use App\Http\Controllers\CursoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FichaController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('cursos', [CursoController::class, 'index'])->name('cursos.index');
        Route::post('cursos', [CursoController::class, 'store'])->name('cursos.store');
        Route::patch('cursos/{curso}', [CursoController::class, 'update'])->whereNumber('curso')->name('cursos.update');
        Route::delete('cursos/{curso}', [CursoController::class, 'destroy'])->whereNumber('curso')->name('cursos.destroy');

        Route::get('fichas', [FichaController::class, 'index'])->name('fichas.index');
        Route::get('fichas/create', [FichaController::class, 'create'])->name('fichas.create');
        Route::post('fichas', [FichaController::class, 'store'])->name('fichas.store');
        Route::get('fichas/{ficha}/edit', [FichaController::class, 'edit'])->whereNumber('ficha')->name('fichas.edit');
        Route::get('fichas/{ficha}/pdf', [FichaController::class, 'pdf'])->whereNumber('ficha')->name('fichas.pdf');
        Route::patch('fichas/{ficha}', [FichaController::class, 'update'])->whereNumber('ficha')->name('fichas.update');
        Route::delete('fichas/{ficha}', [FichaController::class, 'destroy'])->whereNumber('ficha')->name('fichas.destroy');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
