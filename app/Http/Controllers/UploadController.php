<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function store(Request $request)
    {   
        $validator = Validator::make(
            $request->all(),
            [
                'upload-files' => ['required'],
                'upload-files.*' => [
                    'file',
                    'mimes:jpg,jpeg,png',
                    'max:5120', // 5MB
                ],
            ],
            [
                'upload-files.required' => 'Please upload at least one image.',
                'upload-files.*.file'   => 'Each upload must be a valid file.',
                'upload-files.*.mimes'  => 'Only JPG, JPEG, or PNG images are allowed.',
                'upload-files.*.max'    => 'Each image must not exceed 5MB.',
            ]
        );


        if ($validator->fails()) {
            return response($validator->errors(), 422 );
        }

        $user_id = Auth::id();
        $files = $request->file('upload-files');  
        $file =  reset( $files );  

        $filename = $user_id . '-' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        $destination = public_path('uploads/images');

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        return response($filename);
    }

    public function destroy(Request $request)
    {
        $filename = $request->getContent();

        $path = public_path("uploads/images/{$filename}");

        if (File::exists($path)) {
            File::delete($path);
        }

        return response()->json(['status' => 'deleted']);
    }
}
