<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\MonthlyClosingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonthlyClosingController extends Controller
{
    public function index(): Response
    {
        $setting = SystemSetting::instance();

        return Inertia::render('MonthlyClosing/Index', [
            'currentYm' => $setting->closing_ym,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ym' => ['required', 'date_format:Y-m'],
        ]);

        MonthlyClosingService::confirm($validated['ym']);

        return back()->with('success', $validated['ym'] . ' の月次確定処理が完了しました。');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ym' => ['required', 'date_format:Y-m'],
        ]);

        MonthlyClosingService::cancel($validated['ym']);

        return back()->with('success', $validated['ym'] . ' の月次取消処理が完了しました。');
    }
}
