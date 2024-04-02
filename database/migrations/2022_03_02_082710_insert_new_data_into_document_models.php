<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertNewDataIntoDocumentModels extends Migration
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
                'code' => 'AF_TECHNICAL_SHEET',
                'name' => 'FICHE TECHNIQUE AF',
                'default_content' => "<style>\r\n    .p{\r\n        font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;\r\n        font-size:12px;\r\n    }\r\n</style>\r\n<p style=\"text-align: center;\"><strong>{DOCUMENT_TITLE}</strong></p>\r\n<p><strong>{AF_TITLE}</strong></p>\r\n<p><strong>Objectifs de la formation :</strong></p> \r\n<p>{AF_OBJECTIFS}</p>\r\n<p><strong>Contenu de la formation : </strong></p>\r\n<p>{AF_CONTENT}</p>\r\n<p><strong>Modalités pédagogiques : </strong></p>\r\n<p>{AF_MODALITE_PEDAGOGIQUES}</p>\r\n<p><strong>Modalités d’accès : </strong></p>\r\n<p>{AF_MODALITE_ACCES}</p>\r\n<p><strong>Modalités d’évaluation : </strong></p>\r\n<p>{AF_MODALITE_EVALUATION}</p>\r\n<p><strong>Moyens mis à disposition : </strong></p>\r\n<p>{AF_MODALITE_MOYENS_DISPOSITION}</p>\r\n<p><strong>Pré-requis : </strong></p>\r\n<p>{AF_PRE_REQUIS}</p>\r\n<p><strong>Durée de la formation : </strong></p>\r\n<p>Formation théorique : {NB_THEO_DAYS} jours / {NB_THEO_HOURS} heures</p>\r\n<p>Formation pratique : {NB_PRACTICAL_DAYS} jours / {NB_PRACTICAL_HOURS} heures</p>\r\n<p><strong>Profil des intervenants : </strong></p>\r\n<p>{AF_PROFIL_INTERVENANTS}</p>\r\n<p>Référence de l’action de formation :{AF_CODE} </p>",
                'custom_content' => "<style>\r\n    .p{\r\n        font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;\r\n        font-size:12px;\r\n    }\r\n</style>\r\n<p style=\"text-align: center;\"><strong>{DOCUMENT_TITLE}</strong></p>\r\n<p><strong>{AF_TITLE}</strong></p>\r\n<p><strong>Objectifs de la formation :</strong></p> \r\n<p>{AF_OBJECTIFS}</p>\r\n<p><strong>Contenu de la formation : </strong></p>\r\n<p>{AF_CONTENT}</p>\r\n<p><strong>Modalités pédagogiques : </strong></p>\r\n<p>{AF_MODALITE_PEDAGOGIQUES}</p>\r\n<p><strong>Modalités d’accès : </strong></p>\r\n<p>{AF_MODALITE_ACCES}</p>\r\n<p><strong>Modalités d’évaluation : </strong></p>\r\n<p>{AF_MODALITE_EVALUATION}</p>\r\n<p><strong>Moyens mis à disposition : </strong></p>\r\n<p>{AF_MODALITE_MOYENS_DISPOSITION}</p>\r\n<p><strong>Pré-requis : </strong></p>\r\n<p>{AF_PRE_REQUIS}</p>\r\n<p><strong>Durée de la formation : </strong></p>\r\n<p>Formation théorique : {NB_THEO_DAYS} jours / {NB_THEO_HOURS} heures</p>\r\n<p>Formation pratique : {NB_PRACTICAL_DAYS} jours / {NB_PRACTICAL_HOURS} heures</p>\r\n<p><strong>Profil des intervenants : </strong></p>\r\n<p>{AF_PROFIL_INTERVENANTS}</p>\r\n<p>Référence de l’action de formation :{AF_CODE} </p>",
                'default_header' => "<table style=\"width:16.8cm;font-family:Courier New;font-size:12px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"text-align:center;\">\r\n                <img height=\"80px\" src=\"{LOGO_HEADER}\" />\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>",
                'custom_header' => "<table style=\"width:16.8cm;font-family:Courier New;font-size:12px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td style=\"text-align:center;\">\r\n                <img height=\"80px\" src=\"{LOGO_HEADER}\" />\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>",
                'default_footer' => "<table>\r\n <tbody>\r\n <tr>\r\n <td style=\"width:4cm;\"> <img width=\"280px\" src=\"{LOGO_FOOTER}\"> </td>\r\n <td style=\"width:9.4cm;\">\r\n <p class=\"footer-text\">{ADRESS_FOOTER}</p>\r\n <p class=\"footer-text\">Tél : {PHONE_FOOTER}</p>\r\n <p class=\"footer-text\">{EMAIL_FOOTER} • {WEBSITE_FOOTER}</p>\r\n <p class=\"footer-text\">Siret {SIRET_FOOTER}</p>\r\n </td>\r\n </tr>\r\n </tbody>\r\n</table>",
                'custom_footer' => "<table>\r\n <tbody>\r\n <tr>\r\n <td style=\"width:4cm;\"> <img width=\"280px\" src=\"{LOGO_FOOTER}\"> </td>\r\n <td style=\"width:9.4cm;\">\r\n <p class=\"footer-text\">{ADRESS_FOOTER}</p>\r\n <p class=\"footer-text\">Tél : {PHONE_FOOTER}</p>\r\n <p class=\"footer-text\">{EMAIL_FOOTER} • {WEBSITE_FOOTER}</p>\r\n <p class=\"footer-text\">Siret {SIRET_FOOTER}</p>\r\n </td>\r\n </tr>\r\n </tbody>\r\n</table>",
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
        //
    }
}
