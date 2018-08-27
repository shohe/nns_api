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
    private $oneKm = 0.0089831601679492;

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
    public function store(Request $request)
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


    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function match(Request $request)
    {
        $user = Auth::user();
        if (!$user->is_stylist) {
            return response()->json(['error'=>'this user is not stylist'], 401);
        }

        // nominated
        $nominatedOffer = Offer::query();
        $nominatedOffer->where('is_closed', false);
        $nominatedOffer->where('stylist_id', $user->id);
        $nominatedOffer->orderBy('id', 'desc');

        // match required
        $SL = $user->getSalonLocation();
        $matchRequiredOffer = Offer::query();
        $matchRequiredOffer->where('is_closed', false);
        $matchRequiredOffer->where('stylist_id', null);
        $matchRequiredOffer->whereRaw("GLENGTH(GEOMFROMTEXT(CONCAT('LINESTRING(',?,' ',?, ',',X(from_location),' ',Y(from_location),')'))) <= distance_range * ?", [$SL['lat'], $SL['lng'], $this->oneKm]);

        // marge offers
        $results = $nominatedOffer->union($matchRequiredOffer)->get();
        return response()->json(['success' => $results], $this->successStatus);
    }
}
