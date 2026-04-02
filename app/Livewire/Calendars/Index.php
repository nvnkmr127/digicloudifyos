<?php

namespace App\Livewire\Calendars;

use App\Models\Project;
use App\Models\SocialPost;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $currentDate;

    public function mount()
    {
        $this->currentDate = now()->format('Y-m');
    }

    public function previousMonth()
    {
        $this->currentDate = Carbon::parse($this->currentDate.'-01')->subMonth()->format('Y-m');
    }

    public function nextMonth()
    {
        $this->currentDate = Carbon::parse($this->currentDate.'-01')->addMonth()->format('Y-m');
    }

    public function render()
    {
        $currentMonth = Carbon::parse($this->currentDate.'-01');
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $orgId = Auth::user()->organization_id;

        // Fetch Tasks
        $tasks = Task::where('organization_id', $orgId)
            ->whereBetween('due_at', [$startOfMonth, $endOfMonth])
            ->get();

        // Fetch Social Posts
        $posts = SocialPost::where('organization_id', $orgId)
            ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
            ->get();

        // Fetch Projects
        $projects = Project::where('organization_id', $orgId)
            ->whereBetween('deadline', [$startOfMonth, $endOfMonth])
            ->get();

        $startOfWeek = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $calendar = [];
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $calendar[$dateString] = [
                'day' => $date->day,
                'isCurrentMonth' => $date->month === $currentMonth->month,
                'items' => collect()
                    ->merge($tasks->filter(fn ($t) => $t->due_at->format('Y-m-d') === $dateString)->map(fn ($t) => ['type' => 'task', 'title' => $t->title, 'color' => 'indigo']))
                    ->merge($posts->filter(fn ($p) => $p->scheduled_at->format('Y-m-d') === $dateString)->map(fn ($p) => ['type' => 'social', 'title' => 'Social: '.str($p->content)->limit(10), 'color' => 'pink']))
                    ->merge($projects->filter(fn ($pr) => $pr->deadline->format('Y-m-d') === $dateString)->map(fn ($pr) => ['type' => 'project', 'title' => 'Deadline: '.$pr->name, 'color' => 'amber'])),
            ];
        }

        return view('livewire.calendars.index', [
            'calendar' => $calendar,
            'currentMonthLabel' => $currentMonth->format('F Y'),
        ]);
    }
}
