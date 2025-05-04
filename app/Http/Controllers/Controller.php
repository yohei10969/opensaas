<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected const SIGNIN = 'signin';
    protected const SIGNUP = 'signup';

    /**
     * 現在認証されているユーザーのUUIDを取得します。
     * 認証されていない場合は null を返します。
     *
     * @return string|null ユーザーのUUID、または null
     */
    protected function getAuthenticatedUserUuid(): ?string
    {
        // Auth::user() で認証済みユーザーを取得
        $user = Auth::user();

        // ユーザーが認証されており、かつ uuid プロパティを持っているか確認
        if ($user && isset($user->uuid)) return $user->uuid;

        // 認証されていない、または uuid がない場合は null を返す
        return null;
    }
}
