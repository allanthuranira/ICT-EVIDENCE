<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\PerformanceItem;
use App\Rules\GoogleDriveFolder;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;

class PerformanceItemController extends Controller
{
    public function index()
    {
        $items = PerformanceItem::with(['campuses.recipients'])
            ->orderBy('type')->orderBy('code')
            ->paginate(20);

        return view('items.index', compact('items'));
    }

    public function create()
    {
        return view('items.create', [
            'campuses' => Campus::with('recipients')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->extractDrive($data);

        $item = PerformanceItem::create($data);

        $this->syncCampuses($item, $request);

        return redirect()->route('items.index')->with('status', 'Item created.');
    }

    public function edit(PerformanceItem $item)
    {
        $item->load('campuses');

        return view('items.edit', [
            'item' => $item,
            'campuses' => Campus::with('recipients')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PerformanceItem $item)
    {
        $data = $this->validated($request);
        $this->extractDrive($data);

        $item->update($data);

        $this->syncCampuses($item, $request);

        return redirect()->route('items.index')->with('status', 'Item updated.');
    }

    /* ─────────────────────────────────────────────────────────────
     |  Helpers — must live INSIDE the class body, before the final }
     * ───────────────────────────────────────────────────────────── */

    private function validated(Request $request): array
    {
        return $request->validate([
            'type'            => 'required|in:pc,workplan',
            'code'            => 'required|string|max:50',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'unit_of_measure' => 'nullable|string|max:50',
            'annual_target'   => 'nullable|numeric',
            'weight'          => 'nullable|numeric',
            'section'         => 'nullable|string|max:20',
            'strategic_issue' => 'nullable|string',
            'strategic_goal'  => 'nullable|string',
            'kra_group'       => 'nullable|string|max:150',
            'outcomes'        => 'nullable|string',
            'strategic_objective' => 'nullable|string',
            'q1' => 'nullable|boolean',
            'q2' => 'nullable|boolean',
            'q3' => 'nullable|boolean',
            'q4' => 'nullable|boolean',
            'fy_start_year' => 'required|integer|min:2020|max:2100',
            'active'        => 'nullable|boolean',
            'drive_folder'  => ['nullable', 'string', new GoogleDriveFolder],
            'campuses'      => 'nullable|array',
            'campuses.*'    => 'integer|exists:campuses,id',
            'recipients'    => 'nullable|array',
            'recipients.*'  => 'nullable|integer|exists:recipients,id',
        ]);
    }

    private function extractDrive(array &$data): void
    {
        $input = $data['drive_folder'] ?? null;

        $id = GoogleDriveService::parseFolderId($input);
        if ($id) {
            $data['drive_folder_id']  = $id;
            $data['drive_folder_url'] = str_starts_with((string) $input, 'http')
                ? $input
                : GoogleDriveService::folderUrl($id);
        } else {
            $data['drive_folder_id']  = null;
            $data['drive_folder_url'] = null;
        }

        unset($data['drive_folder']);
    }

    private function syncCampuses(PerformanceItem $item, Request $request): void
    {
        $campusIds = $request->input('campuses', []);
        $recipients = $request->input('recipients', []);

        $pivot = [];
        foreach ($campusIds as $campusId) {
            $recipientId = $recipients[$campusId] ?? null;
            $pivot[$campusId] = ['recipient_id' => $recipientId ?: null];
        }

        $item->campuses()->sync($pivot);
    }
}