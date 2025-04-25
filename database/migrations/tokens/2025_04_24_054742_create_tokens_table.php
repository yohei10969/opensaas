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
        Schema::create('tokens', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // トークンID (UUID)

            $table->foreignUuid('issuer') // 発行者・UUID型の外部キーカラムを作成
                  ->constrained('individuals', 'uuid') // individualsテーブルのuuidカラムを参照
                  ->onDelete('restrict'); // 発行者個人がアカウントを消せないようにする

            /**
             * 
             * 1トークンの額は最大10万とする
             * 1トークンの額は最小1000とする
             * 発行は 1000 - 10000 - 100000 この3つの発行のみ可能
             * これにより、流動性の向上を図る
             *
             */
            $table->unsignedInteger('amount')->default(1000); // 1トークンあたりの額面 (例: 円)

            /**
             * 金利はのデフォルト値を1%とする
             * 最大金利は20%
             * 最小金利は0%
             * 金利は0.01%まで設定可能
             * 銭を取り扱わない
             */
            $table->decimal('interest_rate', 4, 3)->default('0.010'); // 金利は1%をデフォルトとする 最大金利は

            $table->timestamp('issue_at'); // 発行日
            /**
             * 償却は発行したトークンの「消去」を意味する
             * 償還は「返済」を意味するため、取引を扱うテーブルで使用
             * 
             */
            $table->timestamp('retirement_at')->nullable(); // 償却日
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            // テーブル削除前に外部キー制約を削除する (より安全)
            if (Schema::hasColumn('tokens', 'issuer')) { // カラム存在チェック
                $table->dropForeign(['issuer']);
            }
        });

        Schema::dropIfExists('tokens');
    }
};
