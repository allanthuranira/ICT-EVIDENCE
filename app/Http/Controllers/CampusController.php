<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampusController extends Controller
{
    public function index()
    {
        return view('campuses.index', ['campuses' => Campus::withCount('recipients')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'is_hq' => 'boolean']);
        Campus::updateOrCreate(['slug' => Str::slug($data['name'])], $data + ['active' => true]);
        return back()->with('success', 'Campus saved.');
    }

    public function update(Request $request, Campus $campus)
    {
        $campus->update($request->validate([
            'name' => 'required|string|max:255',
            'is_hq' => 'boolean', 'active' => 'boolean',
        ]));
        return back()->with('success', 'Campus updated.');
    }
}
