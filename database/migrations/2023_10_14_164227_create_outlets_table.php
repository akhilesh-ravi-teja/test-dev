<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id('outlet_id'); // Auto-incremental outlet ID
            $table->unsignedBigInteger('restaurant_id'); // ID of the associated restaurant
            $table->string('restaurant_name');
            $table->string('logo')->nullable(); // Nullable field for logo
            $table->text('address');
            $table->string('latlong');
            $table->timestamps(); // Created_at and updated_at columns
            $table->index('restaurant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('outlets');
    }
};
