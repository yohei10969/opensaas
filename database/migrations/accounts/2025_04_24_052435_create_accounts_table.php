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
        Schema::create('accounts', function (Blueprint $table) {
            $table->string('uuid', 36)->primary(); // uuid

            $table->string('alphabet', 100)->nullable(); // アカウント名・アルファベット
            $table->string('japanese', 100)->nullable(); // アカウント名・日本語

            $table->foreignUuid('administrator') // 管理者・UUID型の外部キーカラムを作成
            ->constrained('individuals', 'uuid') // individualsテーブルのuuidカラムを参照 (外部キー)
            ->onDelete('restrict'); // DBレベルで整合性を保つ

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
