<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Review;
use App\User;
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
        // reviews
        $user = Auth::user();
        $result = DB::table('reviews as r')
        ->select('u.name as writer_name', 'r.star', 'r.comment', 'r.created_at')
        ->where('r.deal_user_id', $user->id)
        ->join('users as u', 'u.id', '=', 'r.write_user_id')
        ->get();

        // evaluate
        $reviews = Review::where('deal_user_id', $user->id);
        $evaluate['average'] = floatval($reviews->avg('star'));
        for ($i=1; $i <= 5 ; $i++) { $evaluate[$i] = clone $reviews; }
        for ($i=1; $i <= 5 ; $i++) { $evaluate[$i] = $evaluate[$i]->where('star', $i)->count(); }

        // user
        $_user['image_url'] = $user->image_url;
        $_user['star'] = floor($evaluate['average']);
        $_user['status_comment'] = $user->status_comment;

        return response()->json(['success' => $result, 'evaluate' => $evaluate, 'user' => $_user], $this->successStatus);
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
        $_review = Review::find($review->id);
        return response()->json(['success' => $_review], $this->successStatus);
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // reviews
        $user = User::find($id);
        $result = DB::table('reviews as r')
        ->select('u.name as writer_name', 'r.star', 'r.comment', 'r.created_at')
        ->where('r.deal_user_id', $user->id)
        ->join('users as u', 'u.id', '=', 'r.write_user_id')
        ->get();

        // evaluate
        $reviews = Review::where('deal_user_id', $user->id);
        $evaluate['average'] = floatval($reviews->avg('star'));
        for ($i=1; $i <= 5 ; $i++) { $evaluate[$i] = clone $reviews; }
        for ($i=1; $i <= 5 ; $i++) { $evaluate[$i] = $evaluate[$i]->where('star', $i)->count(); }

        // user
        $_user['image_url'] = $user->image_url;
        $_user['star'] = floor($evaluate['average']);
        $_user['status_comment'] = $user->status_comment;

        return response()->json(['success' => $result, 'evaluate' => $evaluate, 'user' => $_user], $this->successStatus);
    }

}
