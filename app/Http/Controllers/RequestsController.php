<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Requests;
use App\Review;
use App\Offer;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class RequestsController extends Controller
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
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'offer_id' => 'required|integer',
            'price' => 'required|numeric',
            'comment' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        // store request
        $input = $request->all();
        $input['stylist_id'] = Auth::user()->id;
        $request = Requests::create($input);
        $request->offer_id = (int)$request->offer_id;
        $request->price = floatval($request->price);

        // update offer
        if (Requests::where('offer_id', $input['offer_id'])->count() >= env('REQUEST_MAX')) {
            $offer = Offer::find($input['offer_id']);
            $offer->is_closed = true;
            $offer->save();
        }

        return response()->json(['success' => $request], $this->successStatus);
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id = 0)
    {
        if ($id == 0) {
            $all = DB::table('requests as r')
            ->select('r.id as request_id', 'u.name', 'u.image_url')
            ->where('o.cx_id', Auth::user()->id)
            ->where('r.is_matched', false)
            ->join('offers as o', 'o.id', '=', 'r.offer_id')
            ->join('users as u', 'u.id', '=', 'r.stylist_id')
            ->get();
            return response()->json(['success' => $all], $this->successStatus);
        } else {
            // request
            $requests = Requests::find($id);
            // stylist
            $user = User::find($requests->stylist_id);
            $stylist['name'] = $user->name;
            $stylist['image_url'] = $user->image_url;
            $stylist['status_comment'] = $user->status_comment;
            $stylist['salon_name'] = $user->salon_name;
            $stylist['salon_address'] = $user->salon_address;
            $stylist['salon_location'] = $user->getSalonLocation();
            // review
            $_reviews = Review::where('deal_user_id', $user->id);
            $reviews = $_reviews->get();
            $reviews['average'] = floor($_reviews->avg('star'));


            return response()->json(['success' => $requests, 'stylist' => $stylist, 'reviews' => $reviews], $this->successStatus);
        }
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id = 0)
    {
        $requests = Requests::find($id);
        $requests->is_matched = true;
        $requests->save();
        return response()->json(['success' => true], $this->successStatus);
    }

    /**
     * reservation as cx
     *
     * @return \Illuminate\Http\Response
     */
    public function reservation(Request $request, $id = 0)
    {
        if ($id == 0) { // without id
            // Validation
            $validator = Validator::make($request->all(), [
                'date_time' => 'required|date_format:Y-m-d H:i:s',
            ]);

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }

            $input = $request->all();
            $results = Offer::where('cx_id', Auth::user()->id)->where('date_time', '>', $input['date_time'])->orderBy('id', 'desc')->first();
            $user = User::find($results->cx_id);
            if (isset($results['from_location'])) {
                $results['from_location'] = Offer::getLocationAttribute($results['from_location']);
            }
            $results['cx_name'] = $user->name;
            $results['cx_image_url'] = $user->image_url;
            return response()->json(['success' => $results], $this->successStatus);
        } else { // with id
            $input = $request->all();
            $results = Offer::where('cx_id', Auth::user()->id)->where('id', $id)->orderBy('id', 'desc')->first();
            $user = User::find($results->cx_id);
            if (isset($results['from_location'])) {
                $results['from_location'] = Offer::getLocationAttribute($results['from_location']);
            }
            $results['cx_name'] = $user->name;
            $results['cx_image_url'] = $user->image_url;
            return response()->json(['success' => $results], $this->successStatus);
        }
    }

    /**
     * reservation as stylist
     *
     * @return \Illuminate\Http\Response
     */
    public function reservationList(Request $request)
    {
        $results = DB::table('requests as r')
        ->select('o.id as offer_id', 'r.price', 'o.date_time', 'o.menu', 'u.image_url', 'u.name')
        ->where('r.stylist_id', Auth::user()->id)
        ->join('offers as o', 'o.id', '=', 'r.offer_id')
        ->join('users as u', 'u.id', '=', 'o.cx_id')
        ->get();

        return response()->json(['success' => $results], $this->successStatus);
    }

}
