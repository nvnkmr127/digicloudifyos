<x-app-container>
    <x-page-header title="Calendars & Appointments">
        <x-button color="outline" class="mr-2">Copy Booking Link</x-button>
        <x-button color="primary">New Appointment</x-button>
    </x-page-header>

    <div class="flex space-x-6">
        <!-- Calendar Sidebar -->
        <div class="w-1/4 space-y-4">
            <x-card class="bg-gray-50 border-none shadow-sm">
                <!-- Mini map of month mock -->
                <div class="text-center font-bold text-text-primary mb-4 pb-2 border-b border-gray-200">October 2026
                </div>
                <div class="grid grid-cols-7 text-center text-xs text-text-muted mb-2 font-medium">
                    <div>Su</div>
                    <div>Mo</div>
                    <div>Tu</div>
                    <div>We</div>
                    <div>Th</div>
                    <div>Fr</div>
                    <div>Sa</div>
                </div>
                <div class="grid grid-cols-7 text-center text-sm gap-y-2">
                    <div class="text-gray-300">27</div>
                    <div class="text-gray-300">28</div>
                    <div class="text-gray-300">29</div>
                    <div class="text-gray-300">30</div>
                    <div>1</div>
                    <div>2</div>
                    <div>3</div>
                    <div>4</div>
                    <div>5</div>
                    <div>6</div>
                    <div>7</div>
                    <div
                        class="bg-primary text-white rounded-full w-7 h-7 flex items-center justify-center mx-auto shadow">
                        8</div>
                    <div>9</div>
                    <div>10</div>
                    <div>11</div>
                    <div>12</div>
                    <div>13</div>
                    <div>14</div>
                    <div class="font-bold text-indigo-600">15</div>
                    <div>16</div>
                    <div>17</div>
                    <div>18</div>
                    <div>19</div>
                    <div>20</div>
                    <div>21</div>
                    <div>22</div>
                    <div>23</div>
                    <div>24</div>
                    <div>25</div>
                    <div>26</div>
                    <div>27</div>
                    <div>28</div>
                    <div>29</div>
                    <div>30</div>
                    <div>31</div>
                </div>
            </x-card>

            <x-card class="shadow-sm">
                <h4 class="font-bold text-sm text-text-primary mb-3">My Calendars</h4>
                <div class="space-y-2 text-sm">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Discovery Calls</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked
                            class="rounded border-gray-300 text-green-500 focus:ring-green-500">
                        <span>Strategy Sessions</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span>Team Syncs</span>
                    </label>
                </div>
            </x-card>
        </div>

        <!-- Main Calendar View -->
        <div class="flex-1">
            <x-card class="h-full p-0 overflow-hidden shadow">
                <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-white">
                    <div class="flex space-x-4 items-center">
                        <span class="text-lg font-bold text-text-primary">Thursday, October 8</span>
                    </div>
                    <div class="flex bg-gray-100 rounded-md p-1">
                        <button
                            class="px-3 py-1 text-sm font-medium rounded-md bg-white shadow-sm text-text-primary">Day</button>
                        <button
                            class="px-3 py-1 text-sm font-medium rounded-md text-text-muted hover:text-text-primary">Week</button>
                        <button
                            class="px-3 py-1 text-sm font-medium rounded-md text-text-muted hover:text-text-primary">Month</button>
                    </div>
                </div>

                <div class="relative bg-white" style="height: 600px; overflow-y: auto;">
                    <!-- Timetable background rules -->
                    <div class="absolute inset-0 z-0">
                        @foreach(['9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM'] as $time)
                            <div class="flex border-b border-gray-100" style="height: 80px;">
                                <div class="w-16 text-right pr-2 text-xs text-gray-400 mt-[-10px]">{{ $time }}</div>
                                <div class="flex-1 border-l border-gray-100"></div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Events Overlay -->
                    <div class="absolute inset-0 z-10 pl-16">
                        <!-- Event 1: 10AM - 11AM -->
                        <div class="absolute w-[95%] bg-indigo-50 border-l-4 border-indigo-500 rounded-sm p-2 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                            style="top: 80px; height: 75px;">
                            <p class="text-xs font-bold text-indigo-900">Discovery Call: Acme Corp</p>
                            <p class="text-[10px] text-indigo-700">10:00 AM - 11:00 AM • Zoom</p>
                        </div>

                        <!-- Event 2: 1:30PM - 2:30PM -->
                        <div class="absolute w-[95%] bg-green-50 border-l-4 border-green-500 rounded-sm p-2 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                            style="top: 360px; height: 75px;">
                            <p class="text-xs font-bold text-green-900">Strategy Presentation: Global Tech</p>
                            <p class="text-[10px] text-green-700">1:30 PM - 2:30 PM • Google Meet</p>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>