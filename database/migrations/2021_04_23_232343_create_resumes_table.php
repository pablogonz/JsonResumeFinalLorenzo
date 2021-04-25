<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // We prepared the Database Schema that we will be using
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->string('Email')->unique(); // Email will Serve as Unique key for every Resume
            $table->json('Resume')->nullable(); // Resume can be null in the init
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resumes');
    }
}

