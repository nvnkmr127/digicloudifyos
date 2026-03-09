<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Task Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold">Task: Review Ad Copy</h3>
                        <div>
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded font-semibold mr-2">In
                                Progress</span>
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded font-semibold mr-2">High
                                Priority</span>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Mark
                                Complete</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
                        <div class="lg:col-span-2">
                            <h4 class="font-semibold text-gray-700 mb-2">Description</h4>
                            <p class="text-gray-600 bg-gray-50 p-4 rounded border">Please review the attached ad copy
                                for the upcoming Black Friday campaign. Make sure it aligns with our brand guidelines
                                and includes the new promotional codes.</p>

                            <h4 class="font-semibold text-gray-700 mt-8 mb-2 border-b pb-2">Comments</h4>
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="font-semibold text-sm mb-1">Sarah Designer <span
                                            class="text-gray-400 font-normal ml-2">2 hours ago</span></div>
                                    <p class="text-sm">I've added the updated visuals that go with this copy.</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <textarea
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="3" placeholder="Add a comment..."></textarea>
                                <button
                                    class="mt-2 bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">Post
                                    Comment</button>
                            </div>
                        </div>

                        <div>
                            <div class="bg-gray-50 p-5 rounded-lg border">
                                <h4 class="font-semibold text-gray-700 border-b pb-2 mb-4">Task Info</h4>
                                <ul class="space-y-3 text-sm">
                                    <li class="flex justify-between"><span>Assignee:</span> <strong>Mike Writer</strong>
                                    </li>
                                    <li class="flex justify-between"><span>Project:</span> <strong>Black Friday
                                            2024</strong></li>
                                    <li class="flex justify-between text-red-600"><span>Due Date:</span>
                                        <strong>Tomorrow at 5 PM</strong></li>
                                </ul>

                                <h4 class="font-semibold text-gray-700 mt-6 border-b pb-2 mb-4">Attachments</h4>
                                <ul class="space-y-2 text-sm text-blue-600">
                                    <li><a href="#" class="hover:underline">ad_copy_v2.docx</a></li>
                                    <li><a href="#" class="hover:underline">promotional_assets.zip</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>