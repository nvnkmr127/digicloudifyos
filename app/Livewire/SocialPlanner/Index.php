<?php

namespace App\Livewire\SocialPlanner;

use Livewire\Component;
use App\Models\SocialChannel;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Index extends Component
{
    public $currentDate;
    public $viewMode = 'calendar'; // 'calendar' or 'list'
    public $showCreateModal = false;
    public $showConnectModal = false;

    // Post Creation state
    public $content = '';
    public $selectedChannels = [];
    public $scheduledDate = '';
    public $scheduledTime = '';

    protected $rules = [
        'content' => 'required|string',
        'selectedChannels' => 'required|array|min:1',
        'selectedChannels.*' => 'exists:social_channels,id',
        'scheduledDate' => 'required|date',
        'scheduledTime' => 'required',
    ];

    public function mount()
    {
        $this->currentDate = now()->format('Y-m');
        $this->scheduledDate = now()->format('Y-m-d');
        $this->scheduledTime = now()->addHour()->format('H:i');
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function toggleChannelSelection($channelId)
    {
        if (in_array($channelId, $this->selectedChannels)) {
            $this->selectedChannels = array_diff($this->selectedChannels, [$channelId]);
        } else {
            $this->selectedChannels[] = $channelId;
        }
    }

    public function createPost()
    {
        $this->validate();

        $scheduledAt = Carbon::parse($this->scheduledDate . ' ' . $this->scheduledTime);

        foreach ($this->selectedChannels as $channelId) {
            SocialPost::create([
                'organization_id' => Auth::user()->organization_id,
                'user_id' => Auth::id(),
                'social_channel_id' => $channelId,
                'content' => $this->content,
                'scheduled_at' => $scheduledAt,
                'status' => 'scheduled',
            ]);
        }

        $this->dispatch('close-modal', 'create-post-modal');
        $this->reset(['content', 'selectedChannels']);
        session()->flash('message', count($this->selectedChannels) > 1 ? 'Posts scheduled successfully.' : 'Post scheduled successfully.');
    }

    public function connectChannel($platform)
    {
        // Simple dummy connection logic for now
        $randomHandles = [
            'facebook' => 'My Business Page',
            'instagram' => '@business_official',
            'twitter' => '@bizupdates',
            'linkedin' => 'Business Inc.',
        ];

        SocialChannel::create([
            'organization_id' => Auth::user()->organization_id,
            'platform' => $platform,
            'account_id' => 'dummy_' . uniqid(),
            'account_name' => $randomHandles[$platform] ?? ucfirst($platform) . ' Account',
            'status' => 'active',
        ]);

        $this->dispatch('close-modal', 'connect-channel-modal');
        session()->flash('message', ucfirst($platform) . ' channel connected.');
    }

    public function previousMonth()
    {
        $this->currentDate = Carbon::parse($this->currentDate . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth()
    {
        $this->currentDate = Carbon::parse($this->currentDate . '-01')->addMonth()->format('Y-m');
    }

    public function render()
    {
        $channels = SocialChannel::where('organization_id', Auth::user()->organization_id)->get();

        $currentMonth = Carbon::parse($this->currentDate . '-01');
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $postsQuery = SocialPost::where('organization_id', Auth::user()->organization_id)
            ->with('channel')
            ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
            ->orderBy('scheduled_at', 'asc');

        $posts = $postsQuery->get();

        // Build calendar grid
        $startOfWeek = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $calendar = [];
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $calendar[$dateString] = [
                'day' => $date->day,
                'isCurrentMonth' => $date->month === $currentMonth->month,
                'posts' => $posts->filter(function ($post) use ($dateString) {
                    return $post->scheduled_at->format('Y-m-d') === $dateString;
                })
            ];
        }

        return view('livewire.social-planner.index', [
            'channels' => $channels,
            'calendar' => $calendar,
            'listPosts' => $posts,
            'currentMonthLabel' => $currentMonth->format('F Y'),
        ]);
    }
}
