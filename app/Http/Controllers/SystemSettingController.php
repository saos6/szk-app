<?php

namespace App\Http\Controllers;

use App\Http\Requests\SystemSettingRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('SystemSettings/Show', [
            'setting' => SystemSetting::instance(),
        ]);
    }

    public function edit(): Response
    {
        return Inertia::render('SystemSettings/Edit', [
            'setting' => SystemSetting::instance(),
        ]);
    }

    public function update(SystemSettingRequest $request): RedirectResponse
    {
        SystemSetting::instance()->update($request->validated());

        return redirect()->route('system-settings.show')->with('success', '設定を更新しました。');
    }
}
