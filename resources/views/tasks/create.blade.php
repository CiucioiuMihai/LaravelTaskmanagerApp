<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Task') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded p-6">
            <form method="post" action="{{ route('tasks.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium">Title</label>
                    <input name="title" value="{{ old('title') }}" class="w-full border rounded p-2" required />
                    @error('title')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" class="w-full border rounded p-2" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Status</label>
                        <select name="status" class="w-full border rounded p-2" required>
                            <option value="pending" @selected(old('status')==='pending')>Pending</option>
                            <option value="in_progress" @selected(old('status')==='in_progress')>In Progress</option>
                            <option value="completed" @selected(old('status')==='completed')>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Category</label>
                        <select name="category_id" class="w-full border rounded p-2">
                            <option value="">None</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id')==$cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm">Cancel</a>
                    <button class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
