<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Offer;
use Illuminate\Support\Facades\Auth;
use Validator;

class OfferController extends Controller
{

    public $successStatus = 200;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'menu' => 'required|string|max:20',
            'price' => 'required|numeric',
            'date_time' => 'required|date_format:Y-m-d H:i:s',
            'distance_range' => 'numeric',
            'from_location_lat' => 'numeric',
            'from_location_lng' => 'numeric',
            'stylist_id' => 'integer',
            'hair_type' => 'required',
            'comment' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        // Registration
        $input = $request->all();
        $user = Auth::user();
        $input['cx_id'] = $user->id;
        $input['charity_id'] = $user->charity_id;
        if (isset($input['from_location_lat']) && isset($input['from_location_lng'])) {
            $geoArray = array('lat' => $input['from_location_lat'], 'lng' => $input['from_location_lng']);
            $input['from_location'] = Offer::castToGeometry($geoArray);
        }
        $offer = Offer::create($input);
        return response()->json(['success' => $offer], $this->successStatus);
    }
}
