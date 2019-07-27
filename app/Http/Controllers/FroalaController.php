<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FroalaEditor\Image as FroalaEditor_Image;

class FroalaController extends Controller
{
    public function imageLoad(Request $request)
    {
        $prePath = public_path('storage');
        $response = FroalaEditor_Image::getList($prePath.'/uploads/editor/images/');
        foreach ($response as &$value) {
            $value->url = str_replace($prePath, '/storage', $value->url);
            $value->thumb = str_replace($prePath, '/storage', $value->thumb);
        }

        return $response;
    }

    public function imageUpload(Request $request)
    {
        // Store the image.
        // https://www.froala.com/wysiwyg-editor/docs/sdks/php/image-server-upload
        $path = $request->file('file')->store('public/uploads/editor/images');
        ///public/uploads/editor/images/AAAKRTgHS1UlwCstWJKLxCILTOzbmVHnEbNU50cg.gif
        return ['link'=> str_replace('public/uploads', '/storage/uploads', $path)];
    }

    public function imageDelete(Request $request)
    {
        $path = $request->input('src');
        $filePath = public_path('storage').str_replace('/storage', '', $path);
        // Check if file exists.
        if (file_exists($filePath)) {
            // Delete file.
            return unlink($filePath);
        }

        return 'Success';
    }
}
