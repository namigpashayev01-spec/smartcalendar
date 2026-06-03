<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return redirect()->route('calendar.month', [
            'year'  => now()->year,
            'month' => now()->month,
        ]);
    }

    public function show(int $year, int $month)
    {
        $current      = Carbon::create($year, $month, 1);
        $prev         = $current->copy()->subMonth();
        $next         = $current->copy()->addMonth();
        $daysInMonth  = $current->daysInMonth;
        $firstDow     = ($current->dayOfWeek + 6) % 7; // Mon=0

        $posts = Post::with('media')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->groupBy(fn($p) => $p->date->format('Y-m-d'));

        return view('calendar.show', compact(
            'current', 'prev', 'next',
            'daysInMonth', 'firstDow', 'posts',
            'year', 'month'
        ));
    }
}
