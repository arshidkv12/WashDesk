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
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
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
            'company_logo'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'company_address' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        if ($request->hasFile('company_logo')) {

            if( $user->company_logo){
                //Delete old image
                $filePath = public_path('uploads/logos/' . $user->company_logo);
                $grayImagepath = public_path('uploads/logos/gray-' . $user->company_logo);

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                if (file_exists($grayImagepath)) {
                    unlink($grayImagepath);
                }
            }
            
            $file = $request->file('company_logo');
            $filename = uniqid().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/logos'), $filename);
            $validated['company_logo'] = $filename;

            $grayImagepath = public_path('uploads/logos/gray-' . $filename);
            $filePath = public_path('uploads/logos/' . $filename);
            $manager = new ImageManager(new ImagickDriver());
            $image = $manager->read($filePath);

           // Scale for 80mm thermal printer
            $image = $image->scale(width: 576);

            $image->greyscale();

            $image->sharpen(5); 

            /** @var \Imagick $imagick */
            $imagick = $image->core()->native();
            $imagick = $this->alphaRemove($imagick);
        
            // 2. Set to truecolor first (removes any leftover transparency)
            $imagick->setImageType(\Imagick::IMGTYPE_TRUECOLOR);
            
            // 3. Apply threshold (50% gray becomes the cutoff)
            $quantumRange = $imagick->getQuantumRange();
            $threshold = $quantumRange['quantumRangeLong'] * 0.5;
            $imagick->thresholdImage($threshold);
            
            // Optional: Add sharpening after threshold
            $imagick->sharpenImage(1, 0.5);
        

            $image->save($grayImagepath, 100);
        }

        $user->fill($validated);
        $user->save();

        return to_route('company.edit');
    }

    /**
     * Remove transparency by flattening on white background
     * (Copied from escpos-php library)
     */
    private function alphaRemove(\Imagick $im): \Imagick
    {
        $flat = new \Imagick();
        $flat->newImage(
            $im->getImageWidth(), 
            $im->getImageHeight(), 
            'white', 
            $im->getImageFormat()
        );
        $flat->compositeImage($im, \Imagick::COMPOSITE_OVER, 0, 0);
        return $flat;
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
