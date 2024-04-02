<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertIntoDocumentModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert some stuff
        DB::table('par_document_models')->insert(
            array(
                'code' => 'TRANSCRIPT',
                'name' => 'BULLETIN DES NOTES',
                'default_content' => "<style>\r\n	table.main-table th, table.main-table td {\r\n		border : 1px solid;\r\n		padding: 5px;\r\n		height: 50px;\r\n	}\r\n	table.main-table td {\r\n		text-align: right;\r\n	}\r\n	table.main-table tr td:first-child {\r\n		text-align: inherit;\r\n		width: 11.4cm;\r\n	}\r\n	table.main-table td .sub-session {\r\n		margin-left: 10px;\r\n		font-style: italic;\r\n	}\r\n</style>\r\n<table\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px; vertical-align: baseline;\">\r\n                <p>Classe : {MEMBER_GROUP}</p>\r\n                <p>Numéro d'étudiant : {MEMBER_NUM}</p>\r\n                <p>Tuteur : {MEMBER_TUTEUR}</p>\r\n                <p>Formateur référent : {MEMBER_REFERENT_FORMER}</p>\r\n            </td>\r\n            <td style=\"font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px; vertical-align: baseline;\">\r\n                <p>{MEMBER_FIRSTNAME} {MEMBER_LASTNAME}</p>\r\n                <p>{MEMBER_ADDRESS}</p>\r\n                <p>{MEMBER_ZIPCODE} {MEMBER_CITY}</p>\r\n                <br>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<table\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px;border: 1px solid;margin-top: 30px;margin-bottom: 10px;padding: 5px 0\">\r\n    <tbody>\r\n        <tr>\r\n            <td\r\n                style=\"font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:14px;text-align: center;\">\r\n                <strong>RELEVÉ DE NOTE : {PERIOD}</strong>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n\r\n<table\r\n	class=\"main-table\"\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px;margin-top: 5px;margin-bottom: 5px;padding: 5px 0;border-collapse: collapse;\">\r\n	<thead style=\"background-color: rgb(211, 211, 211);\">\r\n		{TABLE_HEADER}\r\n	</thead>\r\n    <tbody>\r\n        {TABLE_BODY}\r\n    </tbody>\r\n</table>\r\n<table\r\n	class=\"main-table\"\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px;margin-top: 5px;margin-bottom: 10px;padding: 5px 0;border-collapse: collapse;\">\r\n	{OBSERVATIONS_TABLE}\r\n</table>\r\n",
                'custom_content' => "<style>\r\n	table.main-table th, table.main-table td {\r\n		border : 1px solid;\r\n		padding: 5px;\r\n		height: 50px;\r\n	}\r\n	table.main-table td {\r\n		text-align: right;\r\n	}\r\n	table.main-table tr td:first-child {\r\n		text-align: inherit;\r\n		width: 11.4cm;\r\n	}\r\n	table.main-table td .sub-session {\r\n		margin-left: 10px;\r\n		font-style: italic;\r\n	}\r\n</style>\r\n<table\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px; vertical-align: baseline;\">\r\n                <p>Classe : {MEMBER_GROUP}</p>\r\n                <p>Numéro d'étudiant : {MEMBER_NUM}</p>\r\n                <p>Tuteur : {MEMBER_TUTEUR}</p>\r\n                <p>Formateur référent : {MEMBER_REFERENT_FORMER}</p>\r\n            </td>\r\n            <td style=\"font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px; vertical-align: baseline;\">\r\n                <p>{MEMBER_FIRSTNAME} {MEMBER_LASTNAME}</p>\r\n                <p>{MEMBER_ADDRESS}</p>\r\n                <p>{MEMBER_ZIPCODE} {MEMBER_CITY}</p>\r\n                <br>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<table\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px;border: 1px solid;margin-top: 30px;margin-bottom: 10px;padding: 5px 0\">\r\n    <tbody>\r\n        <tr>\r\n            <td\r\n                style=\"font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:14px;text-align: center;\">\r\n                <strong>RELEVÉ DE NOTE : {PERIOD}</strong>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n\r\n<table\r\n	class=\"main-table\"\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px;margin-top: 5px;margin-bottom: 5px;padding: 5px 0;border-collapse: collapse;\">\r\n	<thead style=\"background-color: rgb(211, 211, 211);\">\r\n		{TABLE_HEADER}\r\n	</thead>\r\n    <tbody>\r\n        {TABLE_BODY}\r\n    </tbody>\r\n</table>\r\n<table\r\n	class=\"main-table\"\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;font-size:12px;margin-top: 5px;margin-bottom: 10px;padding: 5px 0;border-collapse: collapse;\">\r\n	{OBSERVATIONS_TABLE}\r\n</table>\r\n",
                'default_header' => "<table style=\"width:16.8cm;font-family:Courier New;font-size:12px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"text-align:center;\">\r\n                <img height=\"80px\" src=\"{LOGO_HEADER}\" />\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>",
                'custom_header' => "<table style=\"width:16.8cm;font-family:Courier New;font-size:12px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"text-align:center;\">\r\n                <img height=\"80px\" src=\"{LOGO_HEADER}\" />\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>",
                'default_footer' => "<table\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;margin-bottom: 80px;padding-bottom: 80px\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"width:16.8cm; text-align: right;\">\r\n                <p class=\"footer-text\" style=\"font-size: 14px;\">Fait à {CITY_TRANSCRIPT}, le {DATE_TRANSCRIPT}</p>\r\n                <p class=\"footer-text\" style=\"font-size: 14px;\">Signature</p>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>",
                'custom_footer' => "<table\r\n    style=\"width:16.8cm;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;margin-bottom: 80px;padding-bottom: 80px\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"width:16.8cm; text-align: right;\">\r\n                <p class=\"footer-text\" style=\"font-size: 14px;\">Fait à {CITY_TRANSCRIPT}, le {DATE_TRANSCRIPT}</p>\r\n                <p class=\"footer-text\" style=\"font-size: 14px;\">Signature</p>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('par_document_models', function (Blueprint $table) {
            //
        });
    }
}
