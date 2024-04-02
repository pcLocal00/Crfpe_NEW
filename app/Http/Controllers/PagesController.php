<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Schedule;
use App\Models\Ressource;
use App\Models\Sessiondate;
use Illuminate\Http\Request;
use App\Models\Scheduleressource;
use App\Library\Services\DbHelperTools;

class PagesController extends Controller
{
    public function index()
    {
        $userid = auth()->user()->id;
        $roles= auth()->user()->roles;

        if($roles[0]->code=='APPRENANT' || $roles[0]->code=='FORMATEUR'){
            $page_title = '';
            $page_description = '';
    
            return view('pages.dashboard1', compact('page_title', 'page_description'));
        }else{
            $page_title = 'Tableau de bord';
            $page_description = '';
            
            return view('pages.dashboard', compact('page_title', 'page_description'));
        }
    }

    // Quicksearch Result
    public function quickSearch()
    {
        return view('layout.partials.extras._quick_search_result');
    }
    public function dailyschedule()
    {
        return view('pages.dailyschedule');
    }
    public function dailyscheduleJson()
    {
        $DbHelperTools = new DbHelperTools();
        $datas=[];
        $dn = Carbon::now();
        //$dayDate='2021-09-03';
        $dayDate=$dn->format('Y-m-d');
        //res_ressources=====>type=RES_TYPE_LIEU
        $sessiondates = Sessiondate::select('id', 'planning_date','session_id')->where('planning_date', $dayDate)->get();
        if(count($sessiondates)>0){
            $ids_ressources=Ressource::select('id')->where([['type','RES_TYPE_LIEU'],['is_internal',1]])->pluck('id');
            foreach($sessiondates as $sd){
                $af_name='<p class="text-primary mb-0"><strong>'.$sd->session->af->title.'</strong></p>';
                $session_name='<p class="mb-0"><strong>'.$sd->session->title.'</strong></p>';
                $schedules = Schedule::select('id', 'start_hour', 'end_hour', 'duration')->where('sessiondate_id', $sd->id)->get();
                foreach($schedules as $schedule){
                    $groupsArray=$DbHelperTools->getGroupsInSchedule($schedule->id);
                    $groups = implode(',', $groupsArray);
                    $groups=(isset($groups) && !empty($groups))?('<p class="mb-0"><strong>Groupe(s): '.$groups.'</strong></p>'):'';

                    $formersArray=$DbHelperTools->getFormersInSchedule($schedule->id);
                    $formers = implode(',', $formersArray);
                    $formers=(isset($formers) && !empty($formers))?('<p class="mb-0"><strong>Formateur(s): '.$formers.'</strong></p>'):'';
                    //dd($formers);
                    $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                    $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                    $description = $af_name.$session_name.$formers.$groups;
                    $className='fc-event-success';

                    //Les ressources
                    $scheduleressources=Scheduleressource::select('ressource_id')->whereIn('ressource_id',$ids_ressources)->where('schedule_id',$schedule->id)->get();
                    $ressourcesArray=[];
                    if(count($scheduleressources)>0){
                        foreach($scheduleressources as $sr){
                            $ressourcesArray[]=$sr->ressource->name;
                        }
                    }

                    $ressources = implode(',', $ressourcesArray);
                    $title=(isset($ressources) && !empty($ressources))?'Salle :'.$ressources:'Salle inconue';
                    $datas[]=array(
                        'title'=>$title,
                        'start'=>$start_hour->format('Y-m-d').'T'.$start_hour->format('H:i:s'),
                        //'start'=>'2021-09-02T'.$start_hour->format('H:i:s'),
                        'end'=>$end_hour->format('Y-m-d').'T'.$end_hour->format('H:i:s'),
                        //'end'=>'2021-09-02T'.$end_hour->format('H:i:s'),
                        'description'=>$description,
                        'className'=>$className,
                    );
                }
            }
        }
        return response()->json($datas);
    }
}
