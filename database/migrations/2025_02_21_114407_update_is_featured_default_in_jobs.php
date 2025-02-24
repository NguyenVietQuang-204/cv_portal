<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('job', function (Blueprint $table) {
            $table->boolean('isFeatured')->default(1)->change();
        });
    }

    public function down() {
        Schema::table('job', function (Blueprint $table) {
            $table->boolean('isFeatured')->default(0)->change(); // Hoặc null nếu muốn hoàn nguyên
        });
    }
};
