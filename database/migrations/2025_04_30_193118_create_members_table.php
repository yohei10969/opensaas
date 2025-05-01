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
        Schema::create('members', function (Blueprint $table) {
            $table->foreignUuid('individuals_uuid') // UUID型の外部キーカラムを作成
                  ->constrained('individuals', 'uuid') // individualsテーブルのuuidカラムを参照
                  ->onDelete('cascade'); // 親(individuals)が削除されたら子(members)も削除

            // ワークスペース
            $table->foreignUuid('workspaces_uuid') // UUID型の外部キーカラムを作成
                  ->constrained('workspaces', 'uuid') // workspaces テーブルのuuidカラムを参照
                  ->onDelete('cascade'); // 親(workspaces)が削除されたら子(members)も削除
            
            $table->timestamps();
            $table->primary(['individuals_uuid', 'workspaces_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
