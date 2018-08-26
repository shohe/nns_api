<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:8|max:16',
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

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'email' => 'email',
            'password' => 'min:8|max:16',
            'image_url' => 'string',
            'status_comment' => 'string|max:255',
            'charity_id' => 'integer',
            'is_stylist' => 'boolean',
            'salon_name' => 'string|max:30',
            'salon_address' => 'string|max:100',
            'salon_location' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }


        // update user
        $user = Auth::user();
        $input = $request->all();
        if (isset($input['name'])) { $user->name = $input['name']; }
        if (isset($input['email'])) { $user->email = $input['email']; }
        if (isset($input['password'])) { $user->password = $input['password']; }
        if (isset($input['image_url'])) { $user->image_url = $input['image_url']; }
        if (isset($input['status_comment'])) { $user->status_comment = $input['status_comment']; }
        if (isset($input['charity_id'])) { $user->charity_id = $input['charity_id']; }
        if (isset($input['is_stylist'])) { $user->is_stylist = $input['is_stylist']; }
        if (isset($input['salon_name'])) { $user->salon_name = $input['salon_name']; }
        if (isset($input['salon_address'])) { $user->salon_address = $input['salon_address']; }
        if (isset($input['salon_location'])) { $user->salon_location = $input['salon_location']; }
        $user->save();
        return response()->json(['success' => $user], $this->successStatus);
    }


    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function storeImage(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'image' => 'image',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        // store image
        $image = $request->file('image');
        $path = $image->store('users', 's3');
        $url = Storage::disk('s3')->url($path);
        return response()->json(['success' => $url], $this->successStatus);
    }
}
