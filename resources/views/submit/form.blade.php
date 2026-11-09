<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Submit - {{ $item->code }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-4" style="max-width:760px">
<div class="card shadow"><div class="card-body">
    <h5>{{ $item->code }} - {{ $item->name }}</h5>
    <p class="text-muted">{{ $campus->name }} &bull; Quarter {{ $quarter }}, FY {{ $fy }}/{{ $fy + 1 }}
        &bull; {{ $kind === 'evidence' ? 'Evidence Submission' : 'Quarterly Status Report' }}</p>

    @if($already)
        <div class="alert alert-info">This item has already been submitted for this quarter. Reminder emails have stopped.</div>
    @else
        @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
        <form method="post" action="{{ route('submit.store', $token) }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3"><label class="form-label">Your Email</label>
                <input type="email" name="submitted_by_email" class="form-control" required></div>

            @if($kind === 'evidence')
                <div class="mb-3"><label class="form-label">Evidence File(s) (max 50MB each)</label>
                    <input type="file" name="files[]" class="form-control" multiple required></div>
                <div class="mb-3"><label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea></div>
            @else
                <div class="row g-2 mb-2">
                    <div class="col"><label class="form-label">Quarter Target</label>
                        <input name="quarter_target" type="number" step="0.01" class="form-control"></div>
                    <div class="col"><label class="form-label">Actual</label>
                        <input name="quarter_actual" type="number" step="0.01" class="form-control"></div>
                    <div class="col"><label class="form-label">Variance</label>
                        <input name="quarter_variance" type="number" step="0.01" class="form-control"></div>
                </div>
                <div class="mb-2"><label class="form-label">Q Remarks / Explanation & Mitigation</label>
                    <textarea name="quarter_remarks" class="form-control" rows="3"></textarea></div>
                <div class="row g-2 mb-2">
                    <div class="col"><label class="form-label">Cumulative Target</label>
                        <input name="cumulative_target" type="number" step="0.01" class="form-control"></div>
                    <div class="col"><label class="form-label">Cumulative Actual</label>
                        <input name="cumulative_actual" type="number" step="0.01" class="form-control"></div>
                    <div class="col"><label class="form-label">Cumulative Variance</label>
                        <input name="cumulative_variance" type="number" step="0.01" class="form-control"></div>
                </div>
                <div class="mb-2"><label class="form-label">Cumulative Remarks</label>
                    <textarea name="cumulative_remarks" class="form-control" rows="2"></textarea></div>
                <div class="mb-3"><label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="done">Done</option>
                        <option value="initiated">Initiated</option>
                        <option value="evidence_available">Evidence Available</option>
                        <option value="not_done">Not Done</option>
                    </select></div>
            @endif
            <button class="btn btn-dark">Submit</button>
        </form>
    @endif
</div></div></div></body></html>
