<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Recipient;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    public function index()
    {
        $recipients = Recipient::with('campus')->orderBy('campus_id')->get();
        return view('recipients.index', compact('recipients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campus_id' => 'required|exists:campuses,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:representative,ict_head',
        ]);
        Recipient::updateOrCreate(
            ['campus_id' => $data['campus_id'], 'email' => $data['email']],
            $data + ['active' => true]
        );
        return back()->with('success', 'Recipient saved.');
    }

    public function update(Request $request, Recipient $recipient)
    {
        $recipient->update($request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:representative,ict_head',
            'active' => 'boolean',
        ]));
        return back()->with('success', 'Recipient updated.');
    }

    public function destroy(Recipient $recipient)
    {
        $recipient->update(['active' => false]);
        return back()->with('success', 'Recipient deactivated.');
    }
}
