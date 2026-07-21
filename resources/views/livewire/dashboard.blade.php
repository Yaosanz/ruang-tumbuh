<div class="dashboard">
    <header class="admin-header">
        <h1>Dashboard</h1>
        <div class="header-actions">
            <a href="{{ route('quizzes.index') }}" class="text-link">Katalog Quiz</a>
            <a href="{{ route('logout') }}" class="text-link danger"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
        </div>
    </header>

    <div class="stats">
        <div>
            <span>Total Dikerjakan</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>
        <div>
            <span>Rata-rata Skor</span>
            <strong>{{ $stats['total'] ? number_format($stats['avg_score'], 0) : 0 }}%</strong>
        </div>
        <div>
            <span>Quiz Diikuti</span>
            <strong>{{ count($stats['quizzes']) }}</strong>
        </div>
    </div>

    @if ($submissions->isEmpty())
        <div class="empty-state">
            <p>Kamu belum mengerjakan quiz atau assessment apapun.</p>
            <a href="{{ route('quizzes.index') }}" class="button">Jelajahi Quiz</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Quiz</th>
                        <th>Skor</th>
                        <th>Maks</th>
                        <th>Persentase</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($submissions as $submission)
                        <tr>
                            <td><strong>{{ $submission->quiz->title }}</strong></td>
                            <td>{{ $submission->score }}</td>
                            <td>{{ $submission->max_score }}</td>
                            <td>{{ $submission->percentage }}%</td>
                            <td><small>{{ $submission->created_at->format('d M Y') }}</small></td>
                            <td>
                                <a href="{{ route('results.show', $submission) }}" class="text-link">Lihat</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

