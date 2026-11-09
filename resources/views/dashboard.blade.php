@extends('layout')
@section('content')
<h4>Dashboard - Q{{ $quarter }}, FY {{ $fy }}/{{ $fy + 1 }}</h4>
<p class="text-muted">Submission progress per campus (evidence files + quarterly status reports)</p>
<div class="row">
    @foreach($cards as $card)
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
                <strong>{{ $card['campus']->name }}</strong>
                <span class="badge bg-secondary">{{ $card['evidence'] }}/{{ $card['total'] }} evidence</span>
            </div>
            <div class="card-body">
                <div class="mb-1 small">Evidence: {{ $card['evidence_pct'] }}%</div>
                <div class="progress mb-2" style="height:10px">
                    <div class="progress-bar bg-success" style="width:{{ $card['evidence_pct'] }}%"></div>
                </div>
                <div class="mb-1 small">Reports: {{ $card['report_pct'] }}%</div>
                <div class="progress mb-2" style="height:10px">
                    <div class="progress-bar bg-primary" style="width:{{ $card['report_pct'] }}%"></div>
                </div>
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Item</th><th>Evidence</th><th>Report</th></tr></thead>
                    <tbody>
                    @foreach($card['items'] as $row)
                        <tr>
                            <td>{{ $row['item']->code }} - {{ Str::limit($row['item']->name, 45) }}</td>
                            <td>{!! $row['evidence'] ? '&#10004;' : '<span class="text-danger">&#10008;</span>' !!}</td>
                            <td>{!! $row['report'] ? '&#10004;' : '<span class="text-danger">&#10008;</span>' !!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
