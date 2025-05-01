<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // UUIDを自動生成する場合に追加
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable; // 通知機能を使う場合に追加

class Individual extends Authenticatable
{
    use HasFactory, HasUuids;

    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'individuals';

    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 'uuid'; // 主キーのカラム名を指定

    /**
     * 主キーが自動増分されるか
     *
     * @var bool
     */
    public $incrementing = false; // UUIDなので自動増分はしない

    /**
     * 主キーの型
     *
     * @var string
     */
    protected $keyType = 'string'; // 主キーは文字列型

    /**
     * 一括代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * ネイティブな型にキャストする属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime', // メール認証機能を追加する場合に設定
        'password' => 'hashed', // パスワードを自動的にハッシュ化
        // 'remember_token', // 認証関連のトークンを隠蔽する場合に設定
    ];
}
