<?php

namespace App\Http\Middleware;

use App\Models\Users\Databases\Services\UserApiService;
use App\Models\Wallets\Databases\Services\WalletUserApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Traits\Wallets\Auth\WalletUserAuthLoginTrait;

class VerifyWalletMemberApi
{
    use WalletUserAuthLoginTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     *
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {

        // jwt
        if ($request->bearerToken()) {
            $tokenPayload = JWT::decode($request->bearerToken(), new Key(config('app.name'), 'HS256'));
            // toArray
            $tokenPayload = $tokenPayload ? json_decode(json_encode($tokenPayload), 1) : [];
            // 管理者
            if (!empty($tokenPayload['user']['id'])) {
                $userId = Crypt::decryptString($tokenPayload['user']['id']);
                $user = app(UserApiService::class)->find($userId);
                if ($user && $user->wallet_users) {
                    $request->merge([
                        'wallet_user' => $user->wallet_users->keyBy('wallet_id'),
                    ]);
                    return $next($request);
                }
            }
            // 帳簿使用者
            if (!empty($tokenPayload['wallet_user']['id'])) {
                $userId = Crypt::decryptString($tokenPayload['wallet_user']['id']);
                $user = app(WalletUserApiService::class)->getWalletUserByWalletUserId($userId);
                if ($user) {
                    $request->merge([
                        'wallet_user' => $user->keyBy('wallet_id'),
                    ]);
                    return $next($request);
                }
            }
        }

        $member_token = $request->member_token;

        if ($member_token == null) {
            return response()->json([
                'status'  => false,
                'code'    => 400,
                'message' => '請帶入 member_token',
                'data'    => [],
            ]);
        }

        if ($this->checkToken($member_token) === false) {
            Log::channel('token')->info(sprintf("Verify token is empty info : %s ", $request->member_token));
            return response()->json([
                'status'  => false,
                'code'    => 403,
                'message' => "請重新登入",
                'data'    => [],
            ]);
        }
        # 取得快取資料
        $LoginCache = Cache::get($this->getCacheKey($member_token));
        # 若有新增請記得至 ResponseApiServiceProvider 排除
        $request->merge([
            'wallet_user' => $LoginCache,
        ]);

        return $next($request);
    }
}
