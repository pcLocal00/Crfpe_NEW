<?php

namespace App\Http\Controllers;

use App\Exports\AfExport;
use Carbon\Carbon;
use App\Models\Action;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Session;
use App\Models\Member;
use App\Models\Schedule;
use App\Models\Sessiondate;
use App\Models\Enrollment;
use App\Models\Schedulecontact;
use App\Models\Param;
use App\Models\Group;
use App\Models\Attachment;
use App\Models\Media;
use Illuminate\Http\Request;
use App\Library\Helpers\Helper;
use App\Models\Internshiproposal;
use Illuminate\Support\Facades\DB;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use Illuminate\Support\Facades\Storage;
use App\Exports\MyExport;
use App\Exports\TDBPresenceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\File;
class presencesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function presences()
    {   
        $datafilter = new \stdClass();
        $members = Member::where('af_schedulecontacts.is_former', 0)
                ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')
                ->select('af_members.*')
                ->orderBy('en_contacts.firstname','asc')
                ->paginate(50);
        $groups=Group::select('id','title')->get();
        $datafilter->members = $members;
        $datafilter->groups = $groups;

        $page_title = 'Gestion des presences';
        $page_description = '';
        return view('pages.af.presences.list', compact('page_title', 'page_description', 'datafilter'));
    }

    public function generateListAf(Request $request){
        $request_data = $request->all();
        $member_id = $request_data['member_id'];
        //dd($request->all());
        $member_ids = 0;
        $datas = [];
        if(isset($request_data['af_id']) && $request_data['af_id'] > 0){
            $ids_enrollments = Enrollment::select('id')->where([['af_id', $request_data['af_id']], ['enrollment_type', 'S']])->get()->pluck('id');
            $member_ids = Member::whereIn('enrollment_id', $ids_enrollments)->get('id')->pluck('id')->toArray();

        }
            // $member_ids = Enrollment::select('af_members.id')
            // ->join('af_members', 'af_members.enrollment_id', '=', 'af_enrollments.id')
            // ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            // ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')
            // ->where('enrollment_type', 'S')->where('af_id', $request_data['af_id'])->get()->pluck('af_members.id');

        if(isset($request_data['group_id']) && $request_data['group_id'] > 0){
            $member_ids = Member::where('group_id', $request_data['group_id'])->get('id')->pluck('id')->toArray();
        }
            // $member_ids = Enrollment::select('af_members.id')
            // ->join('af_members', 'af_members.enrollment_id', '=', 'af_enrollments.id')
            // // ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            // // ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')
            // ->where('enrollment_type', 'S')->where('group_id', $request_data['group_id'])->get();

        if($request_data['member_id'])
            $member_ids = (array)$request_data['member_id'];


        if($request_data['filter_start'] && $request_data['filter_end']){
            $d1 = Carbon::createFromFormat('d/m/Y', $request_data['filter_start']);
            $d2 = Carbon::createFromFormat('d/m/Y', $request_data['filter_end']);
            $start=$d1->format('Y-m-d');
            $end=$d2->format('Y-m-d');
            $member_ids = Schedulecontact::whereIn('member_id',(array)$member_ids)
            ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
            ->join('af_sessiondates', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
            ->where('af_schedulecontacts.is_former',0)
            // ->whereBetween('af_sessiondates.planning_date', [$start ,$end]);
            ->whereBetween('af_sessiondates.planning_date', [$start ,$end])
            ->select('af_schedulecontacts.member_id')
            ->pluck('af_schedulecontacts.member_id')->unique();
        }

        // dd($member_ids);
        foreach ($member_ids as $member_id) {
            $afs = DB::table('af_schedulecontacts')
                        ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                        ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                        ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                        ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                        // ->select('af_actions.id as id', 'af_actions.title as title', 'af_actions.code as code', 'af_sessions.title as session_title', 'af_sessions.id as session_id')
                        ->select('af_actions.id as id', 'af_actions.title as title', 'af_actions.code as code')
                        ->where('af_schedulecontacts.is_former',0)
                        ->where('af_schedulecontacts.member_id',$member_id)
                        ->get()->unique();
                        // ->pluck('af_actions.id as id', 'af_actions.title as title')->unique()->toArray();

            if (count($afs)) {
                $rs_schedulecontacts = Schedulecontact::where('member_id',$member_id)->get()->first();
                // echo $rs_schedulecontacts->member->contact->firstname;die;
                $std_name = $rs_schedulecontacts->member->contact ? 
                $rs_schedulecontacts->member->contact->firstname . ' ' . $rs_schedulecontacts->member->contact->lastname :
                $rs_schedulecontacts->member->unknown_contact_name;

                if (!$std_name) {
                   continue; 
                }
                $datas [] = array(
                        "id" => 'etudiant ' . $member_id,
                        "text" => $std_name,
                        "state" => array('opened' => false, 'checkbox_disabled' => false),
                        "icon" => "fa fa-user text-dark",
                        "parent" => '#'
                    );
                // foreach ($afs as $af) {
                    // echo count((array) $af); 
                // dd($af);
                    // $datas [] = array(
                    //         "id" => $af->id,
                    //         "text" =>$af->title.' ('.$af->code.')',
                    //         "state" => array('opened' => true, 'checkbox_disabled' => true),
                    //         "icon" => "fa fa-folder text-info",
                    //         "parent" => 'etudiant' . $member_id
                    //     );
                    $sessions = DB::table('af_schedulecontacts')
                        ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                        ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                        ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                        // ->select('af_sessions.title as session_title', 'af_sessions.id as session_id')
                        ->select('af_sessiondates.session_id as session_id', 'af_sessiondates.id as sessiondates_id', 'af_sessions.title as session_title')
                        ->where('af_schedulecontacts.member_id',"=", $member_id);
                        if($request_data['filter_start'] && $request_data['filter_end']){
                            $d1 = Carbon::createFromFormat('d/m/Y', $request_data['filter_start']);
                            $d2 = Carbon::createFromFormat('d/m/Y', $request_data['filter_end']);
                            $start=$d1->format('Y-m-d');
                            $end=$d2->format('Y-m-d');
                            $sessions->whereBetween('af_sessiondates.planning_date', [$start ,$end]);
                        }
                    $sessions = $sessions->get()->unique();
                    // if(count($sessions)){
                        foreach ($sessions as $session) {
                            // $datas [] = array(
                            //         "id" => $session->session_id,
                            //         "text" =>$session->session_title,
                            //         "state" => array('opened' => true, 'checkbox_disabled' => true),
                            //         "icon" => "fa fa-folder text-info",
                            //         "parent" => $af->id
                            //     );
                            $sessiondates = Sessiondate::select('id','planning_date');
                            // if($request_data['filter_start'] && $request_data['filter_end']){
                                // $from = date($request_data['filter_start']);
                                // $to = date($request_data['filter_end']);
                                // $d1 = Carbon::createFromFormat('d/m/Y', $request_data['filter_start']);
                                // $d2 = Carbon::createFromFormat('d/m/Y', $request_data['filter_end']);
                                // $start=$d1->format('Y-m-d');
                                // $end=$d2->format('Y-m-d');
                                // $sessiondates->whereBetween('af_sessiondates.planning_date', [$start ,$end]);
                            // }
                            // $sessiondates = $sessiondates->where('session_id', $session->session_id)->get();//->toSql();
                            $sessiondates = $sessiondates->where('id', $session->sessiondates_id)->get()->unique();//->toSql();
                            // dd(count($sessiondates));
                            $session_title = $session->session_title;
                            if (count($sessiondates) > 0) {
                                foreach ($sessiondates as $sd) {
                                    $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                                     $datas [] = array(
                                            "id" => 'D' . $sd->id. $member_id,
                                            "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                                            "state" => array('opened' => false, 'checkbox_disabled' => false),
                                            "icon" => "fa fa-calendar text-primary",
                                            "parent" => 'etudiant ' . $member_id
                                        );
                                     // dd($datas);
                                    $rs_schedules = Schedule::select('id','start_hour','end_hour','duration')->where('sessiondate_id', $sd->id)->get()->unique();
                                    // $rs_schedules = Schedule::select('af_schedules.id','start_hour','end_hour','duration')
                                    // ->join('af_schedulecontacts', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                                    // ->where('sessiondate_id', $sd->id)
                                    // ->where('af_schedulecontacts.member_id', $member_id)
                                    // ->get();
                                    // dd($rs_schedules);
                                        // echo $rs_schedulecontacts->member->contact->firstname;
                                        // echo '--';
                                        if (count($rs_schedules) > 0) {
                                            foreach ($rs_schedules as $schedule) {
                                                // echo $sd->planning_date;
                                                // echo '<';
                                                // echo $schedule->id;
                                                // echo '>';
                                                // echo '****'.$schedule->start_hour.'****';
                                                // echo '|||';
                                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                                $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');

                                                $duration = Helper::convertTime($schedule->duration);

                                                //Intervenants
                                                // $checkbox_disabled = false;
                                                // <span class="badge rounded-pill bg-success">Present</span>
                                                // <span class="badge rounded-pill bg-danger">Absent non justifié</span>
                                                // <span class="badge rounded-pill bg-warning text-dark">Absent justifié</span>
                                                // <span class="badge rounded-pill bg-light text-dark">Non renseigné</span>
                                                $badge = '';
                                                $rs_schedulecontacts_state = Schedulecontact::where('member_id', '=' , $member_id)
                                                ->where('schedule_id', '=', $schedule->id)
                                                ->get()->first();
                                                // dd($rs_schedulecontacts_state->is_absent);
                                                if(count((array)$rs_schedulecontacts_state)){
                                                    if($rs_schedulecontacts_state->is_absent){
                                                        if($rs_schedulecontacts_state->type_absent == 'Absent non justifié')
                                                            $badge = '<span class="badge rounded-pill bg-danger">Absent non justifié</span>';
                                                        else
                                                            $badge = '<span class="badge rounded-pill bg-warning text-dark">Absent justifié</span><a onclick=_getFormAttachments('.$schedule->id.','.$member_id.')><i style="margin-left:5px;color:blue;" class="fas fa-paperclip"></i></a>';
                                                    }
                                                    else{
                                                        if($rs_schedulecontacts_state->pointing == 'present')
                                                            $badge = '<span class="badge rounded-pill bg-success">Present</span>';
                                                        else
                                                            $badge = '<span class="badge rounded-pill bg-light text-dark">Non renseigné</span>';
                                                    }
                                                }
                                                // if($schedule->id)
                                                $datas [] = array(
                                                    "id" => $schedule->id.'-'. $member_id,
                                                    "li_attr" => ["name" => 'schedule', 'schedule_id' =>$schedule->id, 'member_id' => $member_id ],
                                                    "text" => 'Séance ('.$schedule->id.') : '.strtoupper(substr($session_title, 0,4)).' '.$text . ' <span class="text-success">(' . $duration . ')</span>'.$badge,
                                                    "state" => array('opened' => false, 'checkbox_disabled' => false),
                                                    "icon" => "fa fa-folder text-dark",
                                                    "parent" => 'D' . $sd->id. $member_id
                                                ); 
                                            }
                                        }
                                }
                            }
                        }
                    // }
                // }

            }
        }
        return response()->json($datas);
    }


    public function exportListAf(Request $request)
    {

        // dd($request);
 
            $results = array();
            $status = 0;

            $new_result = array();

            

            $sessions = DB::table('af_schedulecontacts')
            ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
            ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
            ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
            ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
            ->leftJoin('par_params AS pr', function ($join) {
                    $join->on('pr.code', '=', 'af_actions.state')
                        ->where('pr.param_code', '=', 'AF_STATES');
                })
            ->select(
                'af_actions.id',
                'af_actions.title',
                'af_actions.code',
                'af_actions.device_type',
                'pr.name',
                DB::raw('date_format(af_actions.started_at, "%d/%m/%Y") as date_debut'),
                DB::raw('date_format(af_actions.ended_at, "%d/%m/%Y") as date_fin'),
                'en_contacts.firstname as nom',
                'en_contacts.lastname as prenom',


                DB::raw('SUM(af_sessiondates.duration) as nb_heures_af'),


                'af_sessiondates.session_id as session_id',
                'af_sessiondates.id as sessiondates_id',
                'af_sessions.title as session_title',
                'af_schedulecontacts.member_id as member_id',

                'af_schedules.start_hour as start_hour',
                'af_schedules.end_hour as end_hour',

                DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "present" THEN af_sessiondates.duration ELSE 0 END) as total_present_hours'),
                // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "present" THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_present_hours2'),

                DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessiondates.duration ELSE 0 END) as total_absent_just_hours'),
                // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.pointing = 1 THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_just_hours2'),

                DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessiondates.duration ELSE 0 END) as total_absent_non_just_hours'),
                // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 0 THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_non_just_hours2'),

                // DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN af_sessiondates.id ELSE 0 END) as total_absent_non_point_hours_ids'),
                DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN af_sessiondates.duration ELSE 0 END) as total_absent_non_point_hours'),
                // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_non_point_hours'),



                DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN 1 ELSE 0 END) as absent_justified_sessions'),
                DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN DATE_FORMAT(af_sessiondates.planning_date, "%d/%m/%Y") ELSE NULL END SEPARATOR ";") as absent_justified_session_dates'),
                DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessiondates.duration ELSE NULL END SEPARATOR ";") as absent_justified_session_duration'),
                DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessions.title ELSE NULL END SEPARATOR ";") as intituler_absent_justified_session'),


                
                DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN 1 ELSE 0 END) as absent_non_justified_sessions'),
                DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN DATE_FORMAT(af_sessiondates.planning_date, "%d/%m/%Y") ELSE NULL END SEPARATOR ";") as absent_non_justified_session_dates'),
                DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessiondates.duration ELSE NULL END SEPARATOR ";") as absent_non_justified_session_duration'),
                DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessions.title ELSE NULL END SEPARATOR ";") as intituler_non_absent_justified_session')



            );

            // dd($request->group_id,$request->member_id);
            if($request->af_id > 0  && $request->group_id == 0 && $request->member_id == 0)
            {
                $status = 1;
                $result = null;
                $ids_enrollments = Enrollment::select('id')->where([['af_id', $request->af_id], ['enrollment_type', 'S']])->get()->pluck('id');
                $member_ids = Member::whereIn('enrollment_id', $ids_enrollments)->get('id')->pluck('id')->toArray();
                foreach($member_ids as $key => $value)
                {
                    $result = DB::table('af_schedulecontacts')
                    ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                    ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                    ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                    ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                    ->leftJoin('par_params AS pr', function ($join) {
                            $join->on('pr.code', '=', 'af_actions.state')
                                ->where('pr.param_code', '=', 'AF_STATES');
                        })
                        ->select(
                            'af_actions.id',
                            'af_actions.title',
                            'af_actions.code',
                            'af_actions.device_type',
                            'pr.name',
                            DB::raw('date_format(af_actions.started_at, "%d/%m/%Y") as date_debut'),
                            DB::raw('date_format(af_actions.ended_at, "%d/%m/%Y") as date_fin'),
                            'en_contacts.firstname as nom',
                            'en_contacts.lastname as prenom',
            
            
                            DB::raw('SUM(af_sessiondates.duration) as nb_heures_af'),
            
            
                            'af_sessiondates.session_id as session_id',
                            'af_sessiondates.id as sessiondates_id',
                            'af_sessions.title as session_title',
                            'af_schedulecontacts.member_id as member_id',
            
                            'af_schedules.start_hour as start_hour',
                            'af_schedules.end_hour as end_hour',
            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "present" THEN af_sessiondates.duration ELSE 0 END) as total_present_hours'),
                            // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "present" THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_present_hours2'),
            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessiondates.duration ELSE 0 END) as total_absent_just_hours'),
                            // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.pointing = 1 THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_just_hours2'),
            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessiondates.duration ELSE 0 END) as total_absent_non_just_hours'),
                            // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 0 THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_non_just_hours2'),
            
                            // DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN af_sessiondates.id ELSE 0 END) as total_absent_non_point_hours_ids'),
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN af_sessiondates.duration ELSE 0 END) as total_absent_non_point_hours'),
                            // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_non_point_hours'),
            
            
            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN 1 ELSE 0 END) as absent_justified_sessions'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN DATE_FORMAT(af_sessiondates.planning_date, "%d/%m/%Y") ELSE NULL END SEPARATOR ";") as absent_justified_session_dates'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessiondates.duration ELSE NULL END SEPARATOR ";") as absent_justified_session_duration'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessions.title ELSE NULL END SEPARATOR ";") as intituler_absent_justified_session'),
            
            
                            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN 1 ELSE 0 END) as absent_non_justified_sessions'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN DATE_FORMAT(af_sessiondates.planning_date, "%d/%m/%Y") ELSE NULL END SEPARATOR ";") as absent_non_justified_session_dates'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessiondates.duration ELSE NULL END SEPARATOR ";") as absent_non_justified_session_duration'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessions.title ELSE NULL END SEPARATOR ";") as intituler_non_absent_justified_session')
            
            
            
                        )->where('af_schedulecontacts.member_id', '=', $value)->where('af_sessions.af_id', '=', $request->af_id);

                    if(isset($request->filter_start) && isset($request->filter_end))
                    {
                        $d1 = Carbon::createFromFormat('d/m/Y', $request->filter_start)->format('Y-m-d');
                        $d2 = Carbon::createFromFormat('d/m/Y', $request->filter_end)->format('Y-m-d');

                        $result->whereBetween('af_sessiondates.planning_date', [$d1 ,$d2]);
                    }
                    // $results[] = 
                    $results[] = $result->get();
                    // array_push($results, $result);

                }

                foreach($results as $key => $res)
                {
                    // dd($res[0]);
                    $new_result[$key]['title'] = $res[0]->title;
                    $new_result[$key]['code'] = $res[0]->code;
                    $new_result[$key]['device_type'] = $res[0]->device_type;
                    $new_result[$key]['name'] = $res[0]->name;
                    $new_result[$key]['date_debut'] = $res[0]->date_debut;
                    $new_result[$key]['date_fin'] = $res[0]->date_fin;
                    $new_result[$key]['nb_heures_af'] = $res[0]->nb_heures_af;
                    $new_result[$key]['nom'] = $res[0]->nom;
                    $new_result[$key]['prenom'] = $res[0]->prenom;
                    $new_result[$key]['total_present_hours'] = $res[0]->total_present_hours;
                    $new_result[$key]['total_absent_just_hours'] = $res[0]->total_absent_just_hours;
                    $new_result[$key]['total_absent_non_just_hours'] = $res[0]->total_absent_non_just_hours;
                    $new_result[$key]['total_absent_non_point_hours'] = $res[0]->total_absent_non_point_hours;
    
    
    
                    $new_result[$key]['absent_justified_sessions'] = $res[0]->absent_justified_sessions;
                    $new_result[$key]['absent_justified_session_dates'] = $res[0]->absent_justified_session_dates;
                    $new_result[$key]['absent_justified_session_duration'] = $res[0]->absent_justified_session_duration;
    
    
                    // Split the original value by ';'
                    $titles = explode(';', $res[0]->intituler_absent_justified_session);
    
                    // Array to store modified titles
                    $newTitles = [];
    
                    // Loop through each title, extract the first 4 characters and convert them to uppercase
                    foreach ($titles as $title) {
                        $firstFourCharacters = strtoupper(substr(trim($title), 0, 4));
                        // Add the modified title to the newTitles array
                        $newTitles[] = $firstFourCharacters;
                    }
    
                    // Join the modified titles with ';' to create the new value
                    $newValue = implode(';', $newTitles);
    
                    $new_result[$key]['intituler_absent_justified_session'] = $newValue;
    
                    
    
                    $titles = explode(';', $res[0]->intituler_non_absent_justified_session);
    
                    // Array to store modified titles
                    $newTitles = [];
    
                    // Loop through each title, extract the first 4 characters and convert them to uppercase
                    foreach ($titles as $title) {
                        $firstFourCharacters = strtoupper(substr(trim($title), 0, 4));
                        // Add the modified title to the newTitles array
                        $newTitles[] = $firstFourCharacters;
                    }
    
                    // Join the modified titles with ';' to create the new value
                    $newValue = implode(';', $newTitles);
    
    
                    $new_result[$key]['absent_non_justified_sessions'] = $res[0]->absent_non_justified_sessions;
                    $new_result[$key]['absent_non_justified_session_dates'] = $res[0]->absent_non_justified_session_dates;
                    $new_result[$key]['absent_non_justified_session_duration'] = $res[0]->absent_non_justified_session_duration;
                    $new_result[$key]['intituler_non_absent_justified_session'] = $newValue;
                }

                // foreach($results as $res)
                // {
                //     dd($res);
                // }

                // var_dump($results);


                    

            }else if($request->af_id > 0 && $request->group_id > 0 && $request->member_id == 0)
            {
                $status = 2;
                $group = $request->group_id;
                $memberIdsInGroup = Member::where('group_id', $group)->get('id')->pluck('id')->toArray();
                foreach($memberIdsInGroup as $key => $value)
                {
                    // $results[] 
                    $data= DB::table('af_schedulecontacts')
                    ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                    ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                    ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                    ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                    ->leftJoin('par_params AS pr', function ($join) {
                            $join->on('pr.code', '=', 'af_actions.state')
                                ->where('pr.param_code', '=', 'AF_STATES');
                        })
                        ->select(
                            'af_actions.id',
                            'af_actions.title',
                            'af_actions.code',
                            'af_actions.device_type',
                            'pr.name',
                            DB::raw('date_format(af_actions.started_at, "%d/%m/%Y") as date_debut'),
                            DB::raw('date_format(af_actions.ended_at, "%d/%m/%Y") as date_fin'),
                            'en_contacts.firstname as nom',
                            'en_contacts.lastname as prenom',
            
            
                            DB::raw('SUM(af_sessiondates.duration) as nb_heures_af'),
            
            
                            'af_sessiondates.session_id as session_id',
                            'af_sessiondates.id as sessiondates_id',
                            'af_sessions.title as session_title',
                            'af_schedulecontacts.member_id as member_id',
            
                            'af_schedules.start_hour as start_hour',
                            'af_schedules.end_hour as end_hour',
            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "present" THEN af_sessiondates.duration ELSE 0 END) as total_present_hours'),
                            // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "present" THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_present_hours2'),
            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessiondates.duration ELSE 0 END) as total_absent_just_hours'),
                            // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.pointing = 1 THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_just_hours2'),
            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessiondates.duration ELSE 0 END) as total_absent_non_just_hours'),
                            // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 0 THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_non_just_hours2'),
            
                            // DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN af_sessiondates.id ELSE 0 END) as total_absent_non_point_hours_ids'),
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN af_sessiondates.duration ELSE 0 END) as total_absent_non_point_hours'),
                            // DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "not_pointed" THEN ' . Helper::convertTime(DB::raw('af_sessiondates.duration')) . ' ELSE 0 END) as total_absent_non_point_hours'),
            
            
            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN 1 ELSE 0 END) as absent_justified_sessions'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN DATE_FORMAT(af_sessiondates.planning_date, "%d/%m/%Y") ELSE NULL END SEPARATOR ";") as absent_justified_session_dates'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessiondates.duration ELSE NULL END SEPARATOR ";") as absent_justified_session_duration'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent justifié" THEN af_sessions.title ELSE NULL END SEPARATOR ";") as intituler_absent_justified_session'),
            
            
                            
                            DB::raw('SUM(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN 1 ELSE 0 END) as absent_non_justified_sessions'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN DATE_FORMAT(af_sessiondates.planning_date, "%d/%m/%Y") ELSE NULL END SEPARATOR ";") as absent_non_justified_session_dates'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessiondates.duration ELSE NULL END SEPARATOR ";") as absent_non_justified_session_duration'),
                            DB::raw('GROUP_CONCAT(CASE WHEN af_schedulecontacts.pointing = "absent" AND af_schedulecontacts.is_absent = 1 AND af_schedulecontacts.type_absent = "Absent non justifié" THEN af_sessions.title ELSE NULL END SEPARATOR ";") as intituler_non_absent_justified_session')
            
            
            
                        )->where('af_schedulecontacts.member_id', '=', $value)->where('af_sessions.af_id', '=', $request->af_id);

                    if(isset($request->filter_start) && isset($request->filter_end))
                    {
                        $d1 = Carbon::createFromFormat('d/m/Y', $request->filter_start)->format('Y-m-d');
                        $d2 = Carbon::createFromFormat('d/m/Y', $request->filter_end)->format('Y-m-d');

                        $data->whereBetween('af_sessiondates.planning_date', [$d1 ,$d2]);
                    }


                    $results[]  = $data->get();



                }

                // dd($results);

                foreach($results as $key => $res)
                {
                    $new_result[$key]['title'] = $res[0]->title;
                    $new_result[$key]['code'] = $res[0]->code;
                    $new_result[$key]['device_type'] = $res[0]->device_type;
                    $new_result[$key]['name'] = $res[0]->name;
                    $new_result[$key]['date_debut'] = $res[0]->date_debut;
                    $new_result[$key]['date_fin'] = $res[0]->date_fin;
                    $new_result[$key]['nb_heures_af'] = $res[0]->nb_heures_af;
                    $new_result[$key]['nom'] = $res[0]->nom;
                    $new_result[$key]['prenom'] = $res[0]->prenom;
                    $new_result[$key]['total_present_hours'] = $res[0]->total_present_hours;
                    $new_result[$key]['total_absent_just_hours'] = $res[0]->total_absent_just_hours;
                    $new_result[$key]['total_absent_non_just_hours'] = $res[0]->total_absent_non_just_hours;
                    $new_result[$key]['total_absent_non_point_hours'] = $res[0]->total_absent_non_point_hours;
    
    
    
                    $new_result[$key]['absent_justified_sessions'] = $res[0]->absent_justified_sessions;
                    $new_result[$key]['absent_justified_session_dates'] = $res[0]->absent_justified_session_dates;
                    $new_result[$key]['absent_justified_session_duration'] = $res[0]->absent_justified_session_duration;
    
    
                    // Split the original value by ';'
                    $titles = explode(';', $res[0]->intituler_absent_justified_session);
    
                    // Array to store modified titles
                    $newTitles = [];
    
                    // Loop through each title, extract the first 4 characters and convert them to uppercase
                    foreach ($titles as $title) {
                        $firstFourCharacters = strtoupper(substr(trim($title), 0, 4));
                        // Add the modified title to the newTitles array
                        $newTitles[] = $firstFourCharacters;
                    }
    
                    // Join the modified titles with ';' to create the new value
                    $newValue = implode(';', $newTitles);
    
                    $new_result[$key]['intituler_absent_justified_session'] = $newValue;
    
                    
    
                    $titles = explode(';', $res[0]->intituler_non_absent_justified_session);
    
                    // Array to store modified titles
                    $newTitles = [];
    
                    // Loop through each title, extract the first 4 characters and convert them to uppercase
                    foreach ($titles as $title) {
                        $firstFourCharacters = strtoupper(substr(trim($title), 0, 4));
                        // Add the modified title to the newTitles array
                        $newTitles[] = $firstFourCharacters;
                    }
    
                    // Join the modified titles with ';' to create the new value
                    $newValue = implode(';', $newTitles);
    
    
                    $new_result[$key]['absent_non_justified_sessions'] = $res[0]->absent_non_justified_sessions;
                    $new_result[$key]['absent_non_justified_session_dates'] = $res[0]->absent_non_justified_session_dates;
                    $new_result[$key]['absent_non_justified_session_duration'] = $res[0]->absent_non_justified_session_duration;
                    $new_result[$key]['intituler_non_absent_justified_session'] = $newValue;

                }

                // dd($new_result);

            }else if($request->member_id > 0)
            {


                $status = 3;
                $sessions->where('af_schedulecontacts.member_id', '=', $request->member_id)->where('af_sessions.af_id', '=', $request->af_id);


                if(isset($request->filter_start) && isset($request->filter_end))
                {
                    $d1 = Carbon::createFromFormat('d/m/Y', $request->filter_start)->format('Y-m-d');
                    $d2 = Carbon::createFromFormat('d/m/Y', $request->filter_end)->format('Y-m-d');

                    $sessions->whereBetween('af_sessiondates.planning_date', [$d1 ,$d2]);
                }


                $results[]  = $sessions->get();


                $new_result[0]['title'] = $results[0][0]->title;
                $new_result[0]['code'] = $results[0][0]->code;
                $new_result[0]['device_type'] = $results[0][0]->device_type;
                $new_result[0]['name'] = $results[0][0]->name;
                $new_result[0]['date_debut'] = $results[0][0]->date_debut;
                $new_result[0]['date_fin'] = $results[0][0]->date_fin;
                $new_result[0]['nb_heures_af'] = $results[0][0]->nb_heures_af;
                $new_result[0]['nom'] = $results[0][0]->nom;
                $new_result[0]['prenom'] = $results[0][0]->prenom;
                $new_result[0]['total_present_hours'] = $results[0][0]->total_present_hours;
                $new_result[0]['total_absent_just_hours'] = $results[0][0]->total_absent_just_hours;
                $new_result[0]['total_absent_non_just_hours'] = $results[0][0]->total_absent_non_just_hours;
                $new_result[0]['total_absent_non_point_hours'] = $results[0][0]->total_absent_non_point_hours;



                $new_result[0]['absent_justified_sessions'] = $results[0][0]->absent_justified_sessions;
                $new_result[0]['absent_justified_session_dates'] = $results[0][0]->absent_justified_session_dates;
                $new_result[0]['absent_justified_session_duration'] = $results[0][0]->absent_justified_session_duration;


                // Split the original value by ';'
                $titles = explode(';', $results[0][0]->intituler_absent_justified_session);

                // Array to store modified titles
                $newTitles = [];

                // Loop through each title, extract the first 4 characters and convert them to uppercase
                foreach ($titles as $title) {
                    $firstFourCharacters = strtoupper(substr(trim($title), 0, 4));
                    // Add the modified title to the newTitles array
                    $newTitles[] = $firstFourCharacters;
                }

                // Join the modified titles with ';' to create the new value
                $newValue = implode(';', $newTitles);

                $new_result[0]['intituler_absent_justified_session'] = $newValue;

                

                $titles = explode(';', $results[0][0]->intituler_non_absent_justified_session);

                // Array to store modified titles
                $newTitles = [];

                // Loop through each title, extract the first 4 characters and convert them to uppercase
                foreach ($titles as $title) {
                    $firstFourCharacters = strtoupper(substr(trim($title), 0, 4));
                    // Add the modified title to the newTitles array
                    $newTitles[] = $firstFourCharacters;
                }

                // Join the modified titles with ';' to create the new value
                $newValue = implode(';', $newTitles);


                $new_result[0]['absent_non_justified_sessions'] = $results[0][0]->absent_non_justified_sessions;
                $new_result[0]['absent_non_justified_session_dates'] = $results[0][0]->absent_non_justified_session_dates;
                $new_result[0]['absent_non_justified_session_duration'] = $results[0][0]->absent_non_justified_session_duration;
                $new_result[0]['intituler_non_absent_justified_session'] = $newValue;



                // dd($new_result);

            }

            // dd($new_result);

            $arrayHeader=array(
                'titre','code','type_inter','etat','date_debut','date_fin','nb_heures_af','Nom','Prénom','heures_présence',
                'heures_absence_justifiée',
                'heures_absence_injustifiée',
                'heures_absence_non_renseigné',
                'sessions_ou_absence_justifiée',
                'dates_sessions_ou_absence_justifiée',
                'durée_sessions_ou_absence_justifiée',
                'intituler_absent_justified_session',

                'sessions_ou_absence_non_justifiée',
                'dates_sessions_ou_absence_non_justifiée',
                'durée_sessions_ou_absence_non_justifiée',
                'intituler_non_absent_justified_session'

                // 'num_fact','Createur_fact','Date_fact','Statut_fact','mtt_fact'
            );

            // dd($results);

            $datas['TDBPresence'][0]=$arrayHeader;
            $arrayValues=array();
            // dd($new_result);
            if (count ( $new_result ) > 0) {
                foreach ( $new_result as $key => $d ) {

                    // dd($d);

                    // dd($d[0]->total_present);
                    $array=array();
                    $array [] = ($d['title'] == null) ? "0" : $d['title'];
                    $array [] = ($d['code'] == null) ? "0" : $d['code'];
                    $array [] = ($d['device_type'] == null) ? "0" : $d['device_type'];
                    $array [] = ($d['name'] == null) ? "0" : $d['name'];

                    $array [] = ($request->filter_start == null) ? $d['date_debut'] : $request->filter_start;
                    $array [] = ($request->filter_end == null) ? $d['date_fin'] : $request->filter_end;

                    $array [] = ($d['nb_heures_af'] == null) ? "0" : $d['nb_heures_af'];

                    $array [] = ($d['nom'] == null) ? "0" : $d['nom'];
                    $array [] = ($d['prenom'] == null) ? "0" : $d['prenom'];

                    $array [] = ($d['total_present_hours'] == null) ? "0" : $d['total_present_hours'];
                    $array [] = ($d['total_absent_just_hours'] == null) ? "0" : $d['total_absent_just_hours'];
                    $array [] = ($d['total_absent_non_just_hours'] == null) ? "0" : $d['total_absent_non_just_hours'];
                    $array [] = ($d['total_absent_non_point_hours'] == null) ? "0" : $d['total_absent_non_point_hours'];

                    $array [] = ($d['absent_justified_sessions'] == null) ? "0" : $d['absent_justified_sessions'];
                    $array [] = ($d['absent_justified_session_dates'] == null) ? "0" : $d['absent_justified_session_dates'];
                    $array [] = ($d['absent_justified_session_duration'] == null) ? "0" : $d['absent_justified_session_duration'];
                    $array [] = ($d['intituler_absent_justified_session'] == null) ? "0" : $d['intituler_absent_justified_session'];


                    $array [] = ($d['absent_non_justified_sessions'] == null) ? "0" : $d['absent_non_justified_sessions'];
                    $array [] = ($d['absent_non_justified_session_dates'] == null) ? "0" : $d['absent_non_justified_session_dates'];
                    $array [] = ($d['absent_non_justified_session_duration'] == null) ? "0" : $d['absent_non_justified_session_duration'];
                    $array [] = ($d['intituler_non_absent_justified_session'] == null) ? "0" : $d['intituler_non_absent_justified_session'];


                    $arrayValues[]=$array;
                }
            }

            // dd($arrayValues);
            $datas['TDBPresence'][1]=$arrayValues;
            $datas['TDBPresence'][2]='TDBPresence';

            // $xlsNameFile='presences'.time().'.xlsx';
                if (count($datas)>0){
                    // dd('hnaaa');
                    // $export = new TDBPresenceExport($datas);

                         // Set the content type header
                        //  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        //  header('Content-Disposition: attachment; filename="list.xlsx"');


                    // dd($export);
                    // try {
                    //     $export = new TDBPresenceExport($datas);
                    //     return Excel::download($export, $xlsNameFile, \Maatwebsite\Excel\Excel::XLSX);
                    // } catch (\Exception $e) {
                    //     // Log the exception message
                    //     error_log('Error: ' . $e->getMessage());
                
                    //     // Return a response with a specific error message
                    //     return response('Failed to generate Excel file. Please check server logs for details.', 500);
                    // }
                    // return Excel::download($export, $xlsNameFile);


                    // $data = $this->generateListAf($request);
                    $file_name = 'list.xlsx';

                    $export = new TDBPresenceExport($datas);

                    
                    // delete the file if it already exists
                    Storage::delete('public/exports/' . $file_name);
                    
                    Excel::store($export, 'public/exports/' . $file_name);

                    // get the url of the saved file
                    $url = Storage::url('public/exports/' . $file_name);
                
                    return response()->json(['url' => $url]);
                }
            return 0;


            // dd($status,$results);


        

        // dd($results);
        // dd($sessions[0]->session_codes_non_justif,$sessions[0]->planning_dates_non_justif,count($session_dates));

        // $data = $this->generateListAf($request);
        // $file_name = 'list.xlsx';
        
        // // delete the file if it already exists
        // Storage::delete('public/exports/' . $file_name);
        
        // Excel::store(new ListAfExport($data), 'public/exports/' . $file_name);

        // // get the url of the saved file
        // $url = Storage::url('public/exports/' . $file_name);
    
        // return response()->json(['url' => $url]);
    }

    public function getexportListAf()
    {
        $file_path = storage_path('app/public/exports/list.xlsx');

        if (file_exists($file_path)) {
            return response()->download($file_path);
        } else {
            dd(0);
        }
        
        // if (Storage::exists('public/exports/list.xlsx')) {
        //     return response()->download(storage_path('app/public/exports/list.xlsx'));
        // } else {
        //     dd(0);
        // }

    }
    

    public function updatestate(Request $request){
        $request_data = $request->all();
        $schedules = null;
        $schedules = [];
        $attachment_table_row=[];
        $schedule_contact_table_row=[];
        $absent = null;
        $pointing = null;
        $type_absent = null;

        // dd($request_data);

        if(isset($request_data)){
            if ( $request->file('attachments') && $request_data['schedules_data']['state'] == 'Absent justifié') {  

                $request_data = $request_data['schedules_data'];

                $schedules = json_decode($request_data['schedules']);
                $justifications = $request->file('attachments');

                $absent = true;
                $type_absent = $request_data['state'];
                $pointing = 'absent';


                if($request_data['state'] == 'Absent justifié' && $request->file('attachments')){
                    foreach ($justifications as $justification) {
                        $filename = time () . $justification->getClientOriginalName();
                        $fileextention = $justification->getClientOriginalExtension();
                        Storage::disk( 'public_uploads' )->putFileAs( 'presence/justifications', $justification, $filename );
                        $attachment_table_row[] = Attachment::insertGetId(['name' => $request_data['type_absent'], 'path' => $filename, 'type' => $fileextention]);
                    }
                }
            }
            else{

                $schedules = json_decode($request_data['schedules'][0]);
                $absent = ($request_data['state'] == 'Absent non justifié' || $request_data['state'] == 'Absent justifié');
                $pointing = $request_data['state'];
                $type_absent = null;
                if($absent){
                    $pointing = 'absent';
                    $type_absent = $request_data['state'];
                    if($request_data['state'] == 'Absent justifié')
                        $type_absent = $request_data['type_absent'];
                }
            }
        } 
        else{

            $schedules = json_decode($request_data['schedules'][0]);
            $absent = ($request_data['state'] == 'Absent non justifié' || $request_data['state'] == 'Absent justifié');
            $pointing = $request_data['state'];
            $type_absent = null;
            if($absent){
                $pointing = 'absent';
                $type_absent = $request_data['state'];
                if($request_data['state'] == 'Absent justifié')
                    $type_absent = $request_data['type_absent'];
            }
        }

        // dd($schedules);


        foreach ($schedules as $schedule) {
            // dd($schedule,$absent,$pointing,$type_absent);
            Schedulecontact::where('schedule_id', $schedule->schedule_id)
            ->where('member_id', '=', $schedule->member_id)
            ->update(['is_absent' => $absent, 'pointing' => $pointing, 'type_absent' => $type_absent]);
            if($absent == 'Absent justifié' && $request->file('attachments')){
                $schedulecontact = Schedulecontact::where('schedule_id', $schedule->schedule_id)

                ->where('member_id', '=', $schedule->member_id)->get()->first();


                foreach ($attachment_table_row as $id) {
                    // dd($attachment_table_row,$id,$schedulecontact->id);

                    $media = new Media();
                    $media->attachment_id = $id;
                    $media->table_id = $schedulecontact->id;
                    $media->table_name = 'schedulecontact';
                    $media->save();


                    // Media::create(['attachment_id' => $id, 'table_id' => $schedulecontact->id, 'table_name' => 'schedulecontact']);
                }
            }
        }
    }

    public function formPresence($schedulecontact_id)
    {
        // dd($schedulecontact_id);
        $row = $af=null;
        // if ($schedulecontact_id > 0) {
        //     $row = Session::findOrFail($schedulecontact_id);
        // }
        // if ($af_id > 0) {
        //     $af = Action::findOrFail($af_id);
        // }
        $af_id=null;
        // ABSENT_TYPES
        $absence_types = Param::select('code','name')->where('param_code','ABSENT_TYPES')->get();
        return view('pages.af.presences.form', compact('row', 'af_id','af', 'absence_types'));
    }

    public function formPresenceAttachments($schedule_id,$member_id){
        // echo $schedule_id;
        // echo $member_id;
        $schedulecontact = Schedulecontact::where('schedule_id', $schedule_id)->where('member_id', $member_id)->get()->first();

        $medias = Media::where('table_name', 'schedulecontact')->where('table_id', $schedulecontact->id)->get();

        // dd($media[0]->attachment->path);
        $row = $af=null;
        $af_id=null;
        $absence_types = [];
        return view('pages.af.presences.justification_form', compact('row', 'af_id','af', 'absence_types','medias'));
    }

    // public function downloadexcelfile(Request $request)
    // {
    //     $af_id = $request->input('afsSelectEstimate');
    //     $grp_id = $request->input('groupesSelectFilter');
    //     $selected_value = $request->input('selected_value');



    //     // combine the data into a single array with the headings
    //     $data = array_merge([$groups['headings']], $members['data']);

    //     // export the data to Excel and download the file
    //     $filename = 'presences-export.xlsx';
    //     return Excel::download(new AfExport($data), $filename);
    // }

    function downloadexcelfile(Request $request)
    {
        $af_id = $request->input('afsSelectEstimate');
        $grp_id = $request->input('groupesSelectFilter');
        $selected_value = $request->input('selected_value');
        $member_ids = [];

        if(isset($af_id) && $af_id > 0) {
            $ids_enrollments = Enrollment::select('id')
                ->where([['af_id', $af_id], ['enrollment_type', 'S']])
                ->get()
                ->pluck('id');

            $member_ids = Member::whereIn('enrollment_id', $ids_enrollments)
                ->get('id')
                ->pluck('id')
                ->toArray();
        }

        if(isset($grp_id) && $grp_id > 0) {
            $member_ids = Member::where('group_id', $grp_id)
                ->get('id')
                ->pluck('id')
                ->toArray();
        }

        $data = []; // initialize an empty array to hold the data

        foreach($member_ids as $member_id) {
            // replace this with your actual query to get the desired data
            $member_data = Member::find($member_id);
            $enrollment_data = Enrollment::find($member_data->enrollment_id);
            $row_data = [
                $enrollment_data->af_id,
                $member_data->name,
                $member_data->status,
                $member_data->group_id,
                $enrollment_data->enrollment_type,
                $enrollment_data->num_sessions,
                $enrollment_data->num_hours,
                $enrollment_data->name,
                $enrollment_data->date,
                'Malade',
            ];
            
            $data[] = $row_data; // add the row data to the array
        }

        $export = new AfExport($data);

        return Excel::download($export, 'Presences_Export.xlsx');
    }
}
