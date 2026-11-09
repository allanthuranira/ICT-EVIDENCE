<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\PerformanceItem;
use App\Models\Recipient;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PerformanceItemController extends Controller
{
    public function index(Request $request)
    {
        $items = PerformanceItem::forFy((int) $request->get('fy', 2026))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->with('campuses.recipients')
            ->orderBy('type')->orderBy('code')->paginate(50);
        return view('items.index', compact('items'));

        $items = PerformanceItem::with(['campuses' => fn($q) => $q->with('activeRecipient')])->paginate(20);
    }

    public function create()
    {
        return view('items.form', [
            'item' => new PerformanceItem(['fy_start_year' => 2026]),
            'campuses' => $this->campusesWithRecipients(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $sync = $this->campusSyncData($request);

        $item = PerformanceItem::create($data);
        $item->campuses()->sync($sync);

        return redirect()->route('items.index')->with('success', 'Item created.');
    }

    public function edit(PerformanceItem $item)
    {
        $item->load('campuses.recipients');
        return view('items.form', ['item' => $item, 'campuses' => $this->campusesWithRecipients()]);
    }

    public function update(Request $request, PerformanceItem $item)
    {
        $item->update($this->validateData($request));
        $item->campuses()->sync($this->campusSyncData($request));

        return redirect()->route('items.index')->with('success', 'Item updated.');
    }

    public function destroy(PerformanceItem $item)
    {
        $item->update(['active' => false]); // soft-disable so history stays intact
        return back()->with('success', 'Item deactivated.');
    }

    private function campusesWithRecipients()
    {
        return Campus::where('active', true)->with(['recipients' => fn($q) => $q->where('active', true)])->get();
    }

    /*
     * Build the sync array: [campus_id => ['recipient_id' => ...]]
     * Validates that every chosen recipient actually belongs to that campus.
     */
    private function campusSyncData(Request $request): array
    {
        $campusIds = $request->input('campuses', []);
        $choices = $request->input('recipients', []);

        $sync = [];
        foreach ($campusIds as $campusId) {
            $recipientId = $choices[$campusId] ?? null;

            if ($recipientId) {
                $belongs = Recipient::where('id', $recipientId)
                    ->where('campus_id', $campusId)
                    ->where('active', true)
                    ->exists();

                if (! $belongs) {
                    throw ValidationException::withMessages([
                        'recipients.' . $campusId => 'The selected recipient does not belong to that campus (or is inactive).',
                    ]);
                }
            }

            $sync[$campusId] = ['recipient_id' => $recipientId ?: null];
        }

        return $sync;
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
            'campuses' => 'nullable|array',
            'campuses.*' => 'exists:campuses,id',
            'recipients' => 'nullable|array',
            'recipients.*' => 'nullable|exists:recipients,id',
        ] + [
            'q1' => $request->has('q1'), 'q2' => $request->has('q2'),
            'q3' => $request->has('q3'), 'q4' => $request->has('q4'),
            'active' => $request->has('active', true),
        ]);
    }
}