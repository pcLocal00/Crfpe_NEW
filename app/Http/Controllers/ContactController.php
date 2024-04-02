<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Profil;
use App\Models\Contact;
use App\Models\Entitie;
use Illuminate\Http\Request;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use App\Models\Adresse;
use App\Models\Param;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getContacts(Request $request, ?int $entity_id = null)
    {
        $is_intern = $request->has('intern') && $request->get('intern');
        $datas = Contact::select('en_contacts.*');

        if ($is_intern) {
            $datas = $datas->join('users', 'users.contact_id', 'en_contacts.id')
                ->join('par_usr_role_user', 'par_usr_role_user.user_id', 'users.id')
                ->join('par_usr_roles', 'par_usr_roles.id', 'par_usr_role_user.role_id')
                ->join('par_usr_profils', 'par_usr_profils.id', 'par_usr_roles.profil_id')
                ->where('users.active', 1)
                ->where('par_usr_profils.id', 4);
        }

        if ($entity_id) {
            $datas = $datas->where('en_contacts.entitie_id', $entity_id);
        }

        $datas = $datas->groupBy('en_contacts.id')->get()->toArray();
        $contacts = [];

        if (count($datas) > 0) {
            foreach ($datas as $row) {
                if ($row['firstname'] != null && $row['lastname'] != null)
                    $contacts[] = ['id' => $row['id'], 'name' => $row['firstname'] . ' ' . $row['lastname']];
            }
        }

        return response()->json($contacts);
    }

    public function getContactsWithNoUsers()
    {
        $datas = Contact::whereRaw('id NOT IN (SELECT contact_id FROM users WHERE contact_id > 0)')
            ->where('is_active', 1);
        $datas = $datas->get()->toArray();
        $contacts = [];

        if (count($datas) > 0) {
            foreach ($datas as $row) {
                if ($row['firstname'] != null && $row['lastname'] != null)
                    $contacts[] = ['id' => $row['id'], 'name' => $row['firstname'] . ' ' . $row['lastname']];
            }
        }

        return response()->json($contacts);
    }

    public function getEntities()
    {
        $result = $datas = [];
        $datas = Entitie::all();

        if (count($datas) > 0) {
            foreach ($datas as $row) {
                if ($row['name'] != null)
                    $result[] = ['id' => $row['id'], 'name' => $row['name']];
            }
        }

        return response()->json($result);
    }

    public function getContact($id)
    {
        $datas = Contact::findOrFail($id);

        $contacts = ['firstname' => $datas->firstname, 'lastname' => $datas->lastname, 'email' => $datas->email];

        return response()->json($contacts);
    }

    public function getStatsContact($id)
    {
        $datas = Contact::findOrFail($id);

        $contacts = ['firstname' => $datas->firstname, 'lastname' => $datas->lastname];

        return response()->json($contacts);
    }

    public function getStatsTable()
    {

        $etats = Param::where([['param_code', 'Etat'], ['is_active', 1]])->orderBy('id')->take(7)->pluck('id');
        $table_stats = DB::select(DB::raw("SELECT CONCAT(r.firstname,' ' , r.lastname) as nom, s1.etat_1, s2.etat_2, s3.etat_3, s4.etat_4, s5.etat_5

FROM `tasks` t
JOIN en_contacts r on r.id = t.responsable_id
 left join (SELECT r.id as resp, count(t.id) as etat_1 FROM en_contacts r LEFT JOIN tasks t on t.responsable_id = r.id WHERE t.etat_id = " . $etats[0] . " GROUP BY r.id) s1 on s1.resp = r.id
 left join (SELECT r.id as resp, count(t.id) as etat_2 FROM en_contacts r LEFT JOIN tasks t on t.responsable_id = r.id WHERE t.etat_id = " . $etats[1] . " GROUP BY r.id) s2 on s2.resp = r.id
 left join (SELECT r.id as resp, count(t.id) as etat_3 FROM en_contacts r LEFT JOIN tasks t on t.responsable_id = r.id WHERE t.etat_id = " . $etats[2] . " GROUP BY r.id) s3 on s3.resp = r.id
 left join (SELECT r.id as resp, count(t.id) as etat_4 FROM en_contacts r LEFT JOIN tasks t on t.responsable_id = r.id WHERE t.etat_id = " . $etats[3] . " GROUP BY r.id) s4 on s4.resp = r.id
 left join (SELECT r.id as resp, count(t.id) as etat_5 FROM en_contacts r LEFT JOIN tasks t on t.responsable_id = r.id WHERE t.etat_id = " . $etats[4] . " GROUP BY r.id) s5 on s5.resp = r.id
 left join (SELECT r.id as resp, count(t.id) as etat_6 FROM en_contacts r LEFT JOIN tasks t on t.responsable_id = r.id WHERE t.etat_id = " . $etats[5] . " GROUP BY r.id) s6 on s6.resp = r.id
 left join (SELECT r.id as resp, count(t.id) as etat_7 FROM en_contacts r LEFT JOIN tasks t on t.responsable_id = r.id WHERE t.etat_id = " . $etats[6] . " GROUP BY r.id) s7 on s7.resp = r.id;"));
        //dd($table_stats);
        $result = [
            'meta' => 0,
            'data' => $table_stats,
            "recordsTotal" => 200,
            "recordsFiltered" => 200,
        ];
        return response()->json($result);
    }

    public function getContactData($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->adresse = $contact->entitie->adresses->first()->toArray() ?? null;
        return response()->json($contact->toArray());
    }

    public function entitiesDeepSearch()
    {
        $entities = Entitie::where('is_active', 1)->get();

        return view('pages.contact.forms.entities_deep_serach', compact('entities'));
    }

    public function loadDeepSearchEntities(Request $request)
    {
        $entities = Entitie::leftjoin('en_adresses', 'en_adresses.entitie_id', 'en_entities.id')
            ->leftjoin('en_contacts', 'en_contacts.entitie_id', 'en_entities.id')
            ->where('en_entities.is_active', 1)
            ->groupBy('en_entities.id');

        // $entities = $entities->orderBy('\'adresses\' desc');
        if ($request->search['value'] !== null) {
            $keyword = $request->get('search')['value'];
            $entities = $entities->where('en_entities.ref', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_contacts.firstname', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_contacts.lastname', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_contacts.email', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_adresses.line_1', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_adresses.line_2', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_adresses.line_3', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_adresses.postal_code', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_adresses.city', 'like', "%$keyword%");
            $entities = $entities->orWhere('en_adresses.country', 'like', "%$keyword%");
            if (preg_match('/(personne|société)/i', $keyword)) {
                $entities = $entities->orWhereRaw("en_entities.entity_type LIKE '" . $keyword[0]."'");
            }
        }

        $count = clone $entities;
        $recordTotal = $count->get()->count();

        /* order */
        // dd($request->order);
        $order_column = $request->order[0]['column'] ?? 0;
        $order_dir = $request->order[0]['dir'] ?? 'asc';

        switch ($order_column) {
            case 1:
                $entities = $entities->orderBy('en_entities.ref', $order_dir);
                break;
            case 2:
                $entities = $entities
                    ->orderBy('en_adresses.line_1', $order_dir)
                    ->orderBy('en_adresses.line_2', $order_dir)
                    ->orderBy('en_adresses.line_3', $order_dir)
                    ->orderBy('en_adresses.postal_code', $order_dir)
                    ->orderBy('en_adresses.city', $order_dir);
                break;
                /* case 3:
                $entities = $entities->orderBy('en_entities.entity_type', $order_dir);
                break; */
            case 4:
                $entities = $entities
                    ->orderBy('en_contacts.firstname', $order_dir)
                    ->orderBy('en_contacts.lastname', $order_dir)
                    ->orderBy('en_contacts.email', $order_dir);
                break;
            default:
                $entities = $entities->orderBy('en_entities.entity_type', $order_dir);
                break;
        }

        $entities = $entities->selectRaw('
        en_entities.entity_type as entity_type,
        en_entities.ref as ref,
        en_entities.name as name,
        GROUP_CONCAT(CONCAT(
            IFNULL(en_adresses.line_1, \'\'),\' \',
            IFNULL(en_adresses.line_2, \'\'),\' \',
            IFNULL(en_adresses.line_3, \'\'),\' \',
            en_adresses.postal_code,\' \',
            en_adresses.city
        )  SEPARATOR \'##\') as addresses,
        en_entities.is_client as is_client,
        en_entities.is_funder as is_funder,
        en_entities.is_former as is_former,
        en_entities.is_stage_site as is_stage_site,
        en_entities.is_prospect as is_prospect,
        GROUP_CONCAT(CONCAT(
            en_contacts.id,\'*\',
            en_contacts.firstname,\' \',
            en_contacts.lastname,\'*\',
            IFNULL(en_contacts.email, \'email non renseigné\')
        ) SEPARATOR \'##\') as contacts,
        en_entities.id as id
        ')->skip($request->start)->take($request->length)->get();

        $result = [
            'draw' => $request->draw,
            'data' => $entities,
            "recordsTotal" => $recordTotal,
            "recordsFiltered" => $recordTotal,
        ];

        return response()->json($result);
    }
}
