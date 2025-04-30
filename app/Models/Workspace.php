<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workspace extends Model
{
    use HasUuids; // UUIDを主キーとして利用する

    /**
     * モデルに関連付けるテーブル
     *
     * @var string
     */
    protected $table = 'workspaces';

    /**
     * モデルの主キー
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * 主キーが自動増分されるか
     * UUIDを使用するため false に設定
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 主キーの型
     * UUIDは文字列なので 'string' に設定
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'alphabet',
        'japanese',
        'administrator',
    ];

    /**
     * モデルの日付カラムの保存形式
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 必要に応じて型キャストを定義します
        // 例: 'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * このアカウントの管理者である Individual を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function toAdministrator(): BelongsTo
    {
        // 'administrator' カラムを外部キー、'uuid' を関連先の主キーとして指定
        return $this->belongsTo(Individual::class, 'administrator', 'uuid');
    }
}
