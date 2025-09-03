<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    // Controller-level middleware is not available in this base Controller; use route middleware instead.

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->is_admin) {
            $query = Task::query()->with(['category', 'user'])->latest();
        } else {
            $query = Task::query()->where('user_id', $user->id)
                ->with('category')
                ->latest();
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($user && $user->is_admin) {
            if ($filterUserId = $request->query('user_id')) {
                $query->where('user_id', $filterUserId);
            }
        }

        return view('tasks.index', [
            'tasks' => $query->paginate(10)->withQueryString(),
            'categories' => $user && $user->is_admin
                ? Category::orderBy('name')->get()
                : Category::where('user_id', $user->id)->orderBy('name')->get(),
            'filters' => $request->only(['status', 'category_id', 'user_id']),
            'isAdminView' => (bool) ($user && $user->is_admin),
        ]);
    }

    public function adminIndex(Request $request)
    {
        // Admins can see all tasks
        $query = Task::query()->with(['category', 'user'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        return view('tasks.index', [
            'tasks' => $query->paginate(10)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $request->only(['status', 'category_id', 'user_id']),
            'isAdminView' => true,
        ]);
    }

    public function create()
    {
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get();
        return view('tasks.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'due_date' => ['nullable', 'date'],
            'category_id' => ['nullable', 'exists:categories,id']
        ]);

        $validated['user_id'] = $request->user()->id;
        if (($validated['status'] ?? null) === 'completed') {
            $validated['completed_at'] = now();
        }

        $task = Task::create($validated);

        return redirect()->route('tasks.index')->with('status', 'Task created.');
    }

    public function edit(Task $task)
    {
        $this->authorizeTask($task);
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get();
        return view('tasks.edit', compact('task', 'categories'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'due_date' => ['nullable', 'date'],
            'category_id' => ['nullable', 'exists:categories,id']
        ]);

        $validated['completed_at'] = $validated['status'] === 'completed' ? ( $task->completed_at ?? now() ) : null;

        $task->update($validated);

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        // Route-level 'admin' middleware restricts access here.
        $task->delete();
        return redirect()->route('tasks.index')->with('status', 'Task deleted.');
    }

    public function toggleComplete(Task $task)
    {
        $this->authorizeTask($task);
        if ($task->status === 'completed') {
            $task->update(['status' => 'pending', 'completed_at' => null]);
        } else {
            $task->update(['status' => 'completed', 'completed_at' => now()]);
        }
        return back()->with('status', 'Task status updated.');
    }

    protected function authorizeTask(Task $task): void
    {
        $user = Auth::user();
        if ($user && $user->is_admin) {
            return; // admins can access any task for non-destroy actions
        }
        abort_unless($task->user_id === Auth::id(), 403);
    }
}
