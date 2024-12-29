<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create("pdfs", function (Blueprint $table) {
      $table->id();
      $table->foreignId("user_id")->constrained();
      $table->string("original_filename");
      $table->string("summary_path");
      $table->string("summary_type");
      $table->integer("pages_processed");
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists("pdfs");
  }
};
