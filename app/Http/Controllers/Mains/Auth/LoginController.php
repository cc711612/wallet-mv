<?php

/**
 * @Author: Roy
 * @DateTime: 2022/6/19 下午 02:53
 */

namespace App\Http\Controllers\Mains\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Facades\Socialite;


/**
 * Class LoginController
 *
 * @package App\Http\Controllers\Mains\Auth
 * @Author: Roy
 * @DateTime: 2022/6/25 下午 08:32
 */
class LoginController extends Controller
{

    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @Author: Roy
     * @DateTime: 2022/6/20 下午 03:16
     */
    public function login(Request $request)
    {
        return view('mains.auth.login');
    }

    public function lineLogin(Request $request)
    {
        return Socialite::driver('line')->redirect();
    }

    public function lineReturn(Request $request)
    {
        $userInfo = Socialite::driver('line')->user();
        $userInfo = json_decode(json_encode($userInfo), 1);
        $userInfo = Arr::only($userInfo, ['id', 'name', 'email', 'avatar']);
        $url = config('services.easysplit.domain') . 'auth/thirdParty/return?' . http_build_query($userInfo);
        return redirect($url);
    }
}
