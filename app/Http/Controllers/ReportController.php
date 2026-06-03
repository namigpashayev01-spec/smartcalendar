<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private array $statuses = [
        'planned'   => 'Planlaşdırılıb',
        'progress'  => 'Hazırlanır',
        'pending'   => 'Təsdiq gözləyir',
        'published' => 'Paylaşılıb',
    ];

    public function index(Request $request)
    {
        $posts    = $this->filtered($request);
        $companies = Post::distinct()->orderBy('company')->pluck('company');

        return view('report.index', [
            'posts'     => $posts,
            'statuses'  => $this->statuses,
            'companies' => $companies,
            'filters'   => $request->only(['company','status','from','to','search']),
        ]);
    }

    public function export(Request $request)
    {
        $posts = $this->filtered($request);

        $header = ['Tarix','Şirkət','Status','Təsvir','Media sayı'];
        $rows   = $posts->map(fn($p) => [
            $p->date->format('Y-m-d'),
            $p->company,
            $this->statuses[$p->status] ?? $p->status,
            str_replace(["\r\n","\n"], ' ', $p->description ?? ''),
            $p->media->count(),
        ]);

        $csv = collect([$header])->merge($rows)
            ->map(fn($r) => implode(',', array_map(fn($v) => str_contains($v,',') ? '"'.$v.'"' : $v, $r)))
            ->implode("\r\n");

        return response("\xEF\xBB\xBF" . $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="hesabat_'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    private function filtered(Request $request)
    {
        return Post::with('media')
            ->when($request->company, fn($q,$v) => $q->where('company', $v))
            ->when($request->status,  fn($q,$v) => $q->where('status', $v))
            ->when($request->from,    fn($q,$v) => $q->where('date', '>=', $v))
            ->when($request->to,      fn($q,$v) => $q->where('date', '<=', $v))
            ->when($request->search,  fn($q,$v) => $q->where(function($q) use ($v) {
                $q->where('company','like',"%$v%")->orWhere('description','like',"%$v%");
            }))
            ->orderBy('date')
            ->get();
    }
}
