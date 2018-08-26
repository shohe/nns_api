<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
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
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        // Registration
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['id'] =  $user->id;
        return response()->json(['success'=>$success], $this->successStatus);
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function checkEmail(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        // check email
        $input = $request->all();
        if (User::where('email', '=', $input['email'])->count() > 0) {
            return response()->json(['error'=>'already exist'], 401);
        } else {
            return response()->json(['success'=>$input['email']], $this->successStatus);
        }
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function checkPassword(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        // check password
        $input = $request->all();
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        } else {
            return response()->json(['success'=>$input['password']], $this->successStatus);
        }
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        // check user
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }
}
