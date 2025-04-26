<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * 原則
         * 複数トークンの取引時にはその数だけトークンの履歴を記録する。 
         * (例)30トークンの取引申請があった場合、30レコード生成される。 
         */
        Schema::create('token_transactions', function (Blueprint $table) {
            $table->id();

            /**
             * トークン
             */
            $table->foreignUuid('token') // トークン・UUID型の外部キーカラムを作成
                  ->constrained('tokens', 'uuid') // tokensテーブルのuuidカラムを参照
                  ->onDelete('cascade'); // 親(tokens)が削除されたら子(token_transactions)も削除
            
            
            /**
             * 
             */
            $table->foreignUuid('assignment') // 譲渡元・UUID型の外部キーカラムを作成
                  ->nullable()
                  ->constrained('individuals', 'uuid'); // individualsテーブルのuuidカラムを参照
            

            /**
             * 
             */
            $table->foreignUuid('transferee') // 譲受先・UUID型の外部キーカラムを作成
                  ->nullable()
                  ->constrained('individuals', 'uuid'); // individualsテーブルのuuidカラムを参照


                  
            // 出品日時
            // sell(出品) の過去形
            $table->timestamp('sold_at')->nullable();
            
            // 販売中止日時
            // 購入直後は購入不可の状態からスタート
            // restrict(制限) の過去形
            $table->timestamp('restricted_at')->nullable();

            // 購入申請日時
            // apply(申請) の過去形
            $table->timestamp('applied_at')->nullable();
            
            // 購入意思撤回・破談・取引不成立日時
            // break の過去形
            $table->timestamp('broke_at')->nullable();
            
            // 取引成立日時
            // deal の過去形
            $table->timestamp('dealt_at')->nullable();
            
            // 発行日時
            // issue の過去形
            $table->timestamp('issued_at')->nullable();
            
            // 償却日時
            // amortize の過去形
            $table->timestamp('amortized_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_transactions');
    }
};
