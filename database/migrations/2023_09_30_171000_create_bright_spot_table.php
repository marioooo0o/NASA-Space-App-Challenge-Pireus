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
        Schema::create('bright_spots', function (Blueprint $table) {
            $table->id();
            $table->string("country_id")->nullable();
            $table->string("latitude");
            $table->string("longitude");
            $table->string("bright_ti4")->nullable();
            $table->string("scan")->nullable();
            $table->string("track")->nullable();
            $table->string("acq_date")->nullable();
            $table->string("acq_time")->nullable();
            $table->string("satellite")->nullable();
            $table->string("instrument")->nullable();
            $table->string("confidence")->nullable();
            $table->string("version")->nullable();
            $table->string("bright_ti5")->nullable();
            $table->string("frp")->nullable();
            $table->string("daynight")->nullable();
            $table->boolean('from_nasa')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bright_spot');
    }
};
