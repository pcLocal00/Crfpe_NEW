<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Param;
use App\Models\Price;
use App\Models\Sheet;
use App\Models\Action;
use App\Models\Member;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Session;
use App\Models\Contract;
use App\Models\Schedule;
use App\Models\Formation;
use App\Models\Groupment;
use App\Models\Ressource;
use App\Models\Enrollment;
use App\Models\Sheetparam;
use App\Models\Sessiondate;
use Illuminate\Http\Request;
use App\Models\Documentmodel;
use App\Models\Groupmentgroup;
use App\Models\Templateperiod;
use App\Library\Helpers\Helper;
use App\Models\Schedulecontact;
use App\Models\Planningtemplate;
use App\Models\Scheduleressource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;


class WordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    } 
 

    public function generate()
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $section = $phpWord->addSection();
        $text = $section->addText('name');
        $text = $section->addText('email');
        $text = $section->addText('number',array('name'=>'Arial','size' => 20,'bold' => true));
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save(public_path().'/word/Appdividend.docx'); 
        return response()->download(public_path().'/word/Appdividend.docx');

        $row = 'Im here';
        return view('pages.word', ['row' => $row]);
    } 
}
