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
        Schema::create('personals', function (Blueprint $table) {
            $table->foreignUuid('individuals_uuid') // UUID型の外部キーカラムを作成
                  ->primary()
                  ->constrained('individuals', 'uuid') // individualsテーブルのuuidカラムを参照
                  ->onDelete('cascade') // 親(individuals)が削除されたら子(personals)も削除
                  ->onUpdate('cascade'); // 親のuuidが更新されたら子のuuidも更新 (通常UUIDは更新しないが念のため)

            $table->string('alphabet', 50)->nullable(); // 名前・アルファベット
            $table->string('japanese', 50)->nullable(); // 名前・日本語

            $table->string('username', 30)->unique()->nullable(); // ユーザーネーム (一意、任意入力)
            $table->string('penname', 30)->unique()->nullable(); // ペンネーム (一意、任意入力)
            $table->text('bio')->nullable(); // 自己紹介 (任意入力)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personals');
    }
};
