<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Review;
use Validator;

class ReviewController extends Controller
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
        return response()->json(['success' => 0], $this->successStatus);
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
            'deal_user_id' => 'required|integer',
            'star' => 'required|numeric',
            'comment' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        // Registration
        $input = $request->all();
        $input['write_user_id'] = Auth::user()->id;
        $review = Review::create($input);
        return response()->json(['success' => $review], $this->successStatus);
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(['success' => 1], $this->successStatus);
    }

}
