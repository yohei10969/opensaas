<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personal extends Model
{
    /**
     * モデルに関連付けるテーブル
     *
     * @var string
     */
    protected $table = 'personals';

    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 'individuals_uuid';

    /**
     * 主キーの型
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * インクリメント
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * マスアサインメント可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'alphabet',
        'japanese',
        'username',
        'penname',
        'bio',
        'individuals_uuid',
    ];

    /**
     * JSONシリアライズ時に隠蔽する属性
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 必要に応じて隠蔽したい属性を追加
    ];

    /**
     * ネイティブなタイプへキャストする属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'individuals_uuid' => 'string',
    ];

    /**
     * この Personal プロフィールが属する Individual を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function individual(): BelongsTo
    {
        // 関連するモデル (Individual) の名前空間と主キー名を適宜修正してください
        return $this->belongsTo(Individual::class, 'individuals_uuid', 'uuid');
    }
}
