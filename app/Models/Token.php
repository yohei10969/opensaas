<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Token extends Model
{
    use HasFactory; // UUIDを主キーとして使用

    /**
     * モデルの主キーとして使用されるカラム
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * 主キーがインクリメントされるか
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 主キーの型
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * マスアサインメント可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'workspaces_uuid',
        'issuer_uuid',
        'holder_uuid',
        'value',
        'interest_rate',
        'applicant_uuid',
        'sold_at',
        'issued_at',
        'amortized_at',
    ];

    /**
     * ネイティブなタイプへキャストする属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'integer',
        'interest_rate' => 'decimal:3', // decimalの桁数を指定
        'sold_at' => 'datetime',
        'issued_at' => 'datetime',
        'amortized_at' => 'datetime',
    ];

    // --- Relationships ---

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspaces_uuid', 'uuid');
    }

    public function issuer(): BelongsTo // メソッド名を issuer から変更 (カラム名と重複回避)
    {
        return $this->belongsTo(Individual::class, 'issuer_uuid', 'uuid');
    }

    public function holder(): BelongsTo // メソッド名を holder から変更 (カラム名と重複回避)
    {
        return $this->belongsTo(Individual::class, 'holder_uuid', 'uuid');
    }

    public function applicant(): BelongsTo // メソッド名を applicant から変更 (カラム名と重複回避)
    {
        return $this->belongsTo(Individual::class, 'applicant_uuid', 'uuid');
    }
}
