<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    private function range(Request $r): array
    {
        $from = $r->query('from') ? Carbon::parse($r->query('from'))->startOfDay() : null;
        $to   = $r->query('to')   ? Carbon::parse($r->query('to'))->endOfDay()     : null;
        return [$from, $to];
    }

    private function applyRange($query, $from, $to, string $column = 'created_at')
    {
        if ($from) { $query->where($column, '>=', $from); }
        if ($to)   { $query->where($column, '<=', $to); }
        return $query;
    }

    // Overview cards
    public function overview(Request $r)
    {
        [$from, $to] = $this->range($r);

        $usersQ = $this->applyRange(User::query(), $from, $to);
        $examsQ = $this->applyRange(Exam::query(), $from, $to);

        $usersCount = (clone $usersQ)->count();
        $examsCount = (clone $examsQ)->count();

        $avgScore  = round((clone $examsQ)->avg('score') ?? 0, 2);
        $passScore = 50; // adjust if needed
        $passRate  = (clone $examsQ)->selectRaw('AVG(score >= ?) as r', [$passScore])->value('r');
        $passRate  = round(($passRate ?? 0) * 100, 2);

        return response()->json([
            'total_users'   => $usersCount,
            'total_exams'   => $examsCount,
            'avg_score'     => $avgScore,
            'pass_rate_pct' => $passRate,
        ]);
    }

    // Exams by type
    public function examsByType(Request $r)
    {
        [$from, $to] = $this->range($r);

        $rows = Exam::query()
            ->when($from, fn($q)=>$q->where('created_at','>=',$from))
            ->when($to,   fn($q)=>$q->where('created_at','<=',$to))
            ->select('exam_type')
            ->selectRaw('COUNT(*) as exams')
            ->selectRaw('ROUND(AVG(score),2) as avg_score')
            ->groupBy('exam_type')
            ->get();

        return response()->json($rows);
    }

    // Scores by grade
    public function scoresByGrade(Request $r)
    {
        [$from, $to] = $this->range($r);

        $rows = Exam::query()
            ->when($from, fn($q)=>$q->where('created_at','>=',$from))
            ->when($to,   fn($q)=>$q->where('created_at','<=',$to))
            ->select('grade')
            ->selectRaw('ROUND(AVG(score),2) as avg_score')
            ->groupBy('grade')
            ->orderBy('grade')
            ->get();

        return response()->json($rows);
    }
}
