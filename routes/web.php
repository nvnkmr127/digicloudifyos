<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('dashboard');

    // Core Modules
    Route::get('/campaigns', \App\Livewire\Campaigns\KanbanBoard::class)->name('campaigns.index');
    Route::get('/campaigns/create', \App\Livewire\Campaigns\CreateForm::class)->name('campaigns.create');
    Route::get('/campaigns/{id}', \App\Livewire\Campaigns\DetailView::class)->name('campaigns.show');

    Route::get('/tasks', \App\Livewire\Tasks\KanbanBoard::class)->name('tasks.index');
    Route::get('/tasks/{id}', \App\Livewire\Tasks\DetailView::class)->name('tasks.show');

    Route::get('/leads', \App\Livewire\Leads\KanbanBoard::class)->name('leads.index');
    Route::get('/leads/{id}', \App\Livewire\Leads\DetailView::class)->name('leads.show');

    // Additional Modules
    Route::get('/creatives', \App\Livewire\Creatives\RequestsBoard::class)->name('creatives.index');
    Route::get('/creatives/{id}', \App\Livewire\Creatives\RequestsBoard::class)->name('creatives.show');

    // Agency Management
    Route::get('/workflow-monitoring', \App\Livewire\WorkflowMonitoring\Dashboard::class)->name('workflow.index');
    Route::get('/reports', \App\Livewire\Reports\Dashboard::class)->name('reports.index');
    Route::get('/alerts', \App\Livewire\Alerts\Index::class)->name('alerts.index');
    Route::get('/clients', \App\Livewire\Clients\Index::class)->name('clients.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
