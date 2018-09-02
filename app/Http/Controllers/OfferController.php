<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Offer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        if (isset($input['from_location_lat']) && isset($input['from_location_lng'])) {
            $geoArray = array('lat' => $input['from_location_lat'], 'lng' => $input['from_location_lng']);
            $input['from_location'] = Offer::castToGeometry($geoArray);
        }
        $offer = Offer::create($input);

        $_offer = Offer::find($offer->id);
        if (!isset($_offer->stylist_id)) {
            $_offer->from_location = Offer::getLocationAttribute($_offer->from_location);
        }
        return response()->json(['success' => $_offer], $this->successStatus);
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id = 0)
    {
        if ($id == 0) {
            $user = Auth::user();
            if (!$user->is_stylist) {
                return response()->json(['error'=>'this user is not stylist'], 401);
            }

            // nominated
            $nominatedOffer = DB::table('offers as o')
            ->select('o.id as offer_id', 'u.name', 'u.image_url')
            ->where('o.is_closed', false)
            ->where('o.stylist_id', $user->id)
            ->join('users as u', 'u.id', '=', 'o.cx_id');

            // match required
            $SL = $user->getSalonLocation();
            $matchRequiredOffer = DB::table('offers as o')
            ->select('o.id as offer_id', 'u.name', 'u.image_url')
            ->where('o.is_closed', false)
            ->where('o.stylist_id', null)
            ->whereRaw("GLENGTH(GEOMFROMTEXT(CONCAT('LINESTRING(',?,' ',?, ',',X(from_location),' ',Y(from_location),')'))) <= o.distance_range * ?", [$SL['lat'], $SL['lng'], $this->oneKm])
            ->join('users as u', 'u.id', '=', 'o.cx_id');

            // marge offers
            return response()->json(['nominated' => $nominatedOffer->get(), 'located' => $matchRequiredOffer->get()], $this->successStatus);
            //$results = $nominatedOffer->union($matchRequiredOffer)->get();
            //return response()->json(['success' => $results], $this->successStatus);
        } else {
            $results = Offer::find($id);
            $user = User::find($results->cx_id);
            if (!isset($results->stylist_id)) {
                $results['from_location'] = Offer::getLocationAttribute($results->from_location);
            }
            $results['cx_name'] = $user->name;
            $results['cx_image_url'] = $user->image_url;
            return response()->json(['success' => $results], $this->successStatus);
        }
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function offerHistoryList()
    {
        $results = DB::table('offers as o')
        ->select('r.price', 'o.date_time', 'o.menu', 'u.image_url', 'u.name')
        ->where('o.cx_id', Auth::user()->id)
        ->where('o.is_closed', true)
        ->where('r.is_matched', true)
        ->join('requests as r', 'o.id', '=', 'r.offer_id')
        ->join('users as u', 'u.id', '=', 'r.stylist_id')
        ->get();
        return response()->json(['success' => $results], $this->successStatus);
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function offerHistory($id)
    {
        $results = DB::table('offers as o')
        ->select('u.id', 'u.name', 'u.image_url', 'u.status_comment', 'o.menu', 'r.price', 'o.date_time', 'o.hair_type', 'r.comment')
        ->where('o.id', $id)
        ->where('r.is_matched', true)
        ->join('requests as r', 'o.id', '=', 'r.offer_id')
        ->join('users as u', 'u.id', '=', 'r.stylist_id')
        ->get();
        return response()->json(['success' => $results], $this->successStatus);
    }

}
