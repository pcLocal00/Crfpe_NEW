<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Profil;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $page_title = 'Gestion des utilisateurs';
        $page_description = '';
        return view('pages.user.list',compact('page_title', 'page_description'));
    }
    public function sdtUsers(Request $request)
    {
        $tools=new PublicTools();
        $dtRequests = $request->all();
        $data=$meta=[];
        //$datas=User::all();
        $datas = User::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('login', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('name', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('lastname', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('email', 'like', '%'.$request->filter_text.'%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if(!empty($request->filter_start) && !empty($request->filter_end)){
                        $start = Carbon::createFromFormat('d/m/Y',$request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y',$request->filter_end);
                        $datas->whereBetween('created_at', [$start." 00:00:00", $end." 23:59:59"]);
                    }
                }

                if ($request->has('filter_activation') && !empty($request->filter_activation)) {
                    $is_active = ($request->filter_activation=='a')?1:0;
                    $datas->where('active',$is_active);
                }

            }else{
                $datas=User::orderByDesc('id');
            }
        }
        $udatas=$datas->orderByDesc('id')->get();
        foreach ($udatas as $d) {
            $row=array();
                //ID
                $row[]=$d->id;
                //<th>Identifiant</th>
                $row[]=$d->login;
                //<th>Nom</th>
                $row[]=$d->name.' '.$d->lastname;
                //<th>E-mail</th>
                $row[]=$d->email;
                //<th>Rôle</th>
                $roles = '';
                $selectedRoles=$d->roles->pluck('name');
                if(count($selectedRoles)>0){
                    foreach($selectedRoles as $role){
                        $roles .= $tools->constructParagraphLabelDot('xs','primary',$role);
                    }
                }
                $row[]=$roles;
                //Date creation
                $labelActive='Désactivé';
                $cssClassActive='danger';
                if($d->active==1){
                    $labelActive='Activé';
                    $cssClassActive='success';
                }
                /* $spanActive = '<div class="text-dark-75 mb-2"><span class="label label-lg  label-light-'.$cssClassActive.' label-inline">'.$labelActive.'</span></div>';
                $created_at='<div class="mb-2"><span class="label label-outline-info label-inline">C : '.$d->created_at->format('d/m/Y H:i').'</span></div>';
                $updated_at='<div class="mb-2"><span class="label label-outline-info label-inline">M : '.$d->updated_at->format('d/m/Y H:i').'</span></div>'; */
                
                $spanActive = $tools->constructParagraphLabelDot('xs',$cssClassActive,$labelActive);
                if(isset($d->created_at)){
                    $created_at = $tools->constructParagraphLabelDot('xs','primary','C : '.$d->created_at->format('d/m/Y H:i'));
                } else {
                    $created_at = ''; 
                }
                
                if(isset($d->updated_at)){
                    $updated_at = $tools->constructParagraphLabelDot('xs','warning','M : '.$d->updated_at->format('d/m/Y H:i'));
                } else {
                    $updated_at = ''; 
                }
                $row[]=$spanActive.$created_at.$updated_at;
                //Actions
                $btn_edit='<button class="btn btn-sm btn-clean btn-icon" onclick="_formUser('.$d->id.')" title="Edition"><i class="'.$tools->getIconeByAction('EDIT').'"></i></button>';
                $btn_view='<button class="btn btn-sm btn-clean btn-icon" onclick="_viewUser('.$d->id.')" title="Edition"><i class="'.$tools->getIconeByAction('VIEW').'"></i></button>';
                $btn_delete='<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteUser('.$d->id.')" title="Suppression"><i class="'.$tools->getIconeByAction('DELETE').'"></i></button>';
                $row[]=$btn_edit;
            $data[]=$row;
        }
        $sort  = ! empty($dtRequests['sort']['sort']) ? $dtRequests['sort']['sort'] : 'asc';
        $field = ! empty($dtRequests['sort']['field']) ? $dtRequests['sort']['field'] : 'ID';
        $page    = ! empty($dtRequests['pagination']['page']) ? (int)$dtRequests['pagination']['page'] : 1;
        $perpage = ! empty($dtRequests['pagination']['perpage']) ? (int)$dtRequests['pagination']['perpage'] : -1;
        $pages = 1;
        $total = count($data); // total items in array
        $meta = [
            'page'    => $page,
            'pages'   => $pages,
            'perpage' => $perpage,
            'total'   => $total,
            'sort'  => $sort,
            'field' => $field,
        ];           
        $result = [
            'meta' => $meta,
            'data' => $data,
        ];
        return response()->json($result);
    }
    public function formUser($row_id)
    {
        $row = null;
        $profiles = Profil::all();
        $roles = Role::all();
        $collectionRolesForUser = collect([]);
        if ($row_id > 0) {
          $row = User::findOrFail ( $row_id );
          $idsSelectedRoles=$row->roles->pluck('id');
          if(count($idsSelectedRoles)){
             $collectionRolesForUser = collect($idsSelectedRoles); 
          }
        }

        return view('pages.user.form',['row'=>$row,'profiles'=>$profiles,'roles'=>$roles,'collectionRolesForUser'=>$collectionRolesForUser]);
    }
    public function storeFormUser(Request $request)
    {   
        // $datas = Contact::latest();
        // $id="";

        // if (($request->has('name') && !empty($request->name)) || ($request->has('lastname') && !empty($request->lastname)) || ($request->has('email') && !empty($request->email))) {
        //     $datas->where('firstname', 'like', '%' . $request->name . '%')
        //         ->orWhere('lastname', 'like', '%' . $request->lastname . '%')
        //         ->orWhere('email', 'like', '%' . $request->email . '%'); 
        // }

        // $records=$datas->get();
        // foreach ($records as $key => $value) {
        //     $id=$value->id;
        // }

        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $rules = [
                'email' => ($request->id>0)?'required':'required|unique:App\Models\User',
                'login' => ($request->id>0)?'required':'required|unique:App\Models\User',
            ];
            $messages = [
                'email.unique' => 'Cette adresse email est déjà utilisé !',
                'login.unique' => 'Ce login est déjà utilisé !',
            ];
            $validator = Validator::make($request->all(),$rules,$messages);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $msg = '<p>Veuillez vérifier les erreurs ci-dessous : </p>';
                foreach ($errors->get('email') as $message) {
                    $msg .= '<p class="text-danger">'.$message.'</p>';
                }
                foreach ($errors->get('login') as $message) {
                    $msg .= '<p class="text-danger">'.$message.'</p>';
                }
                $success = false;
            }else{
                $DbHelperTools=new DbHelperTools();
                $data = $request->all();
                $user_id = $DbHelperTools->manageUser($data, (int) $request->filter_responsable);
                //On supprime si exist
                $DbHelperTools->detachRolesUser($user_id);
                //Add roles
                if(count($request->roles)>0){
                    foreach($request->roles as $role_id) {
                        $DbHelperTools->attachUserRoles($user_id,$role_id);
                    }
                }
                $success = true;
                $msg = 'L\'utilisateur a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    public function generateRandomPassword(){
        $publicTools=new PublicTools();
        $password = $publicTools->generateRandomPassword();
        return response()->json(['password'=>$password]); 
    }
    public function generateRandomLogin($user_id){
        $DbHelperTools=new DbHelperTools();
        $login = $DbHelperTools->generateRandomLogin($user_id);
        return response()->json(['login'=>$login]); 
    }
}
