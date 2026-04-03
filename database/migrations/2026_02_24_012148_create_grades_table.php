<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('nom_grade');
            $table->string('code_grade')->unique();
            $table->enum('type_grade', [
                'militaire du rang', 
                'sous-officier', 
                'officier subalterne', 
                'officier supérieur', 
                'officier général'
            ]);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};