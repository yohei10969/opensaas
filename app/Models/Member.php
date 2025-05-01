<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\Pivot; // 複合主キーを持つ中間テーブルとして扱う場合

class Member extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル
     *
     * @var string
     */
    protected $table = 'members';

    /**
     * 主キーが自動増分ではないことを示す
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * モデルの主キー
     * Eloquent は複合主キーを直接サポートしていませんが、
     * リレーションシップ定義などで内部的に利用されます。
     * find() など一部メソッドは期待通りに動作しない可能性があります。
     *
     * @var array
     */
    protected $primaryKey = ['individuals_uuid', 'workspaces_uuid'];

    /**
     * マスアサインメント可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'individuals_uuid',
        'workspaces_uuid',
    ];

    /**
     * モデルのタイムスタンプを更新するかどうか
     *
     * @var bool
     */
    public $timestamps = true; // マイグレーションで timestamps() を使用しているため true

    /**
     * このメンバーに関連する Individual を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function individual(): BelongsTo
    {
        // 外部キー名を明示的に指定
        return $this->belongsTo(Individual::class, 'individuals_uuid', 'uuid');
    }

    /**
     * このメンバーに関連する Workspace を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workspace(): BelongsTo
    {
        // 外部キー名を明示的に指定
        return $this->belongsTo(Workspace::class, 'workspaces_uuid', 'uuid');
    }
}
