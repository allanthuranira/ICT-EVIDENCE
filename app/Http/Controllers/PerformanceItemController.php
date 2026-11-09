<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\PerformanceItem;
use Illuminate\Http\Request;

class PerformanceItemController extends Controller
{
    public function index(Request $request)
    {
        $items = PerformanceItem::forFy((int) $request->get('fy', 2026))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->orderBy('type')->orderBy('code')->paginate(50);
        return view('items.index', compact('items'));
    }

    public function create()
    {
        return view('items.form', ['item' => new PerformanceItem(['fy_start_year' => 2026]), 'campuses' => Campus::where('active', true)->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $item = PerformanceItem::create($data);
        $item->campuses()->sync($request->input('campuses', []));
        return redirect()->route('items.index')->with('success', 'Item created.');
    }

    public function edit(PerformanceItem $item)
    {
        return view('items.form', ['item' => $item, 'campuses' => Campus::where('active', true)->get()]);
    }

    public function update(Request $request, PerformanceItem $item)
    {
        $item->update($this->validateData($request));
        $item->campuses()->sync($request->input('campuses', []));
        return redirect()->route('items.index')->with('success', 'Item updated.');
    }

    public function destroy(PerformanceItem $item)
    {
        $item->update(['active' => false]); // soft-disable so history stays intact
        return back()->with('success', 'Item deactivated.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'type' => 'required|in:pc,workplan',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_of_measure' => 'nullable|string|max:50',
            'annual_target' => 'nullable|numeric',
            'q1' => 'boolean', 'q2' => 'boolean', 'q3' => 'boolean', 'q4' => 'boolean',
            'fy_start_year' => 'required|integer|min:2020|max:2100',
            'active' => 'boolean',
        ] + [
            'q1' => $request->has('q1'), 'q2' => $request->has('q2'),
            'q3' => $request->has('q3'), 'q4' => $request->has('q4'),
            'active' => $request->has('active', true),
        ]);
    }
}
