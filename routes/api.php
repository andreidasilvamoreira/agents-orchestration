<?php

use App\Http\Controllers\Api\WorkflowController;
use App\Http\Controllers\Api\WorkflowRunController;
use Illuminate\Support\Facades\Route;

Route::get('/workflows', [WorkflowController::class, 'index']);
Route::post('/workflows', [WorkflowController::class, 'store']);
Route::get('/workflows/{workflow}', [WorkflowController::class, 'show']);
Route::post('/workflows/{workflow}/run', [WorkflowRunController::class, 'store']);
Route::get('/runs/{run}', [WorkflowRunController::class, 'show']);
