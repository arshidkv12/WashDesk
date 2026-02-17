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
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

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
            'company_logo'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048|dimensions:max_width=300,max_height=300',
            'company_address' => 'nullable|string|max:1000',
        ],[
            'company_logo.dimensions' => 'Logo must be maximum 300 × 300 pixels.',
        ]);

        $user = $request->user();

        if ($request->hasFile('company_logo')) {

            $file = $request->file('company_logo');
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/logos'), $filename);
            $validated['company_logo'] = $filename;

            $grayImagepath = public_path('uploads/logos/gray-' . $filename);
            $filePath = public_path('uploads/logos/' . $filename);
            $manager = new ImageManager(new Driver());
            $image = $manager->read($filePath);
            $image->resize(384, null, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                })
                ->greyscale()
                ->contrast(40)
                ->brightness(10);   

            $image->save($grayImagepath, 100);
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
            $grayImagepath = public_path('uploads/logos/gray-' . $user->company_logo);

            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if (file_exists($grayImagepath)) {
                unlink($grayImagepath);
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
