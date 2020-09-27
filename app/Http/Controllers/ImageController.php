<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sessionImages = $request->session()->get('images');
        $images = $sessionImages ? $sessionImages : [];
        Log::info($images);

        usort($images, function ($a, $b) {
            return $a['time'] <= $b['time'];
        });

        return response()->json(['images' => $images]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->get('image')) {

            // Now pass the input and rules into the validator
            $validator = Validator::make(
                array('image' => $request->get('image')),
                array('image' => 'mimes:jpeg,jpg,png,gif|required|max:10000' // max 10000kb
            ));

            // Check to see if validation fails or passes
            if ($validator->fails()) {
                // Redirect or return json to frontend with a helpful message to inform the user 
                // that the provided file was not an adequate type
                return response()->json(['error' => $validator->errors()->getMessages()], 400);
            }




            $encoded = \Image::make($request->get('image'))->encode('png');

            $response = Http::attach(
                'imageData',
                base64_encode($encoded)
            )->post('https://test.rxflodev.com');

            $response = json_decode($response, true);

            if ($response['status'] == 'success') {
                $request->session()->push('images', ['time' => time(), 'url' => $response['url']]);
                Log::info($request->session()->get('images'));
            } else {
                Log::info('error');
            }
        }
        return response()->json(['success' => 'You have successfully uploaded an image'], 200);
    }
}
