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

            $table->foreignUuid('issuer') // 債務者(発行者)・UUID型の外部キーカラムを作成
                  ->constrained('individuals', 'uuid') // individualsテーブルのuuidカラムを参照
                  ->onDelete('restrict'); // 発行者個人がアカウントを消せないようにする
            
            /**
             * 現所有者
             * 債務者（発行者）と違うIDであれば債権者になる
             */
            $table->foreignUuid('holder') // UUID型の外部キーカラムを作成
                  ->constrained('individuals', 'uuid') // individualsテーブルのuuidカラムを参照
                  ->onDelete('restrict'); // 所有者がアカウントを消せないようにする

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
             * 日本円の小数点単位である「銭」を取り扱わない
             */
            $table->decimal('interest_rate', 4, 3)->default('0.010');

            /**
             * 申請者
             * ID が INSERT されていれば、取引希望者となる
             * Null になるパターンは、
             * ・取引が成立
             * ・申請を取り下げた
             * ・何らかの理由で所有者が断った
             */
            $table->foreignUuid('applicant') // UUID型の外部キーカラムを作成
                  ->constrained('individuals', 'uuid') // individualsテーブルのuuidカラムを参照
                  ->nullable()
                  ->onDelete('restrict'); // 所有者がアカウントを消せないようにする

            /**
             * 償却は発行したトークンの「消去」を意味する
             * 償却後のトークンは原則取り扱いを禁止する
             * 買い戻し（償還）できたとしても、償却されてなければ出品は可能
             * 
             */
            // 出品中かどうか
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('issued_at'); // 発行日

            /**
             * 推奨：償却が実行された場合、原則このトークンは使用しない。
             */
            $table->timestamp('amortized_at')->nullable(); // 償却日

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            // テーブル削除前に外部キー制約を削除する (より安全)
            // カラム存在チェック
            if (Schema::hasColumn('tokens', 'issuer')) $table->dropForeign(['issuer']);
            if (Schema::hasColumn('tokens', 'holder')) $table->dropForeign(['holder']);
        });

        Schema::dropIfExists('tokens');
    }
};
