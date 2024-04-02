<?php
namespace App\Library\Helpers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Param;
use App\Models\Sheet;
use App\Models\Action;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Invoice;
use App\Models\Session;
use App\Models\Schedule;
use Carbon\CarbonPeriod;
use App\Models\Categorie;
use App\Models\Formation;
use App\Models\Sheetparam;
use App\Models\Sessiondate;
use App\Models\Templateperiod;
use App\Models\Planningtemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
  
class Helper
{
    public static function getNameParamByCodeStatic($code)
    {
        $name = '';
        $css_class = '';
        if(isset($code) && !empty($code)){
            $row = Param::select('name','css_class')->where('code',$code)->first();
            if($row){
                $name = ($row['name'])?$row['name']:'';
                $css_class = ($row['css_class'])?$row['css_class']:'';
            }else{
                $row=array(
                    'INACTIF_INTERNSHIP_PERIOD'=>['name'=>'Inactif','css_class'=>'danger'],
                    'VALID_INTERNSHIP_PERIOD'=>['name'=>'Validé','css_class'=>'success'],

                    'EJE_SANS_GRATIFICATION_INTERNSHIP_PERIOD'=>['name'=>'EJE Sans Gratification','css_class'=>'info'],
                    'EJE_AVEC_GRATIFICATION_INTERNSHIP_PERIOD'=>['name'=>'EJE Avec Gratification','css_class'=>'info'],
                    'CAP_INTERNSHIP_PERIOD'=>['name'=>'CAP','css_class'=>'info'],
                );
                $name = ($row[$code]['name'])?$row[$code]['name']:'';
                $css_class = ($row[$code]['css_class'])?$row[$code]['css_class']:'';
            }
        }
        return ['name'=>$name,'css_class'=>$css_class];
    }
    public static function getNamePlanningtemplateStatic($id)
    {
        $name = '';
        if($id>0){
            $row = Planningtemplate::select('name')->where('id',$id)->first();
            $name = ($row['name'])?$row['name']:'';
        }
        return $name;
    }
    public static function getCssClassForHoursPlanned($number_of_hours_planned,$session_nb_hours){
        $cssClass = 'danger';
        if($number_of_hours_planned==$session_nb_hours){
            $cssClass = 'success';
        }elseif($number_of_hours_planned>$session_nb_hours){
            $cssClass = 'danger';
        }elseif($number_of_hours_planned<$session_nb_hours){
            $cssClass = 'warning';
        }
        return $cssClass;
    }
    public static function getCssClassForDatesPlanned($number_of_dates_planned,$session_nb_dates_to_program){
        $cssClass = 'danger';
        if($number_of_dates_planned==$session_nb_dates_to_program){
            $cssClass = 'success';
        }elseif($number_of_dates_planned>$session_nb_dates_to_program){
            $cssClass = 'danger';
        }elseif($number_of_dates_planned<$session_nb_dates_to_program){
            $cssClass = 'warning';
        }
        return $cssClass;
    }
    public static function convertTime($dec)
    {
        // start by converting to seconds
        $seconds = ($dec * 3600);
        // we're given hours, so let's get those the easy way
        $hours = floor($dec);
        // since we've "calculated" hours, let's remove them from the seconds variable
        $seconds -= $hours * 3600;
        // calculate minutes left
        $minutes = floor($seconds / 60);
        // remove those from seconds as well
        $seconds -= $minutes * 60;
        // return the time formatted HH:MM:SS
        //return self::lz($hours).":".self::lz($minutes).":".self::lz($seconds);
        return self::lz($hours)."h".self::lz($minutes);
    }

    // lz = leading zero
    public static function lz($num)
    {
        return (strlen($num) < 2) ? "0{$num}" : $num;
    }
    public static function getFilterControlePay()
    {
        $pcd=config('global.pay_check_day');
        $pay_check_day =($pcd>0 && $pcd<=31)?$pcd:20;
        $currentDateTime = Carbon::now();
        $newDateTime = Carbon::now()->subMonth();
        $start_format=$pay_check_day.'/'.$newDateTime->format('m/Y');
        $dt = Carbon::createFromFormat('d/m/Y', $start_format);
        $dt2 = Carbon::createFromFormat('d/m/Y', $start_format);
        $ndt=$dt2->addMonth();
        $dt3 = Carbon::createFromFormat('d/m/Y', $ndt->format('d/m/Y'));
        $end=$dt3->subDay();
        return ['start'=>$dt->format('d/m/Y'),'end'=>$end->format('d/m/Y')];
    }
    public static function getAcountingCodesInInvoices()
    {
        $codes=Invoice::select('accounting_code')->whereNotNull('accounting_code')->pluck('accounting_code')->unique();
        //dd($codes);
        return $codes;
    }
    public static function getclients()
    {
        $clients=Entitie::select('id','ref','name')->get();
        return $clients;
    }
    public static function getFilterControleInvoices()
    {
        $start = new Carbon('first day of this month');
        $end = new Carbon('last day of this month');
        return ['start'=>$start->format('d/m/Y'),'end'=>$end->format('d/m/Y')];
    }
}