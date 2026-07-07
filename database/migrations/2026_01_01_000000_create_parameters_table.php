<?php

use Zitro\Parameters\Enums\ParameterType;
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
        $tableName = config('parameters.table_name', 'parameters');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Polymorphic Relation
            |--------------------------------------------------------------------------
            |
            | Define la relación polimórfica opcional. Si los campos quedan en null,
            | el registro se interpretará como un parámetro global del sistema.
            |
            */
            $table->nullableUuidMorphs('parameterable');
            
            $table->string('key');
            $table->text('value')->nullable();
            
            $table->enum('type', array_column(ParameterType::cases(), 'value'))
                ->default(ParameterType::STRING->value);
                  
            $table->string('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            |
            | Índice compuesto único para garantizar que no existan claves duplicadas
            | para un mismo modelo asignado o a nivel global del sistema.
            |
            */
            $table->unique(
                ['parameterable_type', 'parameterable_id', 'key'], 
                'param_type_id_clave_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('parameters.table_name', 'parameters');

        Schema::dropIfExists($tableName);
    }
};