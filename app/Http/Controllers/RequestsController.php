<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Requests;
use App\Offer;
use Illuminate\Support\Facades\Auth;
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

        // update offer
        if (Requests::where('offer_id', $input['offer_id'])->count() >= env('REQUEST_MAX')) {
            $offer = Offer::find($input['offer_id']);
            $offer->is_closed = true;
            $offer->save();
        }

        return response()->json(['success' => $request], $this->successStatus);
    }
}
