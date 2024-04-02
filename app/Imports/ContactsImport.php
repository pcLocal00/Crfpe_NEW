<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use Maatwebsite\Excel\Row;
use App\Models\FileContact;
use App\Models\Attachmentlog;
use App\Library\Services\DbHelperTools;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;

class ContactsImport implements OnEachRow, WithHeadingRow //ToModel

{
    use RemembersRowNumber;
    public $helper;
    public $attachment_id;

    public function __construct($attachment_id)
    {
        $this->helper = new DbHelperTools();
        $this->attachment_id = $attachment_id;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function onRow(Row $row) //model(array $row)
    {
        $rowIndex = $row->getIndex();

        //dd(Attachmentlog::all());
        $entitie = null;
        $entity_exists = false;
        $contact_exist = true;
        $birth_date = null;
        if (isset($row['date_de_naissance'])) {
            $birth_date = $this->transformDate($row['date_de_naissance'], 'd/m/Y');
        }
        
        $contact = null;
        $c_id = 0;
        $row_cols = $row->toArray();
        $row_cols['line'] = $rowIndex;

        if (!$row['prenom'] || !$row['nom'] || !$row['adresse_mail']) {
            $r = new Attachmentlog();
            $r->attachment_id = $this->attachment_id;
            $r->log_desc = 'La ligne ' . $rowIndex . ' du fichier xlsx manque des informations, il est impossible de la traiter.';
            $r->save();
            return;
        }

        //code est renseigné
        if (isset($row['compte_client'])) {
            $entitie = Entitie::where('auxiliary_customer_account', $row['compte_client'])->first();
            //dd($entitie);
            if (!$entitie) {
                //si on ne retrouve pas l’entité par le code auxiliaire => afficher un erreur
                $r = new Attachmentlog();
                $r->attachment_id = $this->attachment_id;
                $r->log_desc = 'Entité introuvable par le code auxiliaire (' . $row['compte_client'] . ') - la ligne ' . $rowIndex . ' du fichier xlsx';
                $r->save();
            }
        } else {
            //Controle doublons
            $contact_search = array(
                'firstname' => $row['prenom'],
                'lastname' => $row['nom'],
                // 'birth_date' => $birth_date->format('Y-m-d'),
            );
            
            $contacts = Contact::select('id', 'entitie_id', 'is_active')->where($contact_search)->get();
            $contact = null;
            $nb_contacts = count($contacts);
            $to_memorise = false;

            if ($nb_contacts == 1) {
                $contact = $contacts->first();
                if (
                    $row['telephone'] != $contact->pro_phone
                    && $row['telephone_mobile'] != $contact->pro_mobile
                    && (!$contact->entitie
                        || $contact->entitie->adresses->filter(function ($a) use ($row) {
                            return $row['code_postal'] == $a->postal_code;
                        })->isEmpty()
                    )
                ) {
                    $to_memorise = true;
                }
            } elseif ($nb_contacts > 1) {
                $to_memorise = true;
            }

            $c_id = ($contact) ? $contact->id : 0;
            if ($to_memorise) {
                $active_contacts = array_filter($contacts->toArray(), function ($c) {
                    return $c['is_active'] == 1;
                });

                $contacts_ids = array_map(function ($c) {
                    return $c['id'];
                }, $active_contacts);
                /* Save contact line */

                FileContact::firstOrCreate([
                    'file_id' =>  $this->attachment_id,
                    'line_info' => json_encode($row_cols),
                    'suggested_contacts' => json_encode(array_values($contacts_ids)),
                    'state' => 'memorized',
                ]);

                /* Add log */
                $r = new Attachmentlog();
                $r->attachment_id = $this->attachment_id;
                $r->log_desc = 'Ligne ' . $rowIndex . ' mémorisée (' . $nb_contacts . ' propositions) - la ligne ' . $rowIndex . ' du fichier xlsx';
                $r->save();

                return;
            }
            //dd($contact);
            $entity_id = ($contact) ? $contact->entitie->id : 0;
            $contact_exist = ($contact) ? true : false;
            $entity_exists = $contact_exist && $entity_id > 0;
            $entity_data = array(
                'id' => $entity_id,
                'ref' => $this->helper->generateEntityCode(),
                'entity_type' => 'P',
                'name' => $row['nom'] . ' ' . $row['prenom'],
                'pro_phone' => $row['telephone_mobile'],
                'pro_mobile' => $row['telephone_mobile'],
                'email' => $row['adresse_mail'],
                'is_client' => 1,
                'is_active' => 1,
            );
            $entity_id = $this->helper->manageEntitie($entity_data);
            $entitie = Entitie::find($entity_id);
        }
        if (isset($entitie)) {
            $contact_search = array(
                'firstname' => $row['prenom'],
                'lastname' => $row['nom'],
                'birth_date' => $birth_date ? $birth_date->format('Y-m-d') : null,
                'entitie_id' => $entitie->id,
            );
            $contact = Contact::select('id', 'entitie_id')->where($contact_search)->first();
            $c_id = ($contact) ? $contact->id : 0;
            $contact_exist = ($contact) ? true : false;
            $adresse_array = array(
                'entitie_id' => $entitie->id,
                'line_1' => $row['adresse_1'],
                'postal_code' => $row['code_postal'],
            );
            $adrRow = Adresse::select('id')->where($adresse_array)->first();
            $adresse_id = ($adrRow) ? $adrRow->id : 0;
            $dataAdresse = array(
                "id" => $adresse_id,
                "entitie_id" => $entitie->id,
                "line_1" => $row['adresse_1'],
                "line_2" => $row['adresse_2'],
                "line_3" => $row['adresse_3'],
                "postal_code" => $row['code_postal'],
                "city" => ($row['commune']) ? $row['commune'] : 'ND',
                "country" => ($row['pays']) ? $row['pays'] : 'ND',
                "is_main_entity_address" => 1,
                "is_billing" => 1,
                "is_formation_site" => 0,
                "is_stage_site" => 0,
            );
            //dump($dataAdresse);
            $adresse_id = $this->helper->manageAdresse($dataAdresse);
            $dataContact = array(
                'id' => $c_id,
                'gender' => $row['civilite'],
                'firstname' => $row['prenom'],
                'lastname' => $row['nom'],
                'pro_phone' => $row['telephone_mobile'],
                'pro_mobile' => $row['telephone_mobile'],
                'nationality' => $row['pays'],
                'email' => $row['adresse_mail'],
                'entitie_id' => $entitie->id,
                'is_active' => 1,
                'is_main_contact' => 1,
                'birth_date' => $birth_date,
            );
            $contact_id = $this->helper->manageContact($dataContact);
            //dd($contact_id);
            if ($contact_id > 0) {
                FileContact::firstOrCreate([
                    'file_id' =>  $this->attachment_id,
                    'contact_id' => $contact_id,
                    'line_info' => json_encode($row_cols),
                    'state' => ($contact_exist) ? 'old' : 'new',
                ]);
            }
        }

        /* Auxiliary account update */
        $codes_updates = [];
        if (!isset($row['compte_client'])) {
            if (!$entity_exists || !$entitie->auxiliary_customer_accoun) {
                $auxiliary_customer_account = $this->helper->generateAuxiliaryAccountForEntity($entitie->id);
                $codes_updates['auxiliary_customer_account'] = $auxiliary_customer_account;
            }
            if (!$entity_exists || !$entitie->collective_customer_account) {
                $collective_customer_account = $this->helper->generateCodeCollectifs($entitie->id);
                $codes_updates['collective_customer_account'] = $collective_customer_account;
            }
            if (!empty($codes_updates)) {
                Entitie::where('id', $entitie->id)->update($codes_updates);
            }
        }
    }
    public function transformDate($value, $format = 'Y-m-d')
    {
        try {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }
    public function uniqueBy()
    {
        return 'email';
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required|string',
            'prenom' => 'required|string',
            'telephone_mobile' => 'required|numeric',
            'pays' => 'required|numeric',
            'adresse_mail' => 'required|numeric',
            'commune' => 'required|numeric',
        ];
    }
}
