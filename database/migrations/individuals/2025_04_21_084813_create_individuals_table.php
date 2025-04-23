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
         * これはユーザー認証に関するテーブルです。
         * 個人情報（プロフィール）に関しては Personals テーブルを参照。
         * 
         */
        Schema::create('individuals', function (Blueprint $table) {
            $table->string('uuid', 36)->primary(); // uuid
            $table->string('email', 100)->unique(); // email
            $table->string('password', 100); // password
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individuals');
    }
};
