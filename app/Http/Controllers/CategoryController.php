<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Category::query()->orderBy('name');
        if (!($user && $user->is_admin)) {
            $query->where('user_id', $user->id);
        }

        $categories = $query->paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100']
        ]);

        Category::firstOrCreate([
            'user_id' => $request->user()->id,
            'name' => $validated['name']
        ]);

        return back()->with('status', 'Category saved.');
    }

    public function destroy(Category $category)
    {
        $user = Auth::user();
        abort_unless($user && ($user->is_admin || $category->user_id === $user->id), 403);
        $category->delete();
        return back()->with('status', 'Category deleted.');
    }
}
