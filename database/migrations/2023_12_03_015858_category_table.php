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
        Schema::create('category', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('outlet_id'); // ID of the associated restaurant
            $table->string('category_name');
            $table->string('image')->nullable(); // Nullable field for logo
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active'); // Enum with active as default
            $table->timestamps(); // Created_at and updated_at columns
            $table->softDeletes(); // Soft deletes
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category');
    }
};
