<?php
namespace App\Library\Services;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
  
class PublicTools
{
    public function getIconeByAction($action)
    {
      $icone = '';
      switch ($action) {
        case "NEW":
          $icone = 'flaticon2-add-1';
          break;
        case "EDIT":
            $icone = 'flaticon-edit';
            break;
        case "VIEW":
            $icone = 'flaticon-file-2';
            break;
        case "DELETE":
            $icone = 'flaticon-delete';
            break;
        case "CANCEL":
            $icone = 'flaticon-cancel';
            break;
        case "ARCHIVE":
            $icone = 'fas fa-archive';
            break;
        case "UNARCHIVE":
            $icone = 'fas fa-arrow-circle-up';
            break;
        case "PRICE":
            $icone = 'flaticon-price-tag';
            break;
        case "PDF":
            $icone = 'far fa-file-pdf';
            break;
        case "DOWNLOAD":
            $icone = 'flaticon-download';
            break;
        case "MORE":
            $icone = 'flaticon-more-v5';
            break;
        case "DOLLAR":
            $icone = 'fas fa-dollar-sign';
            break;
        case "ENVELOPE":
            $icone = 'flaticon2-black-back-closed-envelope-shape';
            break;
        case "INFO":
            $icone = 'fa fa-info';
            break;
        case "GROUP":
            $icone = 'flaticon2-group';
            break;
        case "LIST":
            $icone = 'flaticon-folder-4';
            break;
        case "CHECK":
            $icone = 'flaticon2-checkmark';
            break;
        case "EYE":
            $icone = 'flaticon-eye';
            break;
        case "EMAIL":
            $icone = 'flaticon2-send-1';
            break;
        case "SEND":
            $icone = 'flaticon2-checkmark';
            break;
        case "VALIDATE":
            $icone = 'flaticon-reply';
            break;
        case "REPORT":
            $icone = 'flaticon-calendar-3';
            break;
        case "SUBTASK":
            $icone = 'flaticon-add';
            break;
        case "COMMENT":
            $icone = 'flaticon-comment';
            break;
        case "SIGN":
            $icone = 'flaticon2-pen';
            break;
        case "SAVE":
            $icone = 'fas fa-save';
            break;
      }
      return $icone;             
    }
    public function generateRandomPassword(){
        $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
        $password = substr($random, 0, 10);
        return $password;
    }
    public function constructParagraphLabelDot($size,$cssClass,$text){
        $paragraph ='<p class="font-size-'.$size.'"><span class="label label-lg label-dot label-'.$cssClass.'"></span> '.$text.'</p>';
        return $paragraph;
    }
    public function constructSpanLabel($size,$cssClass,$text){
        $span ='<span class="label label-'.$size.' '.$cssClass.'">'.$text.'</span>';
        return $span;
    }
    public function getSiretApi($siren){
        $siret = '';
        $API_INSEE_URL=env('API_INSEE_URL','');
        $API_INSEE_TOKEN=env('API_INSEE_TOKEN','');
        if(isset($API_INSEE_URL) && isset($API_INSEE_TOKEN)){
            $url = $API_INSEE_URL.'/siret?q=siren:'.$siren;
            $response = Http::withToken($API_INSEE_TOKEN)->get($url);
            if($response->ok() && $response->successful()){
                if($response->status()===200){
                    $json = $response->json();
                    $etablissements = (isset($json['etablissements']))?$json['etablissements']:[];
                    if(count($etablissements)>0){
                        if(isset($etablissements[0]['siret'])){
                            $siret =$etablissements[0]['siret'];
                        }
                    }
                }
            }
        }
        return $siret;
    }
    public function generateDateRange($started_at,$ended_at,$slot_duration = 15)
    {
        //$tools=new PublicTools();
        //$dates = $tools->generateDateRange('2021-04-16 09:30','2021-04-16 12:30',15);
        $start_date = Carbon::createFromFormat('Y-m-d H:i',$started_at);
        $end_date = Carbon::createFromFormat('Y-m-d H:i',$ended_at);

        $dates = [];
        $slots = $start_date->diffInMinutes($end_date)/$slot_duration;

        //first unchanged time
        $dates[$start_date->toDateString()][]=$start_date->toTimeString();

        for($s = 1;$s <=$slots;$s++){

            $dates[$start_date->toDateString()][]=$start_date->addMinute($slot_duration)->toTimeString();

        }

        return $dates;
    }
}