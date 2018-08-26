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
        return response()->json(['status'=>$this->successStatus]);
    }
}
