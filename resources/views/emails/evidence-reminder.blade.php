<div style="font-family:Arial,sans-serif;max-width:640px">
    <h3>KSG PC & Workplan Evidence Required</h3>
    <p>Dear Campus Representative,</p>
    <p>This is a reminder to submit evidence for the item below:</p>
    <table style="border-collapse:collapse">
        <tr><td style="padding:4px 12px 4px 0"><strong>Item:</strong></td><td>{{ $item->code }} - {{ $item->name }}</td></tr>
        <tr><td style="padding:4px 12px 4px 0"><strong>Campus/HQ:</strong></td><td>{{ $campus->name }}</td></tr>
        <tr><td style="padding:4px 12px 4px 0"><strong>Quarter:</strong></td><td>Q{{ $quarter }}, FY {{ $fy }}/{{ $fy + 1 }}</td></tr>
    </table>
    <p><a href="{{ $submitUrl }}"
          style="background:#1a1a2e;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px">
          Submit Evidence</a></p>
    <p>Uploaded files are stored in the departmental Google Drive under the folder
       <strong>{{ $item->code }} - {{ $item->name }}</strong>, named e.g.
       <strong>{{ $item->code }}_{{ $campus->name }}_Q{{ $quarter }}_filename.pdf</strong>.</p>
    <hr>
    <p style="font-size:12px;color:#777">{{ $disclaimer }}</p>
</div>
