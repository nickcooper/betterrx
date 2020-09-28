<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * Displays json response of image links.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Get session image links
        $sessionImageData = $request->session()->get('images');

        //If session image links is empty set to empty array
        $imageData = $sessionImageData ?? [];

        //Sort images links by time descending
        usort($imageData, function ($a, $b) {
            return $a['time'] <= $b['time'];
        });

        //Return json array
        return response()->json(['images' => $imageData]);
    }

    /**
     * Stores image into remote service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Create image validation
        $validator = Validator::make($request->all(), [
            'image' => 'image'
        ]);

        // Check to see if validation fails or passes
        if ($validator->fails()) {
            // Redirect errors to frontend
            return response()->json(['error' => $validator->errors()->getMessages()]);
        }

        //Encode image into png format
        $encoded = \Image::make($request['image'])->encode('png');

        //Send image to remote service
        $response = Http::attach(
            'imageData',
            base64_encode($encoded)
        )->post('https://test.rxflodev.com');

        //Decode json response into php
        $response = json_decode($response, true);

        //If successful add to session, else return error
        if ($response['status'] == 'success') {
            $request->session()->push('images', ['time' => time(), 'url' => $response['url']]);
            return response()->json(['success' => 'You have successfully uploaded an image'], 200);
        } else {
            return response()->json(['error' => 'Error saving Image'], 200);
        }

    }
}
