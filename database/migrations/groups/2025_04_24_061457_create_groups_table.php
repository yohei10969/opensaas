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
        Schema::create('groups', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // uuid
            $table->string('name', 100); // グループ名

            $table->foreignUuid('accounts_uuid') // 所属アカウント・UUID型の外部キーカラムを作成
            ->constrained('accounts', 'uuid') // accountsテーブルのuuidカラムを参照 (外部キー)
            ->nullable()
            ->onDelete('cascade'); // 親(accounts)が削除されたら子(groups)も削除
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
