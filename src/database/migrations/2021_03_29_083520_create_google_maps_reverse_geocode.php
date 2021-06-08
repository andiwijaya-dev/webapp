<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleMapsReverseGeocode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_maps_reverse_geocode', function (Blueprint $table) {

          $table->bigIncrements('id');
          $table->decimal('latitude', 11, 8);
          $table->decimal('longitude', 11, 8);
          $table->text('data')->nullable();
          $table->timestamps();

          $table->unique([ 'latitude', 'longitude' ], 'google_maps_reverse_geocode|unique|latitude,longitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_maps_reverse_geocode');
    }
}
