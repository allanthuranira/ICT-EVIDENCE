<div style="font-family:Arial,sans-serif;max-width:640px">
    <h3>PC & Workplan Quarterly Status Report Required</h3>
    <p>Dear {{ $recipientName ?? 'Campus Representative' }},</p>
    <p>Please fill the quarterly implementation status for the item below:</p>
    <table style="border-collapse:collapse">
        <tr><td style="padding:4px 12px 4px 0"><strong>Item:</strong></td><td>{{ $item->code }} - {{ $item->name }}</td></tr>
        <tr><td style="padding:4px 12px 4px 0"><strong>Campus/HQ:</strong></td><td>{{ $campus->name }}</td></tr>
        <tr><td style="padding:4px 12px 4px 0"><strong>Quarter:</strong></td><td>Q{{ $quarter }}, FY {{ $fy }}/{{ $fy + 1 }}</td></tr>
    </table>
    <p>Indicate and explain the status level target for the quarter vs actual,
       variance, and remarks, plus cumulative achievement.</p>
    <p><a href="{{ $submitUrl }}"
          style="background:#1a1a2e;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px">
          Fill Quarterly Status</a></p>
    <hr>
    <p style="font-size:12px;color:#777">{{ $disclaimer }}</p>
</div>
