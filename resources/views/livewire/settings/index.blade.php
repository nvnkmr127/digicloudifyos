<x-app-container>
    <x-page-header title="Settings" />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Settings Nav -->
        <div class="md:col-span-1">
            <nav class="space-y-1">
                <a href="#" class="bg-primary text-white flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    General
                </a>
                <a href="#"
                    class="text-text-muted hover:bg-gray-50 flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    Security
                </a>
                <a href="#"
                    class="text-text-muted hover:bg-gray-50 flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    Notifications
                </a>
                <a href="#"
                    class="text-text-muted hover:bg-gray-50 flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    Billing
                </a>
            </nav>
        </div>

        <!-- Settings Content -->
        <div class="md:col-span-3 space-y-6">
            <x-card>
                <div class="border-b border-gray-100 pb-4 mb-4">
                    <h2 class="text-lg font-semibold text-text-primary">Profile Setting</h2>
                    <p class="text-sm text-text-muted mt-1">Update your account's profile information and email address.
                    </p>
                </div>

                <form class="space-y-6 max-w-xl">
                    <div>
                        <label for="name" class="block text-sm font-medium text-text-primary">Name</label>
                        <x-input id="name" type="text" value="Admin User" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-text-primary">Email Address</label>
                        <x-input id="email" type="email" value="admin@example.com" />
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <x-button color="primary">Save Changes</x-button>
                        <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 500)">
                            <span x-show="show" x-transition.opacity.out.duration.1500ms
                                class="text-sm text-accent font-medium" style="display: none;">Saved!</span>
                        </div>
                    </div>
                </form>
            </x-card>

            <x-card>
                <div class="border-b border-gray-100 pb-4 mb-4">
                    <h2 class="text-lg font-semibold text-text-primary">Danger Zone</h2>
                    <p class="text-sm text-text-muted mt-1">Once you delete your account, there is no going back. Please
                        be certain.</p>
                </div>

                <div class="pt-2">
                    <x-button color="danger">Delete Account</x-button>
                </div>
            </x-card>
        </div>
    </div>
</x-app-container>