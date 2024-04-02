<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertIntoParParamsDelai extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('par_params')->insert(
            [
                [
                    'param_code' => 'DELAI_DIFFUSION_AGENDA',
                    'param_name' => 'Délai de diffusion d\'agenda',
                    'code' => '60',
                    'name' => '60',
                    'css_class' => 'info',
                    'order_show' => '2',
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('par_params', function (Blueprint $table) {
            //
        });
    }
}
