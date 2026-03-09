<x-app-container>
    <x-page-header title="Automations & Workflows">
        <x-button color="primary">Create Workflow</x-button>
    </x-page-header>

    <div class="mb-4 flex space-x-4 border-b border-gray-200">
        <button class="pb-2 border-b-2 border-primary font-medium text-primary text-sm">Active (3)</button>
        <button
            class="pb-2 border-b-2 border-transparent hover:border-gray-300 font-medium text-text-muted text-sm">Drafts
            (1)</button>
        <button
            class="pb-2 border-b-2 border-transparent hover:border-gray-300 font-medium text-text-muted text-sm">Archived</button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Automation Card 1 -->
        <x-card class="hover:border-primary transition duration-150 cursor-pointer border-l-4 border-l-green-500">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-text-primary">Lead Nurture Sequence</h3>
                    <p class="text-sm text-text-muted mt-1">Triggers when a new lead is added to Sales Pipeline</p>
                </div>
                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                    <input type="checkbox" name="toggle" id="toggle1" checked
                        class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer border-green-500"
                        style="right: 0;" />
                    <label for="toggle1"
                        class="toggle-label block overflow-hidden h-5 rounded-full bg-green-500 cursor-pointer"></label>
                </div>
            </div>

            <div class="bg-gray-50 rounded-md p-3 mb-4 space-y-2">
                <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <strong>Trigger:</strong> Contact Tag added (Lead)
                </div>
                <div class="flex items-center text-sm text-gray-500 pl-6 border-l-2 border-gray-300 ml-2 py-1">
                    Wait 5 minutes
                </div>
                <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <strong>Action:</strong> Send Welcome Email
                </div>
                <div class="flex items-center text-sm text-gray-500 pl-6 border-l-2 border-gray-300 ml-2 py-1">
                    Wait 2 days
                </div>
                <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    <strong>Action:</strong> Send SMS Follow-up
                </div>
            </div>

            <div class="flex justify-between items-center text-xs text-gray-500">
                <span>Enrolled: 1,423</span>
                <span>Active: <strong class="text-green-600">45</strong></span>
            </div>
        </x-card>

        <!-- Automation Card 2 -->
        <x-card class="hover:border-primary transition duration-150 cursor-pointer border-l-4 border-l-green-500">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-text-primary">Appointment Reminder</h3>
                    <p class="text-sm text-text-muted mt-1">Reduces no-shows for scheduled discovery calls</p>
                </div>
                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                    <input type="checkbox" name="toggle" id="toggle2" checked
                        class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer border-green-500"
                        style="right: 0;" />
                    <label for="toggle2"
                        class="toggle-label block overflow-hidden h-5 rounded-full bg-green-500 cursor-pointer"></label>
                </div>
            </div>

            <div class="bg-gray-50 rounded-md p-3 mb-4 space-y-2">
                <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <strong>Trigger:</strong> Appointment Status (Confirmed)
                </div>
                <div class="flex items-center text-sm text-gray-500 pl-6 border-l-2 border-gray-300 ml-2 py-1">
                    Wait 24 Hours before Appt
                </div>
                <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <strong>Action:</strong> Send Email Reminder
                </div>
                <div class="flex items-center text-sm text-gray-500 pl-6 border-l-2 border-gray-300 ml-2 py-1">
                    Wait 1 Hour before Appt
                </div>
                <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    <strong>Action:</strong> Send SMS Reminder
                </div>
            </div>

            <div class="flex justify-between items-center text-xs text-gray-500">
                <span>Enrolled: 843</span>
                <span>Active: <strong class="text-green-600">12</strong></span>
            </div>
        </x-card>

    </div>
</x-app-container>