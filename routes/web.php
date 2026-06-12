<?php

use App\Http\Controllers\Web\AgentController;
use App\Http\Controllers\Web\AgentPageController;
use App\Http\Controllers\Web\AgentTeamController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TeamPageController;
use App\Http\Controllers\Web\WorkflowBuilderController;
use App\Http\Controllers\Web\WorkflowPageController;
use App\Http\Controllers\Web\WorkflowWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/teams', TeamPageController::class)->name('teams.index');
Route::get('/agents', AgentPageController::class)->name('agents.index');
Route::get('/workflows', WorkflowPageController::class)->name('workflows.index');
Route::get('/workflows/builder', WorkflowBuilderController::class)->name('workflows.builder');
Route::post('/teams', [AgentTeamController::class, 'store'])->name('teams.store');
Route::put('/teams/{team}', [AgentTeamController::class, 'update'])->name('teams.update');
Route::delete('/teams/{team}', [AgentTeamController::class, 'destroy'])->name('teams.destroy');
Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
Route::put('/agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
Route::post('/workflows', [WorkflowWebController::class, 'store'])->name('workflows.store');
Route::put('/workflows/{workflow}', [WorkflowWebController::class, 'update'])->name('workflows.update');
Route::post('/workflows/{workflow}/run', [WorkflowWebController::class, 'run'])->name('workflows.run');
