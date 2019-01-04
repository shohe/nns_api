<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Offer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
        $user = Auth::user();
        if (isset($user->salon_location)) {
            $user->salon_location = $user->getSalonLocation();
        }
        return response()->json(['success'=>$user], $this->successStatus);
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
            'is_stylist' => 'boolean',
            'salon_name' => 'string|max:30',
            'salon_address' => 'string|max:100',
            'salon_location_lat' => 'numeric',
            'salon_location_lng' => 'numeric',
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
        if (isset($input['is_stylist'])) { $user->is_stylist = $input['is_stylist']; }
        if (isset($input['salon_name'])) { $user->salon_name = $input['salon_name']; }
        if (isset($input['salon_address'])) { $user->salon_address = $input['salon_address']; }
        if (isset($input['salon_location_lat']) && isset($input['salon_location_lng'])) {
            $geoArray = array('lat' => $input['salon_location_lat'], 'lng' => $input['salon_location_lng']);
            $user->setSalonLocation($geoArray);
        }
        $user->save();
        $_user = User::find($user->id);
        if (isset($_user->salon_location)) {
            $_user->salon_location = $_user->getSalonLocation();
        }
        return response()->json(['success' => $_user], $this->successStatus);
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

        $disk = Storage::disk('s3');
        $user = Auth::user();
        $resouce = "https://s3-ap-northeast-1.amazonaws.com/nns-jp";

        // delete image if it's set already
        if ($user->image_url != "") {
            $url = str_replace($resouce, '', $user->image_url);
            $disk = Storage::disk('s3');
            $disk->delete($url);
        }

        // store image
        $image = $request->file('image');
        $path = $disk->putFile('users', $image, 'public');
        $url = $disk->url($path);
        $url = $resouce.$url;
        return response()->json(['success' => $url], $this->successStatus);
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function dayCounter(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'date_time' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $input = $request->all();
        $offer = DB::table('offers as o')
        ->select('o.date_time')
        ->where('o.date_time', '>', $input['date_time'])
        ->where('o.cx_id', Auth::user()->id)
        ->where('r.is_matched', true)
        ->join('requests as r', 'r.offer_id', '=', 'o.id');

        if ($offer->count() > 0) {
            $date = strtotime($offer->first()->date_time);
            $today = strtotime($input['date_time']);
            $dif = $this->time_diff($today, $date);
            return response()->json(['success' => $dif, 'origin' => $offer->first()->date_time, 'matched' => true], $this->successStatus);
        } else {
            $offer = DB::table('offers as o')
            ->select('o.date_time')
            ->where('o.date_time', '>', $input['date_time'])
            // ->where('o.is_closed', false)
            ->where('o.cx_id', Auth::user()->id);
            if ($offer->count() > 0) {
                $date = strtotime($offer->first()->date_time);
                $today = strtotime($input['date_time']);
                $dif = $this->time_diff($today, $date);
                return response()->json(['success' => $dif, 'origin' => $offer->first()->date_time, 'matched' => false], $this->successStatus);
            }
        }
        return response()->json(['success' => -1, 'origin' => $offer->first()->date_time], $this->successStatus);
    }

    private function time_diff($time_from, $time_to)
    {
        // get secoundly differences.
        $dif = $time_to - $time_from;
        // get daily differences.
        $dif_days = (strtotime(date("Y-m-d", $dif)) - strtotime("1970-01-01")) / 86400;
        return $dif_days;
    }

}
