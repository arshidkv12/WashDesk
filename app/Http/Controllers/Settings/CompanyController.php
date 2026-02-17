<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CompanyUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Show the user's company settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Company', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's company information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name'    => 'required|string|max:255',
            'currency_symbol' => 'required|string|max:5',
            'company_logo'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'company_address' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        if ($request->hasFile('company_logo')) {

            $file = $request->file('company_logo');
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/logos'), $filename);

            $validated['company_logo'] = $filename;
        }

        $user->fill($validated);
        $user->save();

        return to_route('company.edit');
    }

    public function removeLogo(Request $request)
    {
        $user = $request->user();
        if ($user->company_logo) {

            $filePath = public_path('uploads/logos/' . $user->company_logo);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $user->company_logo = null;
            $user->save();
        }
    }

    /**
     * Delete the user's company.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
