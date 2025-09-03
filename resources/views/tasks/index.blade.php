<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tasks') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">New Task</a>
            <form method="get" class="flex flex-col sm:flex-row gap-2 sm:items-center flex-wrap">
                <select name="status" class="w-full sm:w-48 min-w-[160px] border rounded p-2">
                    <option value="">All statuses</option>
                    <option value="pending" @selected(($filters['status'] ?? '')==='pending')>Pending</option>
                    <option value="in_progress" @selected(($filters['status'] ?? '')==='in_progress')>In Progress</option>
                    <option value="completed" @selected(($filters['status'] ?? '')==='completed')>Completed</option>
                </select>
                <select name="category_id" class="w-full sm:w-48 min-w-[160px] border rounded p-2">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '')==$cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">Filter</button>
            </form>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded">
            <div class="overflow-x-auto">
                <table class="min-w-full w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-left hidden md:table-cell">Category</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left hidden md:table-cell">Due</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($tasks as $task)
                            @php
                                $statusText = ucfirst(str_replace('_',' ',$task->status));
                                $badge = match($task->status) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-2 align-top">
                                    <div class="font-medium text-gray-900">{{ $task->title }}</div>
                                    @if(!empty($isAdminView))
                                        <div class="text-xs text-gray-500">by {{ $task->user->name ?? 'Unknown' }}</div>
                                    @endif
                                    <div class="mt-1 text-sm text-gray-500 md:hidden">
                                        {{-- Mobile-only meta row --}}
                                        @if($task->category)
                                            <span class="mr-2">{{ $task->category->name }}</span>
                                        @endif
                                        @if($task->due_date)
                                            <span>{{ $task->due_date?->format('Y-m-d') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 hidden md:table-cell">{{ $task->category->name ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">{{ $statusText }}</span>
                                </td>
                                <td class="px-4 py-2 hidden md:table-cell">{{ $task->due_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex flex-wrap gap-2 justify-end">
                                        <form method="post" action="{{ route('tasks.toggle', $task) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="inline-flex items-center justify-center rounded-md border px-3 py-1.5 text-xs sm:text-sm whitespace-normal break-words max-w-full">
                                                @if($task->status==='completed')
                                                    <span class="sm:hidden">Pending</span>
                                                    <span class="hidden sm:inline">Mark Pending</span>
                                                @else
                                                    <span class="sm:hidden">Complete</span>
                                                    <span class="hidden sm:inline">Mark Completed</span>
                                                @endif
                                            </button>
                                        </form>
                                        <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center justify-center rounded-md border px-3 py-1.5 text-xs sm:text-sm whitespace-normal break-words">Edit</a>
                                        @if(auth()->check() && auth()->user()->is_admin)
                                        <form method="post" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center justify-center rounded-md border px-3 py-1.5 text-xs sm:text-sm text-red-600 whitespace-normal break-words">Delete</button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-6" colspan="5">No tasks found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $tasks->links() }}</div>
        </div>
    </div>
</x-app-layout>
