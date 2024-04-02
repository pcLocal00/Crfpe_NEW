<?php

namespace App\Library\Services;

use Carbon\Carbon;
use DateTime;
use App\Models\Role;
use App\Models\User;
use App\Models\Group;
use App\Models\Param;
use App\Models\Price;
use App\Models\Sheet;
use App\Models\Action;
use App\Models\Member;
use App\Models\Refund;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Funding;
use App\Models\Invoice;
use App\Models\Session;
use App\Models\Setting;
use App\Models\Contract;
use App\Models\Estimate;
use App\Models\Schedule;
use Carbon\CarbonPeriod;
use App\Models\Agreement;
use App\Models\Categorie;
use App\Models\Formation;
use App\Models\Groupment;
use App\Models\Helpindex;
use App\Models\Ressource;
use App\Models\Attachment;
use App\Models\Enrollment;
use App\Models\Sheetparam;
use App\Models\Certificate;
use App\Models\Convocation;
use App\Models\Invoiceitem;
use App\Models\Sessiondate;
use App\Models\Estimateitem;
use App\Models\Agreementitem;
use App\Models\Documentmodel;
use App\Models\Emailmodel;
use App\Models\Studentstatus;
use App\Models\Timestructure;
use App\Models\Fundingpayment;
use App\Models\Groupmentgroup;
use App\Models\Invoicepayment;
use App\Models\Templateperiod;
use App\Library\Helpers\Helper;
use App\Models\AfSchedulegroup;
use App\Models\Schedulecontact;
use App\Models\Planningtemplate;
use App\Models\Internshiproposal;
use App\Models\Scheduleressource;
use App\Models\Task;
use App\Models\Comment;
use App\Models\GedSignature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Timestructurecategory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DbHelperTools
{
    const PF_TYPE_FORMATION = "PF_TYPE_FORMATION";
    const PF_STATUS_FORMATION = "PF_STATUS_FORMATION";
    const PF_STATE_FORMATION = "PF_STATE_FORMATION";

    /*
      Create / Edit Formation
      Table pf_formation
    */
    public function manageFormation($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Formation();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Formation::find($row_id);
                if (!$row) {
                    $row = new Formation();
                }
            }
            if (isset($data['code'])) {
                $row->code = (isset($data['code'])) ? $data['code'] : null;
            }
            $row->title = (isset($data['title'])) ? $data['title'] : null;
            $row->description = (isset($data['description'])) ? $data['description'] : null;
            $row->max_availability = (isset($data['max_availability'])) ? $data['max_availability'] : null;
            $row->nb_days = (isset($data['nb_days'])) ? $data['nb_days'] : null;
            $row->nb_hours = (isset($data['nb_hours'])) ? $data['nb_hours'] : null;
            $row->nb_pratical_days = (isset($data['nb_pratical_days'])) ? $data['nb_pratical_days'] : null;
            $row->nb_pratical_hours = (isset($data['nb_pratical_hours'])) ? $data['nb_pratical_hours'] : null;
            $row->bpf_main_objective = (isset($data['bpf_main_objective'])) ? $data['bpf_main_objective'] : null;
            $row->bpf_training_specialty = (isset($data['bpf_training_specialty'])) ? $data['bpf_training_specialty'] : null;
            $row->accounting_code = (isset($data['accounting_code'])) ? $data['accounting_code'] : null;
            $row->analytical_codes = (isset($data['analytical_codes'])) ? $data['analytical_codes'] : null;
            $row->autorize_af = (isset($data['autorize_af'])) ? $data['autorize_af'] : 0;
            $row->categorie_id = (isset($data['categorie_id'])) ? $data['categorie_id'] : null;
            //v2
            $row->product_type = (isset($data['product_type'])) ? $data['product_type'] : null;
            $row->sort = (isset($data['sort'])) ? $data['sort'] : 0;
            $row->timestructure_id = (isset($data['timestructure_id'])) ? $data['timestructure_id'] : null;
            $row->timestructure_sort = (isset($data['timestructure_sort'])) ? $data['timestructure_sort'] : 1;
            $row->parent_id = (isset($data['parent_id'])) ? $data['parent_id'] : null;
            $row->nb_session_duplication = (isset($data['nb_session_duplication'])) ? $data['nb_session_duplication'] : 1;
            $row->nb_sessiondates = (isset($data['nb_sessiondates'])) ? $data['nb_sessiondates'] : 1;
            $row->is_evaluation = (isset($data['is_evaluation'])) ? $data['is_evaluation'] : 0;
            $row->ects = (isset($data['ects'])) ? $data['ects'] : null;
            $row->coefficient = (isset($data['coefficient'])) ? $data['coefficient'] : null;
            $row->save();
            $id = $row->id;
            //Type de formation
            $param_type_id = (isset($data['param_type_id'])) ? $data['param_type_id'] : 0;
            $this->attachFormationParams($id, $param_type_id, self::PF_TYPE_FORMATION);
            //Status de formation
            $param_status_id = (isset($data['param_status_id'])) ? $data['param_status_id'] : 0;
            $this->attachFormationParams($id, $param_status_id, self::PF_STATUS_FORMATION);
            //Etat de formation
            $param_state_id = (isset($data['param_state_id'])) ? $data['param_state_id'] : 0;
            $this->attachFormationParams($id, $param_state_id, self::PF_STATE_FORMATION);
        }
        return $id;
    }

    /*
      Attacher les params a la formation
      Type, status, etat de formation
      Table pf_formation_param
    */
    public function attachFormationParams($formation_id, $param_id, $param_code)
    {
        $success = false;
        if ($formation_id && $param_id) {
            $param = Param::findOrFail($param_id);
            $pf = Formation::findOrFail($formation_id);
            //On supprime si exist
            $this->detachParamsFormation($formation_id, $param_code);
            // On attach
            $pf->params()->attach($param_id);
            $success = true;
        }
        return $success;
    }

    public function detachParamsFormation($formation_id, $param_code)
    {
        $params = Param::where('param_code', $param_code)->get();
        foreach ($params as $param) {
            $param->formations()->detach($formation_id);
        }
    }

    /*
      Détacher les params de la formation
      Type, status, etat de formation
      Table pf_formation_param
    */
    public function detachFormationParams($formation_id, $param_id)
    {
        $success = false;
        if ($formation_id && $param_id) {
            $row = Formation::find($formation_id);
            $row->params()->detach($param_id);
            $success = true;
        }
        return $success;
    }

    /*
      Create / Update Params
      Table par_pf_params
    */
    public function manageParams($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Param();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Param::find($row_id);
                if (!$row) {
                    $row = new Param();
                }
            }
            $row->param_code = (isset($data['param_code'])) ? $data['param_code'] : null;
            $row->param_name = (isset($data['param_name'])) ? $data['param_name'] : null;
            $row->code = (isset($data['code'])) ? $data['code'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->css_class = (isset($data['css_class'])) ? $data['css_class'] : null;
            $row->order_show = (isset($data['order_show'])) ? $data['order_show'] : 1;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->accounting_code = (isset($data['accounting_code'])) ? $data['accounting_code'] : null;
            $row->analytical_code = (isset($data['analytical_code'])) ? $data['analytical_code'] : null;
            $row->amount = (isset($data['amount'])) ? $data['amount'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageModel($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Timestructurecategory();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Timestructurecategory::find($row_id);
                if (!$row) {
                    $row = new Timestructurecategory();
                }
            }

            $row->code = (isset($data['code'])) ? $data['code'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->sort = (isset($data['order_show'])) ? $data['order_show'] : 1;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageStructure($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Timestructure();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Timestructure::find($row_id);
                if (!$row) {
                    $row = new Timestructure();
                }
            }
            $row->category_id = (isset($data['model_id'])) ? $data['model_id'] : null;
            $row->sort = (isset($data['order_show'])) ? $data['order_show'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->parent_id = (isset($data['parent_id'])) ? $data['parent_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    /*
      Create / Update Categories
      Table par_pf_categories
    */
    public function manageCategories($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Categorie();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Categorie::find($row_id);
                if (!$row) {
                    $row = new Categorie();
                }
            }
            $row->code = (isset($data['code'])) ? $data['code'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->description = (isset($data['description'])) ? $data['description'] : null;
            $row->site_name = (isset($data['site_name'])) ? $data['site_name'] : null;
            $row->site_broadcast = (isset($data['site_broadcast'])) ? $data['site_broadcast'] : 0;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->order_show = (isset($data['order_show'])) ? $data['order_show'] : 0;
            $row->archival_reason = (isset($data['archival_reason'])) ? $data['archival_reason'] : null;
            $row->categorie_id = (isset($data['categorie_id'])) ? $data['categorie_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    /*
      Create / Update Sheets
      Table pf_sheets
    */
    public function manageSheets($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Sheet();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Sheet::find($row_id);
                if (!$row) {
                    $row = new Sheet();
                }
            }
            $row->ft_code = (isset($data['ft_code'])) ? $data['ft_code'] : null;
            $row->version = (isset($data['version'])) ? $data['version'] : 0;
            $row->description = (isset($data['description'])) ? $data['description'] : null;
            $row->is_default = (isset($data['is_default'])) ? $data['is_default'] : 0;
            $row->param_id = (isset($data['param_id'])) ? $data['param_id'] : null;
            $row->formation_id = (isset($data['formation_id'])) ? $data['formation_id'] : null;
            $row->action_id = (isset($data['action_id'])) ? $data['action_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    /*
      Create / Update Groups
      Table af_groups
    */
    public function manageGroups($data)
    {

        $id = 0;
        if (count($data) > 0) {
            $row = new Group();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Group::find($row_id);
                if (!$row) {
                    $row = new Group();
                }
            }
            $row->title = (isset($data['title'])) ? $data['title'] : null;
            $row->ref_contact_id = (isset($data['ref_contact_id'])) ? $data['ref_contact_id'] : null;
            $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : 0;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    /*
      Create / Update SheetsParams
      Table pf_sheet_param
    */
    public function manageSheetsParams($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Sheetparam();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Sheetparam::find($row_id);
                if (!$row) {
                    $row = new Sheetparam();
                }
            }
            $row->title = (isset($data['title'])) ? $data['title'] : null;
            $row->content = (isset($data['content'])) ? $data['content'] : null;
            $row->order_show = (isset($data['order_show'])) ? $data['order_show'] : 1;
            $row->sheet_id = (isset($data['sheet_id'])) ? $data['sheet_id'] : null;
            $row->param_id = (isset($data['param_id'])) ? $data['param_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function getParamsByParamCode($param_code)
    {
        $params = Param::select('id', 'param_code', 'code', 'name')->where([['is_active', 1], ['param_code', $param_code]])->orderBy('order_show')->get();
        return $params->toArray();
    }

    public function getFullPathCategories($row_id)
    {
        $html = '';
        if ($row_id > 0) {
            $row = Categorie::find($row_id);
            if ($row->categorie_id == null) {
                return $row->name . ' > ';
            }
            $html = $this->getFullPathCategories($row->parent_categorie->id) . $row->name . ' > ';
        }
        return $html;
    }

    public function resetIsDefaultSheetFormation($formation_id)
    {
        if ($formation_id > 0) {
            $rsParam = Param::select('id')->where([['param_code', 'PF_STATE_SHEETS'], ['code', 'PF_STATE_SHEETS_ARC']])->first();
            //dd($rsParam);
            $datas = Sheet::where('formation_id', $formation_id)->get();
            if ($datas) {
                foreach ($datas as $sheet) {
                    $sheet->is_default = 0;
                    if (isset($rsParam)) {
                        $param_id = $rsParam->id;
                        $sheet->param_id = $param_id; //Archivée//
                    }
                    $sheet->save();
                }
            }
        }
        return true;
    }

    public function generatePfCode($formation_id, $categorie_id)
    {
        $code = '';
        $new_formation_id = $formation_id;
        if ($formation_id == 0) {
            $lastFormation = Formation::select('id')->orderByDesc('id')->first();
            $last_formation_id = ($lastFormation && $lastFormation['id']) ? $lastFormation['id'] : 0;
            $new_formation_id = $last_formation_id + 1;
        }
        if ($categorie_id > 0) {
            $row = Categorie::find($categorie_id);
            $treeStructure = $row->code . '_';
            //$treeStructure=$this->getCodeTreeStructure($categorie_id);
            //$code = 'PFO_'.$treeStructure.sprintf('%06d',$new_formation_id);
            $code = 'PFO_' . $treeStructure . $new_formation_id;
        } else {
            $code = 'PFO_' . $new_formation_id;
        }
        return $code;
    }

    public function generateCodeForCategorieProcess($data)
    {
        $code = '';
        if (count($data) > 0) {
            $categorie_id = $data['categorie_id'];
            $parent_categorie_id = $data['parent_categorie_id'];
            $categorie_name = trim($data['categorie_name']);
            $rs_code = strtoupper(substr($categorie_name, 0, 2));
            $new_categorie_id = $categorie_id;
            if ($categorie_id == 0) {
                $lastCategorie = Categorie::select('id')->orderByDesc('id')->first();
                $last_categorie_id = ($lastCategorie && $lastCategorie['id']) ? $lastCategorie['id'] : 0;
                $new_categorie_id = $last_categorie_id + 1;
            }
            if ($parent_categorie_id > 0) {
                //$treeStructure=$this->getCodeTreeStructure($parent_categorie_id);
                //$code = $treeStructure.$rs_code.$new_categorie_id;
                $parentCategorie = Categorie::find($parent_categorie_id);
                $code = $parentCategorie->code . '_' . $rs_code . $new_categorie_id;
            } else {
                $code = $rs_code . $new_categorie_id;
            }
        }
        return $code;
    }

    public function getCodeTreeStructure($row_id)
    {
        $treeStructure = '';
        if ($row_id > 0) {
            $row = Categorie::find($row_id);
            if ($row->categorie_id == null) {
                return $row->code . '_';
            }
            $treeStructure = $this->getCodeTreeStructure($row->parent_categorie->id) . $row->code . '_';
        }
        return $treeStructure;
    }

    public function generateVesrionAndCodeForSheet($formation_id)
    {
        $version = 0;
        $code = '';
        if ($formation_id > 0) {
            $maxVersion = Sheet::where('formation_id', $formation_id)->max('version');
            $count = Sheet::where('formation_id', $formation_id)->count();
            //dd($count);
            $version = ($count > 0) ? $maxVersion + 1 : 0;
            //$version=$maxVersion+1;
            $code = 'PFO' . $formation_id . '_FT' . $version;
        }
        return ['code' => $code, 'version' => $version];
    }

    public function generateVesrionAndCodeForAfSheet($af_id)
    {
        $version = 0;
        $code = '';
        if ($af_id > 0) {
            $maxVersion = Sheet::where('action_id', $af_id)->whereNull('formation_id')->max('version');
            $count = Sheet::where('action_id', $af_id)->whereNull('formation_id')->count();
            $version = ($count > 0) ? $maxVersion + 1 : 0;
            $code = 'AF' . $af_id . '_FT' . $version;
        }
        return ['code' => $code, 'version' => $version];
    }

    public function massDeletes($ids, $type, $force_delete)
    {
        $deletedRows = 0;
        if ($type == 'formation') {
            if ($force_delete == 1) {
                $deletedRows = Formation::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Formation::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'sheet') {
            if ($force_delete == 1) {
                $deletedRows = Sheet::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Sheet::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'sheetparam') {
            if ($force_delete == 1) {
                $deletedRows = Sheetparam::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Sheetparam::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'categorie') {
            if ($force_delete == 1) {
                $deletedRows = Categorie::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Categorie::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'enrollment') {
            if ($force_delete == 1) {
                $deletedRows = Enrollment::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Enrollment::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'member') {
            if ($force_delete == 1) {
                $deletedRows = Member::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Member::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'schedulecontact') {
            if ($force_delete == 1) {
                $deletedRows = Schedulecontact::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Schedulecontact::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'scheduleressource') {
            if ($force_delete == 1) {
                $deletedRows = Scheduleressource::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Scheduleressource::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'contract') {
            if ($force_delete == 1) {
                $deletedRows = Contract::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Contract::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'funding') {
            $deletedRows = Fundingpayment::where('funding_id', $ids[0])->forceDelete();
            $row = Funding::findOrFail($ids[0]);
            if ($force_delete == 1) {
                $row->forceDelete();
            } else {
                $row->delete();
            }
        } elseif ($type == 'fundingpayment') {
            if ($force_delete == 1) {
                $deletedRows = Fundingpayment::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Fundingpayment::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'schedule') {
            if ($force_delete == 1) {
                $deletedRows = Schedule::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Schedule::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'sessiondate') {
            if ($force_delete == 1) {
                $deletedRows = Sessiondate::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Sessiondate::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'session') {
            if ($force_delete == 1) {
                $deletedRows = Session::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Session::whereIn('id', $ids)->delete();
            }
        } elseif ($type == 'invoiceitem') {
            if ($force_delete == 1) {
                $deletedRows = Invoiceitem::whereIn('id', $ids)->forceDelete();
            } else {
                $deletedRows = Invoiceitem::whereIn('id', $ids)->delete();
            }
        }
        return $deletedRows;
    }

    public function deleteFormationParams($formation_id)
    {
        $deletedRows = 0;
        if ($formation_id > 0) {
            $deletedRows = DB::table('pf_formation_param')->where('formation_id', $formation_id)->delete();
        }
        return $deletedRows;
    }

    public function archiveFormation($formation_id)
    {
        $success = false;
        if ($formation_id) {
            $deletedRows = $this->massDeletes([$formation_id], 'formation', 0);
            if ($deletedRows > 0) {
                $ids_sheets = Sheet::where('formation_id', $formation_id)->get()->pluck('id');
                if (count($ids_sheets) > 0) {
                    $ids_sheetparams = Sheetparam::whereIn('sheet_id', $ids_sheets)->get()->pluck('id');
                    if (count($ids_sheetparams)) {
                        $deletedRowsSheetsParams = $this->massDeletes($ids_sheetparams, 'sheetparam', 0);
                    }
                    $deletedRowsSheets = $this->massDeletes($ids_sheets, 'sheet', 0);
                }
                $success = true;
            }
        }
        return $success;
    }

    public function archiveCategorieProcess($categorie_id, $motif_text)
    {
        $success = false;
        if ($categorie_id) {
            //Save motif archivage
            if (!empty($motif_text)) {
                $row = Categorie::find($categorie_id);
                $row->archival_reason = $motif_text;
                $row->save();
            }
            //soft delete
            $archivedRows = $this->massDeletes([$categorie_id], 'categorie', 0);
            if ($archivedRows > 0) {
                $ids_formations = Formation::where('categorie_id', $categorie_id)->get()->pluck('id');
                if (count($ids_formations) > 0) {
                    foreach ($ids_formations as $formation_id) {
                        $success = $this->archiveFormation($formation_id);
                    }
                }
                $success = true;
            }
        }
        return $success;
    }

    public function unarchiveCategorieProcess($categorie_id)
    {
        $success = false;
        if ($categorie_id) {
            $row = Categorie::withTrashed()->where('id', $categorie_id)->first();
            if ($row) {
                $row->deleted_at = null;
                $row->archival_reason = null;
                $row->save();
                if ($row->id) {
                    $success = true;
                }
            }
        }
        return $success;
    }

    public function manageEntitie($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Entitie();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Entitie::find($row_id);
                if (!$row) {
                    $row = new Entitie();
                }
            }
            $row->entity_type = (isset($data['entity_type'])) ? $data['entity_type'] : null;
            $row->ref = (isset($data['ref'])) ? $data['ref'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->type = (isset($data['type'])) ? $data['type'] : null;
            $row->type_establishment = (isset($data['type_establishment'])) ? $data['type_establishment'] : null;
            $row->siren = (isset($data['siren'])) ? $data['siren'] : null;
            $row->siret = (isset($data['siret'])) ? $data['siret'] : null;
            $row->acronym = (isset($data['acronym'])) ? $data['acronym'] : null;
            $row->naf_code = (isset($data['naf_code'])) ? $data['naf_code'] : null;
            //$row->tva = (isset($data['tva']))?$data['tva']:null;
            $row->pro_phone = (isset($data['pro_phone'])) ? $data['pro_phone'] : null;
            $row->pro_mobile = (isset($data['pro_mobile'])) ? $data['pro_mobile'] : null;
            $row->fax = (isset($data['fax'])) ? $data['fax'] : null;
            $row->email = (isset($data['email'])) ? $data['email'] : null;
            $row->prospecting_area = (isset($data['prospecting_area'])) ? $data['prospecting_area'] : null;
            $row->matricule_code = (isset($data['matricule_code'])) ? $data['matricule_code'] : '';
            $row->personal_thirdparty_code = (isset($data['personal_thirdparty_code'])) ? $data['personal_thirdparty_code'] : '';
            $row->vendor_code = (isset($data['vendor_code'])) ? $data['vendor_code'] : '';
            $row->is_client = (isset($data['is_client'])) ? $data['is_client'] : 0;
            $row->is_funder = (isset($data['is_funder'])) ? $data['is_funder'] : 0;
            $row->is_former = (isset($data['is_former'])) ? $data['is_former'] : 0;
            $row->is_stage_site = (isset($data['is_stage_site'])) ? $data['is_stage_site'] : 0;
            $row->is_prospect = (isset($data['is_prospect'])) ? $data['is_prospect'] : 0;
            $row->rep_id = (isset($data['rep_id'])) ? $data['rep_id'] : null;
            $row->iban = (isset($data['iban'])) ? $data['iban'] : null;
            $row->bic = (isset($data['bic'])) ? $data['bic'] : null;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : null;
            //$row->collective_customer_account = (isset($data['collective_customer_account'])) ? $data['collective_customer_account'] : null;
            //$row->auxiliary_customer_account = (isset($data['auxiliary_customer_account'])) ? $data['auxiliary_customer_account'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageContact($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Contact();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Contact::find($row_id);
                if (!$row) {
                    $row = new Contact();
                }
            }
            $row->gender = (isset($data['gender'])) ? $data['gender'] : null;
            $row->firstname = (isset($data['firstname'])) ? $data['firstname'] : null;
            $row->lastname = (isset($data['lastname'])) ? $data['lastname'] : null;
            $row->email = (isset($data['email'])) ? $data['email'] : null;
            $row->pro_phone = (isset($data['pro_phone'])) ? $data['pro_phone'] : null;
            $row->pro_mobile = (isset($data['pro_mobile'])) ? $data['pro_mobile'] : null;
            $row->function = (isset($data['function'])) ? $data['function'] : null;
            $row->is_main_contact = (isset($data['is_main_contact'])) ? $data['is_main_contact'] : 0;
            $row->is_billing_contact = (isset($data['is_billing_contact'])) ? $data['is_billing_contact'] : 0;
            $row->is_order_contact = (isset($data['is_order_contact'])) ? $data['is_order_contact'] : 0;
            $row->is_trainee_contact = (isset($data['is_trainee_contact'])) ? $data['is_trainee_contact'] : 0;
            $row->birth_date = (isset($data['birth_date'])) ? $data['birth_date'] : null;
            $row->type_former_intervention = (isset($data['type_former_intervention'])) ? $data['type_former_intervention'] : null;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->is_valid_accounting = (isset($data['is_valid_accounting'])) ? $data['is_valid_accounting'] : 0;
            $row->is_former = (isset($data['is_former'])) ? $data['is_former'] : 0;
            $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : null;
            $row->birth_name = (isset($data['birth_name'])) ? $data['birth_name'] : null;
            $row->birth_department = (isset($data['birth_department'])) ? $data['birth_department'] : null;
            $row->birth_city = (isset($data['birth_city'])) ? $data['birth_city'] : null;
            $row->social_security_number = (isset($data['social_security_number'])) ? $data['social_security_number'] : null;
            $row->nationality = (isset($data['nationality'])) ? $data['nationality'] : null;
            if (isset($data['registration_code']) && !empty($data['registration_code'])) {
                $row->registration_code = (isset($data['registration_code'])) ? $data['registration_code'] : null;
            }
            $row->student_status = (isset($data['student_status'])) ? $data['student_status'] : null;
            $row->student_status_date = (isset($data['student_status_date'])) ? $data['student_status_date'] : null;

            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageAdresse($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Adresse();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Adresse::find($row_id);
                if (!$row) {
                    $row = new Adresse();
                }
            }
            $row->line_1 = (isset($data['line_1'])) ? $data['line_1'] : null;
            $row->line_2 = (isset($data['line_2'])) ? $data['line_2'] : null;
            $row->line_3 = (isset($data['line_3'])) ? $data['line_3'] : null;
            $row->postal_code = (isset($data['postal_code'])) ? $data['postal_code'] : null;
            $row->city = (isset($data['city'])) ? $data['city'] : null;
            $row->country = (isset($data['country'])) ? $data['country'] : null;
            $row->is_billing = (isset($data['is_billing'])) ? $data['is_billing'] : 0;
            $row->is_formation_site = (isset($data['is_formation_site'])) ? $data['is_formation_site'] : 0;
            $row->is_stage_site = (isset($data['is_stage_site'])) ? $data['is_stage_site'] : 0;
            $row->is_main_contact_address = (isset($data['is_main_contact_address'])) ? $data['is_main_contact_address'] : 0;
            $row->is_main_entity_address = (isset($data['is_main_entity_address'])) ? $data['is_main_entity_address'] : 0;
            $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function getDatasFromTableToArray($tableName)
    {
        $result = DB::select('SELECT * FROM `' . $tableName . '`');
        $result = array_map(function ($value) {
            return (array)$value;
        }, $result);
        return $result;
    }

    /* public function generateEntityCode()
    {
        $code = '';
        $new_entity_id = 0;
        $lastEntitie = Entitie::select('id')->orderByDesc('id')->first();
        $last_entity_id = ($lastEntitie && $lastEntitie['id']) ? $lastEntitie['id'] : 0;
        $new_entity_id = $last_entity_id + 1; 
        $code = 'CLN' . sprintf('%07d', $new_entity_id);
        return $code;
    } */
    public function generateEntityCode($lastEntitie = null)
    {
        if (!$lastEntitie) {
            $lastEntitie = Entitie::where('ref', 'like', 'CLN%')->orderByDesc('ref')->first();
        }
        $last_entity_id = ($lastEntitie && $lastEntitie['ref']) ? $lastEntitie['ref'] : 'CLN0';
        $last_entity_id = explode('CLN', $last_entity_id)[1];
        $new_entity_id = $last_entity_id + 1;
        $code = 'CLN' . sprintf('%07d', $new_entity_id);
        $new_entity = Entitie::where('ref', $code)->first();
        if (!$new_entity) {
            return $code;
        }
        return $this->generateEntityCode($new_entity);
    }

    public function manageUser($data, $iduser)
    {
        $channel = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/users.log'),
        ]);

        Log::stack(['slack', $channel])->info("-----CREATION COMPTE DU CONTACT: $iduser - PAR FORMULAIRE");

        $id = 0;
        if (count($data) > 0) {
            $row = new User();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = User::find($row_id);
                if (!$row) {
                    $row = new User();
                }
            }
            $row->login = (isset($data['login'])) ? $data['login'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : 'firstname'; //firstname
            $row->lastname = (isset($data['lastname'])) ? $data['lastname'] : null;
            $row->email = (isset($data['email'])) ? $data['email'] : null;
            $row->email_verified_at = (isset($data['email_verified_at'])) ? $data['email_verified_at'] : null;

            if (isset($data['password']) && !empty($data['password'])) {
                $row->password = Hash::make($data['password']);
            }

            //$row->password = (isset($data['password']))?$data['password']:null;
            $row->remember_token = (isset($data['_token'])) ? $data['_token'] : null;
            $row->active = (isset($data['active'])) ? $data['active'] : 0;
            // if(isset($data['valcontact']))
            //     $row->contact_id = (isset($data['valcontact']))  ? $data['valcontact'] : null;
            $row->contact_id = (isset($iduser)) ? $iduser : null;

            // if($data['valcontact']==""){
            //     $row->contact_id = (isset($iduser)) ? $iduser : null;
            // }else{
            //     $row->contact_id = (isset($data['valcontact']))  ? $data['valcontact'] : null;
            // }

            // $row->contact_id = (isset($iduser)) ? $iduser : null;


            // $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            // dd($row);
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageUserFromAf($data, $iduser)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new User();

            $row->login = (isset($data['login'])) ? $data['login'] : null;
            $row->name = (isset($data['firstname'])) ? $data['firstname'] : null;
            $row->lastname = (isset($data['lastname'])) ? $data['lastname'] : null;
            $row->email = (isset($data['email'])) ? $data['email'] : null;
            $row->email_verified_at = (isset($data['email_verified_at'])) ? $data['email_verified_at'] : null;

            if (isset($data['password']) && !empty($data['password'])) {
                $row->password = Hash::make($data['password']);
            }

            //$row->password = (isset($data['password']))?$data['password']:null;
            $row->remember_token = (isset($data['_token'])) ? $data['_token'] : null;
            $row->active = (isset($data['active'])) ? $data['active'] : 0;
            $row->contact_id = (isset($iduser)) ? $iduser : null;
            // $row->id = (isset($data['id'])) ? $data['id'] : null;
            // dd($row);
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function attachUserRoles($user_id, $role_id)
    {
        $success = false;
        if ($user_id && $role_id) {
            $role = Role::findOrFail($role_id);
            $user = User::findOrFail($user_id);
            // On attach
            $user->roles()->attach($role_id);
            $success = true;
        }
        return $success;
    }

    public function detachRolesUser($user_id)
    {
        $deletedRows = DB::table('par_usr_role_user')->where('user_id', $user_id)->delete();
    }

    public function generateRandomLogin($user_id)
    {
        $login = '';
        $new_user_id = $user_id;
        if ($user_id == 0) {
            $last = User::select('id')->orderByDesc('id')->first();
            $last_user_id = ($last && $last['id']) ? $last['id'] : 0;
            $new_user_id = $last_user_id + 1;
        }
        $login = 'CRFPE' . sprintf('%04d', $new_user_id);
        return $login;
    }

    public function generateOrderShowForCategorie($parent_id)
    {
        $order_show = 0;
        if ($parent_id > 0) {
            $maxOrder = Categorie::where('categorie_id', $parent_id)->max('order_show');
        } else {
            $maxOrder = Categorie::whereNull('categorie_id')->max('order_show');
        }
        $order_show = $maxOrder + 1;
        return $order_show;
    }

    public function manageAFavoirs($inv_af_id)
    {
        $af = Action::find($inv_af_id)->first();

        return $af->code;
    }

    public function manageAFavoirsTitle($inv_af_id)
    {
        $af = Action::find($inv_af_id)->first();

        return $af->title;
    }

    public function manageEntityAvoirs($inv_id)
    {
        $inv = Invoice::find($inv_id)->first();
        $entitie_id = Entitie::select('name','entity_type','ref','id')->where('id',$inv->entitie_id)->first();
        return $entitie_id;
    }

    public function manageAvoirsClient($req_entitie_id)
    {
        //$entity_id = Entitie::find($req_entitie_id)->first();
        $invoice_id = Invoice::select('id')->where('entitie_id',$req_entitie_id)->first();
        return $invoice_id->id;
    }

    public function manageAF($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Action();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Action::find($row_id);
                if (!$row) {
                    $row = new Action();
                }
            }
            if (isset($data['code'])) {
                $row->code = (isset($data['code'])) ? $data['code'] : null;
            }
            $row->title = (isset($data['title'])) ? $data['title'] : null;
            $row->description = (isset($data['description'])) ? $data['description'] : null;
            $row->nb_days = (isset($data['nb_days'])) ? $data['nb_days'] : null;
            $row->nb_hours = (isset($data['nb_hours'])) ? $data['nb_hours'] : null;
            $row->nb_pratical_days = (isset($data['nb_pratical_days'])) ? $data['nb_pratical_days'] : null;
            $row->nb_pratical_hours = (isset($data['nb_pratical_hours'])) ? $data['nb_pratical_hours'] : null;
            $row->is_uknown_date = (isset($data['is_uknown_date'])) ? $data['is_uknown_date'] : 0;
            $row->bpf_main_objective = (isset($data['bpf_main_objective'])) ? $data['bpf_main_objective'] : null;
            $row->bpf_training_specialty = (isset($data['bpf_training_specialty'])) ? $data['bpf_training_specialty'] : null;
            $row->device_type = (isset($data['device_type'])) ? $data['device_type'] : null;
            $row->max_nb_trainees = (isset($data['max_nb_trainees'])) ? $data['max_nb_trainees'] : null;
            $row->nb_groups = (isset($data['nb_groups'])) ? $data['nb_groups'] : null;
            $row->training_site = (isset($data['training_site'])) ? $data['training_site'] : null;
            $row->other_training_site = (isset($data['other_training_site'])) ? $data['other_training_site'] : null;
            $row->state = (isset($data['state'])) ? $data['state'] : null;
            $row->status = (isset($data['status'])) ? $data['status'] : null;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->formation_id = (isset($data['formation_id'])) ? $data['formation_id'] : 0;
            $row->started_at = (isset($data['started_at'])) ? $data['started_at'] : null;
            $row->ended_at = (isset($data['ended_at'])) ? $data['ended_at'] : null;
            $row->accounting_code = (isset($data['accounting_code'])) ? $data['accounting_code'] : null;
            $row->analytical_code = (isset($data['analytical_code'])) ? $data['analytical_code'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function getPlanningTemplates()
    {
        $templates = Planningtemplate::select('id', 'code', 'name')->where('is_active', 1)->orderBy('order_show')->get();
        return $templates->toArray();
    }

    public function generateAfCode($formation_id, $af_id)
    {
        $code = '';
        if ($af_id > 0) {
            $row = Action::select('code')->where('id', $af_id)->first();
            return $row['code'];
        }
        $now = Carbon::now();
        $year = $now->year;
        if ($formation_id > 0) {
            $new_af_id = $af_id;
            if ($af_id == 0) {
                $lastAf = Action::select('id')->orderByDesc('id')->first();
                $last_af_id = ($lastAf && $lastAf['id']) ? $lastAf['id'] : 0;
                $new_af_id = $last_af_id + 1;
            }

            $rowPF = Formation::find($formation_id);
            $code = 'AF_PFO_' . $rowPF->categorie->code . '_' . $year . '_' . sprintf('%04d', $new_af_id);
        }
        return $code;
    }

    public function getNameParamByCode($code)
    {
        $name = '';
        if (isset($code) && !empty($code)) {
            $row = Param::select('name')->where('code', $code)->first();
            $name = ($row && $row['name']) ? $row['name'] : '';
        }
        return $name;
    }

    public function getParamByCode($code)
    {
        $name = '';
        $css_class = '';
        if (isset($code) && !empty($code)) {
            $row = Param::select('name', 'css_class')->where('code', $code)->first();
            $name = ($row['name']) ? $row['name'] : '';
            $css_class = ($row['css_class']) ? $row['css_class'] : '';
        }
        return ['name' => $name, 'css_class' => $css_class];
    }

    public function managePlanningTemplate($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Planningtemplate();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Planningtemplate::find($row_id);
                if (!$row) {
                    $row = new Planningtemplate();
                }
            }
            $row->code = (isset($data['code'])) ? $data['code'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->order_show = (isset($data['order_show'])) ? $data['order_show'] : 1;
            $row->duration = (isset($data['duration'])) ? $data['duration'] : 0;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageTemplatePeriod($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Templateperiod();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Templateperiod::find($row_id);
                if (!$row) {
                    $row = new Templateperiod();
                }
            }
            $row->type = (isset($data['type'])) ? $data['type'] : null; //M : matin or A : après midi
            $row->start_hour = (isset($data['start_hour'])) ? $data['start_hour'] : null;
            $row->end_hour = (isset($data['end_hour'])) ? $data['end_hour'] : null;
            $row->duration = (isset($data['duration'])) ? $data['duration'] : null;
            $row->planning_template_id = (isset($data['planning_template_id'])) ? $data['planning_template_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageSession($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Session();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Session::find($row_id);
                if (!$row) {
                    $row = new Session();
                }
            }
            $row->code = (isset($data['code'])) ? $data['code'] : null;
            $row->title = (isset($data['title'])) ? $data['title'] : null;
            $row->description = (isset($data['description'])) ? $data['description'] : null;
            $row->nb_days = (isset($data['nb_days'])) ? $data['nb_days'] : null;
            $row->nb_hours = (isset($data['nb_hours'])) ? $data['nb_hours'] : null;
            $row->is_uknown_date = (isset($data['is_uknown_date'])) ? $data['is_uknown_date'] : 0;
            $row->nb_dates_to_program = (isset($data['nb_dates_to_program'])) ? $data['nb_dates_to_program'] : null;
            $row->nb_total_dates_to_program = (isset($data['nb_total_dates_to_program'])) ? $data['nb_total_dates_to_program'] : null;
            $row->max_nb_trainees = (isset($data['max_nb_trainees'])) ? $data['max_nb_trainees'] : null;
            $row->session_type = (isset($data['session_type'])) ? $data['session_type'] : null;
            $row->state = (isset($data['state'])) ? $data['state'] : null;
            $row->training_site = (isset($data['training_site'])) ? $data['training_site'] : null;
            $row->other_training_site = (isset($data['other_training_site'])) ? $data['other_training_site'] : null;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->is_main_session = (isset($data['is_main_session'])) ? $data['is_main_session'] : 0;
            $row->started_at = (isset($data['started_at'])) ? $data['started_at'] : null;
            $row->ended_at = (isset($data['ended_at'])) ? $data['ended_at'] : null;
            $row->planning_template_id = (isset($data['planning_template_id'])) ? $data['planning_template_id'] : null;
            $row->session_mode = (isset($data['session_mode'])) ? $data['session_mode'] : 'SESSION';
            $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            $row->is_internship_period = (isset($data['is_internship_period'])) ? $data['is_internship_period'] : 0;
            $row->attachment_semester = (isset($data['attachment_semester'])) ? $data['attachment_semester'] : null;
            $row->timestructure_id = (isset($data['timestructure_id'])) ? $data['timestructure_id'] : $row->timestructure_id;
            $row->session_parent_id = (isset($data['session_parent_id'])) ? $data['session_parent_id'] : null;
            $row->ects = (isset($data['ects'])) ? $data['ects'] : null;
            $row->coefficient = (isset($data['coefficient'])) ? $data['coefficient'] : null;
            $row->is_evaluation = (isset($data['is_evaluation'])) ? $data['is_evaluation'] : 0;
            $row->evaluation_mode = (isset($data['evaluation_mode'])) ? $data['evaluation_mode'] : 0;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageSessiondate($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Sessiondate();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Sessiondate::find($row_id);
                if (!$row) {
                    $row = new Sessiondate();
                }
            }
            $row->code = (isset($data['code'])) ? $data['code'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->planning_date = (isset($data['planning_date'])) ? $data['planning_date'] : null;
            $row->duration = (isset($data['duration'])) ? $data['duration'] : 0;
            $row->session_id = (isset($data['session_id'])) ? $data['session_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageSchedule($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Schedule();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Schedule::find($row_id);
                if (!$row) {
                    $row = new Schedule();
                }
            }
            $row->type = (isset($data['type'])) ? $data['type'] : null;
            $row->code = (isset($data['code'])) ? $data['code'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->start_hour = (isset($data['start_hour'])) ? $data['start_hour'] : null;
            $row->end_hour = (isset($data['end_hour'])) ? $data['end_hour'] : null;
            $row->duration = (isset($data['duration'])) ? $data['duration'] : null;
            $row->sessiondate_id = (isset($data['sessiondate_id'])) ? $data['sessiondate_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function generateSessionCode($af_id, $session_id)
    {
        $code = '';
        if ($session_id > 0) {
            $row = Session::select('code')->where('id', $session_id)->first();
            return $row['code'];
        }
        if ($af_id > 0) {
            $new_session_id = $session_id;
            if ($session_id == 0) {
                $lastSession = Session::select('id')->orderByDesc('id')->first();
                $last_session_id = ($lastSession && $lastSession['id']) ? $lastSession['id'] : 0;
                $new_session_id = $last_session_id + 1;
            }
            $rowAf = Action::select('id')->where('id', $af_id)->first();
            $code = 'S_AF' . sprintf('%04d', $rowAf['id']) . '_' . sprintf('%04d', $new_session_id);
        }
        return $code;
    }

    public function getDatesToProgram($start_date, $nbdaysToProgram, $typeSession)
    {
        $period = CarbonPeriod::create($start_date, $nbdaysToProgram);
        //Session continue sans samedi
        if ($typeSession == 'AF_SESSION_TYPE_SCSS') {
            $weekendFilter = function ($date) {
                return !$date->isWeekend();
            };
            $period->filter($weekendFilter);
        }
        //Session continue avec samedi
        if ($typeSession == 'AF_SESSION_TYPE_SCAS') {
            $sandayFilter = function ($date) {
                return !$date->isSunday();
            };
            $period->filter($sandayFilter);
        }
        //Session discontinue
        /*
    * pas de mardi,jeudi, samedi, dimanche
    */
        if ($typeSession == 'AF_SESSION_TYPE_DISC') {
            $startDate = $period->getStartDate();
            $dayOfWeek = '';
            if ($startDate->isMonday()) {
                $dayOfWeek = 'monday';
            } elseif ($startDate->isTuesday()) {
                $dayOfWeek = 'tuesday';
            } elseif ($startDate->isWednesday()) {
                $dayOfWeek = 'wednesday';
            } elseif ($startDate->isThursday()) {
                $dayOfWeek = 'thursday';
            } elseif ($startDate->isFriday()) {
                $dayOfWeek = 'friday';
            }
            $discontinousFilter = function ($date) use ($startDate, $dayOfWeek) {
                ($startDate == $date) ? $isStartDate = true : $isStartDate = false;
                if ($isStartDate) {
                    return true;
                } else {

                    if ($dayOfWeek == 'monday') {
                        return (!$date->isTuesday() && !$date->isThursday() && !$date->isMonday() && !$date->isWeekend());
                    } elseif ($dayOfWeek == 'tuesday') {
                        return (!$date->isWednesday() && !$date->isFriday() && !$date->isMonday() && !$date->isWeekend());
                    } elseif ($dayOfWeek == 'wednesday') {
                        return (!$date->isThursday() && !$date->isMonday() && !$date->isWeekend());
                    } elseif ($dayOfWeek == 'thursday') {
                        return (!$date->isFriday() && !$date->isTuesday() && !$date->isWeekend());
                    } elseif ($dayOfWeek == 'friday') {
                        return (!$date->isMonday() && !$date->isWednesday() && !$date->isWeekend());
                    }
                    return (!$date->isTuesday() && !$date->isThursday() && !$date->isWeekend());
                }
            };
            $period->filter($discontinousFilter);
        }
        $dates = [];
        foreach ($period as $key => $date) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }

    public function sessionPlanningProcessPlanified($session_id, $val)
    {
        foreach ($val as $key => $value) {
            if ($key == "nb_decoupages") {
                $nbdecoupage = $value;
            }
            if ($key == "nb_seances") {
                $nbseance = $value;;
            }
            if ($key == "nb_hours") {
                $nb_hours = $value;
            }
        }

        if ($session_id > 0) {
            $session = Session::findOrFail($session_id);
            // if ($session->sessiondates()->count() == 0) {
            $datesToProgram = [];
            if ($session->nb_dates_to_program > 0) {
                $started_at = Carbon::createFromFormat('Y-m-d H:i:s', $session->started_at);
                $datesToProgram = $this->getDatesToProgram($started_at, (int)$session->nb_dates_to_program, $session->session_type);
            }
            //On va créer les dates
            if (count($datesToProgram) > 0) {
                foreach ($datesToProgram as $planning_date) {
                    $date = Carbon::createFromFormat('Y-m-d', $planning_date);
                    $dataSessiondate = array(
                        'id' => 0,
                        'planning_date' => $date,
                        'duration' => 0,
                        'session_id' => $session_id,
                    );
                    $session_date_id = $this->manageSessiondate($dataSessiondate);
                    $sessionDateDuration = 0;
                    if ($session_date_id > 0 && $session->planning_template_id > 0) {
                        $pt = Planningtemplate::findOrFail($session->planning_template_id);
                        if ($pt->id) {
                            //morning_period
                            $periods = Templateperiod::where('planning_template_id', $pt->id)->get();
                            if (count($periods) > 0) {
                                foreach ($periods as $p) {
                                    $laststart = Carbon::createFromFormat('Y-m-d H:i:s', $p->start_hour);
                                    foreach (range(1, $nbdecoupage) as $dindex) { //4
                                        foreach (range(1, $nbseance) as $sindex) { //
                                            $start = clone $laststart;
                                            $end = clone $start;
                                            $dataSchedule = array(
                                                'id' => 0,
                                                'type' => $p->type,
                                                'start_hour' => $start,
                                                'end_hour' => $end->addHours($nb_hours),
                                                'duration' => $nb_hours,
                                                'sessiondate_id' => $session_date_id,
                                            );
                                            $schedule_id = $this->manageSchedule($dataSchedule);
                                        }
                                        $laststart->addHours($nb_hours);
                                    }
                                    $sessionDateDuration += $p->duration;
                                }
                            }
                        }
                    }
                    //update duration
                    if ($sessionDateDuration > 0) {
                        $row = Sessiondate::find($session_date_id);
                        $row->duration = $sessionDateDuration;
                        $row->save();
                    }
                }
            }
            // }
        }
    }

    public function sessionPlanningProcess($session_id)
    {
        if ($session_id > 0) {
            $session = Session::findOrFail($session_id);
            // if ($session->sessiondates()->count() == 0) {
            $datesToProgram = [];
            if ($session->nb_dates_to_program > 0) {
                $started_at = Carbon::createFromFormat('Y-m-d H:i:s', $session->started_at);
                $datesToProgram = $this->getDatesToProgram($started_at, (int)$session->nb_dates_to_program, $session->session_type);
            }
            //On va créer les dates
            if (count($datesToProgram) > 0) {
                foreach ($datesToProgram as $planning_date) {
                    $date = Carbon::createFromFormat('Y-m-d', $planning_date);
                    $dataSessiondate = array(
                        'id' => 0,
                        'planning_date' => $date,
                        'duration' => 0,
                        'session_id' => $session_id,
                    );
                    $session_date_id = $this->manageSessiondate($dataSessiondate);
                    $sessionDateDuration = 0;
                    if ($session_date_id > 0 && $session->planning_template_id > 0) {
                        $pt = Planningtemplate::findOrFail($session->planning_template_id);
                        if ($pt->id) {
                            //morning_period
                            $periods = Templateperiod::where('planning_template_id', $pt->id)->get();
                            if (count($periods) > 0) {
                                foreach ($periods as $p) {
                                    $start = Carbon::createFromFormat('Y-m-d H:i:s', $p->start_hour);
                                    $end = Carbon::createFromFormat('Y-m-d H:i:s', $p->end_hour);
                                    $start_hour = $date->format('Y-m-d') . ' ' . $start->format('H:i');
                                    $end_hour = $date->format('Y-m-d') . ' ' . $end->format('H:i');
                                    $dataSchedule = array(
                                        'id' => 0,
                                        'type' => $p->type,
                                        'start_hour' => Carbon::createFromFormat('Y-m-d H:i', $start_hour),
                                        'end_hour' => Carbon::createFromFormat('Y-m-d H:i', $end_hour),
                                        'duration' => $p->duration,
                                        'sessiondate_id' => $session_date_id,
                                    );
                                    $schedule_id = $this->manageSchedule($dataSchedule);
                                    $sessionDateDuration += $p->duration;
                                }
                            }
                        }
                    }
                    //update duration
                    if ($sessionDateDuration > 0) {
                        $row = Sessiondate::find($session_date_id);
                        $row->duration = $sessionDateDuration;
                        $row->save();
                    }
                }
            }
            // }
        }
    }

    public function getFormersInSchedule($schedule_id)
    {
        $formers = [];
        if ($schedule_id > 0) {
            $rsFormers = Schedulecontact::select('member_id')->where('schedule_id', $schedule_id)->where('is_former', 1)->get();
            if (count($rsFormers) > 0) {
                foreach ($rsFormers as $s) {
                    if ($s->member->contact_id > 0) {
                        $formers[] = $s->member->contact->firstname . ' ' . $s->member->contact->lastname;
                    }
                }
            }
        }
        return $formers;
    }
    public function getSessionPlanning($session_id, $start = '', $end = '')
    {
        if (isset($start) && isset($end) && !empty($start) && !empty($end)) {
            //dd($start);
            $sessiondates = Sessiondate::where('session_id', $session_id)->whereBetween('planning_date', [$start, $end])->orderBy('planning_date', 'asc')->get();
        } else {
            $sessiondates = Sessiondate::where('session_id', $session_id)->orderBy('planning_date', 'asc')->get();
        }
        $sessiondatesArray = [];
        if (count($sessiondates) > 0) {
            foreach ($sessiondates as $sd) {
                $rs_schedules = Schedule::where('sessiondate_id', $sd->id)->get();
                $schedules = [];
                if (count($rs_schedules) > 0) {
                    foreach ($rs_schedules as $schedule) {
                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                        //les formateurs
                        $formers = $this->getFormersInSchedule($schedule->id);
                        /* $rsFormers = Schedulecontact::select('member_id')->where('schedule_id', $schedule->id)->where('is_former',1)->get();
                        if(count($rsFormers)>0){
                            foreach($rsFormers as $s){
                                if($s->member->contact_id>0){
                                    $formers[]=$s->member->contact->firstname.' '.$s->member->contact->lastname;
                                }
                            }
                        } */
                        $schedules[$schedule->type] = array(
                            "id" => $schedule->id,
                            "type" => $schedule->type,
                            "start_hour" => $start_hour->format('H:i'),
                            "end_hour" => $end_hour->format('H:i'),
                            "duration" => $schedule->duration,
                            "sessiondate_id" => $schedule->sessiondate_id,
                            "formers" => $formers,
                        );
                    }
                }
                //$planning_date = Carbon::createFromFormat('Y-m-d',$sd->planning_date);
                $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                $sessiondatesArray[] = array(
                    "id" => $sd->id,
                    "planning_date" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                    "duration" => $sd->duration,
                    "session_id" => $sd->session_id,
                    "schedules" => $schedules,
                    "session_code" => $sd->session->code,
                    "session_title" => $sd->session->title,
                );
            }
        }
        return $sessiondatesArray;
    }
    public function getMemberSessionPlanningsByMember($sessions_ids, $member_id, $start = '', $end = '')
    {
        $sessiondatesArray = [];
        $ids_sessiondates = [];
        $ids_schedules = Schedulecontact::select('schedule_id')->where('member_id', $member_id)->pluck('schedule_id');
        if (count($ids_schedules) > 0) {
            $ids_sessiondates = Schedule::select('sessiondate_id')->whereIn('id', $ids_schedules)->pluck('sessiondate_id');
        }
        if (count($ids_sessiondates) > 0) {
            if (isset($start) && isset($end) && !empty($start) && !empty($end)) {
                //dd($start);
                $sessiondates = Sessiondate::whereIn('session_id', $sessions_ids)->whereIn('id', $ids_sessiondates)->whereBetween('planning_date', [$start, $end])->orderBy('planning_date', 'asc')->get();
            } else {
                $sessiondates = Sessiondate::whereIn('session_id', $sessions_ids)->whereIn('id', $ids_sessiondates)->orderBy('planning_date', 'asc')->get();
            }

            if (count($sessiondates) > 0) {
                foreach ($sessiondates as $sd) {
                    $rs_schedules = Schedule::where('sessiondate_id', $sd->id)->whereIn('id', $ids_schedules)->get();
                    $schedules = [];
                    if (count($rs_schedules) > 0) {
                        foreach ($rs_schedules as $schedule) {
                            $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                            $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                            //les formateurs
                            $formers = $this->getFormersInSchedule($schedule->id);
                            $schedules[$schedule->type] = array(
                                "id" => $schedule->id,
                                "type" => $schedule->type,
                                "start_hour" => $start_hour->format('H:i'),
                                "end_hour" => $end_hour->format('H:i'),
                                "duration" => $schedule->duration,
                                "sessiondate_id" => $schedule->sessiondate_id,
                                "formers" => $formers,
                            );
                        }
                    }
                    //$planning_date = Carbon::createFromFormat('Y-m-d',$sd->planning_date);
                    $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                    $sessiondatesArray[] = array(
                        "id" => $sd->id,
                        "planning_date" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                        "duration" => $sd->duration,
                        "session_id" => $sd->session_id,
                        "schedules" => $schedules,
                        "session_code" => $sd->session->code,
                        "session_title" => $sd->session->title,
                    );
                }
            }
        }
        return $sessiondatesArray;
    }
    public function getSessionPlanningByMember($session_id, $member_id, $start = '', $end = '')
    {
        $sessiondatesArray = [];
        $ids_sessiondates = [];
        $ids_schedules = Schedulecontact::select('schedule_id')->where('member_id', $member_id)->pluck('schedule_id');
        if (count($ids_schedules) > 0) {
            $ids_sessiondates = Schedule::select('sessiondate_id')->whereIn('id', $ids_schedules)->pluck('sessiondate_id');
        }
        if (count($ids_sessiondates) > 0) {
            if (isset($start) && isset($end) && !empty($start) && !empty($end)) {
                //dd($start);
                $sessiondates = Sessiondate::where('session_id', $session_id)->whereIn('id', $ids_sessiondates)->whereBetween('planning_date', [$start, $end])->orderBy('planning_date', 'asc')->get();
            } else {
                $sessiondates = Sessiondate::where('session_id', $session_id)->whereIn('id', $ids_sessiondates)->orderBy('planning_date', 'asc')->get();
            }

            if (count($sessiondates) > 0) {
                foreach ($sessiondates as $sd) {
                    $rs_schedules = Schedule::where('sessiondate_id', $sd->id)->whereIn('id', $ids_schedules)->get();
                    $schedules = [];
                    if (count($rs_schedules) > 0) {
                        foreach ($rs_schedules as $schedule) {
                            $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                            $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                            //les formateurs
                            $formers = $this->getFormersInSchedule($schedule->id);
                            $schedules[$schedule->type] = array(
                                "id" => $schedule->id,
                                "type" => $schedule->type,
                                "start_hour" => $start_hour->format('H:i'),
                                "end_hour" => $end_hour->format('H:i'),
                                "duration" => $schedule->duration,
                                "sessiondate_id" => $schedule->sessiondate_id,
                                "formers" => $formers,
                            );
                        }
                    }
                    //$planning_date = Carbon::createFromFormat('Y-m-d',$sd->planning_date);
                    $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                    $sessiondatesArray[] = array(
                        "id" => $sd->id,
                        "planning_date" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                        "duration" => $sd->duration,
                        "session_id" => $sd->session_id,
                        "schedules" => $schedules,
                        "session_code" => $sd->session->code,
                        "session_title" => $sd->session->title,
                    );
                }
            }
        }
        return $sessiondatesArray;
    }

    public function getSessiondateSchedules($sessiondate_id)
    {
        $sd = Sessiondate::findOrFail($sessiondate_id);
        $sessiondateArray = [];
        $rs_schedules = Schedule::where('sessiondate_id', $sd->id)->get();
        $schedules = [];
        if (count($rs_schedules) > 0) {
            foreach ($rs_schedules as $schedule) {
                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                $schedules[$schedule->type] = array(
                    "id" => $schedule->id,
                    "type" => $schedule->type,
                    "start_hour" => $start_hour->format('H:i'),
                    "end_hour" => $end_hour->format('H:i'),
                    "duration" => $schedule->duration,
                    "sessiondate_id" => $schedule->sessiondate_id,
                );
            }
        }
        $planning_date = Carbon::createFromFormat('Y-m-d', $sd->planning_date);
        $sessiondateArray = array(
            "id" => $sd->id,
            "planning_date" => $planning_date->format('d/m/Y'),
            "duration" => $sd->duration,
            "session_id" => $sd->session_id,
            "schedules" => $schedules,
        );
        return $sessiondateArray;
    }

    public function managePrice($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Price();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Price::find($row_id);
                if (!$row) {
                    $row = new Price();
                }
            }
            $row->title = (isset($data['title'])) ? $data['title'] : null;
            $row->entity_type = (isset($data['entity_type'])) ? $data['entity_type'] : null;
            $row->device_type = (isset($data['device_type'])) ? $data['device_type'] : null;
            $row->price = (isset($data['price'])) ? $data['price'] : 0;
            $row->price_type = (isset($data['price_type'])) ? $data['price_type'] : null;
            $row->accounting_code = (isset($data['accounting_code'])) ? $data['accounting_code'] : null;
            $row->is_broadcast = (isset($data['is_broadcast'])) ? $data['is_broadcast'] : 0;
            $row->is_forbidden = (isset($data['is_forbidden'])) ? $data['is_forbidden'] : 0;
            $row->is_ondemande = (isset($data['is_ondemande'])) ? $data['is_ondemande'] : 0;
            $row->is_former_price = (isset($data['is_former_price'])) ? $data['is_former_price'] : 0;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageEnrollment($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Enrollment();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Enrollment::find($row_id);
                if (!$row) {
                    $row = new Enrollment();
                }
            }
            $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : null;
            $row->nb_participants = (isset($data['nb_participants'])) ? $data['nb_participants'] : null;
            if (isset($data['price'])) {
                $row->price = (isset($data['price'])) ? $data['price'] : null;
            }
            if (isset($data['price_type'])) {
                $row->price_type = (isset($data['price_type'])) ? $data['price_type'] : null;
            }
            $row->enrollment_type = (isset($data['enrollment_type'])) ? $data['enrollment_type'] : 'S'; //Formateur or Stagiaire (F or S)
            $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageMember($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Member();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Member::find($row_id);
                if (!$row) {
                    $row = new Member();
                }
            }
            $row->unknown_contact_name = (isset($data['unknown_contact_name'])) ? $data['unknown_contact_name'] : null;
            $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            $row->enrollment_id = (isset($data['enrollment_id'])) ? $data['enrollment_id'] : null;
            $row->group_id = (isset($data['group_id'])) ? $data['group_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageSchedulecontact($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Schedulecontact();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Schedulecontact::find($row_id);
                if (!$row) {
                    $row = new Schedulecontact();
                }
            }
            $row->is_former = (isset($data['is_former'])) ? $data['is_former'] : 0;
            $row->price = (isset($data['price'])) ? $data['price'] : null;
            $row->price_type = (isset($data['price_type'])) ? $data['price_type'] : null;
            $row->total_cost = (isset($data['total_cost'])) ? $data['total_cost'] : null;
            $row->is_absent = (isset($data['is_absent'])) ? $data['is_absent'] : 0;
            $row->type_absent = (isset($data['type_absent'])) ? $data['type_absent'] : null;
            $row->type_of_intervention = (isset($data['type_of_intervention'])) ? $data['type_of_intervention'] : null;
            $row->schedule_id = (isset($data['schedule_id'])) ? $data['schedule_id'] : null;
            $row->member_id = (isset($data['member_id'])) ? $data['member_id'] : null;
            $row->contract_id = (isset($data['contract_id'])) ? $data['contract_id'] : null;
            $row->score = (isset($data['score'])) ? $data['score'] : null;
            $row->ects = (isset($data['ects'])) ? $data['ects'] : null;
            if (isset($data['pointing'])) {
                $row->pointing = $data['pointing'];
            }
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageContract($data)
    {
        $id = 0;
        $last_state = false;
        if (count($data) > 0) {
            $row = new Contract();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Contract::find($row_id);
                $last_state = $row->state;
                if (!$row) {
                    $row = new Contract();
                }
            }
            if (isset($data['number'])) {
                $row->number = $data['number'];
            }
            $row->price = (isset($data['price'])) ? $data['price'] : 0;
            $row->accounting_code = (isset($data['accounting_code'])) ? $data['accounting_code'] : null;
            $row->state = (isset($data['state'])) ? $data['state'] : null;
            $row->status = (isset($data['status'])) ? $data['status'] : null;
            $row->signed_at = (isset($data['signed_at'])) ? $data['signed_at'] : null;
            $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            if (isset($data['ct_sage_paie'])) {
                $row->ct_sage_paie = $data['ct_sage_paie'];
            }
            $row->save();
            $id = $row->id;

            if ($last_state == 'SC_SENT' && in_array($row->state, ['SC_RECEIVED', 'SC_CANCELED'])) {
                /* Close the contract's task */
                $ged_signature = GedSignature::where('contract_id', $row_id)->first();
                if ($ged_signature && $task = $ged_signature->task) {
                    $task->etat_id = Param::where([['param_code', 'Etat'], ['code', 'Terminée'], ['is_active', 1]])->pluck('id')->first();
                    $task->save();
                }
            }
        }
        return $id;
    }

    public function getNumberHoursPlannedForContactBySessions($member_id, $af_id)
    {
        $session_array = $schedules_ids = $duration_array = $tab_hours_by_sessions = [];
        if ($member_id > 0) {

            $qb = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                ->select('af_schedules.id as schedule_id', 'af_sessions.id as session_id', 'af_schedulecontacts.member_id')
                ->where('af_actions.id', $af_id)
                ->where('af_schedulecontacts.member_id', $member_id);
            $results = $qb->get();
            foreach ($results as $rs) {
                $schedules_ids[] = $rs->schedule_id;
                $session_array[] = $rs->session_id;
                $duration_array[$rs->session_id][] = $rs->schedule_id;
            }
            //dd($result);
            /* $schedules_ids = Schedulecontact::select('schedule_id')->where('member_id', $member_id)->get()->pluck('schedule_id');
            if (count($schedules_ids) > 0) {
                $schedules = Schedule::whereIn('id', $schedules_ids)->get();
                if (count($schedules) > 0) {
                    foreach ($schedules as $schedule) {
                        //dd($schedule->sessiondate->session->af->id);
                        if ($af_id == $schedule->sessiondate->session->af->id) {
                            $session_array[] = $schedule->sessiondate->session->id;
                        }
                    }
                }
            } */
        }
        /* if (count($session_array) > 0) {
            $session_array = array_unique($session_array);
            foreach ($session_array as $session_id) {
                $schedules = Schedule::whereIn('id', $schedules_ids)->get();
                if (count($schedules) > 0) {
                    foreach ($schedules as $schedule) {
                        if ($af_id == $schedule->sessiondate->session->af->id) {
                            if ($schedule->sessiondate->session->id == $session_id) {
                                $duration_array[$session_id][] = $schedule->id;
                            }
                        }
                    }
                }
            }
        } */
        //dd($duration_array);
        if (count($duration_array) > 0) {
            foreach ($duration_array as $session_id => $ids_schedules) {
                $hours = Schedule::whereIn('id', $ids_schedules)->sum('duration');
                $tab_hours_by_sessions[$session_id] = $hours;
            }
        }
        return $tab_hours_by_sessions;
    }
    public function getNumberHoursPlannedForContactBySessions_BKP($member_id, $af_id)
    {
        $session_array = $duration_array = $tab_hours_by_sessions = [];
        if ($member_id > 0) {

            /* $qb = DB::table('af_schedulecontacts')
            ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
            ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
            ->select('af_schedules.id as schedule_id','af_sessions.id as session_id')->where('af_actions.id', $af_id);
            $result=$qb->get(); */

            $schedules_ids = Schedulecontact::select('schedule_id')->where('member_id', $member_id)->get()->pluck('schedule_id');
            if (count($schedules_ids) > 0) {
                $schedules = Schedule::whereIn('id', $schedules_ids)->get();
                if (count($schedules) > 0) {
                    foreach ($schedules as $schedule) {
                        //dd($schedule->sessiondate->session->af->id);
                        if ($af_id == $schedule->sessiondate->session->af->id) {
                            $session_array[] = $schedule->sessiondate->session->id;
                        }
                    }
                }
            }
        }
        if (count($session_array) > 0) {
            $session_array = array_unique($session_array);
            foreach ($session_array as $session_id) {
                $schedules = Schedule::whereIn('id', $schedules_ids)->get();
                if (count($schedules) > 0) {
                    foreach ($schedules as $schedule) {
                        if ($af_id == $schedule->sessiondate->session->af->id) {
                            if ($schedule->sessiondate->session->id == $session_id) {
                                $duration_array[$session_id][] = $schedule->id;
                            }
                        }
                    }
                }
            }
        }
        if (count($duration_array) > 0) {
            foreach ($duration_array as $session_id => $ids_schedules) {
                $hours = Schedule::whereIn('id', $ids_schedules)->sum('duration');
                $tab_hours_by_sessions[$session_id] = $hours;
            }
        }
        return $tab_hours_by_sessions;
    }

    public function getPlanifContact($member_id, $af_id)
    {
        $spanPlanif = '';
        $tab_hours_by_sessions = $this->getNumberHoursPlannedForContactBySessions($member_id, $af_id);
        if (count($tab_hours_by_sessions) > 0) {
            $spanPlanif .= '<ul class="list-unstyled mb-0">';
            foreach ($tab_hours_by_sessions as $session_id => $nb_hours) {
                $session = Session::find($session_id);
                $cssClassHours = Helper::getCssClassForHoursPlanned($nb_hours, $session->nb_hours);
                $spanPlanif .= '<li class="font-size-xs font-weight-bold">' . $session->title . ' (' . $session->code . ')<ul><li class="text-' . $cssClassHours . '">' . Helper::convertTime($nb_hours) . ' / ' . $session->nb_hours . 'h</li></ul></li>';
            }
            $spanPlanif .= '</ul>';
        }
        return $spanPlanif;
    }

    public function getNumberHoursPlannedForEnrollmentBySessions($enrollment_id, $af_id)
    {
        $session_array = $duration_array = $tab_hours_by_sessions = [];

        $ids_contacts = Member::select('contact_id')->where([['enrollment_id', $enrollment_id], ['contact_id', '>', 0]])->get()->pluck('contact_id');
        if (count($ids_contacts) > 0) {
            $schedules_ids = Schedulecontact::select('schedule_id')->whereIn('contact_id', $ids_contacts)->get()->pluck('schedule_id');
            if (count($schedules_ids) > 0) {
                $schedules = Schedule::whereIn('id', $schedules_ids)->get();
                if (count($schedules) > 0) {
                    foreach ($schedules as $schedule) {
                        if ($af_id == $schedule->sessiondate->session->af->id) {
                            $session_array[] = $schedule->sessiondate->session->id;
                        }
                    }
                }
            }
        }
        if (count($session_array) > 0) {
            $session_array = array_unique($session_array);
            foreach ($session_array as $session_id) {
                foreach ($schedules_ids as $schedule_id) {
                    $schedules = Schedule::where('id', $schedule_id)->get();
                    if (count($schedules) > 0) {
                        foreach ($schedules as $schedule) {
                            if ($af_id == $schedule->sessiondate->session->af->id) {
                                if ($schedule->sessiondate->session->id == $session_id) {
                                    $duration_array[$session_id][] = $schedule->duration;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (count($duration_array) > 0) {
            foreach ($duration_array as $session_id => $ids_schedules) {
                $hours = array_sum($ids_schedules);
                $tab_hours_by_sessions[$session_id] = $hours;
            }
        }
        //dd($tab_hours_by_sessions);
        return $tab_hours_by_sessions;
    }

    public function getPlanifEnrollment($enrollment_id, $af_id)
    {
        $spanPlanif = '';
        if ($enrollment_id > 0) {
            $tab_hours_by_sessions = $this->getNumberHoursPlannedForEnrollmentBySessions($enrollment_id, $af_id);
            if (count($tab_hours_by_sessions) > 0) {
                $spanPlanif .= '<ul class="list-unstyled mb-0">';
                foreach ($tab_hours_by_sessions as $session_id => $nb_hours) {
                    $session = Session::find($session_id);
                    $enrollment = Enrollment::find($enrollment_id);
                    $session_nb_hours = $session->nb_hours * $enrollment->nb_participants;
                    $cssClassHours = Helper::getCssClassForHoursPlanned($nb_hours, $session_nb_hours);
                    $spanPlanif .= '<li class="font-size-xs font-weight-bold">' . $session->code . '<ul><li class="text-' . $cssClassHours . '">' . Helper::convertTime($nb_hours) . ' / ' . $session_nb_hours . 'h</li></ul></li>';
                }
                $spanPlanif .= '</ul>';
            }
        }
        return $spanPlanif;
    }

    public function attachFormationPrices($data)
    {
        $success = false;
        /* $data = array(
      'formation_id'=>1,
      'prices_ids'=>[1,2,3],
    ); */
        $this->detachFormationPrices($data);
        if ($data['formation_id'] > 0) {
            $pf = Formation::findOrFail($data['formation_id']);
            $attachedIds = $pf->prices()->whereIn('id', $data['prices_ids'])->pluck('id')->toArray();
            if (count($attachedIds) > 0) {
                $newIds = array_diff($data['prices_ids'], $attachedIds);
            } else {
                $newIds = $data['prices_ids'];
            }
            // On attach
            if (count($newIds) > 0) {
                $pf->prices()->attach($newIds);
            }
            $success = true;
        }
        return $success;
    }

    public function detachFormationPrices($data)
    {
        $success = false;
        if ($data['formation_id'] > 0) {
            if (count($data['prices_ids']) > 0) {
                $pf = Formation::findOrFail($data['formation_id']);
                $pf->prices()->detach();
            }
            $success = true;
        }
        return $success;
    }

    public function attachActionFormationPrices($data)
    {
        $success = false;
        /* $data = array(
      'af_id'=>1,
      'prices_ids'=>[1,2,3],
    ); */
        $this->detachActionFormationPrices($data);
        if ($data['af_id'] > 0) {
            $af = Action::findOrFail($data['af_id']);
            $attachedIds = $af->prices()->whereIn('id', $data['prices_ids'])->pluck('id')->toArray();
            if (count($attachedIds) > 0) {
                $newIds = array_diff($data['prices_ids'], $attachedIds);
            } else {
                $newIds = $data['prices_ids'];
            }
            //dd($newIds);
            // On attach
            if (count($newIds) > 0) {
                $af->prices()->attach($newIds);
            }
            $success = true;
        }
        return $success;
    }

    public function detachActionFormationPrices($data)
    {
        $success = false;
        if ($data['af_id'] > 0) {
            if (count($data['prices_ids']) > 0) {
                $af = Action::findOrFail($data['af_id']);
                $af->prices()->detach();
            }
            $success = true;
        }
        return $success;
    }

    public function getPriceId($data)
    {
        $price_id = 0;
        if (count($data) > 0) {
            if ($data['is_former_price'] == 1) {
                $price = Price::select('id')->where([
                    ['is_former_price', $data['is_former_price']],
                    ['price', $data['price']],
                    ['price_type', $data['price_type']],
                ])->first();
            } else {
                $price = Price::select('id')->where([
                    ['entity_type', $data['entity_type']],
                    ['device_type', $data['device_type']],
                    ['price', $data['price']],
                    ['price_type', $data['price_type']],
                ])->first();
            }
            if ($price) {
                $price_id = $price['id'];
            }
        }
        return $price_id;
    }

    public function getScheduleFormer($schedule_id)
    {
        $contactFormer = null;
        $schedule_contact_id = 0;
        if ($schedule_id > 0) {
            $rs_schedulecontacts = Schedulecontact::where([['schedule_id', $schedule_id], ['is_former', 1]])->first();
            //dd($rs_schedulecontacts);
            if ($rs_schedulecontacts) {
                $schedule_contact_id = $rs_schedulecontacts->id;
                $contact = ($rs_schedulecontacts->member) ? $rs_schedulecontacts->member->contact : null;
                $price = '';
                if ($rs_schedulecontacts->member) {
                    $price = $rs_schedulecontacts->member->enrollment->price . ' € / ' . $this->getNameParamByCode($rs_schedulecontacts->member->enrollment->price_type);
                }
                if ($contact) {
                    $contactFormer = $contact->firstname . ' ' . $contact->lastname . ' - ' . $price . ' - ' . $contact->type_former_intervention;
                }
            }
        }
        return [$contactFormer, $schedule_contact_id];
    }

    public function manageRessource($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Ressource();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Ressource::find($row_id);
                if (!$row) {
                    $row = new Ressource();
                }
            }
            $row->type = (isset($data['type'])) ? $data['type'] : null;
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->is_active = (isset($data['is_active'])) ? $data['is_active'] : 0;
            $row->is_dispo = (isset($data['is_dispo'])) ? $data['is_dispo'] : 0;
            $row->is_internal = (isset($data['is_internal'])) ? $data['is_internal'] : 0;
            $row->ressource_id = (isset($data['ressource_id'])) ? $data['ressource_id'] : null;
            $row->address_training_location = (isset($data['address_training_location'])) ? $data['address_training_location'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageScheduleressource($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Scheduleressource();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Scheduleressource::find($row_id);
                if (!$row) {
                    $row = new Scheduleressource();
                }
            }
            $row->schedule_id = (isset($data['schedule_id'])) ? $data['schedule_id'] : null;
            $row->ressource_id = (isset($data['ressource_id'])) ? $data['ressource_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }
    public function getStartDateEndDateContractFormer($contract_id)
    {
        $start_contract = $end_contract = null;
        if ($contract_id > 0) {
            $schedule_ids = Schedulecontact::select('schedule_id')->where([['contract_id', $contract_id], ['is_former', 1]])->get()->pluck('schedule_id')->unique();
            if (count($schedule_ids) > 0) {
                $sessiondates_ids = Schedule::select('sessiondate_id')->whereIn('id', $schedule_ids)->pluck('sessiondate_id')->unique();
                if (count($sessiondates_ids) > 0) {
                    $start_contract = Sessiondate::select('planning_date')->whereIn('id', $sessiondates_ids)->min('planning_date');
                    $end_contract = Sessiondate::select('planning_date')->whereIn('id', $sessiondates_ids)->max('planning_date');
                }
            }
        }
        return ['start_contract' => $start_contract, 'end_contract' => $end_contract];
    }
    public function getPointingInfos($pointing_code, $is_abs_justified = null)
    {
        $pointingArray = ['not_pointed' => 'Non pointé', 'absent' => 'Absent', 'present' => 'Présent'];
        $pointingCssArray = ['not_pointed' => 'danger', 'absent' => 'warning', 'present' => 'success'];
        $justifiedArray = ['0' => 'Absence non justifiée', '1' => 'Absence justifiée'];
        $justifiedCssArray = ['0' => 'danger', '1' => 'success'];
        $rs = ' <span class="label label-sm label-' . $pointingCssArray[$pointing_code] . ' label-pill label-inline">' . $pointingArray[$pointing_code] . '</span>';
        if ($pointing_code == 'absent' && $is_abs_justified !== null) {
            $rs .= ' <span class="label label-sm label-' . $justifiedCssArray[(int)$is_abs_justified] . ' label-pill label-inline">' . $justifiedArray[(int)$is_abs_justified] . '</span>';
        }
        return $rs;
    }
    public function getInterventionContractFormer($contract_id, $start = null, $end = null)
    {
        $schedulecontacts_ids = $schedule_ids = $sessiondate_ids = $session_ids = $action_ids = [];
        if ($contract_id > 0) {
            if (isset($start) && isset($end)) {
                $schedulecontacts_ids = Schedulecontact::select('id')->where([['contract_id', $contract_id], ['is_former', 1]])
                    ->whereNull('validated_at')
                    ->pluck('id');
            } else {
                $schedulecontacts_ids = Schedulecontact::select('id')->where([['contract_id', $contract_id], ['is_former', 1]])->pluck('id');
            }


            if (count($schedulecontacts_ids) > 0) {
                $schedule_ids = Schedulecontact::select('schedule_id')->where([['contract_id', $contract_id], ['is_former', 1]])
                    ->whereIn('id', $schedulecontacts_ids)
                    ->pluck('schedule_id')->unique();
                if (count($schedule_ids) > 0) {

                    $sessiondate_ids = Schedule::select('sessiondate_id')->whereIn('id', $schedule_ids)->pluck('sessiondate_id')->unique();

                    if (count($sessiondate_ids) > 0) {

                        if (isset($start) && isset($end)) {
                            $from = Carbon::createFromFormat('Y-m-d', $start);
                            $to = Carbon::createFromFormat('Y-m-d', $end);
                            $session_ids = Sessiondate::select('session_id')->whereIn('id', $sessiondate_ids)
                                ->whereDate('planning_date', '<=', $to)
                                ->pluck('session_id');

                            $sessiondate_ids = Sessiondate::select('id')->whereIn('id', $sessiondate_ids)->whereDate('planning_date', '<=', $to)->pluck('id');
                        } else {
                            $session_ids = Sessiondate::select('session_id')->whereIn('id', $sessiondate_ids)->pluck('session_id')->unique();
                        }

                        if (count($session_ids) > 0) {
                            $action_ids = Session::select('af_id')->whereIn('id', $session_ids)->pluck('af_id')->unique();
                        }
                    }
                }
            }
        }
        //dd($sessiondate_ids);
        $html = '';
        if (count($action_ids) > 0) {
            $html .= '<ul class="list-unstyled font-size-sm">';
            $actions = Action::select('id', 'code', 'title')->whereIn('id', $action_ids)->get();
            foreach ($actions as $a) {
                $html .= '<li>' . $a['title'] . ' (' . $a['code'] . ')';
                if (count($session_ids) > 0) {
                    $html .= '<ul>';
                    $sessions = Session::select('id', 'code', 'title')->where('af_id', $a['id'])->whereIn('id', $session_ids)->get();
                    foreach ($sessions as $s) {
                        $html .= '<li>' . $s['title'] . ' (' . $s['code'] . ')';
                        if (count($sessiondate_ids) > 0) {
                            $html .= '<ul>';
                            $sessiondates = Sessiondate::select('id', 'planning_date')->where('session_id', $s['id'])->whereIn('id', $sessiondate_ids)->get();
                            foreach ($sessiondates as $sd) {
                                $planning_date = (isset($sd['planning_date']) && !empty($sd['planning_date'])) ? Carbon::createFromFormat('Y-m-d', $sd['planning_date']) : null;
                                $html .= '<li>' . $planning_date->format('d/m/Y');
                                if (count($schedule_ids) > 0) {
                                    $html .= '<ul>';
                                    $schedules = Schedule::select('id', 'start_hour', 'end_hour', 'duration')->where('sessiondate_id', $sd['id'])->whereIn('id', $schedule_ids)->get();
                                    foreach ($schedules as $schedule) {
                                        $duration = Helper::convertTime($schedule['duration']);
                                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule['start_hour']);
                                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule['end_hour']);
                                        $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');
                                        $html .= '<li>' . $text . ' <span class="text-success">(' . $duration . ')</span>';
                                        if (count($schedulecontacts_ids) > 0) {
                                            $html .= '<ul>';
                                            $schedulecontacts = Schedulecontact::select('id', 'price', 'price_type', 'total_cost', 'pointing', 'validated_at', 'validated_by', 'member_id')->where('schedule_id', $schedule['id'])->whereIn('id', $schedulecontacts_ids)->get();
                                            if (count($schedulecontacts) > 0) {
                                                foreach ($schedulecontacts as $sc) {

                                                    $price = '';

                                                    $type_former_intervention = $sc->member->contact->type_former_intervention;
                                                    $scf_total_cost = $this->getCostScheduleContact($schedule->duration, $sc->price, $type_former_intervention);
                                                    $total_cost = ($scf_total_cost > 0) ? '<span class="text-primary"> - coût total : ' . $scf_total_cost . ' €</span>' : '';
                                                    if ($sc['price'] > 0) {
                                                        $price = '<span class="text-primary"> ' . $sc['price'] . ' €/' . $this->getNameParamByCode($sc['price_type']) . $total_cost . '</span>';
                                                    } else {
                                                        $price = $total_cost;
                                                    }
                                                    $pointage = $this->getPointingInfos($sc->pointing);

                                                    $validation = '';
                                                    if (isset($sc->validated_at) && $sc->validated_by > 0) {
                                                        $user = User::select('name')->where('id', $sc->validated_by)->first();
                                                        $validated_at = Carbon::createFromFormat('Y-m-d H:i:s', $sc->validated_at);
                                                        $validation = '<p class="text-info"><small>Validé le ' . $validated_at->format('d/m/Y') . ' à ' . $validated_at->format('H:i:s') . ' par ' . $user->name . '</small></p>';
                                                    }
                                                    $btn_pointage = '<a style="cursor: pointer;" class="ml-2 mr-2" data-toggle="tooltip" title="" onclick="_formPointage(' . $sc->id . ')" data-original-title="Pointage"><i class="fas fa-edit text-primary"></i></a>';
                                                    $html .= '<li>' . $price . $pointage . $validation . $btn_pointage . '</li>';
                                                }
                                            }
                                            $html .= '</ul>';
                                        }
                                        $html .= '</li>';
                                    }
                                    $html .= '</ul>';
                                }

                                $html .= '</li>';
                            }
                            $html .= '</ul>';
                        }
                        $html .= '</li>';
                    }
                    $html .= '</ul>';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    public function generateContractNumber($contract_id)
    {
        $number = '';
        if ($contract_id > 0) {
            $row = Contract::find($contract_id);
            return $row->number;
        }
        $dtNow = Carbon::now();
        $lastContract = Contract::select('id')->orderByDesc('id')->first();
        $last_contract_id = ($lastContract && $lastContract['id']) ? $lastContract['id'] : 0;
        $new_contract_id = $last_contract_id + 1;
        $number = 'C' . $dtNow->format('Y') . $dtNow->format('m') . sprintf('%06d', $new_contract_id);
        return $number;
    }

    public function manageDocumentmodel($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Documentmodel();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Documentmodel::find($row_id);
                if (!$row) {
                    $row = new Documentmodel();
                }
            }
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            if (isset($data['code'])) {
                $row->code = $data['code'];
            }
            if (isset($data['default_header'])) {
                $row->default_header = $data['default_header'];
            }
            $row->custom_header = (isset($data['custom_header'])) ? $data['custom_header'] : null;
            if (isset($data['default_content'])) {
                $row->default_content = $data['default_content'];
            }
            $row->custom_content = (isset($data['custom_content'])) ? $data['custom_content'] : null;
            if (isset($data['default_footer'])) {
                $row->default_footer = $data['default_footer'];
            }
            $row->custom_footer = (isset($data['custom_footer'])) ? $data['custom_footer'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageEmailmodel($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Emailmodel();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Emailmodel::find($row_id);
                if (!$row) {
                    $row = new Emailmodel();
                }
            }
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            if (isset($data['code'])) {
                $row->code = $data['code'];
            }
            if (isset($data['default_header'])) {
                $row->default_header = $data['default_header'];
            }
            $row->custom_header = (isset($data['custom_header'])) ? $data['custom_header'] : null;
            if (isset($data['default_content'])) {
                $row->default_content = $data['default_content'];
            }
            $row->custom_content = (isset($data['custom_content'])) ? $data['custom_content'] : null;
            if (isset($data['default_footer'])) {
                $row->default_footer = $data['default_footer'];
            }
            $row->custom_footer = (isset($data['custom_footer'])) ? $data['custom_footer'] : null;
            if (isset($data['view_table'])) {
                $row->view_table = $data['view_table'];
            }
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function generateDocumentCode()
    {
        $code = '';
        $new_id = 0;
        $last = Documentmodel::select('id')->orderByDesc('id')->first();
        $last_id = ($last && $last['id']) ? $last['id'] : 0;
        $new_id = $last_id + 1;
        $code = 'DOC_MOD_' . sprintf('%04d', $new_id);
        return $code;
    }

    public function getSettingByName($name, $type)
    {
        $value = '';
        if (isset($name) && isset($type)) {
            $rs = Setting::select('value')->where([['name', $name], ['type', $type]])->first();
            $value = $rs['value'];
        }
        return $value;
    }

    public function mainCopyPricesFromPfToAf($af_id)
    {
        $success = false;
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
            $attachedPfPricesIds = $af->formation->prices()->pluck('id')->toArray();
            if (count($attachedPfPricesIds) > 0) {
                $filtred_ids_price = Price::whereIn('id', $attachedPfPricesIds)->where('device_type', 'like', '%' . $af->device_type . '%')->pluck('id')->toArray();
                $data = array(
                    'af_id' => $af_id,
                    'prices_ids' => $filtred_ids_price,
                );
                $success = $this->attachActionFormationPrices($data);
            }
        }
        return $success;
    }

    public function mainCopySheetFromPfToAf($af_id)
    {
        $success = false;
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
            $formation_id = $af->formation->id;
            $sheet_pf = Sheet::where([['formation_id', $formation_id], ['is_default', 1]])->whereNull('action_id')->first();
            if (isset($sheet_pf->id) && $sheet_pf->id > 0) {
                $sheet_af = Sheet::where('action_id', $af_id)->whereNull('formation_id')->first();
                $sheet_af_id = (isset($sheet_af->id) && $sheet_af->id > 0) ? $sheet_af->id : 0;
                $generatedCode = $generatedVersion = '';
                if ($sheet_af_id == 0) {
                    $generatedData = $this->generateVesrionAndCodeForAfSheet($af_id);
                    $generatedCode = $generatedData['code'];
                    $generatedVersion = $generatedData['version'];
                } else {
                    $generatedCode = $sheet_af->ft_code;
                    $generatedVersion = $sheet_af->version;
                }
                $data = array(
                    'id' => $sheet_af_id,
                    'ft_code' => $generatedCode,
                    'version' => $generatedVersion,
                    'description' => $sheet_pf->description,
                    'is_default' => $sheet_pf->is_default,
                    'param_id' => $sheet_pf->param_id,
                    'formation_id' => null,
                    'action_id' => $af_id
                );
                $new_sheet_id = $this->manageSheets($data);
                $sheetPfParams = Sheetparam::select('id', 'title', 'content', 'order_show', 'sheet_id', 'param_id')->where([['sheet_id', $sheet_pf->id]])->get()->toArray();
                foreach ($sheetPfParams as $sp) {
                    $sheetParam = Sheetparam::select('id')->where([['sheet_id', $new_sheet_id], ['param_id', $sp['param_id']]])->get()->first();
                    $sheet_param_id = (isset($sheetParam->id) && $sheetParam->id > 0) ? $sheetParam->id : 0;
                    $sp['id'] = $sheet_param_id;
                    $sp['sheet_id'] = $new_sheet_id;
                    $this->manageSheetsParams($sp);
                }
                $success = true;
            }
        }
        return $success;
    }

    public function checkIfAfIsUknownDateAndUpdate($af_id)
    {
        $rs = Action::findOrFail($af_id);
        $sessions_ids = Session::select('id')->where('af_id', $af_id)->pluck('id')->toArray();
        $nbSessionDates = Sessiondate::whereIn('session_id', $sessions_ids)->count();
        //dd($nbSessionDates);
        if ($nbSessionDates > 0) {
            $rs->is_uknown_date = 0;
        } else {
            $rs->is_uknown_date = 1;
        }
        $rs->save();
        return $rs->is_uknown_date;
    }

    public function manageEstimate($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Estimate();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Estimate::find($row_id);
                if (!$row) {
                    $row = new Estimate();
                }
            }
            if ($row_id == 0) {
                $estimate_number = $this->generateEstimateNumber('ESTIMATE');
                if (isset($estimate_number)) {
                    $row->estimate_number = $estimate_number;
                }
            }
            if (isset($data['estimate_date'])) {
                $row->estimate_date = (isset($data['estimate_date'])) ? $data['estimate_date'] : null;
            }
            if (isset($data['valid_until'])) {
                $row->valid_until = (isset($data['valid_until'])) ? $data['valid_until'] : null;
            }
            if (isset($data['note'])) {
                $row->note = (isset($data['note'])) ? $data['note'] : null;
            }
            if (isset($data['last_email_sent_date'])) {
                $row->last_email_sent_date = (isset($data['last_email_sent_date'])) ? $data['last_email_sent_date'] : null;
            }
            if (isset($data['state'])) {
                $row->state = (isset($data['state'])) ? $data['state'] : null;
            }
            if (isset($data['status'])) {
                $row->status = (isset($data['status'])) ? $data['status'] : null;
            }
            if (isset($data['tax_percentage'])) {
                $row->tax_percentage = (isset($data['tax_percentage'])) ? $data['tax_percentage'] : null;
            }
            if (isset($data['discount_type'])) {
                $row->discount_type = (isset($data['discount_type'])) ? $data['discount_type'] : null;
            }
            if (isset($data['discount_amount'])) {
                $row->discount_amount = (isset($data['discount_amount'])) ? $data['discount_amount'] : null;
            }
            if (isset($data['discount_amount_type'])) {
                $row->discount_amount_type = (isset($data['discount_amount_type'])) ? $data['discount_amount_type'] : null;
            }
            if (isset($data['entitie_id'])) {
                $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : null;
            }
            if (isset($data['contact_id'])) {
                $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            }
            if (isset($data['af_id'])) {
                $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            }
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageEstimateItem($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Estimateitem();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Estimateitem::find($row_id);
                if (!$row) {
                    $row = new Estimateitem();
                }
            }
            if (isset($data['title'])) {
                $row->title = (isset($data['title'])) ? $data['title'] : null;
            }
            if (isset($data['description'])) {
                $row->description = (isset($data['description'])) ? $data['description'] : null;
            }
            if (isset($data['quantity'])) {
                $row->quantity = (isset($data['quantity'])) ? $data['quantity'] : null;
            }
            if (isset($data['unit_type'])) {
                $row->unit_type = (isset($data['unit_type'])) ? $data['unit_type'] : null;
            }
            if (isset($data['rate'])) {
                $row->rate = (isset($data['rate'])) ? $data['rate'] : null;
            }
            if (isset($data['total'])) {
                $row->total = (isset($data['total'])) ? $data['total'] : null;
            }
            if (isset($data['is_main_item'])) {
                $row->is_main_item = (isset($data['is_main_item'])) ? $data['is_main_item'] : 0;
            }
            if (isset($data['estimate_id'])) {
                $row->estimate_id = (isset($data['estimate_id'])) ? $data['estimate_id'] : null;
            }
            if (isset($data['pf_id'])) {
                $row->pf_id = (isset($data['pf_id'])) ? $data['pf_id'] : null;
            }
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function getAmountsEstimate($estimate_id)
    {
        $total = 0;
        $sous_total = 0;
        $discount_amount = 0;
        if ($estimate_id > 0) {
            $estimate = Estimate::find($estimate_id);
            $sous_total = Estimateitem::where('estimate_id', $estimate_id)->sum('total');
            //Réduction : //'percentage', 'fixed_amount'
            //'before_tax', 'after_tax'
            $tax_amount = 0;
            $amout_to_discount = $sous_total;
            if ($estimate->discount_type == 'after_tax') {
                if ($estimate->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($sous_total, $estimate->tax_percentage);
                }
                $amout_to_discount = $sous_total + $tax_amount;
                $discount_amount = $this->calculateDiscount($amout_to_discount, $estimate->discount_amount_type, $estimate->discount_amount);
            } elseif ($estimate->discount_type == 'before_tax') {
                $amout_to_discount = $sous_total;
                $discount_amount = $this->calculateDiscount($amout_to_discount, $estimate->discount_amount_type, $estimate->discount_amount);
                if ($estimate->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($sous_total - $discount_amount, $estimate->tax_percentage);
                }
            } else {
                if ($estimate->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($sous_total, $estimate->tax_percentage);
                }
            }

            $total = $sous_total - $discount_amount + $tax_amount;
        }
        return array(
            'total' => number_format($total, 2),
            'sous_total' => number_format($sous_total, 2),
            'discount_amount' => number_format($discount_amount, 2),
            'tax_amount' => number_format($tax_amount, 2),
            'unformated_total' => $total,
        );
    }

    public function getAmountsEstimateFact($estimate_id)
    {
        $total = 0;
        $sous_total = 0;
        $discount_amount = 0;
        if ($estimate_id > 0) {
            $estimate = Estimate::find($estimate_id);
            $sous_total = Estimateitem::where([['estimate_id',$estimate_id],['statut','actif']])->sum('total');
            //Réduction : //'percentage', 'fixed_amount'
            //'before_tax', 'after_tax'
            $tax_amount = 0;
            $amout_to_discount = $sous_total;
            if ($estimate->discount_type == 'after_tax') {
                if ($estimate->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($sous_total, $estimate->tax_percentage);
                }
                $amout_to_discount = $sous_total + $tax_amount;
                $discount_amount = $this->calculateDiscount($amout_to_discount, $estimate->discount_amount_type, $estimate->discount_amount);
            } elseif ($estimate->discount_type == 'before_tax') {
                $amout_to_discount = $sous_total;
                $discount_amount = $this->calculateDiscount($amout_to_discount, $estimate->discount_amount_type, $estimate->discount_amount);
                if ($estimate->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($sous_total - $discount_amount, $estimate->tax_percentage);
                }
            } else {
                if ($estimate->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($sous_total, $estimate->tax_percentage);
                }
            }

            $total = $sous_total - $discount_amount + $tax_amount;
        }
        return array(
            'total' => number_format($total, 2),
            'sous_total' => number_format($sous_total, 2),
            'discount_amount' => number_format($discount_amount, 2),
            'tax_amount' => number_format($tax_amount, 2),
            'unformated_total' => $total,
        );
    }

    public function getAmountsAgreement($agreement_id)
    {
        $total = 0;
        $sous_total = 0;
        $discount_amount = 0;
        if ($agreement_id > 0) {
            $estimate = Agreement::find($agreement_id);
            $sous_total = Agreementitem::where('agreement_id', $agreement_id)->sum('total');
            $tax_amount = 0;
            $amout_to_discount = $sous_total;
            if ($estimate != null)
                if ($estimate->discount_type == 'after_tax') {
                    if ($estimate->tax_percentage > 0) {
                        $tax_amount = $this->calculateTaxAmount($sous_total, $estimate->tax_percentage);
                    }
                    $amout_to_discount = $sous_total + $tax_amount;
                    $discount_amount = $this->calculateDiscount($amout_to_discount, $estimate->discount_amount_type, $estimate->discount_amount);
                } elseif ($estimate->discount_type == 'before_tax') {
                    $amout_to_discount = $sous_total;
                    $discount_amount = $this->calculateDiscount($amout_to_discount, $estimate->discount_amount_type, $estimate->discount_amount);
                    if ($estimate->tax_percentage > 0) {
                        $tax_amount = $this->calculateTaxAmount($sous_total - $discount_amount, $estimate->tax_percentage);
                    }
                } else {
                    if ($estimate->tax_percentage > 0) {
                        $tax_amount = $this->calculateTaxAmount($sous_total, $estimate->tax_percentage);
                    }
                }
            $total = $sous_total - $discount_amount + $tax_amount;
        }
        return array(
            'total' => $total,
            'sous_total' => $sous_total,
            'discount_amount' => $discount_amount,
            'tax_amount' => $tax_amount,
        );
    }

    public function calculateDiscount($amout_to_discount, $discount_amount_type, $estimate_discount_amount)
    {
        $discount_amount = 0;
        if ($discount_amount_type == 'percentage') {
            $discount_amount = ($amout_to_discount * $estimate_discount_amount) / 100;
        } elseif ($discount_amount_type == 'fixed_amount') {
            $discount_amount = $estimate_discount_amount;
        }
        return $discount_amount;
    }

    public function calculateTaxAmount($amount, $tax_percentage)
    {
        $tax_amount = 0;
        if ($tax_percentage > 0) {
            $tax_amount = ($amount * $tax_percentage) / 100;
        }
        return $tax_amount;
    }

    public function getIndexNumber($type)
    {
        $Number = 0;
        $indice = 0;
        $now = Carbon::now();
        $m = $now->format('m');
        $index = Helpindex::where('type', $type)->first();
        if ($index) {
            $index_date = Carbon::createFromFormat('Y-m-d H:i:s', $index->index_date);
            $mIndex = $index_date->format('m');
            $indice = $index->index + 1;
            // if ($m != $mIndex) {
            if ($m != $mIndex && !in_array($type, ['INVOICE', 'REFUND'])) {
                $indice = 1;
            }
        }
        if ($indice > 0) {
            $ym = $now->format('Y') . $now->format('m');
            if (in_array($type, ['INVOICE', 'REFUND']))
                $ym = $now->format('Y') . '-' . $now->format('m');
            $num_format = $this->formatNombreChiffre(array(
                'ID' => $indice,
                'NOMBRE_CHIFFRE' => 4
            ));
            $Number = $ym . $num_format;
            if (in_array($type, ['INVOICE', 'REFUND'])) {
                $Number = $ym . '-' . $num_format;
            }
        }
        return array(
            'indice' => $indice,
            'number' => $Number
        );
    }
    public function getIndexNumberByBillDate($type, $bill_date)
    {
        $Number = 0;
        $indice = 0;
        //$now = Carbon::now();
        $now = $bill_date;
        //dd($now);
        $m = $now->format('m');
        $index = Helpindex::where('type', $type)->first();
        if ($index) {
            $index_date = Carbon::createFromFormat('Y-m-d H:i:s', $index->index_date);
            $mIndex = $index_date->format('m');
            $indice = $index->index + 1;
            // if ($m != $mIndex) {
            if ($m != $mIndex && !in_array($type, ['INVOICE', 'REFUND'])) {
                $indice = 1;
            }
        }
        if ($indice > 0) {
            $ym = $now->format('Y') . $now->format('m');
            if (in_array($type, ['INVOICE', 'REFUND', 'CONVENTION_FORMATION_PROFESSIONNELLE', 'CONTRACT_FORMATION_PROFESSIONNELLE']))
                $ym = $now->format('Y') . '-' . $now->format('m');
            $num_format = $this->formatNombreChiffre(array(
                'ID' => $indice,
                'NOMBRE_CHIFFRE' => 4
            ));
            $Number = $ym . $num_format;
            if (in_array($type, ['INVOICE', 'REFUND', 'CONVENTION_FORMATION_PROFESSIONNELLE', 'CONTRACT_FORMATION_PROFESSIONNELLE'])) {
                $Number = $ym . '-' . $num_format;
            }
        }
        return array(
            'indice' => $indice,
            'number' => $Number
        );
    }

    public function formatNombreChiffre(array $data)
    {
        $maxRang = ($data['ID']) . "";
        $left = $data['NOMBRE_CHIFFRE'] - strlen($maxRang);
        if ($left > 0) {
            $maxRang = str_repeat("0", $left) . $maxRang;
        }
        return $maxRang;
    }

    public function generateEstimateNumber($type)
    {
        $tab = $this->getIndexNumber($type);
        $indice = $tab['indice'];
        $estimateNumber = $tab['number'];
        $this->setIndexNumber($indice, $type);
        return $estimateNumber;
    }

    public function setIndexNumber($indice, $type)
    {
        $now = Carbon::now();
        $index_date = Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d H:i:s'));
        //Helpindex::where('type',$type)->update(['index' => $indice],['index_date' => $index_date]);
        $row = Helpindex::where('type', $type)->first();
        $row->index = $indice;
        $row->index_date = $index_date;
        $row->save();
        return true;
    }

    public function setIndexNumberByBillDate($indice, $type, $bill_date)
    {
        //$now = Carbon::now();
        $index_date = Carbon::createFromFormat('Y-m-d H:i:s', $bill_date->format('Y-m-d H:i:s'));
        $row = Helpindex::where('type', $type)->first();
        $row->index = $indice;
        $row->index_date = $index_date;
        $row->save();
        return true;
    }

    public function getItemDescription($af_id)
    {
        $description = '';
        if ($af_id) {
            $af = Action::select('device_type', 'started_at', 'ended_at', 'nb_hours', 'nb_days', 'max_nb_trainees', 'training_site')->where('id', $af_id)->first();
            $FIRST_SCHEDULE_HOUR = $LAST_SCHEDULE_HOUR = '';
            $sessions_ids = Session::select('id')->where('af_id', $af_id)->pluck('id');
            if (count($sessions_ids) > 0) {
                $sessiondates_ids = Sessiondate::select('id')->whereIn('session_id', $sessions_ids)->pluck('id');
                if (count($sessiondates_ids) > 0) {
                    $rs_start_schedule = Schedule::select('start_hour')->whereIn('sessiondate_id', $sessiondates_ids)->orderBy('start_hour', 'asc')->first();
                    $rs_end_schedule = Schedule::select('end_hour')->whereIn('sessiondate_id', $sessiondates_ids)->orderBy('end_hour', 'desc')->first();
                    if ($rs_start_schedule && $rs_end_schedule) {
                        $dtStart = Carbon::createFromFormat('Y-m-d H:i:s', $rs_start_schedule->start_hour);
                        $dtEnd = Carbon::createFromFormat('Y-m-d H:i:s', $rs_end_schedule->end_hour);
                        $FIRST_SCHEDULE_HOUR = $dtStart->format('H:i');
                        $LAST_SCHEDULE_HOUR = $dtEnd->format('H:i');
                    }
                }
            }
            $descriptionInfos = '<p>Formation : {device_type}</p><p>Date : du {started_at} au {ended_at}</p><p>Horaires : de {FIRST_SCHEDULE_HOUR} à {LAST_SCHEDULE_HOUR}</p><p>Nombre d’heures : {nb_hours}</p><p>Nombre de jours : {nb_days}</p><p>Nombre de participants maximum : {max_nb_trainees}</p><p>Lieu de Formation : {training_site}</p>';
            if ($af) {
                $keywords = array(
                    '{device_type}',
                    '{started_at}',
                    '{ended_at}',
                    '{FIRST_SCHEDULE_HOUR}',
                    '{LAST_SCHEDULE_HOUR}',
                    '{nb_hours}',
                    '{nb_days}',
                    '{max_nb_trainees}',
                    '{training_site}',
                );
                $dtStartedAt = Carbon::createFromFormat('Y-m-d H:i:s', $af->started_at);
                $dtEndedAt = Carbon::createFromFormat('Y-m-d H:i:s', $af->ended_at);

                $training_site = ($af->training_site != 'OTHER') ? $af->training_site : $af->other_training_site;
                //dd($training_site);

                $keywordReplaced = array(
                    $af->device_type,
                    $dtStartedAt->format('d/m/Y'),
                    $dtEndedAt->format('d/m/Y'),
                    $FIRST_SCHEDULE_HOUR,
                    $LAST_SCHEDULE_HOUR,
                    $af->nb_hours,
                    $af->nb_days,
                    $af->max_nb_trainees,
                    $training_site
                );
                $description = str_replace($keywords, $keywordReplaced, $descriptionInfos);
            }
        }
        return $description;
    }

    public function getQuantityUnittypeRate($af_id, $entity_id)
    {
        $quantity = 1;
        $unit_type = '';
        $rate = 0;
        if ($af_id > 0 && $entity_id > 0) {
            $af = Action::select('nb_hours')->where('id', $af_id)->first();
            $entity = Entitie::select('entity_type')->where('id', $entity_id)->first();
            $enrollment = Enrollment::where([['af_id', $af_id], ['entitie_id', $entity_id], ['enrollment_type', 'S']])->first();
            if ($entity && $enrollment) {
                $price_type = $enrollment->price_type;
                $rate = $enrollment->price;

                if ($entity->entity_type == 'S') {
                    //Si type de tarif = personne => pour le devis : quantité = nombre d'inscrit pour le client - unité = personne - prix unitaire = tarif
                    if ($price_type == 'TTEII_PERSONNE') {
                        $nb_members = Member::where('enrollment_id', $enrollment->id)->count();
                        $quantity = $nb_members;
                        $unit_type = 'personne';
                    }
                    //Si type de tarif = forfait => pour le devis : quantité = 1 - unité = Forfait - prix unitaire = tarif
                    if ($price_type == 'TTEII_FORFAIT') {
                        $quantity = 1;
                        $unit_type = 'Forfait';
                    }
                    //Si type de tarif = Groupe => pour le devis : quantité = 1 - unité = Groupe - prix unitaire = tarif
                    if ($price_type == 'TTEII_GROUPE') {
                        $quantity = 1;
                        $unit_type = 'Groupe';
                    }
                    //Si type de tarif = Heure total/personne => pour le devis : quantité = nb heure de l'AF x nb inscrit client - unité = heure - prix unitaire = tarif
                    if ($price_type == 'TTEII_HEURE_TOTAL_PERSONNE') {
                        $nb_members = Member::where('enrollment_id', $enrollment->id)->count();
                        $quantity = $af->nb_hours * $nb_members;
                        $unit_type = 'heure';
                    }
                    //Si type de tarif = Heure total/groupe=> pour le devis : quantité = nb heure de l'AF  - unité = heure - prix unitaire = tarif
                    if ($price_type == 'TTEII_HEURE_TOTAL_GROUPE') {
                        $quantity = $af->nb_hours;
                        $unit_type = 'heure';
                    }
                } elseif ($entity->entity_type == 'P') {
                    //Si type de tarif = personne => pour le devis : quantité = 1 - unité = personne - prix unitaire = tarif
                    if ($price_type == 'TTPI_PERSONNE') {
                        $quantity = 1;
                        $unit_type = 'personne';
                    }
                    //Si type de tarif = Heure total/personne => pour le devis : quantité = nb heure de l'AF - unité = heure - prix unitaire = tarif
                    if ($price_type == 'TTPI_HEURE_TOTAL_PERSONNE') {
                        $quantity = ($af->nb_hours) ? $af->nb_hours : 0;
                        $unit_type = 'heure';
                    }
                }
            }
        }
        return ['quantity' => $quantity, 'unit_type' => $unit_type, 'rate' => $rate];
    }

    public function getHtmlEstimateItems($estimate_id)
    {
        $htmlItems = '<style>#ESTIMATE_ITEMS tbody tr td p{margin:0px !important;}</style>';
        // $items = null;
        // $calcul = [];
        // $discount_type = 'before_tax';
        // $tax_percentage = 0;
        // $discount_amount_type = $discount_amount = '';
        // if ($estimate_id > 0) {
        //     $items = Estimateitem::where('estimate_id', $estimate_id)->get();
        //     $calcul = $this->getAmountsEstimate($estimate_id);
        //     $estimate = Estimate::select('discount_type', 'discount_amount', 'discount_amount_type', 'tax_percentage', 'discount_label', 'af_actions.code')
        //     ->join('af_actions', 'af_actions.id', '=', 'dev_estimates.af_id')
        //     ->where('dev_estimates.id', $estimate_id)
        //     ->first();

        //     $af_code = $estimate->code;
        //     $discount_type = $estimate->discount_type;
        //     $discount_amount = $estimate->discount_amount;
        //     $tax_percentage = $estimate->tax_percentage;
        //     $discount_label = $estimate->discount_label;
        //     $discount_amount_type = $estimate->discount_amount_type;
        // }

        // $percent = '';
        // if ($discount_amount_type == 'percentage') {
        //     $percent = '(' . $discount_amount . '%)';
        // }
        
        // if (count($items) > 0) {
        //     foreach ($items as $item) {
        //         // dd($item->is_main_item);
        //         $htmlItems .= '<tr style="border:0.5px solid #000;border-collapse: collapse;">';
        //         if($item->is_main_item==0){
        //             //Designation
        //             $htmlItems .= '<td style="border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;"><p>' . $item->title . '</p><p>' . $item->description . '</p></td>';
        //         }else if($item->is_main_item==1){
        //             //Designation
        //             $htmlItems .= '<td style="border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;"><p>' . $item->title . '</p><p>(' . $af_code . ')</p><p>' . $item->description . '</p></td>';
        //         }
        //               //Quantité
        //         $unit = $item->unit_type;
        //         if ($item->is_main_item == 1) {
        //             $unit = ($item->unit_type != '') ? $item->unit_type . '(s)' : '';
        //         }
        //         $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . $item->quantity . ' ' . $unit . '</td>';
        //         //Prix unitaire
        //         $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . number_format($item->rate, 2) . ' €</td>';
        //         //Total
        //         $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . number_format($item->total, 2) . ' €</td>';
        //         $htmlItems .= '</tr>';
        //     }
        //     //Sous-Total
        //     $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>Sous-Total : </strong></td><td style="text-align:right;padding-right: 4px;"><strong>' . $calcul['sous_total'] . ' €</strong></td></tr>';
        //     //Remise before_tax
        //     if ($discount_type == 'before_tax' && $calcul['discount_amount'] > 0) {
        //         $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>' . ($discount_label . ' ' ?? 'Remise ') . $percent . ' : </strong></td><td style="text-align:right;padding-right: 4px;"><strong>' . $calcul['discount_amount'] . ' €</strong></td></tr>';
        //     }
        //     //Tax
        //     if ($tax_percentage > 0) {
        //         $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>Tax (' . $tax_percentage . '%) : </strong></td><td style="text-align:right;padding-right: 4px;"><strong>' . $calcul['tax_amount'] . ' €</strong></td></tr>';
        //     }
        //     //Remise after_tax
        //     if ($discount_type == 'after_tax' && $calcul['discount_amount'] > 0) {
        //         $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>' . ($discount_label . ' ' ?? 'Remise ') . $percent . ' : </strong></td><td style="text-align:right;padding-right: 4px;"><strong>' . $calcul['discount_amount'] . ' €</strong></td></tr>';
        //     }
        //     //Total
        //     $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>Total : </strong></td><td style="text-align:right;background-color: #e4e1e1;padding-right: 4px;"><strong>' . number_format($calcul['unformated_total'], 2) . ' €</strong></td></tr>';
        // }

        return $htmlItems;
    }

    public function manageAgreement($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Agreement();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Agreement::find($row_id);
                if (!$row) {
                    $row = new Agreement();
                }
            }
            if (isset($data['number'])) {
                $row->number = (isset($data['number'])) ? $data['number'] : null;
            }
            if (isset($data['agreement_type'])) {
                $row->agreement_type = (isset($data['agreement_type'])) ? $data['agreement_type'] : null;
            }
            if (isset($data['last_email_sent_date'])) {
                $row->last_email_sent_date = (isset($data['last_email_sent_date'])) ? $data['last_email_sent_date'] : null;
            }
            if (isset($data['status'])) {
                $row->status = (isset($data['status'])) ? $data['status'] : null;
            }

            $row->tax_percentage = (isset($data['tax_percentage'])) ? $data['tax_percentage'] : null;

            if (isset($data['discount_type'])) {
                $row->discount_type = (isset($data['discount_type'])) ? $data['discount_type'] : null;
            }
            if (isset($data['discount_amount'])) {
                $row->discount_amount = (isset($data['discount_amount'])) ? $data['discount_amount'] : null;
            }
            if (isset($data['discount_amount_type'])) {
                $row->discount_amount_type = (isset($data['discount_amount_type'])) ? $data['discount_amount_type'] : null;
            }
            if (isset($data['entitie_id'])) {
                $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : null;
            }
            if (isset($data['contact_id'])) {
                $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            }
            if (isset($data['af_id'])) {
                $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            }
            if (isset($data['estimate_id'])) {
                $row->estimate_id = (isset($data['estimate_id'])) ? $data['estimate_id'] : null;
            }
            $row->agreement_date = (isset($data['agreement_date'])) ? $data['agreement_date'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function getAgreementTypeByEntity($entity_id)
    {
        $agreement_type = $typeIndice = $prefix = '';
        if ($entity_id > 0) {
            $entity = Entitie::select('id', 'entity_type')->where('id', $entity_id)->first();
            //dd($entity->entity_type);
            if ($entity->entity_type == 'S') {
                $agreement_type = 'convention';
                $typeIndice = 'CONVENTION_FORMATION_PROFESSIONNELLE';
                $prefix = 'CVT';
            } elseif ($entity->entity_type == 'P') {
                $agreement_type = 'contract';
                $typeIndice = 'CONTRACT_FORMATION_PROFESSIONNELLE';
                $prefix = 'CTR';
            }
        }
        return ['agreement_type' => $agreement_type, 'typeIndice' => $typeIndice, 'prefix' => $prefix];
    }

    public function generateAgreementFromEstimate($estimate_id)
    {
        $success = false;
        if ($estimate_id > 0) {
            $estimate = Estimate::find($estimate_id);
            $nb_agreements = Agreement::select('id')->where('estimate_id', $estimate_id)->count();
            if ($nb_agreements == 0) {
                $rs_type = $this->getAgreementTypeByEntity($estimate->entitie_id);
                $prefix = $rs_type['prefix'];
                $typeIndice = $rs_type['typeIndice'];
                $agreement_type = $rs_type['agreement_type'];
                $agreement_date = Carbon::createFromFormat('Y-m-d', $estimate->estimate_date);
                //estimate_date
                $data = array(
                    'id' => 0,
                    'number' => $prefix . $this->generateAgreementNumberByAgreementDate($typeIndice, $agreement_date),
                    'agreement_date' => $agreement_date,
                    'agreement_type' => $agreement_type,
                    'status' => 'draft',
                    "tax_percentage" => $estimate->tax_percentage,
                    "discount_type" => $estimate->discount_type,
                    "discount_amount" => $estimate->discount_amount,
                    "discount_amount_type" => $estimate->discount_amount_type,
                    'entitie_id' => $estimate->entitie_id,
                    'contact_id' => $estimate->contact_id,
                    'af_id' => $estimate->af_id,
                    'estimate_id' => $estimate_id,
                );
                $id = $this->manageAgreement($data);
                if ($id > 0) {
                    $success = true;
                    //get estimate items
                    $items = Estimateitem::where('estimate_id', $estimate_id)->get();
                    if (count($items) > 0) {
                        foreach ($items as $item) {
                            $data = array(
                                "id" => 0,
                                "title" => $item->title,
                                "description" => $item->description,
                                "quantity" => $item->quantity,
                                "unit_type" => $item->unit_type,
                                "rate" => $item->rate,
                                "total" => $item->total,
                                "is_main_item" => $item->is_main_item,
                                "agreement_id" => $id,
                                "pf_id" => $item->pf_id,
                            );
                            $item_id = $this->manageAgreementItem($data);
                        }
                    }
                    //Changer status a "Accepter" SE_ACCEPTED
                    $estimate->status = 'SE_ACCEPTED';
                    $estimate->save();
                }
            }
        }
        return $success;
    }

    public function generateAgreementNumber($type)
    {
        $tab = $this->getIndexNumber($type);
        $indice = $tab['indice'];
        $agreementNumber = $tab['number'];
        $this->setIndexNumber($indice, $type);
        return $agreementNumber;
    }
    public function generateAgreementNumberByAgreementDate($type, $agreement_date)
    {
        $tab = $this->getIndexNumberByBillDate($type, $agreement_date);
        $indice = $tab['indice'];
        $agreementNumber = $tab['number'];
        $this->setIndexNumberByBillDate($indice, $type, $agreement_date);
        return $agreementNumber;
    }

    public function manageAgreementItem($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Agreementitem();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Agreementitem::find($row_id);
                if (!$row) {
                    $row = new Agreementitem();
                }
            }
            if (isset($data['title'])) {
                $row->title = (isset($data['title'])) ? $data['title'] : null;
            }
            if (isset($data['description'])) {
                $row->description = (isset($data['description'])) ? $data['description'] : null;
            }
            if (isset($data['quantity'])) {
                $row->quantity = (isset($data['quantity'])) ? $data['quantity'] : null;
            }
            if (isset($data['unit_type'])) {
                $row->unit_type = (isset($data['unit_type'])) ? $data['unit_type'] : null;
            }
            if (isset($data['rate'])) {
                $row->rate = (isset($data['rate'])) ? $data['rate'] : null;
            }
            if (isset($data['total'])) {
                $row->total = (isset($data['total'])) ? $data['total'] : null;
            }
            if (isset($data['is_main_item'])) {
                $row->is_main_item = (isset($data['is_main_item'])) ? $data['is_main_item'] : 0;
            }
            if (isset($data['agreement_id'])) {
                $row->agreement_id = (isset($data['agreement_id'])) ? $data['agreement_id'] : null;
            }
            if (isset($data['pf_id'])) {
                $row->pf_id = (isset($data['pf_id'])) ? $data['pf_id'] : null;
            }
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function getHtmlAgreementItems($agreement_id)
    {
        $htmlItems = '';
        $items = null;
        $calcul = [];
        $discount_type = 'before_tax';
        $tax_percentage = 0;
        $discount_amount_type = $discount_amount = '';
        if ($agreement_id > 0) {
            $items = Agreementitem::where('agreement_id', $agreement_id)->get();
            $calcul = $this->getAmountsAgreement($agreement_id);
            $agreement = Agreement::select('discount_type', 'discount_amount', 'discount_amount_type', 'tax_percentage', 'discount_label')->where('id', $agreement_id)->first();
            $discount_type = $agreement != null ? $agreement->discount_type : '';
            $discount_amount = $agreement != null ? $agreement->discount_amount : '';
            $tax_percentage = $agreement != null ? $agreement->tax_percentage : '';
            $discount_label = $agreement != null ? $agreement->discount_label : '';
            $discount_amount_type = $agreement != null ? $agreement->discount_amount_type : '';
        }

        $percent = '';
        if ($discount_amount_type == 'percentage') {
            $percent = '(' . $discount_amount . '%)';
        }

        if (count($items) > 0) {
            foreach ($items as $item) {
                $htmlItems .= '<tr style="border:0.5px solid #000;border-collapse: collapse;">';
                //Designation
                $htmlItems .= '<td style="border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;"><p>' . $item->title . '</p><p>' . $item->description . '</p></td>';
                //Quantité
                $unit = $item->unit_type;
                if ($item->is_main_item == 1) {
                    $unit = ($item->unit_type != '') ? $item->unit_type . '(s)' : '';
                }
                $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . $item->quantity . ' ' . $unit . '</td>';
                //Prix unitaire
                $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . number_format($item->rate, 2) . ' €</td>';
                //Total
                $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . number_format($item->total, 2) . ' €</td>';
                $htmlItems .= '</tr>';
            }
            //Sous-Total
            $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>Sous-Total : </strong></td><td style="text-align:right;padding-right: 4px;"><strong>' . number_format($calcul['sous_total'], 2) . ' €</strong></td></tr>';
            //Remise before_tax
            if ($discount_type == 'before_tax' && $calcul['discount_amount'] > 0) {
                $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>' . ($discount_label . ' ' ?? 'Remise ') . $percent . ' : </strong></td><td style="text-align:right;padding-right: 4px;"><strong>' . number_format($calcul['discount_amount'], 2) . ' €</strong></td></tr>';
            }
            //Tax
            if ($tax_percentage > 0) {
                $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>Tax (' . $tax_percentage . '%) : </strong></td><td style="text-align:right;padding-right: 4px;"><strong>' . number_format($calcul['tax_amount'], 2) . ' €</strong></td></tr>';
            }
            //Remise after_tax
            if ($discount_type == 'after_tax' && $calcul['discount_amount'] > 0) {
                $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>' . ($discount_label . ' ' ?? 'Remise ') . $percent . ' : </strong></td><td style="text-align:right;padding-right: 4px;"><strong>' . number_format($calcul['discount_amount'], 2) . ' €</strong></td></tr>';
            }
            //Total
            $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>Total : </strong></td><td style="text-align:right;background-color: #e4e1e1;padding-right: 4px;"><strong>' . number_format($calcul['total'], 2) . ' €</strong></td></tr>';
        }

        return $htmlItems;
    }

    public function getAfDefaultSheetDetails($af_id)
    {
        $results = [];
        if ($af_id > 0) {
            $params = Param::select('id', 'code')->where([['param_code', 'PF_TYPE_SHEETS'], ['is_active', 1]])->pluck('code', 'id');
            //dd($params);
            $default_sheet = Sheet::select('id')->where([['action_id', $af_id], ['is_default', 1]])->whereNull('formation_id')->first();
            if ($default_sheet) {
                //$sheetParams=Sheetparam::select('content','param_id')->where('sheet_id',$default_sheet->id)->get()->toArray();
                $sheetParams = Sheetparam::select('content', 'param_id')->where('sheet_id', $default_sheet->id)->pluck('content', 'param_id');
                if (count($sheetParams) > 0) {
                    foreach ($sheetParams as $key => $content) {

                        $results[$params[$key]] = $content;
                        //$params[$key];
                    }
                }
            }
        }
        return $results;
    }

    public function getHtmlAfRecapSchedules($af_id)
    {
        $html = '';
        if ($af_id > 0) {
            $sessions = Session::select('id', 'code')->where('af_id', $af_id)->get();
            if (count($sessions) > 0) {
                $html = '<thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;"></th>
                        <th colspan="2" style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Séance 1</th>
                        <th colspan="2" style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Séance 2</th>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Dates</th>
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Début (h)</th>
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Fin (h)</th>
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Début (h)</th>
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Fin (h)</th>
                    </tr>
                </thead>
                <tbody>';
                foreach ($sessions as $s) {
                    $html .= '<tr style="border:1px solid #000;border-collapse: collapse;"><td colspan="5" style="border:1px solid #000;border-collapse: collapse;padding:5px;">Session : ' . $s->code . '</td></tr>';


                    $sessiondates = Sessiondate::where('session_id', $s->id)->orderBy('planning_date')->get();
                    if (count($sessiondates) > 0) {

                        foreach ($sessiondates as $sd) {
                            $html .= '<tr style="border:1px solid #000;border-collapse: collapse;">';
                            $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;

                            $date = ($planning_date) ? $planning_date->format('d-m-Y') : '';

                            $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $date . '</td>';

                            //Mooning
                            $rs_m_schedule = Schedule::where([['type', 'M'], ['sessiondate_id', $sd->id]])->get();
                            if (count($rs_m_schedule) > 0) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_m_schedule[0]->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_m_schedule[0]->end_hour);
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $start_hour->format('H') . 'h' . $start_hour->format('i') . '</td>';
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $end_hour->format('H') . 'h' . $end_hour->format('i') . '</td>';
                            } else {
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">-</td>';
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">-</td>';
                            }
                            //Afternoon
                            $rs_a_schedule = Schedule::where([['type', 'A'], ['sessiondate_id', $sd->id]])->get();
                            if (count($rs_a_schedule) > 0) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_a_schedule[0]->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_a_schedule[0]->end_hour);
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $start_hour->format('H') . 'h' . $start_hour->format('i') . '</td>';
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $end_hour->format('H') . 'h' . $end_hour->format('i') . '</td>';
                            } else {
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">-</td>';
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">-</td>';
                            }

                            $html .= '</tr>';
                        }
                    }
                }
                $html .= '</tbody>';
            }
        }
        return $html;
    }

    //getHtmlAgreementFundings
    public function getHtmlAgreementFundings($agreement_id)
    {
        $htmlItems = '';
        $items = null;
        $calcul = [];
        $fundings = [];
        $tax_percentage = 0;
        $discount_amount_type = $discount_amount = '';
        if ($agreement_id > 0) {
            $fundings = Funding::where('agreement_id', $agreement_id)->get();
            $calcul = $this->getAmountsAgreement($agreement_id);
        }
        if (count($fundings) > 0) {
            $agreement_total_amount = $calcul['total'];
            foreach ($fundings as $funding) {
                $funder_amount = $funding->amount;
                $pType = 'Montant fixe';
                if ($funding->amount_type == 'percentage') {
                    $pType = 'Pourcentage de ' . $funding->amount . '%';
                    $funder_amount = ($agreement_total_amount * $funding->amount) / 100;
                }
                $htmlItems .= '<p><strong>' . $funding->entity->name . ' (' . $pType . ' : ' . number_format($funder_amount, 2) . '€) suivant le calendrier suivant :</strong></p>';
                $items = Fundingpayment::where('funding_id', $funding->id)->orderBy('due_date')->get();
                if (count($items) > 0) {
                    $htmlItems .= '<ul>';
                    foreach ($items as $k => $item) {
                        $k++;
                        $item_amount = $item->amount;
                        if ($item->amount_type == 'percentage') {
                            $item_amount = ($funder_amount * $item->amount) / 100;
                        }
                        $due_date = Carbon::createFromFormat('Y-m-d', $item->due_date);
                        //due_date
                        $htmlItems .= '<li>Echéance ' . $k . ' - ' . $due_date->format('d-m-Y') . ' - ' . number_format($item_amount, 2) . ' €</li>';
                    }
                    $htmlItems .= '</ul>';
                }
            }
        }
        return $htmlItems;
    }

    public function getHtmlAgreementFundingsForForm($agreement_id)
    {
        $htmlItems = '';
        $items = null;
        $calcul = [];
        $fundings = [];
        $tax_percentage = 0;
        $discount_amount_type = $discount_amount = '';
        if ($agreement_id > 0) {
            $fundings = Funding::where('agreement_id', $agreement_id)->get();
            $calcul = $this->getAmountsAgreement($agreement_id);
        }
        if (count($fundings) > 0) {
            $agreementHasInvoice = $this->agreementHasInvoice($agreement_id);
            $agreement_total_amount = $calcul['total'];
            foreach ($fundings as $funding) {
                $funder_amount = $funding->amount;
                $pType = 'Montant fixe';
                if ($funding->amount_type == 'percentage') {
                    $pType = 'Pourcentage de ' . $funding->amount . '%';
                    $funder_amount = ($agreement_total_amount * $funding->amount) / 100;
                }


                $btn_edit_fund = $btn_delete_fund = '';
                if (!$agreementHasInvoice) {
                    //$btn_edit_fund = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_formFunding(' . $funding->id . ',' . $funding->agreement_id . ')" data-toggle="tooltip" title="Modifier la ligne"><i class="flaticon-edit"></i></button>';
                    $btn_delete_fund = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_deleteFunding(' . $funding->id . ')" title="Suppression"><i class="flaticon-delete"></i></button>';
                }

                $htmlItems .= '<p><strong>' . $funding->entity->name . ' <span class="text-primary">(' . $pType . ' : ' . number_format($funder_amount, 2) . '€)</span> :</strong>' . $btn_edit_fund . $btn_delete_fund . '</p>';
                $arr = $this->getEcheanceTotalAmount($funding->id);
                $htmlItems .= '<p>Montant : <strong class="text-primary">' . number_format($funder_amount, 2) . ' €</strong> - Total Echéances : <strong class="text-success">' . number_format($arr['total'], 2) . ' €</strong> - Reste : <strong class="text-danger">' . number_format($arr['rest'], 2) . ' €</strong></p>';

                $items = Fundingpayment::where('funding_id', $funding->id)->orderBy('due_date')->get();
                $btn_add_item = '';
                if (!$agreementHasInvoice) {
                    if ($arr['rest'] > 0)
                        $btn_add_item = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_formFundingPayment(0,' . $funding->id . ')" data-toggle="tooltip" title="Ajouter"><i class="flaticon2-add-1"></i></button>';
                }
                $htmlItems .= '<table class="table table-bordered">';
                $htmlItems .= '<thead><tr>
          <th>Echéance</th>
          <th>Date</th>
          <th>Type</th>
          <th>Montant</th>
          <th>' . $btn_add_item . '</th>
          </tr>
          </thead>
          <tbody>';
                if (count($items) > 0) {
                    foreach ($items as $k => $item) {
                        $k++;
                        $item_amount = $item->amount;
                        $itemType = 'Montant fixe';
                        if ($item->amount_type == 'percentage') {
                            $itemType = 'Pourcentage de ' . $item->amount . '%';
                            $item_amount = ($funder_amount * $item->amount) / 100;
                        }
                        $due_date = Carbon::createFromFormat('Y-m-d', $item->due_date);
                        $btn_edit_item = $btn_delete_item = '';
                        if (!$agreementHasInvoice) {
                            //$btn_edit_item = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_formFundingPayment(' . $item->id . ',' . $funding->id . ')" data-toggle="tooltip" title="Modifier la ligne"><i class="flaticon-edit"></i></button>';
                            $btn_delete_item = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_deleteFundingPayment(' . $item->id . ')" title="Suppression"><i class="flaticon-delete"></i></button>';
                        }
                        $htmlItems .= '<tr>
                        <td>Echéance ' . $k . '</td>
                        <td>' . $due_date->format('d-m-Y') . '</td>
                        <td>' . $itemType . '</td>
                        <td>' . number_format($item_amount, 2) . ' €</td>
                        <td>' . $btn_edit_item . $btn_delete_item . '</td>
                        </tr>';
                    }
                }
                $htmlItems .= '</tbody></table>';
                //divider
                $htmlItems .= '<div class="separator separator-dashed my-5"></div>';
            }
        }
        return $htmlItems;
    }

    public function manageFunding($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Funding();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Funding::find($row_id);
                if (!$row) {
                    $row = new Funding();
                }
            }
            if (isset($data['amount_type'])) {
                $row->amount_type = (isset($data['amount_type'])) ? $data['amount_type'] : null;
            }
            if (isset($data['amount'])) {
                $row->amount = (isset($data['amount'])) ? $data['amount'] : null;
            }
            if (isset($data['status'])) {
                $row->status = (isset($data['status'])) ? $data['status'] : null;
            }
            $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : 0;
            $row->agreement_id = (isset($data['agreement_id'])) ? $data['agreement_id'] : null;
            $row->invoice_id = (isset($data['invoice_id'])) ? $data['invoice_id'] : null;
            $row->is_cfa = (isset($data['is_cfa'])) ? $data['is_cfa'] : 0;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageFundingPayment($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Fundingpayment();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Fundingpayment::find($row_id);
                if (!$row) {
                    $row = new Fundingpayment();
                }
            }
            if (isset($data['amount_type'])) {
                $row->amount_type = (isset($data['amount_type'])) ? $data['amount_type'] : null;
            }
            if (isset($data['amount'])) {
                $row->amount = (isset($data['amount'])) ? $data['amount'] : null;
            }
            if (isset($data['due_date'])) {
                $row->due_date = (isset($data['due_date'])) ? $data['due_date'] : null;
            }
            if (isset($data['payment_date'])) {
                $row->payment_date = (isset($data['payment_date'])) ? $data['payment_date'] : null;
            }
            $row->funding_id = (isset($data['funding_id'])) ? $data['funding_id'] : 0;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function getFundingsTotalAmount($agreement_id)
    {
        $total = $rest = 0;
        if ($agreement_id > 0) {
            $calcul = $this->getAmountsAgreement($agreement_id);
            $agreement_total_amount = $calcul['total'];
            $fundings = Funding::select('id', 'amount_type', 'amount')->where('agreement_id', $agreement_id)->get();
            if (count($fundings) > 0) {
                foreach ($fundings as $funding) {
                    $funder_amount = $funding->amount;
                    if ($funding->amount_type == 'percentage') {
                        $funder_amount = ($agreement_total_amount * $funding->amount) / 100;
                    }
                    $total += $funder_amount;
                }
            }
            $rest = $agreement_total_amount - $total;
        }
        return ['total' => $total, 'rest' => $rest];
    }

    public function getEcheanceTotalAmount($funding_id)
    {
        $total = $rest = 0;
        if ($funding_id > 0) {
            $funding = Funding::select('id', 'amount_type', 'amount', 'agreement_id')->where('id', $funding_id)->first();
            $funder_amount = $this->calculateAmountFunding($funding_id);
            $items = Fundingpayment::select('id', 'amount_type', 'amount')->where('funding_id', $funding_id)->get();
            if (count($items) > 0) {
                foreach ($items as $item) {
                    $item_amount = $item->amount;
                    if ($item->amount_type == 'percentage') {
                        $item_amount = ($funder_amount * $item->amount) / 100;
                    }
                    $total += $item_amount;
                }
            }
            $rest = $funder_amount - $total;
        }

        return ['total' => $total, 'rest' => $rest];
    }

    public function calculateAmountFunding($funding_id)
    {
        $funder_amount = 0;
        if ($funding_id > 0) {
            $funding = Funding::select('id', 'amount_type', 'amount', 'agreement_id')->where('id', $funding_id)->first();
            $calcul = $this->getAmountsAgreement($funding->agreement_id);
            $agreement_total_amount = $calcul['total'];
            $funder_amount = $funding->amount;
            if ($funding->amount_type == 'percentage') {
                $funder_amount = ($agreement_total_amount * $funding->amount) / 100;
            }
        }
        return $funder_amount;
    }

    public function manageInvoice($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Invoice();
            $id = (isset($data['id'])) ? $data['id'] : 0;
            if ($id > 0) {
                $row = Invoice::find($id);
                if (!$row) {
                    $row = new Invoice();
                }
            }
            if (isset($data['number'])) {
                $row->number = (isset($data['number'])) ? $data['number'] : null;
            }

            $row->bill_date = (isset($data['bill_date'])) ? $data['bill_date'] : null;
            $row->due_date = (isset($data['due_date'])) ? $data['due_date'] : null;
            $row->accounting_code = (isset($data['accounting_code'])) ? $data['accounting_code'] : null;
            $row->analytical_code = (isset($data['analytical_code'])) ? $data['analytical_code'] : null;
            $row->collective_code = (isset($data['collective_code'])) ? $data['collective_code'] : null;
            $row->note = (isset($data['note'])) ? $data['note'] : null;
            $row->invoice_type = (isset($data['invoice_type'])) ? $data['invoice_type'] : null;
            if (isset($data['tax_percentage'])) {
                $row->tax_percentage = (isset($data['tax_percentage'])) ? $data['tax_percentage'] : null;
            }
            if (isset($data['discount_label'])) {
                $row->discount_label = (isset($data['discount_label'])) ? $data['discount_label'] : null;
            }
            if (isset($data['discount_amount'])) {
                $row->discount_amount = (isset($data['discount_amount'])) ? $data['discount_amount'] : null;
            }
            if (isset($data['discount_amount_type'])) {
                $row->discount_amount_type = (isset($data['discount_amount_type'])) ? $data['discount_amount_type'] : null;
            }
            if (isset($data['discount_type'])) {
                $row->discount_type = (isset($data['discount_type'])) ? $data['discount_type'] : null;
            }
            if (isset($data['status'])) {
                $row->status = (isset($data['status'])) ? $data['status'] : null;
            }
            if (isset($data['cancelled_at'])) {
                $row->cancelled_at = (isset($data['cancelled_at'])) ? $data['cancelled_at'] : null;
            }
            if (isset($data['cancelled_by'])) {
                $row->cancelled_by = (isset($data['cancelled_by'])) ? $data['cancelled_by'] : null;
            }

            if (isset($data['created_by'])) {
                $row->created_by = (isset($data['created_by'])) ? $data['created_by'] : null;
            }
            if (isset($data['entitie_id'])) {
                $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : null;
            }
            if (isset($data['contact_id'])) {
                $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            }
            if (isset($data['agreement_id'])) {
                $row->agreement_id = (isset($data['agreement_id'])) ? $data['agreement_id'] : null;
            }
            if (isset($data['entitie_funder_id'])) {
                $row->entitie_funder_id = (isset($data['entitie_funder_id'])) ? $data['entitie_funder_id'] : null;
            }
            if (isset($data['contact_funder_id'])) {
                $row->contact_funder_id = (isset($data['contact_funder_id'])) ? $data['contact_funder_id'] : null;
            }
            if (isset($data['fundingpayment_id'])) {
                $row->fundingpayment_id = (isset($data['fundingpayment_id'])) ? $data['fundingpayment_id'] : null;
            }
            $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            $row->funding_option = (isset($data['funding_option'])) ? $data['funding_option'] : null;

            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageInvoiceItem($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Invoiceitem();
            $id = (isset($data['id'])) ? $data['id'] : 0;
            if ($id > 0) {
                $row = Invoiceitem::find($id);
                if (!$row) {
                    $row = new Invoiceitem();
                }
            }

            if (isset($data['title'])) {
                $row->title = (isset($data['title'])) ? $data['title'] : null;
            }
            if (isset($data['description'])) {
                $row->description = (isset($data['description'])) ? $data['description'] : null;
            }
            $row->accounting_code = (isset($data['accounting_code'])) ? $data['accounting_code'] : null;
            $row->analytical_code = (isset($data['analytical_code'])) ? $data['analytical_code'] : null;
            $row->quantity = (isset($data['quantity'])) ? $data['quantity'] : 1;
            $row->unit_type = (isset($data['unit_type'])) ? $data['unit_type'] : null;
            $row->rate = (isset($data['rate'])) ? $data['rate'] : null;
            $row->total = (isset($data['total'])) ? $data['total'] : null;
            $row->sort = (isset($data['sort'])) ? $data['sort'] : 1;
            $row->fundingpayment_id = (isset($data['fundingpayment_id'])) ? $data['fundingpayment_id'] : null;
            $row->invoice_id = (isset($data['invoice_id'])) ? $data['invoice_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function manageInvoicePayment($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Invoicepayment();
            $id = (isset($data['id'])) ? $data['id'] : 0;
            if ($id > 0) {
                $row = Invoicepayment::find($id);
                if (!$row) {
                    $row = new Invoicepayment();
                }
            }
            $row->amount = (isset($data['amount'])) ? $data['amount'] : null;
            $row->payment_date = (isset($data['payment_date'])) ? $data['payment_date'] : null;
            $row->payment_method = (isset($data['payment_method'])) ? $data['payment_method'] : null;
            $row->reference = (isset($data['reference'])) ? $data['reference'] : null;
            $row->note = (isset($data['note'])) ? $data['note'] : null;
            $row->invoice_id = (isset($data['invoice_id'])) ? $data['invoice_id'] : null;
            $row->funding_payment_id = (isset($data['funding_payment_id'])) ? $data['funding_payment_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function generateInvoiceNumber($type)
    {
        $tab = $this->getIndexNumber($type);
        $indice = $tab['indice'];
        $invoiceNumber = $tab['number'];
        $this->setIndexNumber($indice, $type);
        return 'F' . $invoiceNumber;
    }
    public function generateInvoiceNumberByBillDate($type, $bill_date)
    {
        $tab = $this->getIndexNumberByBillDate($type, $bill_date);
        $indice = $tab['indice'];
        $invoiceNumber = $tab['number'];
        $this->setIndexNumberByBillDate($indice, $type, $bill_date);
        return 'F' . $invoiceNumber;
    }
    public function generateRefundNumberByBillDate($type, $bill_date)
    {
        $tab = $this->getIndexNumberByBillDate($type, $bill_date);
        $indice = $tab['indice'];
        $invoiceNumber = $tab['number'];
        $this->setIndexNumberByBillDate($indice, $type, $bill_date);
        return 'A' . $invoiceNumber;
    }

    public function getAmountsInvoice($invoice_id)
    {
        $total = $subtotal = $total_paid = $total_refund = $discount_amount = 0;
        if ($invoice_id > 0) {
            $invoice = Invoice::find($invoice_id);

            $subtotal = Invoiceitem::where('invoice_id', $invoice_id)->sum('total');
            $total_paid = Invoicepayment::where('invoice_id', $invoice_id)->sum('amount');

            //Réduction : //'percentage', 'fixed_amount'
            //'before_tax', 'after_tax'
            $tax_amount = 0;
            $amout_to_discount = $subtotal;
            if ($invoice->discount_type == 'after_tax') {
                if ($invoice->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($subtotal, $invoice->tax_percentage);
                }
                $amout_to_discount = $subtotal + $tax_amount;
                $discount_amount = $this->calculateDiscount($amout_to_discount, $invoice->discount_amount_type, $invoice->discount_amount);
            } elseif ($invoice->discount_type == 'before_tax') {
                $amout_to_discount = $subtotal;
                $discount_amount = $this->calculateDiscount($amout_to_discount, $invoice->discount_amount_type, $invoice->discount_amount);
                if ($invoice->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($subtotal - $discount_amount, $invoice->tax_percentage);
                }
            } else {
                if ($invoice->tax_percentage > 0) {
                    $tax_amount = $this->calculateTaxAmount($subtotal, $invoice->tax_percentage);
                }
            }

            $total = $subtotal - $discount_amount + $tax_amount;
        }
        return array(
            'total' => $total,
            'total_paid' => $total_paid,
            'subtotal' => $subtotal,
            'discount_amount' => $discount_amount,
            'tax_amount' => $tax_amount,
            'total_refund' => $total_refund,
        );
    }

    public function getHtmlInvoiceItems($invoice_id, $type = 1)
    {
        /* 
            $type == 1 => facture
            $type == 2 => avoir
        */
        $htmlItems = '<style>#INVOICE_ITEMS tbody tr td p{margin:0px !important;}</style>';
        $items = null;
        $calcul = [];
        if ($invoice_id > 0) {
            $items = Invoiceitem::where('invoice_id', $invoice_id)->get();
            $calcul = $this->getAmountsInvoice($invoice_id);
        }
        if (count($items) > 0) {
            $invoice = Invoice::where('id', $invoice_id)->first();
            if($invoice->agreement_id > 0){
                $items = Agreementitem::where('agreement_id', $invoice->agreement_id)->get();
            }
            foreach ($items as $item) {
                $htmlItems .= '<tr style="border:0.5px solid #000;border-collapse: collapse;">';
                //Designation
                $htmlItems .= '<td style="border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;"><p>' . $item->title . '</p><p>' . $item->description . '</p></td>';
                //Quantité
                // $unit = $item->unit_type;
                /* if($item->is_main_item==1){
          $unit=($item->unit_type!='')?$item->unit_type.'(s)':'';
        } */
                $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . $item->quantity . ' ' .'</td>';
                //Prix unitaire
                $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . number_format($item->rate, 2) . ' €</td>';
                //Total
                $htmlItems .= '<td style="text-align:right;border:0.5px solid #000;border-collapse: collapse;padding-left: 4px;padding-right: 4px;">' . number_format($item->total, 2) . ' €</td>';
                $htmlItems .= '</tr>';
            }
            //Total
            $htmlItems .= '<tr><td colspan="3" style="text-align:right;">Sous Total : </td><td style="text-align:right;background-color: #e4e1e1;padding-right: 4px;">' . number_format($calcul['subtotal'], 2) . ' €</td></tr>';

            $percent = '';
            if ($invoice->discount_amount_type == 'percentage') {
                $percent = '(' . $invoice->discount_amount . '%)';
            }
            if ($calcul['discount_amount']) {
                $htmlItems .= '<tr><td colspan="3" style="text-align:right;">' . $invoice->discount_label . ' ' . $percent . ': </td><td style="text-align:right;background-color: #e4e1e1;padding-right: 4px;">' . number_format($calcul['discount_amount'], 2) . ' €</td></tr>';
            }
            if ($calcul['tax_amount']) {
                $htmlItems .= '<tr><td colspan="3" style="text-align:right;">Tax (' . $invoice->tax_percentage . '%): </td><td style="text-align:right;background-color: #e4e1e1;padding-right: 4px;">' . number_format($calcul['tax_amount'], 2) . ' €</td></tr>';
            }
            $total = $calcul['total'];
            if ($type == 2) {
                $total = $total * -1;
            }
            $htmlItems .= '<tr><td colspan="3" style="text-align:right;"><strong>Total : </strong></td><td style="text-align:right;background-color: #e4e1e1;padding-right: 4px;"><strong>' . number_format($total, 2) . ' €</strong></td></tr>';
        }

        return $htmlItems;
    }

    public function getStatusInvoice($invoice_id)
    {
        $status = 'not_paid';
        //enum('draft', 'not_paid','partial_paid', 'paid','cancelled')
        if ($invoice_id > 0) {
            $totalPayments = Invoicepayment::where('invoice_id', $invoice_id)->sum('amount');
            $calcul = $this->getAmountsInvoice($invoice_id);
            $totalInvoice = $calcul['total'];
            if ($totalPayments == $totalInvoice || $totalPayments >= $totalInvoice) {
                $status = 'paid';
            }
            if ($totalPayments > 0 && $totalPayments < $totalInvoice) {
                $status = 'partial_paid';
            }
        }
        return $status;
    }

    public function getFundingPayment($agreement_id, $entitie_id)
    {
        $htmlItems = '';
        $funding_id = 0;
        $rsF = Funding::select('id', 'amount_type', 'amount')->where([['entitie_id', $entitie_id], ['agreement_id', $agreement_id]])->first();
        if (isset($rsF)) {
            $calcul = $this->getAmountsAgreement($agreement_id);
            $agreement_total_amount = $calcul['total'];
            $funding_id = $rsF->id;
            $funder_amount = $rsF->amount;
            if ($rsF->amount_type == 'percentage') {
                $funder_amount = ($agreement_total_amount * $rsF->amount) / 100;
            }
        }
        $items = Fundingpayment::where('funding_id', $funding_id)->orderBy('due_date')->get();
        if (count($items) > 0) {
            $htmlItems .= '<ul class="list-unstyled">';
            foreach ($items as $k => $item) {
                $k++;
                $item_amount = $item->amount;
                if ($item->amount_type == 'percentage') {
                    $item_amount = ($funder_amount * $item->amount) / 100;
                }
                $due_date = Carbon::createFromFormat('Y-m-d', $item->due_date);
                //due_date
                $htmlItems .= '<li>Echéance ' . $k . ' - ' . $due_date->format('d-m-Y') . ' - ' . number_format($item_amount, 2) . ' €</li>';
            }
            $htmlItems .= '</ul>';
        }
        return $htmlItems;
    }

    public function getFundingPaymentById($funding_payment_id, $entitie_id, $agreement_id)
    {
        $htmlItems = '';
        $funding_id = 0;
        $rsF = Funding::select('id')->where([['entitie_id', $entitie_id], ['agreement_id', $agreement_id]])->first();
        if (isset($rsF)) {
            $funding_id = $rsF->id;
        }
        $funder_amount = $this->calculateAmountFunding($funding_id);
        $items = Fundingpayment::where('id', $funding_payment_id)->orderBy('due_date')->get();
        //dd($funding_payment_id);
        if (count($items) > 0) {
            $htmlItems .= '<ul class="list-unstyled">';
            foreach ($items as $k => $item) {
                $k++;
                $item_amount = $item->amount;
                if ($item->amount_type == 'percentage') {
                    $item_amount = ($funder_amount * $item->amount) / 100;
                }
                $due_date = Carbon::createFromFormat('Y-m-d', $item->due_date);
                //due_date
                $htmlItems .= '<li>Echéance ' . $k . ' - ' . $due_date->format('d-m-Y') . ' - ' . number_format($item_amount, 2) . ' €</li>';
            }
            $htmlItems .= '</ul>';
        }
        return $htmlItems;
    }

    public function calculateAmountFundingPayment($funding_payment_id)
    {
        $fundingpayment_amount = 0;
        $fundingpayment = Fundingpayment::where('id', $funding_payment_id)->first();
        if (isset($fundingpayment)) {
            $calcul = $this->getAmountsAgreement($fundingpayment->funding->agreement_id);
            $agreement_total_amount = $calcul['total'];
            $funder_amount = $fundingpayment->funding->amount;
            if ($fundingpayment->funding->amount_type == 'percentage') {
                $funder_amount = ($agreement_total_amount * $fundingpayment->funding->amount) / 100;
            }
            $fundingpayment_amount = $fundingpayment->amount;
            if ($fundingpayment->amount_type == 'percentage') {
                $fundingpayment_amount = ($funder_amount * $fundingpayment->amount) / 100;
            }
        }
        return $fundingpayment_amount;
    }

    public function getAgreementByEstimate($estimate_id)
    {
        $agreement_type = $number = null;
        if ($estimate_id > 0) {
            $agreement = Agreement::select('agreement_type', 'number')->where('estimate_id', $estimate_id)->first();
            if (isset($agreement)) {
                $agreement_type = $agreement->agreement_type;
                $number = $agreement->number;
            }
        }
        return ['agreement_type' => $agreement_type, 'number' => $number];
    }

    public function getStatisticsPf($pf_id)
    {
        $nb_versions = 0;
        if ($pf_id > 0) {
            $nb_versions = Sheet::where('formation_id', $pf_id)->count();
        }
        return ['nb_versions' => $nb_versions];
    }

    public function getStatisticsAf($af_id)
    {
        $nb_sessions = $nb_enrollments_stagiaires = $nb_enrollments_intervenants = $nb_devis = $nb_conventions = $nb_contrats = 0;
        if ($af_id > 0) {
            $nb_sessions = Session::where('af_id', $af_id)->where('is_internship_period', 0)->count();
            $nb_stage_periods = Session::where('af_id', $af_id)->where('is_internship_period', 1)->count();
            $nb_enrollments_stagiaires = Enrollment::where([['af_id', $af_id], ['enrollment_type', 'S']])->count();
            $nb_enrollments_intervenants = Enrollment::where([['af_id', $af_id], ['enrollment_type', 'F']])->count();
            $nb_devis = Estimate::where('af_id', $af_id)->count();
            $nb_conventions = Agreement::where([['af_id', $af_id], ['agreement_type', 'convention']])->count();
            $nb_contrats = Agreement::where([['af_id', $af_id], ['agreement_type', 'contract']])->count();
        }
        return [
            'nb_sessions' => $nb_sessions,
            'nb_enrollments_stagiaires' => $nb_enrollments_stagiaires,
            'nb_enrollments_intervenants' => $nb_enrollments_intervenants,
            'nb_devis' => $nb_devis,
            'nb_conventions' => $nb_conventions,
            'nb_contrats' => $nb_contrats,
            'nb_stage_periods' => $nb_stage_periods,
        ];
    }

    public function getHtmlInvoiceFromAgreement($agreement_id)
    {
        $htmlItems = '';
        $items = null;
        $calcul = [];
        $fundings = [];
        $tax_percentage = 0;
        $discount_amount_type = $discount_amount = '';
        if ($agreement_id > 0) {
            $fundings = Funding::where('agreement_id', $agreement_id)->get();
            $calcul = $this->getAmountsAgreement($agreement_id);
        }
        if (count($fundings) > 0) {
            $agreement_total_amount = $calcul['total'];
            foreach ($fundings as $funding) {
                $funder_amount = $funding->amount;
                $pType = 'Montant fixe';
                if ($funding->amount_type == 'percentage') {
                    $pType = 'Pourcentage de ' . $funding->amount . '%';
                    $funder_amount = ($agreement_total_amount * $funding->amount) / 100;
                }
                $htmlItems .= '<p><strong>' . $funding->entity->name . ' <span class="text-primary">(' . $pType . ' : ' . number_format($funder_amount, 2) . '€)</span> :</strong></p>';
                $arr = $this->getEcheanceTotalAmount($funding->id);
                /* $htmlItems .='<p>Montant : <strong class="text-primary">'.number_format($funder_amount,2).' €</strong> - Total Echéances : <strong class="text-success">'.number_format($arr['total'],2).' €</strong> - Reste : <strong class="text-danger">'.number_format($arr['rest'],2).' €</strong></p>'; */
                $items = Fundingpayment::where('funding_id', $funding->id)->orderBy('due_date')->get();
                //$checkbox_group_checkable='<label class="checkbox checkbox-single"><input type="checkbox" value="" class="group-checkable"/><span></span></label>';
                $checkbox_group_checkable = '';
                $htmlItems .= '<table class="table table-bordered">';
                $htmlItems .= '<thead><tr>
          <th>' . $checkbox_group_checkable . '</th>
          <th>Echéance</th>
          <th>Date</th>
          <th>Type</th>
          <th>Montant</th>
          </tr>
          </thead>
          <tbody>';
                if (count($items) > 0) {
                    foreach ($items as $k => $item) {
                        $k++;
                        $item_amount = $item->amount;
                        $itemType = 'Montant fixe';
                        if ($item->amount_type == 'percentage') {
                            $itemType = 'Pourcentage de ' . $item->amount . '%';
                            $item_amount = ($funder_amount * $item->amount) / 100;
                        }
                        $due_date = Carbon::createFromFormat('Y-m-d', $item->due_date);
                        $number = $this->getInvoiceFundingPayment($item->id);
                        $checkBox = '';
                        if ($number == null) {
                            if ($item_amount > 0) {
                                $checkBox = '<label class="checkbox checkbox-single"><input type="checkbox" name="IDS_FUNDING_PAYMENTS[]" value="' . $item->id . '" class="checkable"><span></span></label>';
                            }
                        } else {
                            $checkBox = '<p class="text-primary">' . $number . '</p>';
                        }
                        $htmlItems .= '<tr>
                        <td>' . $checkBox . '</td>
                        <td>Echéance ' . $k . '</td>
                        <td>' . $due_date->format('d-m-Y') . '</td>
                        <td>' . $itemType . '</td>
                        <td>' . number_format($item_amount, 2) . ' €</td>
                        </tr>';
                    }
                }
                $htmlItems .= '</tbody></table>';
                //divider
                $htmlItems .= '<div class="separator separator-dashed my-5"></div>';
            }
        } else {
            $htmlItems = '<p class="text-warning">Aucun échéance pour la facturation !</p>';
        }
        return $htmlItems;
    }

    public function getInvoiceFundingPayment($fundingpayment_id)
    {
        $number = null;
        if ($fundingpayment_id > 0) {
            $rsInv = Invoice::select('id', 'number')->where('fundingpayment_id', $fundingpayment_id)->first();
            if ($rsInv) {
                $number = $rsInv->number;
            }
        }
        return $number;
    }

    public function checkIfCanInvoiceFundingPayment($fundingpayment_id)
    {
        $canInvoice = true;
        if ($fundingpayment_id > 0) {
            $nbInv = Invoice::select('id', 'number')
                ->where('fundingpayment_id', $fundingpayment_id)
                ->where('status', '!=', 'cancelled')
                ->count();
            if ($nbInv > 0) {
                $canInvoice = false;
            }
        }
        return $canInvoice;
    }

    public function getAgreementStatistics($agreement_id)
    {
        $fundings = $deadlines = $invoices = 0;
        if ($agreement_id > 0) {
            $rsfundings = Funding::select('id')->where('agreement_id', $agreement_id)->get();
            $fundings = count($rsfundings);
            if ($fundings > 0) {
                foreach ($rsfundings as $f) {
                    $rsFp = Fundingpayment::select('id')->where('funding_id', $f->id)->get();
                    foreach ($rsFp as $fp) {
                        $invoices += Invoice::select('id')->where('fundingpayment_id', $fp->id)->count();
                    }
                    $deadlines += count($rsFp);
                }
            }
        }
        return ['fundings' => $fundings, 'deadlines' => $deadlines, 'invoices' => $invoices];
    }

    public function manageConvocation($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Convocation();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Convocation::find($row_id);
                if (!$row) {
                    $row = new Convocation();
                }
            }
            if (isset($data['number'])) {
                $row->number = (isset($data['number'])) ? $data['number'] : null;
            }
            if (isset($data['last_email_sent_date'])) {
                $row->last_email_sent_date = (isset($data['last_email_sent_date'])) ? $data['last_email_sent_date'] : null;
            }
            if (isset($data['status'])) {
                $row->status = (isset($data['status'])) ? $data['status'] : null;
            }
            if (isset($data['entitie_id'])) {
                $row->entitie_id = (isset($data['entitie_id'])) ? $data['entitie_id'] : null;
            }
            if (isset($data['contact_id'])) {
                $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            }
            if (isset($data['af_id'])) {
                $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            }
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    public function getScheduledMembersBySession($session_id)
    {
        $ids_members = [];
        if ($session_id > 0) {
            $ids_sessiondates = Sessiondate::select('id')->where('session_id', $session_id)->pluck('id');
            if (count($ids_sessiondates) > 0) {
                $ids_schedules = Schedule::whereIn('sessiondate_id', $ids_sessiondates)->pluck('id');
                if (count($ids_schedules) > 0) {
                    $ids_members = Schedulecontact::select('member_id')->whereIn('schedule_id', $ids_schedules)->where('is_former', 0)->pluck('member_id');
                }
            }
        }
        return $ids_members;
    }

    public function getAfInformations($af_id)
    {
        $description = '';
        if ($af_id > 0) {
            $af = Action::select('device_type', 'started_at', 'ended_at', 'nb_hours', 'nb_days', 'nb_pratical_hours', 'nb_pratical_days', 'max_nb_trainees', 'training_site')->where('id', $af_id)->first();
            $FIRST_SCHEDULE_HOUR = $LAST_SCHEDULE_HOUR = '';
            $sessions_ids = Session::select('id')->where('af_id', $af_id)->pluck('id');
            if (count($sessions_ids) > 0) {
                // $sessiondates_ids = Sessiondate::select('id')->whereIn('session_id', $sessions_ids)->pluck('id');
                $sessiondates_first_ids = (array) Sessiondate::select('id')->whereIn('session_id', $sessions_ids)->orderBy('planning_date', 'asc')->pluck('id')->first();
                $sessiondates_last_ids = (array) Sessiondate::select('id')->whereIn('session_id', $sessions_ids)->orderBy('planning_date', 'desc')->pluck('id')->first();
                // dd($sessiondates_first_ids);
                // if (count($sessiondates_ids) > 0) {
                if (count($sessiondates_first_ids) > 0 && count($sessiondates_last_ids) > 0) {
                    $rs_start_schedule = Schedule::select('start_hour')->whereIn('sessiondate_id', $sessiondates_first_ids)->orderBy('start_hour', 'asc')->first();
                    $rs_end_schedule = Schedule::select('end_hour')->whereIn('sessiondate_id', $sessiondates_last_ids)->orderBy('end_hour', 'desc')->first();
                    if ($rs_start_schedule && $rs_end_schedule) {
                        $dtStart = Carbon::createFromFormat('Y-m-d H:i:s', $rs_start_schedule->start_hour);
                        $dtEnd = Carbon::createFromFormat('Y-m-d H:i:s', $rs_end_schedule->end_hour);
                        $FIRST_SCHEDULE_HOUR = $dtStart->format('H:i');
                        $LAST_SCHEDULE_HOUR = $dtEnd->format('H:i');
                    }
                }
            }
            $descriptionInfos = '<p>Formation : {device_type}</p><p>Date : du {started_at} au {ended_at}</p><p>Horaires : de {FIRST_SCHEDULE_HOUR} à {LAST_SCHEDULE_HOUR}</p><p>Nombre d’heures : {nb_hours}</p><p>Nombre de jours : {nb_days}</p><p>Nombre de participants maximum : {max_nb_trainees}</p><p>Lieu de Formation : {training_site}</p>';
            if ($af) {
                $dtStartedAt = Carbon::createFromFormat('Y-m-d H:i:s', $af->started_at);
                $dtEndedAt = Carbon::createFromFormat('Y-m-d H:i:s', $af->ended_at);
                $training_site = ($af->training_site != 'OTHER') ? $af->training_site : $af->other_training_site;
            }
            //
            $rs = Ressource::select('address_training_location')->where([['name', $af->training_site], ['type', 'RES_TYPE_LIEU'], ['is_internal', 0]])->first();
            $address_training_location = ($rs) ? $rs->address_training_location : '--';
            $ids = Enrollment::select('id')->where([['enrollment_type', 'F'], ['af_id', $af_id]])->pluck('id');
            $mainFormer = '';
            if (count($ids) > 0) {
                $rM = Member::select('contact_id')->whereIn('enrollment_id', $ids)->first();
                $mainFormer = ($rM) ? ($rM->contact->firstname . ' ' . $rM->contact->lastname) : '';
            }
        }
        //dd($training_site);
        return [
            'started_at' => $dtStartedAt->format('d/m/Y'),
            'ended_at' => $dtEndedAt->format('d/m/Y'),
            'FIRST_SCHEDULE_HOUR' => $FIRST_SCHEDULE_HOUR,
            'LAST_SCHEDULE_HOUR' => $LAST_SCHEDULE_HOUR,
            'nb_hours' => $af->nb_hours,
            'nb_days' => $af->nb_days,
            'nb_pratical_hours' => $af->nb_pratical_hours,
            'nb_pratical_days' => $af->nb_pratical_days,
            'max_nb_trainees' => $af->max_nb_trainees,
            'training_site' => $training_site,
            'address_training_location' => $address_training_location,
            'mainFormer' => $mainFormer,
        ];
    }

    public function getAfInformationswith($af_id, $id2)
    {
        $description = '';
        if ($af_id > 0) {
            $af = Action::select('device_type', 'started_at', 'ended_at', 'nb_hours', 'nb_days', 'nb_pratical_hours', 'nb_pratical_days', 'max_nb_trainees', 'training_site')->where('id', $af_id)->first();
            $FIRST_SCHEDULE_HOUR = $LAST_SCHEDULE_HOUR = '';
            $sessions_ids = Session::select('id')->where('af_id', $af_id)->pluck('id');
            if (count($sessions_ids) > 0) {
                $sessiondates_first_ids = (array) Sessiondate::select('id')->whereIn('session_id', $sessions_ids)->orderBy('planning_date', 'asc')->pluck('id')->first();
                $sessiondates_last_ids = (array) Sessiondate::select('id')->whereIn('session_id', $sessions_ids)->orderBy('planning_date', 'desc')->pluck('id')->first();
                if (count($sessiondates_first_ids) > 0 && count($sessiondates_last_ids) > 0) {
                    $Internshiproposal = Internshiproposal::select('session_id')->where('id', $id2)->first();
                    $rs_start_schedule = Schedule::select('start_hour')->whereIn('sessiondate_id', $sessiondates_first_ids)->orderBy('start_hour', 'asc')->first();
                    $rs_end_schedule = Schedule::select('end_hour')->whereIn('sessiondate_id', $sessiondates_last_ids)->orderBy('end_hour', 'desc')->first();
                    $session_type = Session::where('id', $Internshiproposal->session_id)->pluck('session_type');
                    if ($rs_start_schedule && $rs_end_schedule) {
                        $dtStart = Carbon::createFromFormat('Y-m-d H:i:s', $rs_start_schedule->start_hour);
                        $dtEnd = Carbon::createFromFormat('Y-m-d H:i:s', $rs_end_schedule->end_hour);
                        $FIRST_SCHEDULE_HOUR = $dtStart->format('H:i');
                        $LAST_SCHEDULE_HOUR = $dtEnd->format('H:i');
                    }
                }
            }
            $descriptionInfos = '<p>Formation : {device_type}</p><p>Date : du {started_at} au {ended_at}</p><p>Horaires : de {FIRST_SCHEDULE_HOUR} à {LAST_SCHEDULE_HOUR}</p><p>Nombre d’heures : {nb_hours}</p><p>Nombre de jours : {nb_days}</p><p>Nombre de participants maximum : {max_nb_trainees}</p><p>Lieu de Formation : {training_site}</p>';
            if ($af) {
                $dtStartedAt = Carbon::createFromFormat('Y-m-d H:i:s', $af->started_at);
                $dtEndedAt = Carbon::createFromFormat('Y-m-d H:i:s', $af->ended_at);
                $training_site = ($af->training_site != 'OTHER') ? $af->training_site : $af->other_training_site;
            }

            if ($session_type) {
                $session_type = $session_type[0];
            }
            //
            $rs = Ressource::select('address_training_location')->where([['name', $af->training_site], ['type', 'RES_TYPE_LIEU'], ['is_internal', 0]])->first();
            $address_training_location = ($rs) ? $rs->address_training_location : '--';
            $ids = Enrollment::select('id')->where([['enrollment_type', 'F'], ['af_id', $af_id]])->pluck('id');
            $mainFormer = '';
            if (count($ids) > 0) {
                $rM = Member::select('contact_id')->whereIn('enrollment_id', $ids)->first();
                $mainFormer = ($rM) ? ($rM->contact->firstname . ' ' . $rM->contact->lastname) : '';
            }
        }
        //dd($training_site);
        return [
            'started_at' => $dtStartedAt->format('d/m/Y'),
            'ended_at' => $dtEndedAt->format('d/m/Y'),
            'FIRST_SCHEDULE_HOUR' => $FIRST_SCHEDULE_HOUR,
            'LAST_SCHEDULE_HOUR' => $LAST_SCHEDULE_HOUR,
            'nb_hours' => $af->nb_hours,
            'nb_days' => $af->nb_days,
            'nb_pratical_hours' => $af->nb_pratical_hours,
            'nb_pratical_days' => $af->nb_pratical_days,
            'max_nb_trainees' => $af->max_nb_trainees,
            'training_site' => $training_site,
            'address_training_location' => $address_training_location,
            'mainFormer' => $mainFormer,
            'session_type' => $session_type
        ];
    }

    public function createSessionFromPf($arrayAfData)
    {
        $success = true;
        $af_id = $arrayAfData['af_id'];
        $nbTotalDaysToProgram = $arrayAfData['nb_total_dates_to_program'];
        $nbdaysToProgram = $arrayAfData['nb_dates_to_program'];
        $session_type = $arrayAfData['session_type'];
        $started_at = $arrayAfData['started_at'];
        $ended_at = null;
        $datesToProgram = [];
        if ($nbdaysToProgram > 0) {
            if (isset($started_at) && !empty($started_at)) {
                $datesToProgram = $this->getDatesToProgram($started_at, (int)$nbdaysToProgram, $session_type);
                if (collect($datesToProgram)->count() > 0) {
                    $dt_end = collect($datesToProgram)->last();
                    if ($dt_end) {
                        $ended_at = Carbon::createFromFormat('Y-m-d', $dt_end);
                    }
                }
            }
        }
        $dataSession = array(
            'id' => 0,
            'code' => $this->generateSessionCode($af_id, 0),
            'title' => $arrayAfData['title'],
            'description' => $arrayAfData['description'],
            'nb_days' => $arrayAfData['nb_days'],
            'nb_hours' => $arrayAfData['nb_hours'],
            'is_uknown_date' => $arrayAfData['is_uknown_date'],
            'nb_dates_to_program' => $nbdaysToProgram,
            'nb_total_dates_to_program' => $nbTotalDaysToProgram,
            'max_nb_trainees' => $arrayAfData['max_nb_trainees'],
            'session_type' => $session_type,
            'state' => 'AF_STATES_OPEN',
            'training_site' => $arrayAfData['training_site'],
            'other_training_site' => $arrayAfData['other_training_site'],
            'is_active' => $arrayAfData['is_active'],
            'is_main_session' => 1,
            'started_at' => $started_at,
            'ended_at' => $ended_at,
            'planning_template_id' => $arrayAfData['planning_template_id'],
            'af_id' => $af_id,
        );
        $session_id = $this->manageSession($dataSession);
        if ($session_id > 0 && isset($started_at)) {
            $this->sessionPlanningProcess($session_id);
        }
        return $success;
    }
    public function getParamPFormation($pf_id, $param_code_needed)
    {
        $name = '';
        if ($pf_id > 0) {
            $pf = Formation::find($pf_id);
            $formationParams = ($pf) ? $pf->params->pluck('code', 'param_code') : [];
            if (count($formationParams) > 0) {
                foreach ($formationParams as $param_code => $name) {
                    if ($param_code == $param_code_needed) {
                        return $name;
                    }
                }
            }
        }
        return $name;
    }
    public function filter_ids_product($pf_id)
    {
        $ids = [];
        $dataFormation = Formation::select('id', 'parent_id')->get()->toArray();
        if (count($dataFormation) > 0) {
            $list = $this->fetch_recursive($dataFormation, $pf_id);
            if (count($list) > 0) {
                foreach ($list as $l) {
                    $ids[] = $l['id'];
                }
            }
        }
        return $ids;
    }
    public function fetch_recursive($src_arr, $currentid, $parentfound = false, $cats = array())
    {
        foreach ($src_arr as $row) {
            if ((!$parentfound && $row['id'] == $currentid) || $row['parent_id'] == $currentid) {
                $rowdata = array();
                foreach ($row as $k => $v)
                    $rowdata[$k] = $v;
                $cats[] = $rowdata;
                if ($row['parent_id'] == $currentid)
                    $cats = array_merge($cats, $this->fetch_recursive($src_arr, $row['id'], true));
            }
        }
        return $cats;
    }

    // public function checkIfRessourceAvailableToSchedule($ressource_id, $schedule_id)
    // {
    //     $is_available = true;
    //     $arrayMessages = [];
    //     $message = '';
    //     $toschedule = false;
    //     if ($ressource_id > 0 && $schedule_id > 0) {
    //         $ressourcename = Ressource::select('name')->where('id', $ressource_id)->pluck('name')->first();
    //         $schedule = Schedule::find($schedule_id);
    //         if ($schedule) {
    //             $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
    //             $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
    //             //echo $ressource_id.' --';
    //             $session_date = $schedule->sessiondate->planning_date;
    //             $rsScheduleressources = DB::table('af_scheduleressources AS SRE')
    //                 ->leftJoin('af_schedules AS SCH', 'SCH.id', '=', 'SRE.schedule_id')
    //                 ->leftJoin('af_sessiondates AS SDA', 'SDA.id', '=', 'SCH.sessiondate_id')
    //                 ->leftJoin('af_sessions AS SES', 'SES.id', '=', 'SDA.session_id')
    //                 ->leftJoin('af_actions AS AF', 'AF.id', '=', 'SES.af_id')
    //                 ->leftJoin('res_ressources AS RES', 'RES.id', '=', 'SRE.ressource_id')
    //                 ->where('SRE.ressource_id', $ressource_id)    //res																
    //                 ->whereDate('SDA.planning_date', '=', $session_date)
    //                 ->whereRaw("( SCH.start_hour <= '" . $start_hour . "' AND SCH.end_hour>= '" . $start_hour . "' ) OR ( SCH.start_hour <= '" . $end_hour . "' AND SCH.end_hour >= '" . $end_hour . "' )")
    //                 ->where('RES.name', 'LIKE', $ressourcename)->get();

    //             $rsScheduleressources1 = DB::table('af_scheduleressources AS SRE')
    //                 ->leftJoin('af_schedules AS SCH', 'SCH.id', '=', 'SRE.schedule_id')
    //                 ->leftJoin('af_sessiondates AS SDA', 'SDA.id', '=', 'SCH.sessiondate_id')
    //                 ->leftJoin('af_sessions AS SES', 'SES.id', '=', 'SDA.session_id')
    //                 ->leftJoin('af_actions AS AF', 'AF.id', '=', 'SES.af_id')
    //                 ->leftJoin('res_ressources AS RES', 'RES.id', '=', 'SRE.ressource_id')
    //                 ->where('SRE.ressource_id', $ressource_id)    //res																
    //                 ->whereDate('SDA.planning_date', '=', $session_date)
    //                 ->where(function ($query) use ($start_hour, $end_hour) {
    //                     $query->whereBetween('SCH.start_hour', [$start_hour, $end_hour])
    //                         ->orWhereBetween('SCH.end_hour', [$start_hour, $end_hour]);
    //                 })->where('RES.name', 'LIKE', $ressourcename)->get();

    //             if (count($rsScheduleressources) > 0 || count($rsScheduleressources1) > 0) {
    //                 $toschedule = false;
    //                 $is_available = false;
    //                 $merged = $rsScheduleressources->merge($rsScheduleressources1);
    //                 $newmerged = $merged->unique();
    //                 foreach ($newmerged as $sr) {
    //                     $arrayMessages[] = [
    //                         'ressource_id' => $sr->ressource_id,
    //                         'session_id' => $sr->session_id,
    //                         'sessiondate_id' => $sr->sessiondate_id,
    //                         'schedule_id' => $sr->schedule_id
    //                     ];
    //                 }
    //             } else {
    //                 $toschedule = true;
    //             }
    //         }
    //     }
    //     return ['is_available' => $is_available, 'messages' => $arrayMessages, 'toschedule' => $toschedule];
    // }

    // public function checkIfRessourceAvailableToSchedule($ressource_id, $schedule_id)
    // {
    //     $is_available = true;
    //     $arrayMessages = [];
    //     $message = '';
    //     $toschedule = false;
        
    //     if ($ressource_id > 0 && $schedule_id > 0) {
    //         $ressourcename = Ressource::select('name')->where('id', $ressource_id)->pluck('name')->first();
    //         $schedule = Schedule::find($schedule_id);
            
    //         if ($schedule) {
    //             $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
    //             $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
    //             $session_date = $schedule->sessiondate->planning_date;
                
    //             $rsScheduleressources = DB::table('af_scheduleressources AS SRE')
    //                 ->leftJoin('af_schedules AS SCH', 'SCH.id', '=', 'SRE.schedule_id')
    //                 ->leftJoin('af_sessiondates AS SDA', 'SDA.id', '=', 'SCH.sessiondate_id')
    //                 ->leftJoin('af_sessions AS SES', 'SES.id', '=', 'SDA.session_id')
    //                 ->leftJoin('af_actions AS AF', 'AF.id', '=', 'SES.af_id')
    //                 ->leftJoin('res_ressources AS RES', 'RES.id', '=', 'SRE.ressource_id')
    //                 ->where('SRE.ressource_id', $ressource_id)
    //                 ->whereDate('SDA.planning_date', '=', $session_date)
    //                 ->where(function ($query) use ($start_hour, $end_hour) {
    //                     $query->whereBetween('SCH.start_hour', [$start_hour, $end_hour])
    //                         ->orWhereBetween('SCH.end_hour', [$start_hour, $end_hour]);
    //                 })->where('RES.name', 'LIKE', $ressourcename)->get();

    //             if (count($rsScheduleressources) > 0) {
    //                 foreach ($rsScheduleressources as $scheduleressource) {
    //                     $is_internal = ($scheduleressource && $scheduleressource->type == "RES_TYPE_LIEU") ? $scheduleressource->is_internal : null;
    //                     if ($is_internal == 0) {
    //                         $toschedule = true;
    //                     } else {
    //                         $is_available = false;
    //                         $arrayMessages[] = [
    //                             'ressource_id' => $scheduleressource->ressource_id,
    //                             'session_id' => $scheduleressource->session_id,
    //                             'sessiondate_id' => $scheduleressource->sessiondate_id,
    //                             'schedule_id' => $scheduleressource->schedule_id
    //                         ];
    //                     }
    //                 }
    //             } else {
    //                 $toschedule = true;
    //             }
    //         }
    //     }
        
    //     return ['is_available' => $is_available, 'messages' => $arrayMessages, 'toschedule' => $toschedule];
    // }

    private function isReserved($ressource_id, $session_date, $start_hour, $end_hour)
    {
        $parent = Ressource::find($ressource_id);
        if ($parent->parent_ressource) {
            $father_id = $parent->parent_ressource->id;
            $reserved = Schedule::leftJoin('af_sessiondates', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
                ->leftJoin('af_scheduleressources', 'af_schedules.id', '=', 'af_scheduleressources.schedule_id')
                ->where('af_scheduleressources.ressource_id', $father_id)
                ->whereDate('af_sessiondates.planning_date', '=', $session_date)
                ->where(function ($query) use ($start_hour, $end_hour) {
                    $query->whereBetween('af_schedules.start_hour', [$start_hour, $end_hour])
                        ->orWhereBetween('af_schedules.end_hour', [$start_hour, $end_hour]);
                })
                ->count();
                
            if ($reserved) {
                return true;
            }
        }
        return false;
    }

    private function isReserved1($ressource_id, $session_date, $start_hour, $end_hour)
    {
        $resource = Ressource::find($ressource_id);
    
        // Check if any children are reserved
        foreach ($resource->children as $child) {
            if ($this->isReserved1($child->id, $session_date, $start_hour, $end_hour)) {
                return true;
            }
        }
    
        // Check if the resource is reserved
        $reserved = Schedule::leftJoin('af_sessiondates', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
            ->leftJoin('af_scheduleressources', 'af_schedules.id', '=', 'af_scheduleressources.schedule_id')
            ->where('af_scheduleressources.ressource_id', $resource->id)
            ->whereDate('af_sessiondates.planning_date', '=', $session_date)
            ->where(function ($query) use ($start_hour, $end_hour) {
                $query->whereBetween('af_schedules.start_hour', [$start_hour, $end_hour])
                    ->orWhereBetween('af_schedules.end_hour', [$start_hour, $end_hour]);
            })
            ->count();
    
        return ($reserved > 0);
    }
    



    public function checkIfRessourceAvailableToSchedule($ressource_id, $schedule_id)
    {
        $is_available = true;
        $arrayMessages = [];
        $message = '';
        $toschedule = false;
        
        if ($ressource_id > 0 && $schedule_id > 0) {
            $ressourcename = Ressource::select('name')->where('id', $ressource_id)->pluck('name')->first();
            $schedule = Schedule::find($schedule_id);
            
            if ($schedule) {
                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                $session_date = $schedule->sessiondate->planning_date;
                
                if ($this->isReserved($ressource_id, $session_date, $start_hour, $end_hour)) {
                    $is_available = false;
                    // $arrayMessages[] = [
                    //     'ressource_id' => $ressource_id,
                    //     'session_id' => null,
                    //     'sessiondate_id' => null,
                    //     'schedule_id' => $schedule_id
                    // ];

                    $rsScheduleressources = DB::table('af_scheduleressources AS SRE')
                        ->leftJoin('af_schedules AS SCH', 'SCH.id', '=', 'SRE.schedule_id')
                        ->leftJoin('af_sessiondates AS SDA', 'SDA.id', '=', 'SCH.sessiondate_id')
                        ->leftJoin('af_sessions AS SES', 'SES.id', '=', 'SDA.session_id')
                        ->leftJoin('af_actions AS AF', 'AF.id', '=', 'SES.af_id')
                        ->leftJoin('res_ressources AS RES', 'RES.id', '=', 'SRE.ressource_id')
                        ->where('SRE.ressource_id', $ressource_id)
                        ->whereDate('SDA.planning_date', '=', $session_date)
                        ->where(function ($query) use ($start_hour, $end_hour) {
                            $query->whereBetween('SCH.start_hour', [$start_hour, $end_hour])
                                ->orWhereBetween('SCH.end_hour', [$start_hour, $end_hour]);
                        })->where('RES.name', 'LIKE', $ressourcename)->get();

                    if (count($rsScheduleressources) > 0) {
                        foreach ($rsScheduleressources as $scheduleressource) {
                            $arrayMessages[] = [
                                'ressource_id' => $scheduleressource->ressource_id,
                                'session_id' => $scheduleressource->session_id,
                                'sessiondate_id' => $scheduleressource->sessiondate_id,
                                'schedule_id' => $scheduleressource->schedule_id
                            ];
                        }
                    }        
                }else if ($this->isReserved1($ressource_id, $session_date, $start_hour, $end_hour)) {
                    $is_available = false;
                    // $arrayMessages[] = [
                    //     'ressource_id' => $ressource_id,
                    //     'session_id' => null,
                    //     'sessiondate_id' => null,
                    //     'schedule_id' => $schedule_id
                    // ];

                    $rsScheduleressources = DB::table('af_scheduleressources AS SRE')
                        ->leftJoin('af_schedules AS SCH', 'SCH.id', '=', 'SRE.schedule_id')
                        ->leftJoin('af_sessiondates AS SDA', 'SDA.id', '=', 'SCH.sessiondate_id')
                        ->leftJoin('af_sessions AS SES', 'SES.id', '=', 'SDA.session_id')
                        ->leftJoin('af_actions AS AF', 'AF.id', '=', 'SES.af_id')
                        ->leftJoin('res_ressources AS RES', 'RES.id', '=', 'SRE.ressource_id')
                        ->where('SRE.ressource_id', $ressource_id)
                        ->whereDate('SDA.planning_date', '=', $session_date)
                        ->where(function ($query) use ($start_hour, $end_hour) {
                            $query->whereBetween('SCH.start_hour', [$start_hour, $end_hour])
                                ->orWhereBetween('SCH.end_hour', [$start_hour, $end_hour]);
                        })->where('RES.name', 'LIKE', $ressourcename)->get();

                    if (count($rsScheduleressources) > 0) {
                        foreach ($rsScheduleressources as $scheduleressource) {
                            $arrayMessages[] = [
                                'ressource_id' => $scheduleressource->ressource_id,
                                'session_id' => $scheduleressource->session_id,
                                'sessiondate_id' => $scheduleressource->sessiondate_id,
                                'schedule_id' => $scheduleressource->schedule_id
                            ];
                        }
                    }        
                }else{
                    $rsScheduleressources = DB::table('af_scheduleressources AS SRE')
                        ->leftJoin('af_schedules AS SCH', 'SCH.id', '=', 'SRE.schedule_id')
                        ->leftJoin('af_sessiondates AS SDA', 'SDA.id', '=', 'SCH.sessiondate_id')
                        ->leftJoin('af_sessions AS SES', 'SES.id', '=', 'SDA.session_id')
                        ->leftJoin('af_actions AS AF', 'AF.id', '=', 'SES.af_id')
                        ->leftJoin('res_ressources AS RES', 'RES.id', '=', 'SRE.ressource_id')
                        ->where('SRE.ressource_id', $ressource_id)
                        ->whereDate('SDA.planning_date', '=', $session_date)
                        ->where(function ($query) use ($start_hour, $end_hour) {
                            $query->whereBetween('SCH.start_hour', [$start_hour, $end_hour])
                                ->orWhereBetween('SCH.end_hour', [$start_hour, $end_hour]);
                        })->where('RES.name', 'LIKE', $ressourcename)->get();

                    if (count($rsScheduleressources) > 0) {
                        foreach ($rsScheduleressources as $scheduleressource) {
                            $is_internal = ($scheduleressource && $scheduleressource->type == "RES_TYPE_LIEU") ? $scheduleressource->is_internal : null;
                            if ($is_internal == 0) {
                                $toschedule = true;
                            } else {
                                $is_available = false;
                                $arrayMessages[] = [
                                    'ressource_id' => $scheduleressource->ressource_id,
                                    'session_id' => $scheduleressource->session_id,
                                    'sessiondate_id' => $scheduleressource->sessiondate_id,
                                    'schedule_id' => $scheduleressource->schedule_id
                                ];
                            }
                        }
                    } else {
                        $toschedule = true;
                    }
                } 
            }
        }
        
        return ['is_available' => $is_available, 'messages' => $arrayMessages, 'toschedule' => $toschedule];
    }

    public function checkIfRessourceAvailableToSchedule_2($ressource_id, $schedule_id)
    {
        $is_available = true;
        $arrayMessages = [];
        $schedule = Schedule::find($schedule_id);
        $planning_date = null;
        $start_hour = null;
        $end_hour = null;
        if ($schedule) {
            $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
            $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
            $planning_date = $schedule->sessiondate->planning_date;
        }
        // $schedules=Scheduleressource::select('schedule_id')->where('ressource_id',$ressource_id)->pluck('schedule_id');
        $schedules = Scheduleressource::select('Scheduleressource.id')
            ->join('af_schedules', 'af_schedules.id', '=', 'af_scheduleressources.schedule_id')
            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            ->where('ressource_id', $ressource_id)
            ->where('af_sessiondates.planning_date', $planning_date)
            ->where([['af_schedules.start_hour', $start_hour], ['af_schedules.end_hour', $end_hour]])->count();
        // ->pluck('scheduleressources_id');
        //pluck('schedule_id');
        // $srcMaxDateDisponibility=Scheduleressource::select('schedule_id')->where('ressource_id',$ressource_id);
        // $srcMaxDateDisponibility = $schedules->first();
        // $srcMinDateDisponibility = $schedules->last();
        if ($schedules) {
            $is_available = false;
            $rsScheduleressources = Scheduleressource::select('id', 'schedule_id')->where('schedule_id', $schedule_id)->get();
            if (count($rsScheduleressources) > 0) {
                $can_be_scheduled = false;
                foreach ($rsScheduleressources as $sr) {
                    $arrayMessages[] = [
                        'ressource_id' => $ressource_id,
                        'session_id' => $sr->schedule->sessiondate->session->id,
                        'sessiondate_id' => $sr->schedule->sessiondate->id,
                        'schedule_id' => $sr->schedule_id
                    ];
                }
            }
        }
        // dd($schedules);
        return ['is_available' => $is_available, 'messages' => $arrayMessages];
    }

    public function checkRessourceParentScheduled($ressource_id, $schedule_id)
    {
        $can_be_scheduled = true;
        $arrayMessages = [];
        if ($ressource_id > 0 && $schedule_id > 0) {
            // $rsRessource=Ressource::select('id','ressource_id')->where('id',$ressource_id)->first();
            // dd($rsRessource);
            // $parent_id=$rsRessource->ressource_id;
            $parent_id = $ressource_id;
            if ($parent_id > 0) {
                //echo 'R:'.$parent_id.'-----S:'.$schedule_id.'----';
                $schedule = Schedule::find($schedule_id);
                if ($schedule) {
                    $rsScheduleressources = Scheduleressource::where('schedule_id', $schedule_id)->where('ressource_id', $parent_id)->get();
                    if (count($rsScheduleressources) > 0) {
                        foreach ($rsScheduleressources as $sr) {
                            // if($sr->ressource->type=="RES_TYPE_LIEU" && $sr->ressource->is_internal != 1){
                            //     $can_be_scheduled = true;
                            // }else{
                            //     $can_be_scheduled = false;*
                            // }
                            $can_be_scheduled = false;
                            $arrayMessages[] = [
                                'ressource_id' => $ressource_id,
                                'session_id' => $sr->schedule->sessiondate->session->id,
                                'sessiondate_id' => $sr->schedule->sessiondate->id,
                                'schedule_id' => $sr->schedule_id,
                                'type' => $sr->ressource->type,
                                'is_internal' => $sr->ressource->is_internal
                            ];
                        }
                    }
                }
                //$arrayMessages=$rsCheck['messages'];
            }
        }
        // dd($arrayMessages);
        return ['can_be_scheduled' => $can_be_scheduled, 'messages' => $arrayMessages];
    }
    
    public function generateMessagesControlRessourcesParent($messages)
    {
        $html = '';
        if (count($messages) > 0) {
            foreach ($messages as $ressource_id => $dt) {
                //echo $ressource_id.'----';
                foreach ($dt as $d) {
                    // dd($datas);
                    //pas possible d'affecté la ressource 101B si 101 est déjà réservé pour
                    $ressource = Ressource::select('name', 'ressource_id')->where('id', $ressource_id)->first();
                    // dd($ressource);
                    $html .= '<p class="text-danger"><strong>Pas possible d\'affecté la ressource car "' . $ressource->name . '" est déjà réservé pour :<strong></p>';
                    $html .= '<ul>';
                    // foreach($datas as $d){
                    // dd($d);
                    $rs_session = Session::select('code', 'title')->where('id', $d['session_id'])->first();
                    $rs_sessiondate = Sessiondate::select('planning_date')->where('id', $d['sessiondate_id'])->first();
                    $rs_schedule = Schedule::select('start_hour', 'end_hour')->where('id', $d['schedule_id'])->first();
                    $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_schedule->start_hour);
                    $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_schedule->end_hour);
                    $planning_date = Carbon::createFromFormat('Y-m-d', $rs_sessiondate->planning_date);
                    $html .= '<li><span class="text-info">' . $rs_session->code . '</span>';
                    $html .= '<ul>';
                    $html .= '<li><span class="text-primary">' . $planning_date->format('d-m-Y') . '</span>';
                    $html .= '<ul><li>' . $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i') . '</li></ul>';
                    $html .= '</li>';
                    $html .= '</ul>';
                    // }
                    $html .= '</ul>';
                }
            }
        }
        return $html;
    }
    public function generateMessagesControlRessources($messages)
    {       
        $html = '';
        if (count($messages) > 0) {
            foreach ($messages as $ressource_id => $dt) {
                //echo $ressource_id.'----';
                foreach ($dt as $datas) {
                    $ressource = Ressource::select('name','ressource_id')->where('id', $ressource_id)->first();
                    $html .= '<p class="text-danger"><strong>La ressource "' . $ressource->name . '" est déjà utilisé pour :<strong></p>';
                    $html .= '<ul>';
                    foreach ($datas as $d) {
                        $rs_session = Session::select('code', 'title', 'af_id')->where('id', $d['session_id'])->first();
                        $action = Action::select('code')->where('id', $rs_session->af_id)->first();
                        $rs_sessiondate = Sessiondate::select('planning_date')->where('id', $d['sessiondate_id'])->first();
                        $rs_schedule = Schedule::select('start_hour', 'end_hour')->where('id', $d['schedule_id'])->first();
                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_schedule->start_hour);
                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_schedule->end_hour);
                        $planning_date = Carbon::createFromFormat('Y-m-d', $rs_sessiondate->planning_date);
                        $html .= '<li><span class="text-info">' . $action->code . ' / ' . $rs_session->code . '</span>';
                        $html .= '<ul>';
                        $html .= '<li><span class="text-primary">' . $planning_date->format('d-m-Y') . '</span>';
                        $html .= '<ul><li>' . $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i') . '</li></ul>';
                        $html .= '</li>';
                        $html .= '</ul>';
                    }
                    $html .= '</ul>';
                }
            }
        }
        return $html;
    }
    public function checkRessourcesChildsParentScheduled($ressource_id, $schedule_id)
    {
        $can_be_scheduled = true;
        if ($ressource_id > 0 && $schedule_id > 0) {
            //echo $ressource_id;
            $childs_ressources_ids = Ressource::select('id')->where('ressource_id', $ressource_id)->pluck('id');
            //dd($childs_ressources_ids);
            if (count($childs_ressources_ids) > 0) {
                $schedule = Schedule::find($schedule_id);
                if ($schedule) {
                    $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                    $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                    $session_date = $schedule->sessiondate->planning_date;
                    if ($session_date) {
                        $ids_sessiondates = Sessiondate::select('id')->where([['planning_date', $session_date], ['id', '!=', $schedule->sessiondate->id]])->pluck('id');
                        //dd($ids_sessiondates);
                        if (count($ids_sessiondates) > 0) {
                            $ids_schedules = Schedule::select('id')->whereIn('sessiondate_id', $ids_sessiondates)->where([['start_hour', $start_hour], ['end_hour', $end_hour]])->pluck('id');
                            if (count($ids_schedules) > 0) {
                                //af_scheduleressources
                                $count = Scheduleressource::select('id')->whereIn('schedule_id', $ids_schedules)->whereIn('ressource_id', $childs_ressources_ids)->count();
                                //dd($count);
                                if ($count > 0) {
                                    $can_be_scheduled = false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $can_be_scheduled;
    }
    public function manageGroupment($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Groupment();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Groupment::find($row_id);
                if (!$row) {
                    $row = new Groupment();
                }
            }
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->ref_contact_id = (isset($data['ref_contact_id'])) ? $data['ref_contact_id'] : null;
            $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }
    public function manageGroupmentGroup($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Groupmentgroup();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Groupmentgroup::find($row_id);
                if (!$row) {
                    $row = new Groupmentgroup();
                }
            }
            $row->groupment_id = (isset($data['groupment_id'])) ? $data['groupment_id'] : null;
            $row->group_id = (isset($data['group_id'])) ? $data['group_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }
    public function getIdsContactsFormersByIdsSessions($sessions_ids)
    {
        $ids_contacts_formers = [];
        if (count($sessions_ids) > 0) {
            $ids_sessiondates = Sessiondate::select('id')->whereIn('session_id', $sessions_ids)->pluck('id');
            if (count($ids_sessiondates) > 0) {
                $ids_schedules = Schedule::select('id')->whereIn('sessiondate_id', $ids_sessiondates)->pluck('id');
                if (count($ids_schedules) > 0) {
                    $rs = Schedulecontact::select('member_id')->whereIn('schedule_id', $ids_schedules)->where('is_former', 1)->get();
                    if (count($rs) > 0) {
                        foreach ($rs as $s) {
                            if ($s->member->contact_id > 0) {
                                $ids_contacts_formers[] = $s->member->contact_id;
                            }
                        }
                    }
                }
            }
        }
        return $ids_contacts_formers;
    }
    public function getContactFormersBySession($session_id)
    {
        $ids_contacts = [];
        if ($session_id > 0) {
            $ids_sessiondates = Sessiondate::select('id')->where('session_id', $session_id)->pluck('id');
            if (count($ids_sessiondates) > 0) {
                $ids_schedules = Schedule::select('id')->whereIn('sessiondate_id', $ids_sessiondates)->pluck('id');
                if (count($ids_schedules) > 0) {
                    $rs = Schedulecontact::select('member_id')->whereIn('schedule_id', $ids_schedules)->where('is_former', 1)->get();
                    if (count($rs) > 0) {
                        foreach ($rs as $s) {
                            if ($s->member->contact_id > 0) {
                                $ids_contacts[] = $s->member->contact_id;
                            }
                        }
                    }
                }
            }
        }
        return $ids_contacts;
    }
    public function getPeriodDates($start_date, $nb_days)
    {
        $dates = [];

        if ($nb_days > 0) {
            $period = CarbonPeriod::create($start_date, '1 day', (int)$nb_days);
            //$period=CarbonPeriod::create('now', '1 day', (int)$nb_days);
            foreach ($period as $key => $date) {
                //$dates[]=$date->format('d-m-Y');
                $dates[] = $date->format('Y-m-d');
            }
        }

        return $dates;
    }
    public function getSessionDateInfos($sessiondate_id, $ids_schedules)
    {
        $cssClass = ['success', 'danger'];
        $html = '';

        $userid = auth()->user()->id;
        $roles = auth()->user()->roles;

        $dNow = Carbon::now();
        $datenow = $dNow->format('Y-m-d H:i:s');

        $idsparams = Param::select('id', 'name')->where([['param_code', 'DELAI_DIFFUSION_AGENDA'], ['is_active', 1]])->first();
        $newdate = date('Y-m-d H:i:s', strtotime($datenow . ' + ' . $idsparams->name . ' days'));

        if ($sessiondate_id > 0) {
            $sessiondate = Sessiondate::find($sessiondate_id);
            //dd($sessiondate);
            if (isset($sessiondate)) {
                $afName = '<p class="mb-0"><strong>AF : </strong>' . $sessiondate->session->af->title . '</p>';
                $sessionName = '<p class="mb-0"><strong>Session :</strong> ' . $sessiondate->session->title . ' (' . $sessiondate->session->code . ')</p>';
                //60 days
                if ($roles[0]->code == 'APPRENANT') {
                    $schedules = Schedule::select('id', 'start_hour', 'end_hour')->where('sessiondate_id', $sessiondate_id)->whereIn('id', $ids_schedules)->where('start_hour', '<', $newdate)->orderBy('start_hour', 'asc')->get();
                } else {
                    $schedules = Schedule::select('id', 'start_hour', 'end_hour')->where('sessiondate_id', $sessiondate_id)->whereIn('id', $ids_schedules)->orderBy('start_hour', 'asc')->get();
                }

                if (count($schedules) > 0) {
                    foreach ($schedules as $schedule) {

                        $formersArray = $this->getFormersInSchedule($schedule->id);
                        $formers = implode(',', $formersArray);
                        $pFormers = (isset($formers) && !empty($formers)) ? ('<p class="mb-0"><strong>Formateur(s): </strong>' . $formers . '</p>') : '';

                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                        $pSeance = '<p class="mb-0"><strong>Séance : </strong><mark>' . $start_hour->format('H:i') . ' - ' . $end_hour->format('H:i') . '</mark></p>';

                        //Stagiaires
                        $nbStagiaires = Schedulecontact::select('member_id')->where('schedule_id', $schedule->id)->where('is_former', 0)->count();
                        $pStagiaires = '<p class="mb-0"><strong>Stagiaires: </strong> ' . $nbStagiaires;

                        $html .= '<div class="alert alert-custom alert-light-primary mb-1" role="alert" style="padding: 0.5rem;">
                        <div class="alert-text">' . $afName . $sessionName . $pSeance . $pFormers . $pStagiaires . '</div>
                        </div>';
                    }
                }
            }
        }
        return $html;
    }
    public function generateHtmlPdfAttendanceAbsenceSheet($af_id, $render_type, $group_id, $start_date = '', $end_date = '', $session_id = '', $member_id = '', $training_site = '')
    {
        //phpinfo();exit();
        $htmlHeader = $htmlMain = $htmlFooter = '';
        if ($af_id > 0) {
            $af = Action::select('id', 'device_type', 'title', 'code', 'training_site')->where('id', $af_id)->first();
            $type_af = $af->device_type;
            $document_type = ($type_af == 'INTRA') ? 'ATTENDANCE_ABSENCE_SHEET_INTRA' : 'ATTENDANCE_ABSENCE_SHEET_INTER';
            $afInfos = $this->getAfInformations($af->id);
            if ($session_id > 0) {
                $sessions_ids[] = $session_id;
            } elseif (isset($training_site) && !empty($training_site)) {
                //$sessions=Session::select('id')->where([['af_id',$af->id],['training_site',$training_site]])->get();
                $sessions_ids = Session::select('id')->where([['af_id', $af->id], ['training_site', $training_site]])->pluck('id');
            } else {
                //$sessions=Session::select('id')->where('af_id',$af->id)->get();
                $sessions_ids = Session::select('id')->where('af_id', $af->id)->pluck('id');
            }

            $members = null;
            $pFormers = '';
            $globalIdsMembers = $newIdsMembers = $arrayFormers = $arraySessionPlanning = [];
            $listEntities = null;
            $entities = $idsEntities = $idsParticipantMembers = [];
            //Récupérer les membres
            if (count($sessions_ids) > 0) {
                $ids_sessiondates = Sessiondate::select('id')->whereIn('session_id', $sessions_ids)->pluck('id');
                if (count($ids_sessiondates) > 0) {
                    $ids_schedules = Schedule::whereIn('sessiondate_id', $ids_sessiondates)->pluck('id');
                    if (count($ids_schedules) > 0) {

                        if ($group_id > 0) {
                            $globalIdsMembers = Schedulecontact::select('member_id')
                                ->whereIn('schedule_id', $ids_schedules)
                                ->where('is_former', 0)
                                ->whereIn('member_id', function ($query) use ($group_id) {
                                    $query->select('id')
                                        ->from(with(new Member)->getTable())
                                        ->where('group_id', $group_id);
                                })
                                ->pluck('member_id')->unique();
                        } else {
                            $globalIdsMembers = Schedulecontact::select('member_id')
                                ->whereIn('schedule_id', $ids_schedules)
                                ->where('is_former', 0)
                                ->pluck('member_id')->unique();
                        }
                    }
                }
            }
            //Les formateurs
            $ids_contacts_formers = $this->getIdsContactsFormersByIdsSessions($sessions_ids);
            //dd($ids_contacts_formers);
            if (count($ids_contacts_formers) > 0) {
                $contacts = Contact::select('firstname', 'lastname')->whereIn('id', $ids_contacts_formers)->get();
                foreach ($contacts as $i => $c) {
                    $arrayFormers[] = $c->firstname . ' ' . $c->lastname;
                }
                $collection = collect($arrayFormers)->unique();
                $pFormers = implode(',', $collection->toArray());
            }

            //
            if ($member_id > 0) {
                //    $members=Member::where('id',$member_id)->get();
                $globalIdsMembers = [];
                $globalIdsMembers[] = $member_id;
            }
            if (count($globalIdsMembers) > 0) {
                foreach ($globalIdsMembers as $member_id) {
                    $arr = $this->getMemberSessionPlanningsByMember($sessions_ids, $member_id, $start_date, $end_date);
                    if (count($arr) > 0) {
                        $newIdsMembers[] = $member_id;
                        $arraySessionPlanning[$member_id][] = $arr;
                    }
                }
                if (count($newIdsMembers) > 0) {
                    $members = Member::whereIn('id', $newIdsMembers)->get();
                }
            }
            //dd($globalIdsMembers);
            //dd($arraySessionPlanning);
            //dd(count($members));
            //dd(count($globalIdsMembers));
            //dd($pFormers);

            $intraArrayDatas = $intra_ids_entities = [];
            if ($type_af == 'INTRA') {

                $intra_ids_entities = DB::table('af_enrollments')
                    ->select('af_enrollments.entitie_id', 'af_enrollments.enrollment_type', 'af_enrollments.af_id')
                    ->where([['af_enrollments.af_id', $af_id], ['af_enrollments.enrollment_type', 'S']])
                    ->pluck('af_enrollments.entitie_id')->unique();

                if (count($intra_ids_entities) > 0) {
                    foreach ($intra_ids_entities as $entity_id) {
                        $en = Entitie::select('name')->find($entity_id);
                        $intraArrayDatas[$entity_id]['name'] = $en->name;

                        $intra_ids_members = DB::table('af_members')
                            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                            ->select('af_members.id', 'af_members.contact_id', 'af_enrollments.enrollment_type', 'af_enrollments.af_id')
                            ->where([['af_enrollments.entitie_id', $entity_id], ['af_enrollments.af_id', $af_id], ['af_enrollments.enrollment_type', 'S']])
                            ->pluck('af_members.id')->unique();

                        //sessions
                        $intra_ids_sessions = DB::table('af_schedulecontacts')
                            ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                            ->select('af_schedulecontacts.id', 'af_sessions.id')
                            ->where('af_schedulecontacts.is_former', 0)
                            ->whereIn('af_schedulecontacts.member_id', $intra_ids_members)
                            ->pluck('af_sessions.id')->unique();
                        if (count($intra_ids_sessions) > 0) {
                            foreach ($intra_ids_sessions as $ss_id) {
                                $ss = Session::select('code', 'title')->find($ss_id);
                                $intraArrayDatas[$entity_id]['sessions'][$ss_id]['code'] = $ss->code;
                                $intraArrayDatas[$entity_id]['sessions'][$ss_id]['title'] = $ss->title;
                                //planning dates
                                $intra_ids_sessiondates = DB::table('af_schedulecontacts')
                                    ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                                    ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                                    //->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                                    ->select('af_schedulecontacts.id', 'af_sessiondates.id', 'af_sessiondates.planning_date')
                                    ->where([['af_schedulecontacts.is_former', 0], ['af_sessiondates.session_id', $ss_id]])
                                    ->whereIn('af_schedulecontacts.member_id', $intra_ids_members)
                                    ->pluck('af_sessions.id')->unique();
                                if (count($intra_ids_sessiondates) > 0) {
                                    foreach ($intra_ids_sessiondates as $sd_id) {
                                        $sessiondate = Sessiondate::select('planning_date')->find($sd_id);
                                        $carbon_planning_date = Carbon::createFromFormat('Y-m-d', $sessiondate->planning_date);
                                        $intraArrayDatas[$entity_id]['sessions'][$ss_id]['dates'][$sd_id]['planning_date'] = $carbon_planning_date->format('d/m/Y');

                                        $rs_schedules = Schedule::where('sessiondate_id', $sd_id)->get();
                                        $intra_ids_schedules = [];
                                        $schedules = [];
                                        if (count($rs_schedules) > 0) {
                                            foreach ($rs_schedules as $schedule) {
                                                $intra_ids_schedules[] = $schedule->id;
                                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                                $schedules[$schedule->type] = array(
                                                    "id" => $schedule->id,
                                                    "type" => $schedule->type,
                                                    "start_hour" => $start_hour->format('H:i'),
                                                    "end_hour" => $end_hour->format('H:i'),
                                                    "duration" => $schedule->duration,
                                                );
                                            }
                                        }
                                        $intraArrayDatas[$entity_id]['sessions'][$ss_id]['dates'][$sd_id]['schedules'] = $schedules;
                                        //les members
                                        $intra_ids_contacts_members = DB::table('af_schedulecontacts')
                                            ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                                            ->whereIn('af_schedulecontacts.schedule_id', $intra_ids_schedules)
                                            ->pluck('af_members.id')->unique();

                                        $info_member = [];
                                        if (count($intra_ids_contacts_members) > 0) {
                                            foreach ($intra_ids_contacts_members as $m_id) {
                                                $rs_member = Member::find($m_id);
                                                $member_name = ($rs_member->contact) ? ($rs_member->contact->firstname . ' ' . $rs_member->contact->lastname) : $rs_member->unknown_contact_name;
                                                $info_member[$m_id]['name'] = $member_name;
                                            }
                                        }

                                        $intraArrayDatas[$entity_id]['sessions'][$ss_id]['dates'][$sd_id]['members'] = $info_member;
                                    }
                                }
                            }
                        }
                    }
                }

                // dd($intraArrayDatas);




                /* $arraySessionPlanning=array();
                $arraySessionPlanning=$entities=$idsEntities=$idsParticipantMembers=[];
                $globalIdsMembers=$globalIdsEntities=[];
                $sessions=Session::whereIn('id',$sessions_ids)->get();
                if(count($sessions)>0){
                    foreach($sessions as $session){
                        $intraArrayDatas[$session->id]['code']=$session->code;
                        $intraArrayDatas[$session->id]['dates']=$this->getSessionPlanning($session->id,$start_date,$end_date);

                        //Les formateurs
                        $ids_contacts=$this->getContactFormersBySession($session->id);
                        if(count($ids_contacts)>0){
                            $contacts=Contact::select('firstname','lastname')->whereIn('id',$ids_contacts)->get();
                            foreach($contacts as $i=>$c){
                                $arrayFormers[]=$c->firstname.' '.$c->lastname;
                            }
                        }
                        $collectionIdsMembers=$this->getScheduledMembersBySession($session->id);
                        //dd($collectionIdsMembers);
                        if(count($collectionIdsMembers)>0){
                            $collectionIdsMembers=$collectionIdsMembers->unique();
                        }
                    }
                }
                $membersCollection = collect($globalIdsMembers)->unique();
                if($membersCollection->count()>0){
                    $members=Member::whereIn('id',$membersCollection)->get();
                }
                if(count($arrayFormers)>0){
                    $collection = collect($arrayFormers)->unique();
                    $pFormers = implode(',', $collection->toArray());
                }
                if($type_af=='INTRA' && isset($members)){
                    foreach($members as $m){
                        $entities[]=$m->enrollment->entity->id;
                        $idsParticipantMembers[$m->enrollment->entity->id][]=$m;
                    }
                    if(count($entities)>0){
                        $collectionIdsEntities = collect($entities)->unique();
                        $idsEntities=$collectionIdsEntities->toArray();
                        $listEntities=Entitie::select('id','name','ref')->whereIn('id',$idsEntities)->get();                    
                    }
                } */
            }

            //dd($arraySessionPlanning);
            //dd($intraArrayDatas);

            $PARAMS = array(
                'COMPANY_NAME' => config('global.company_name'),
                'AF_TITLE' => $af->title,
                'AF_CODE' => $af->code,
                'AF_LIEU_FORMATION' => $training_site,
                'AF_ADRESSE_LIEU_FORMATION' => $afInfos['address_training_location'],
                'FORMERS' => $pFormers,
                'STARTED_AT' => $afInfos['started_at'],
                'ENDED_AT' => $afInfos['ended_at'],
                'FIRST_SCHEDULE_HOUR' => $afInfos['FIRST_SCHEDULE_HOUR'],
                'LAST_SCHEDULE_HOUR' => $afInfos['LAST_SCHEDULE_HOUR'],
                'NB_THEO_DAYS' => $afInfos['nb_days'],
                'NB_THEO_HOURS' => $afInfos['nb_hours'],
                'NB_PRACTICAL_DAYS' => $afInfos['nb_pratical_days'],
                'NB_PRACTICAL_HOURS' => number_format($afInfos['nb_pratical_hours'], 0),
            );

            $html_table = view('pages.pdf.fiche-emargement', compact('type_af', 'members', 'arraySessionPlanning', 'listEntities', 'idsParticipantMembers', 'PARAMS', 'intraArrayDatas'))->render();
            $training_site = ($af->training_site != 'OTHER') ? $af->training_site : $af->other_training_site;

            $dm = Documentmodel::where('code', $document_type)->first();
            $content = $dm->custom_content;
            $header = $dm->custom_header;
            $footer = $dm->custom_footer;
            $dn = Carbon::now();
            $keywordHeader = array(
                '{LOGO_HEADER}',
            );
            $keywordHeaderReplace = array(
                public_path('media/logo/logo-light.png'),
            );
            $htmlHeader = str_replace($keywordHeader, $keywordHeaderReplace, $header);
            //CONTENT
            $keyword = array(
                /* "{COMPANY_NAME}",
                    "{AF_TITLE}",
                    "{AF_CODE}",
                    "{AF_LIEU_FORMATION}",
                    "{AF_ADRESSE_LIEU_FORMATION}",
                    "{FORMERS}",
                    "{STARTED_AT}",
                    "{ENDED_AT}",
                    "{FIRST_SCHEDULE_HOUR}",
                    "{LAST_SCHEDULE_HOUR}",

                    "{NB_THEO_DAYS}",
                    "{NB_THEO_HOURS}",
                    "{NB_PRACTICAL_DAYS}",
                    "{NB_PRACTICAL_HOURS}", */

                "{HTML_TABLE}",
            );
            $keyreplace = array(
                /* config('global.company_name'),
                    $af->title,
                    $af->code,
                    $training_site,
                    $afInfos['address_training_location'],
                    $pFormers,
                    $afInfos['started_at'],
                    $afInfos['ended_at'],
                    $afInfos['FIRST_SCHEDULE_HOUR'],
                    $afInfos['LAST_SCHEDULE_HOUR'],

                    $afInfos['nb_hours'],
                    $afInfos['nb_days'],
                    $afInfos['nb_hours'],
                    $afInfos['nb_days'], */

                $html_table
            );
            $htmlMain = str_replace($keyword, $keyreplace, $content);
            //Footer
            $keywordFooter = array(
                '{LOGO_FOOTER}',
                '{ADRESS_FOOTER}',
                '{PHONE_FOOTER}',
                '{FAX_FOOTER}',
                '{EMAIL_FOOTER}',
                '{WEBSITE_FOOTER}',
                '{SIRET_FOOTER}',
            );
            $keywordFooterReplace = array(
                public_path('media/logo/footer.jpg'),
                config('global.company_address'),
                config('global.company_phone'),
                config('global.company_fax'),
                config('global.company_email'),
                config('global.company_website'),
                config('global.company_siret'),
            );
            $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);
        }
        return ['htmlHeader' => $htmlHeader, 'htmlMain' => $htmlMain, 'htmlFooter' => $htmlFooter];
    }
    public function getInervenantsIdsByGroup($group_id)
    {
        $ids_sessions = [];
        if ($group_id > 0) {
            $ids_members = Member::select('af_members.id')
                ->join('af_schedulecontacts', 'af_schedulecontacts.id', 'af_members.contact_id')
                ->where('is_former', true)
                ->where('af_members.group_id', $group_id)->pluck('af_members.id')->unique();
            //dd($ids_members);
            // $ids_schedules=Schedulecontact::select('schedule_id')->whereIn('member_id',$ids_members)->pluck('schedule_id')->unique();
            // $ids_sessiondates=Schedule::select('sessiondate_id')->whereIn('id',$ids_schedules)->pluck('sessiondate_id')->unique();

            // if(isset($start) && isset($end)){
            //     $from=Carbon::createFromFormat('Y-m-d', $start);
            //     $to=Carbon::createFromFormat('Y-m-d', $end);
            //     $ids_sessions=Sessiondate::select('session_id')->whereIn('id',$ids_sessiondates)->whereBetween('planning_date', [$from, $to])->pluck('session_id')->unique();
            //     //$ids_sessiondates=Schedule::select('sessiondate_id')->whereIn('id',$ids_schedules)->whereBetween('planning_date', [$start, $end])->pluck('sessiondate_id')->unique();
            // }
            // else{
            //     $ids_sessions=Sessiondate::select('session_id')->whereIn('id',$ids_sessiondates)->pluck('session_id')->unique();
            // }

        }
        return $ids_sessions;
    }
    public function getSessionsIdsByGroup($group_id, $start = null, $end = null)
    {
        $ids_sessions = [];
        if ($group_id > 0) {
            $ids_members = Member::select('id')->where('group_id', $group_id)->pluck('id')->unique();
            //dd($ids_members);
            $ids_schedules = Schedulecontact::select('schedule_id')->whereIn('member_id', $ids_members)->pluck('schedule_id')->unique();
            $ids_sessiondates = Schedule::select('sessiondate_id')->whereIn('id', $ids_schedules)->pluck('sessiondate_id')->unique();

            if (isset($start) && isset($end)) {
                $from = Carbon::createFromFormat('Y-m-d', $start);
                $to = Carbon::createFromFormat('Y-m-d', $end);
                $ids_sessions = Sessiondate::select('session_id')->whereIn('id', $ids_sessiondates)->whereBetween('planning_date', [$from, $to])->pluck('session_id')->unique();
                //$ids_sessiondates=Schedule::select('sessiondate_id')->whereIn('id',$ids_schedules)->whereBetween('planning_date', [$start, $end])->pluck('sessiondate_id')->unique();
            } else {
                $ids_sessions = Sessiondate::select('session_id')->whereIn('id', $ids_sessiondates)->pluck('session_id')->unique();
            }
        }
        return $ids_sessions;
    }

    public function getSessionsIdsByMembre($member_id, $start = null, $end = null)
    {
        $ids_sessions = [];
        if ($member_id > 0) {
            //dd($ids_members);
            $ids_schedules = Schedulecontact::select('schedule_id')->whereIn('member_id', (array)$member_id)->pluck('schedule_id')->unique();
            $ids_sessiondates = Schedule::select('sessiondate_id')->whereIn('id', $ids_schedules)->pluck('sessiondate_id')->unique();

            if (isset($start) && isset($end)) {
                $from = Carbon::createFromFormat('Y-m-d', $start);
                $to = Carbon::createFromFormat('Y-m-d', $end);
                $ids_sessions = Sessiondate::select('session_id')->whereIn('id', $ids_sessiondates)->whereBetween('planning_date', [$from, $to])->pluck('session_id')->unique();
                //$ids_sessiondates=Schedule::select('sessiondate_id')->whereIn('id',$ids_schedules)->whereBetween('planning_date', [$start, $end])->pluck('sessiondate_id')->unique();
            } else {
                $ids_sessions = Sessiondate::select('session_id')->whereIn('id', $ids_sessiondates)->pluck('session_id')->unique();
            }
        }
        return $ids_sessions;
    }

    public function getNbHoursAndPricesContractFormer($contract_id, $code, $start_filter, $end_filter)
    {
        //$code = 'not_pointed', 'absent', 'present'
        $nb_hours = $total_cost = 0;
        $sd_ids = $sc_ids = [];
        $from = Carbon::createFromFormat('Y-m-d', $start_filter);
        $to = Carbon::createFromFormat('Y-m-d', $end_filter);

        $schedule_ids = Schedulecontact::select('schedule_id')
            ->where([['contract_id', $contract_id], ['is_former', 1], ['pointing', $code]])
            ->whereNull('validated_at')
            ->pluck('schedule_id')->unique();
        if (count($schedule_ids) > 0) {
            $sessiondate_ids = Schedule::select('sessiondate_id')->whereIn('id', $schedule_ids)->pluck('sessiondate_id')->unique();
            if (count($sessiondate_ids) > 0) {
                $sd_ids = Sessiondate::select('id')->whereIn('id', $sessiondate_ids)->whereDate('planning_date', '<=', $to)->pluck('id');
            }
        }
        if (count($sd_ids) > 0) {
            $sc_ids = Schedule::select('id')->whereIn('sessiondate_id', $sd_ids)->pluck('id');
        }

        if ($contract_id > 0 && count($sc_ids) > 0) {
            $total_cost = Schedulecontact::select('total_cost')->where([['contract_id', $contract_id], ['is_former', 1], ['pointing', $code]])
                ->whereIn('schedule_id', $sc_ids)
                ->whereNull('validated_at')
                ->sum('total_cost');
            $schedule_ids = Schedulecontact::select('schedule_id')->where([['contract_id', $contract_id], ['is_former', 1], ['pointing', $code]])
                ->whereIn('schedule_id', $sc_ids)
                ->whereNull('validated_at')
                ->pluck('schedule_id');
            if (count($schedule_ids) > 0) {
                $nb_hours = Schedule::whereIn('id', $schedule_ids)->sum('duration');
            }
        }
        return ['nb_hours' => $nb_hours, 'total_cost' => $total_cost];
    }
    public function getNbDaysContractFormer($contract_id, $start_filter, $end_filter)
    {
        $nb_days = 0;
        if ($contract_id > 0) {
            $from = Carbon::createFromFormat('Y-m-d', $start_filter);
            $to = Carbon::createFromFormat('Y-m-d', $end_filter);
            $nb_days = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date')
                ->where([['af_schedulecontacts.contract_id', $contract_id], ['af_schedulecontacts.is_former', 1]])
                ->whereDate('af_sessiondates.planning_date', '<=', $to)
                ->whereNull('af_schedulecontacts.validated_at')
                ->pluck('af_schedules.planning_date')->unique()->count();
        }
        return $nb_days;
    }
    public function getNbHoursContractFormer($contract_id, $start_filter = null, $end_filter = null)
    {
        $nb_hours = 0;
        if ($contract_id > 0) {
            if (isset($start_filter) && isset($end_filter)) {
                $from = Carbon::createFromFormat('Y-m-d', $start_filter);
                $to = Carbon::createFromFormat('Y-m-d', $end_filter);
                $nb_hours = DB::table('af_schedulecontacts')
                    ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_schedules.duration', 'af_sessiondates.planning_date')
                    ->where([['af_schedulecontacts.contract_id', $contract_id], ['af_schedulecontacts.is_former', 1]])
                    ->whereDate('af_sessiondates.planning_date', '<=', $to)
                    ->whereNull('af_schedulecontacts.validated_at')
                    ->sum('af_schedules.duration');
            } else {
                $nb_hours = DB::table('af_schedulecontacts')
                    ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_schedules.duration')
                    ->where([['af_schedulecontacts.contract_id', $contract_id], ['af_schedulecontacts.is_former', 1]])
                    ->sum('af_schedules.duration');
            }
        }
        return $nb_hours;
    }
    public function getNbHoursMembreFormer($member_id, $start_filter = null, $end_filter = null)
    {
        $nb_hours = 0;
        if ($member_id > 0) {
            if (isset($start_filter) && isset($end_filter)) {
                $from = Carbon::createFromFormat('Y-m-d', $start_filter);
                $to = Carbon::createFromFormat('Y-m-d', $end_filter);
                $nb_hours = DB::table('af_schedulecontacts')
                    ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_schedules.duration', 'af_sessiondates.planning_date')
                    ->where([['af_schedulecontacts.member_id', $member_id], ['af_schedulecontacts.is_former', 1]])
                    ->whereDate('af_sessiondates.planning_date', '<=', $to)
                    ->whereNull('af_schedulecontacts.validated_at')
                    ->sum('af_schedules.duration');
            } else {
                $nb_hours = DB::table('af_schedulecontacts')
                    ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_schedules.duration')
                    ->where([['af_schedulecontacts.member_id', $member_id], ['af_schedulecontacts.is_former', 1]])
                    ->sum('af_schedules.duration');
            }
        }
        return $nb_hours;
    }
    public function getTotalPriceContractFormer($contract_id)
    {
        $total = 0;
        if ($contract_id > 0) {
            /* $total = DB::table('af_schedulecontacts')
                        ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                        ->select('af_schedulecontacts.id','af_schedulecontacts.total_cost')
                        ->where([['af_schedulecontacts.contract_id', $contract_id], ['af_schedulecontacts.is_former', 1]])
                        ->sum('af_schedulecontacts.total_cost'); */
            $rsSchedulecontacts = Schedulecontact::join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->select('af_schedulecontacts.*')
                ->where([['af_schedulecontacts.contract_id', $contract_id], ['af_schedulecontacts.is_former', 1]])
                ->get();
            if (count($rsSchedulecontacts) > 0) {
                foreach ($rsSchedulecontacts as $s) {
                    $type_former_intervention = $s->member->contact->type_former_intervention;
                    $scf_total_cost = $this->getCostScheduleContact($s->schedule->duration, $s->price, $type_former_intervention);
                    $total += $scf_total_cost;
                }
            }
            // dd($total);
        }
        return $total;
    }
    public function getTotalPriceMembreFormer($member_id)
    {
        $total = 0;
        if ($member_id > 0) {
            /* $total = DB::table('af_schedulecontacts')
                        ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                        ->select('af_schedulecontacts.id','af_schedulecontacts.total_cost')
                        ->where([['af_schedulecontacts.member_id', $member_id], ['af_schedulecontacts.is_former', 1]])
                        ->sum('af_schedulecontacts.total_cost'); */
            $rsSchedulecontacts = Schedulecontact::join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->select('af_schedulecontacts.*')
                ->where([['af_schedulecontacts.member_id', $member_id], ['af_schedulecontacts.is_former', 1]])
                ->get();
            if (count($rsSchedulecontacts) > 0) {
                foreach ($rsSchedulecontacts as $s) {
                    $type_former_intervention = $s->member->contact->type_former_intervention;
                    $scf_total_cost = $this->getCostScheduleContact($s->schedule->duration, $s->price, $type_former_intervention);
                    $total += $scf_total_cost;
                }
            }
            // dd($total);
        }
        return $total;
    }

    public function getStateContractFormer($contract_id, $date_start_contract, $date_end_contract, $start_filter, $end_filter)
    {
        $is_cdd_less_60_days = $is_dsn = $is_bulletin_c = $is_sommeil = false;
        if (isset($date_start_contract) && isset($date_end_contract)) {
            $dc1 = Carbon::createFromFormat('Y-m-d', $date_start_contract);
            $dc2 = Carbon::createFromFormat('Y-m-d', $date_end_contract);
            $d_start_filter_1 = Carbon::createFromFormat('Y/m/d', $start_filter);
            $d_end_filter_2 = Carbon::createFromFormat('Y/m/d', $end_filter);
            //CDD<60j
            $nb_days = $dc1->diffInDays($dc2);
            $is_cdd_less_60_days = ($nb_days < 60) ? true : false;
            //DSN
            $check1 = $dc1->between($d_start_filter_1, $d_end_filter_2);
            $check2 = $dc2->between($d_start_filter_1, $d_end_filter_2);
            $is_dsn = ($check1 || $check2) ? true : false;
            /* 
                Sommeil : 
                Date de début est avant la période sélectionnée 
                et date de fin est après la période sélectionnée 
                mais pas de séance planifié sur la période sélectionnée) 
            */
            if (($dc1 < $d_start_filter_1 || $dc1 < $d_end_filter_2)  && ($dc2 > $d_start_filter_1 || $dc2 > $d_end_filter_2)) {
                $ids_schedules = Schedulecontact::select('schedule_id')->where([['contract_id', $contract_id], ['is_former', 1], ['pointing', 'present']])->pluck('schedule_id')->unique();
                if (count($ids_schedules) > 0) {
                    $ids_sessiondates = Schedule::select('sessiondate_id')->whereIn('id', $ids_schedules)->pluck('sessiondate_id')->unique();
                    if (count($ids_sessiondates) > 0) {
                        $nb_sessiondates = Sessiondate::select('id')->whereIn('id', $ids_sessiondates)->whereBetween('planning_date', [$d_start_filter_1, $d_end_filter_2])->count();
                        $is_sommeil = ($nb_sessiondates == 0) ? true : false;
                    }
                }
            }
            /*
                Bulletin C
             */
            $firstOfMonthOfEndFilterDate = Carbon::createFromFormat('Y/m/d', $end_filter)->firstOfMonth();
            if ($dc2 < $firstOfMonthOfEndFilterDate) {
                $nbsc = Schedulecontact::select('id')->where([['contract_id', $contract_id], ['is_former', 1]])->whereIn('pointing', ['not_pointed', 'present'])->whereNull('validated_at')->count();
                $is_bulletin_c = ($nbsc > 0) ? true : false;
            }
        }
        return ['is_cdd_less_60_days' => $is_cdd_less_60_days, 'is_bulletin_c' => $is_bulletin_c, 'is_dsn' => $is_dsn, 'is_sommeil' => $is_sommeil];
    }
    public function checkRequiredComptaFields($contact_id)
    {
        $res = false;
        if ($contact_id > 0) {
            $count = Contact::select('id')->where('id', $contact_id)
                ->whereNotNull('birth_name')
                ->whereNotNull('gender')
                ->whereNotNull('firstname')
                ->whereNotNull('lastname')
                ->whereNotNull('birth_date')
                ->whereNotNull('birth_department')
                ->whereNotNull('birth_city')
                ->whereNotNull('nationality')
                ->whereNotNull('social_security_number')
                ->whereNotNull('email')
                ->count();
            $res = ($count > 0) ? true : false;
            //Verifier les adresses
            if ($res == true) {
                $ct = Contact::select('id', 'entitie_id')->where('id', $contact_id)->first();
                if ($ct->entitie_id > 0) {
                    $nb = Adresse::select('id')->where('entitie_id', $ct->entitie_id)
                        ->whereNotNull('line_1')
                        ->whereNotNull('postal_code')
                        ->whereNotNull('city')
                        ->count();
                    if ($nb == 0) {
                        $res == false;
                    }
                }
            }
        }
        return $res;
    }
    public function getRegroupmentContractFormer($contract_id, $code, $start_filter, $end_filter)
    {
        //$code = 'not_pointed', 'absent', 'present'
        $rgroupmentArray = [];
        $sd_ids = $sc_ids = [];
        $from = Carbon::createFromFormat('Y-m-d', $start_filter);
        $to = Carbon::createFromFormat('Y-m-d', $end_filter);

        $schedule_ids = Schedulecontact::select('schedule_id')
            ->where([['contract_id', $contract_id], ['is_former', 1], ['pointing', $code]])->whereNull('validated_at')
            ->pluck('schedule_id')->unique();
        if (count($schedule_ids) > 0) {
            $sessiondate_ids = Schedule::select('sessiondate_id')->whereIn('id', $schedule_ids)->pluck('sessiondate_id')->unique();
            if (count($sessiondate_ids) > 0) {
                $sd_ids = Sessiondate::select('id')->whereIn('id', $sessiondate_ids)->whereDate('planning_date', '<=', $to)->pluck('id');
            }
        }
        if (count($sd_ids) > 0) {
            $sc_ids = Schedule::select('id')->whereIn('sessiondate_id', $sd_ids)->pluck('id');
        }

        if ($contract_id > 0 && count($sc_ids) > 0) {
            $rsss = Schedulecontact::select('price')->where([['contract_id', $contract_id], ['is_former', 1], ['pointing', $code]])
                ->whereIn('schedule_id', $sc_ids)
                ->whereNull('validated_at')
                ->groupBy('price')->pluck('price');
            if ($rsss->count() > 0) {
                foreach ($rsss as $price) {

                    $duration = DB::table('af_schedulecontacts')
                        ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                        ->select('af_schedulecontacts.id', 'af_schedulecontacts.price', 'af_schedulecontacts.schedule_id', 'af_schedulecontacts.validated_at', 'af_schedules.duration')
                        ->where([['af_schedulecontacts.contract_id', $contract_id], ['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.pointing', $code], ['af_schedulecontacts.price', $price]])
                        ->whereIn('af_schedulecontacts.schedule_id', $sc_ids)
                        ->whereNull('af_schedulecontacts.validated_at')
                        ->sum('duration');

                    $rgroupmentArray[$price] = $duration;
                }
            }
        }
        return $rgroupmentArray;
    }
    public function getTotalCostContractFormer($contract_id, $start_filter, $end_filter)
    {
        $total_cost = 0;
        $from = Carbon::createFromFormat('Y-m-d', $start_filter);
        $to = Carbon::createFromFormat('Y-m-d', $end_filter);
        if ($contract_id > 0) {
            $total_cost = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date')
                ->where([['af_schedulecontacts.contract_id', $contract_id], ['af_schedulecontacts.is_former', 1]])
                ->whereDate('af_sessiondates.planning_date', '<=', $to)
                ->sum('total_cost');
            //->get();
        }
        return $total_cost;
    }
    public function getAfByContractId($contract_id)
    {
        $af_id = 0;
        $title = '';
        if ($contract_id > 0) {
            $arr = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_sessions.af_id')
                ->where([['af_schedulecontacts.contract_id', $contract_id], ['af_schedulecontacts.is_former', 1]])
                ->pluck('af_sessions.af_id')->unique();
            $af_id = ($arr->count() > 0) ? $arr[0] : 0;
            if ($af_id > 0) {
                $rs = Action::select('title')->where('id', $af_id)->first();
                $title = $rs->title;
            }
        }
        return ['af_id' => $af_id, 'title' => $title];
    }
    public function getIdsByMemberAf($member_id, $af_id)
    {
        $ids_sessions = $ids_sessiondates = $ids_schedules = [];
        if ($af_id > 0) {
            $ids_sessions = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id], ['af_sessions.af_id', $af_id]])
                ->pluck('af_sessiondates.session_id')->unique();

            $ids_sessiondates = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id], ['af_sessions.af_id', $af_id]])
                ->pluck('af_schedules.sessiondate_id')->unique();

            $ids_schedules = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id], ['af_sessions.af_id', $af_id]])
                ->pluck('af_schedulecontacts.schedule_id')->unique();
        } else {
            $ids_sessions = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id]])
                ->pluck('af_sessiondates.session_id')->unique();

            $ids_sessiondates = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id]])
                ->pluck('af_schedules.sessiondate_id')->unique();

            $ids_schedules = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id]])
                ->pluck('af_schedulecontacts.schedule_id')->unique();
        }
        return ['ids_sessions' => $ids_sessions, 'ids_sessiondates' => $ids_sessiondates, 'ids_schedules' => $ids_schedules];
    }
    public function getIdsByContactAf($contact_id, $af_id)
    {
        $ids_sessions = $ids_sessiondates = $ids_schedules = [];
        $ids_members = Member::select('id')->where('contact_id', $contact_id)->pluck('id');
        if ($af_id > 0) {
            $ids_sessions = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_sessions.af_id', $af_id]])
                ->whereIn('af_schedulecontacts.member_id', $ids_members)
                ->pluck('af_sessiondates.session_id')->unique();

            $ids_sessiondates = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_sessions.af_id', $af_id]])
                ->whereIn('af_schedulecontacts.member_id', $ids_members)
                ->pluck('af_schedules.sessiondate_id')->unique();

            $ids_schedules = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where([['af_schedulecontacts.is_former', 1], ['af_sessions.af_id', $af_id]])
                ->whereIn('af_schedulecontacts.member_id', $ids_members)
                ->pluck('af_schedulecontacts.schedule_id')->unique();
        } else {
            $ids_sessions = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where('af_schedulecontacts.is_former', 1)
                ->whereIn('af_schedulecontacts.member_id', $ids_members)
                ->pluck('af_sessiondates.session_id')->unique();

            $ids_sessiondates = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where('af_schedulecontacts.is_former', 1)
                ->whereIn('af_schedulecontacts.member_id', $ids_members)
                ->pluck('af_schedules.sessiondate_id')->unique();

            $ids_schedules = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
                ->where('af_schedulecontacts.is_former', 1)
                ->whereIn('af_schedulecontacts.member_id', $ids_members)
                ->pluck('af_schedulecontacts.schedule_id')->unique();
        }
        return ['ids_members' => $ids_members, 'ids_sessions' => $ids_sessions, 'ids_sessiondates' => $ids_sessiondates, 'ids_schedules' => $ids_schedules];
    }
    public function getSchedulecontactsWithoutContracts($contact_id)
    {
        $ids_schedulecontacts = [];
        $ids_members = DB::table('af_members')
            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
            ->select('af_members.id', 'af_members.contact_id', 'af_enrollments.enrollment_type', 'af_enrollments.af_id')
            ->where([['af_members.contact_id', $contact_id], ['af_enrollments.enrollment_type', 'F']])
            ->pluck('af_members.id')->unique();

        if (count($ids_members) > 0) {
            $ids_schedulecontacts = DB::table('af_schedulecontacts')->select('id')->where('is_former', 1)
                ->whereIn('member_id', $ids_members)
                ->whereNull('contract_id')
                ->pluck('id')->unique();
        }
        $exist = (count($ids_schedulecontacts) > 0) ? true : false;
        return $exist;
    }
    public function manageInternshiproposal($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Internshiproposal();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Internshiproposal::find($row_id);
                if (!$row) {
                    $row = new Internshiproposal();
                }
            }
            $row->state = (isset($data['state'])) ? $data['state'] : null;
            $row->started_at = (isset($data['started_at'])) ? $data['started_at'] : null;
            $row->ended_at = (isset($data['ended_at'])) ? $data['ended_at'] : null;
            $row->representing_contact_id = (isset($data['representing_contact_id'])) ? $data['representing_contact_id'] : null;
            $row->internship_referent_contact_id = (isset($data['internship_referent_contact_id'])) ? $data['internship_referent_contact_id'] : null;
            $row->trainer_referent_contact_id = (isset($data['trainer_referent_contact_id'])) ? $data['trainer_referent_contact_id'] : null;
            $row->service = (isset($data['service'])) ? $data['service'] : null;
            $row->entity_id = (isset($data['entity_id'])) ? $data['entity_id'] : null;
            $row->member_id = (isset($data['member_id'])) ? $data['member_id'] : null;
            $row->session_id = (isset($data['session_id'])) ? $data['session_id'] : null;
            $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            $row->adresse_id = (isset($data['adresse_id'])) ? $data['adresse_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }
    public function getStatsProposalsBySession($session_id)
    {
        //'draft','approuved','invalid','validated','imposed'
        $nb_draft = Internshiproposal::select('id')->where([['session_id', $session_id], ['state', 'draft']])->count();
        $nb_approuved = Internshiproposal::select('id')->where([['session_id', $session_id], ['state', 'approuved']])->count();
        $nb_invalid = Internshiproposal::select('id')->where([['session_id', $session_id], ['state', 'invalid']])->count();
        $nb_validated = Internshiproposal::select('id')->where([['session_id', $session_id], ['state', 'validated']])->count();
        $nb_imposed = Internshiproposal::select('id')->where([['session_id', $session_id], ['state', 'imposed']])->count();
        return ['draft' => $nb_draft, 'approuved' => $nb_approuved, 'invalid' => $nb_invalid, 'validated' => $nb_validated, 'imposed' => $nb_imposed];
    }
    public function getCostScheduleContact($duration, $price, $type_former_intervention)
    {
        $cost_total = 0;
        $duration = (float) $duration;
        $price = (float) $price;
        $total_cost = ($type_former_intervention == "Sur contrat") ? $duration * $price : $price;
        //dump($duration.'======>'.$price.'======>'.$type_former_intervention.'======>'.$total_cost);
        return $total_cost;
    }
    public function getFunderByInvoice($invoice_id)
    {
        $entity_funder_id = 0;
        $name = $siret = $line_1 = $line_2 = $postal_code = $city = $contact_firstname = $contact_lastname = $ref = $entity_type = '';
        if ($invoice_id > 0) {
            $invoice = Invoice::find($invoice_id);
            $funding_option = $invoice->funding_option;
            if ($funding_option == 'contact_itself') {
                $entity_funder_id = $invoice->entity->id;
                $name = $invoice->contact->firstname . ' ' . $invoice->contact->lastname;
                $ref = $invoice->entity->ref;
                $entity_type = $invoice->entity->entity_type;
                $entity_adresse = Adresse::where([['entitie_id', $invoice->entity->id], ['is_main_entity_address', 1]])->first();
                if ($entity_adresse) {
                    $line_1 = $entity_adresse->line_1;
                    $line_2 = $entity_adresse->line_2;
                    $postal_code = $entity_adresse->postal_code;
                    $city = $entity_adresse->city;
                }
                $contact_firstname = $invoice->contact->firstname;
                $contact_lastname = $invoice->contact->lastname;
            } elseif ($funding_option == 'entity_contact') {
                $entity_funder_id = $invoice->entity->id;
                $name = $invoice->entity->name;
                $ref = $invoice->entity->ref;
                $entity_type = $invoice->entity->entity_type;
                $entity_adresse = Adresse::where([['entitie_id', $invoice->entity->id], ['is_main_entity_address', 1]])->first();
                if ($entity_adresse) {
                    $line_1 = $entity_adresse->line_1;
                    $line_2 = $entity_adresse->line_2;
                    $postal_code = $entity_adresse->postal_code;
                    $city = $entity_adresse->city;
                }
                $contact_firstname = $invoice->contact->firstname;
                $contact_lastname = $invoice->contact->lastname;
            } elseif ($funding_option == 'other_funders') {
                if ($invoice->entity_funder) {
                    $entity_funder_id = $invoice->entity_funder->id;
                    $ref = $invoice->entity_funder->ref;
                    $name = $invoice->entity_funder->name;
                    $entity_type = $invoice->entity_funder->entity_type;

                    $pRef = ' pour le compte de ' . $name;
                    $siret = ($invoice->entity_funder->siret) ? 'Votre SIRET :' . $invoice->entity_funder->siret . $pRef : '';
                    $entity_adresse = Adresse::where([['entitie_id', $invoice->entity_funder->id], ['is_main_entity_address', 1]])->first();
                    if ($entity_adresse) {
                        $line_1 = $entity_adresse->line_1;
                        $line_2 = $entity_adresse->line_2;
                        $postal_code = $entity_adresse->postal_code;
                        $city = $entity_adresse->city;
                    }
                }
                $contact_firstname = ($invoice->contact_funder) ? $invoice->contact_funder->firstname : '';
                $contact_lastname = ($invoice->contact_funder) ? $invoice->contact_funder->lastname : '';
            }
        }
        return [
            'name' => $name,
            'ref' => $ref,
            'entity_type' => $entity_type,
            'siret' => $siret,
            'line_1' => $line_1,
            'line_2' => $line_2,
            'postal_code' => $postal_code,
            'city' => $city,
            'contact_firstname' => $contact_firstname,
            'contact_lastname' => $contact_lastname,
            'entity_funder_id' => $entity_funder_id,
        ];
    }
    public function manageRefund($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Refund();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Refund::find($row_id);
                if (!$row) {
                    $row = new Refund();
                }
            }
            $row->number = (isset($data['number'])) ? $data['number'] : null;
            $row->reason = (isset($data['reason'])) ? $data['reason'] : null;
            $row->refund_date = (isset($data['refund_date'])) ? $data['refund_date'] : null;
            $row->invoice_id = (isset($data['invoice_id'])) ? $data['invoice_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }
    public function getRefundByInvoice($invoice_id)
    {
        $id = 0;
        $number = null;
        $is_synched_to_sage = null;
        if ($invoice_id > 0) {
            $rs = Refund::where('invoice_id', $invoice_id)->first();
            if ($rs) {
                $id = $rs->id;
                $number = $rs->number;
                $is_synched_to_sage = $rs->is_synched_to_sage;
            }
        }
        return [
            'id' => $id,
            'number' => $number,
            'is_synched_to_sage' => $is_synched_to_sage,
        ];
    }
    /* Remove accents and special chars from a string*/
    public function removeAccentsAndSpecial($string, $include_space = true)
    {
        $no_accented_chars = array(
            'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y'
        );
        $result = strtr($string, $no_accented_chars);
        $result = preg_replace('/[^A-Za-z0-9 ]/', '', $result);
        if ($include_space) {
            $result = preg_replace('/ +/', '', $result);
        } else {
            $result = preg_replace('/ {2,}/', ' ', $result);
        }

        return $result;
    }

    public function retrunTotalPercentageFunders($agreement_id)
    {
        $sum_percentage = 0;
        $sum_percentage = Funding::select('id', 'amount')->where([['agreement_id', $agreement_id], ['amount_type', 'percentage']])->sum('amount');
        $sum_fixed_amount = Funding::select('id', 'amount')->where([['agreement_id', $agreement_id], ['amount_type', 'fixed_amount']])->sum('amount');
        $calcul = $this->getAmountsAgreement($agreement_id);
        $agreement_amount = $calcul['total'];
        $percentage = 0;
        if ($agreement_amount > 0) {
            $percentage = $sum_fixed_amount * 100 / $agreement_amount;
        }
        $sum_percentage += $percentage;
        return ['sum_percentage' => $sum_percentage, 'remain_percentage' => 100 - $sum_percentage];
    }
    public function returnTotalPercentageEcheances($funding_id)
    {
        $funding = Funding::select('id', 'agreement_id', 'amount')->where('id', $funding_id)->first();
        $agreement_id = $funding->agreement_id;
        $sum_percentage = 0;
        $sum_percentage = Fundingpayment::select('id', 'amount')->where([['funding_id', $funding_id], ['amount_type', 'percentage']])->sum('amount');
        $sum_fixed_amount = Fundingpayment::select('id', 'amount')->where([['funding_id', $funding_id], ['amount_type', 'fixed_amount']])->sum('amount');
        $calcul = $this->getAmountsAgreement($agreement_id);
        $agreement_total_amount = $calcul['total'];
        $funder_amount = $funding->amount;
        if ($funding->amount_type == 'percentage') {
            $funder_amount = ($agreement_total_amount * $funding->amount) / 100;
        }
        $percentage = 0;
        if ($funder_amount > 0) {
            $percentage = $sum_fixed_amount * 100 / $funder_amount;
        }
        $sum_percentage += $percentage;
        return ['sum_percentage' => $sum_percentage, 'remain_percentage' => 100 - $sum_percentage];
    }
    public function agreementHasInvoice($agreement_id)
    {
        $hasInvoice = false;
        if ($agreement_id > 0) {
            $nb = Invoice::select('id')->where('agreement_id', $agreement_id)->count();
            $hasInvoice = ($nb > 0) ? true : false;
        }
        //dd($hasInvoice);
        return $hasInvoice;
    }
    public function manageCertificate($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Certificate();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Certificate::find($row_id);
                if (!$row) {
                    $row = new Certificate();
                }
            }
            if (isset($data['number']))
                $row->number = (isset($data['number'])) ? $data['number'] : null;
            //$row->type = (isset($data['type'])) ? $data['type'] : null;
            $row->status = (isset($data['status'])) ? $data['status'] : null;
            $row->signed_at = (isset($data['signed_at'])) ? $data['signed_at'] : null;
            $row->cancelled_at = (isset($data['cancelled_at'])) ? $data['cancelled_at'] : null;
            $row->session_id = (isset($data['session_id'])) ? $data['session_id'] : null;
            $row->af_id = (isset($data['af_id'])) ? $data['af_id'] : null;
            $row->enrollment_id = (isset($data['enrollment_id'])) ? $data['enrollment_id'] : null;
            $row->type = (isset($data['type'])) ? $data['type'] : 'employer';
            $row->contact_id = (isset($data['contact_id'])) ? $data['contact_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

   


    public function manageTask($data)
    {
        $id = 0;

        $contactid = auth()->user()->contact_id;
        // if($contactid){
        //     $task->contact_id = (isset($contactid)) ?? null;
        //     $comment->contact_id = (isset($contactid)) ?? null;

        //     $entitie_id = DB::table('en_contacts')->where('id', $contactid)->pluck('entitie_id');
        //     if($entitie_id){
        //         $task->entitite_id = (isset($entitie_id)) ?? null;
        //     }
        // }
        if (count($data) > 0) {
            $task = new Task();
            $comment = new Comment();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $task = Task::find($row_id);
                if (!$task) {
                    $task = new Task();
                }
            }
            if ($data['datedebut'] != null) {
                $start = DateTime::createFromFormat('d/m/Y', $data['datedebut']);
                $datedebut = $start->format('Y-m-d H:i:s');
                $task->start_date = (isset($datedebut)) ? $datedebut : null;
            }

            if ($data['dateecheance'] != null) {
                $end = DateTime::createFromFormat('d/m/Y', $data['dateecheance']);
                $dateecheance = $end->format('Y-m-d H:i:s');
                $task->ended_date = (isset($dateecheance)) ? $dateecheance : null;
            }

            if ($data['daterappel'] != null) {
                $end = DateTime::createFromFormat('d/m/Y', $data['daterappel']);
                $daterappel = $end->format('Y-m-d H:i:s');
                $task->callback_date = (isset($daterappel)) ? $daterappel : null;
            }

            $valide_etat = Param::where([['param_code', 'Etat'], ['code', 'Validé'], ['is_active', 1]])->pluck('id')->first();

            $task->title = (isset($data['resume'])) ? $data['resume'] : null;
            $task->description = (isset($data['description'])) ? $data['description'] : null;
            $task->source_id = (isset($data['sourcevalue'])) ? $data['sourcevalue'] : null;
            $task->type_id = (isset($data['typevalue'])) ? $data['typevalue'] : null;
            $task->etat_id = (isset($data['etatvalue'])) ? $data['etatvalue'] : $valide_etat;
            $task->priority = (isset($data['prioritetext'])) ? $data['prioritetext'] : null;
            $task->responsable_id = (isset($data['responsablevalue'])) ? $data['responsablevalue'] : null;
            $task->apporteur_id = (isset($data['rapporteurvalue'])) ? $data['rapporteurvalue'] : null;

            $task->callback_mode = (isset($data['moderappel'])) ? $data['moderappel'] : null;
            $task->reponse_mode_id = (isset($data['reponsevalue'])) ? $data['reponsevalue'] : null;
            $task->af_id = (isset($data['aflistvalue'])) ? $data['aflistvalue'] : null;
            $task->pf_id = (isset($data['pflistvalue'])) ? $data['pflistvalue'] : null;
            $task->entite_id = (isset($data['entitielistvalue'])) ? $data['entitielistvalue'] : null;
            $task->contact_id = (isset($data['contactlistvalue'])) ? $data['contactlistvalue'] : null;
            $task->is_read = (isset($data['isread'])) ? $data['isread'] : null;

            if (isset($data['is_sent']))
                $task->is_sent = $data['is_sent'];
            if (isset($data['file']))
                $task->file = $data['file'];
            if (isset($data['task_parent_id']))
                $task->task_parent_id = $data['task_parent_id'];
            if (isset($data['sub_task']))
                $task->sub_task = $data['sub_task'];

            $task->save();
            $task_id = $task->id;
            $comment_id = 0;
            if (isset($data['comment'])) {
                $comment->description = (isset($data['comment'])) ? $data['comment'] : null;
                $comment->date_comment = (isset($datedebut)) ? $datedebut : null;
                $comment->contact_id = (isset($data['contactlistvalue'])) ? $data['contactlistvalue'] : null;

                if ($task_id != null) {
                    $comment->task_id = (isset($task_id)) ? $task_id : null;
                    $comment->save();
                    $comment_id = $comment->id;
                }
            }
        }
        return ['task' => $task_id, 'comment' => $comment_id];
    }

    public function manageComment($data)
    {
        $id = 0;

        $contactid = Auth::user()->contact_id;

        $dtNow = Carbon::now();

        if (count($data) > 0) {

            if (count($data['comments']) > 0) {
                foreach ($data['comments'] as $key => $value) {
                    $comment = new Comment();

                    $comment->description = (isset($value)) ? $value : null;
                    $comment->task_id =  $data['taskid']  ? $data['taskid'] : null;
                    $comment->contact_id =  $contactid  ? $contactid : null;
                    $comment->date_comment =  $dtNow  ? $dtNow : null;

                    $comment->save();
                }
            }
        }

        return ['comment' => $comment];
    }


    public function manageStateTask($row_id)
    {
        if ($row_id > 0) {
            $params = Param::where([['param_code', 'Etat'], ['code', 'Annulée'], ['is_active', 1]])->pluck('id')->first();

            if ($row_id > 0) {
                $task = Task::find($row_id);
                $task->etat_id =  $params;
                $task->save();
            }
        }

        return ['task' => $task];
    }

    public function manageTerminateTask($row_id)
    {
        if ($row_id > 0) {
            $params = Param::where([['param_code', 'Etat'], ['code', 'Terminée'], ['is_active', 1]])->pluck('id')->first();

            if ($row_id > 0) {
                $task = Task::find($row_id);
                $task->etat_id =  $params;
                $task->is_read =  1;
                $task->save();
            }
        }

        return ['task' => $task];
    }

    public function reportTask($data)
    {
        $id = 0;

        $contactid = auth()->user()->contact_id;
        if (count($data) > 0) {
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $task = Task::find($row_id);
            }

            if ($data['date'] != null) {
                $date = DateTime::createFromFormat('d/m/Y', $data['date']);
                $daterappel = $date->format('Y-m-d H:i:s');
                $task->callback_date = (isset($daterappel)) ? $daterappel : null;
            }

            $task->save();
        }

        return ['task' => $task];
    }

    public function transfertTask($data)
    {
        $id = 0;

        $contactid = auth()->user()->contact_id;
        if (count($data) > 0) {
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $task = Task::find($row_id);
            }

            $task->responsable_id = (isset($data['responsable'])) ? $data['responsable'] : null;
            $task->save();
        }

        return ['task' => $task];
    }

    public function generateCertificateNumber($certificate_id)
    {
        $number = '';
        if ($certificate_id > 0) {
            $row = Certificate::find($certificate_id);
            return $row->number;
        }
        $dtNow = Carbon::now();
        $lastCertificate = Certificate::select('id')->orderByDesc('id')->first();
        $last_certificate_id = ($lastCertificate && $lastCertificate['id']) ? $lastCertificate['id'] : 0;
        $new_certificate_id = $last_certificate_id + 1;
        $number = 'ATT' . $dtNow->format('Y') . $dtNow->format('m') . sprintf('%06d', $new_certificate_id);
        return $number;
    }
    public function getHtmlAfRecapSchedulesForMember($af_id, $member_id)
    {
        $html = '';
        if ($af_id > 0) {
            $ids_sessiondates = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->where('af_schedulecontacts.member_id', $member_id)
                ->pluck('af_schedules.sessiondate_id')->unique();

            $ids_sessions = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->where('af_schedulecontacts.member_id', $member_id)
                ->pluck('af_sessiondates.session_id')->unique();
            $sessions = Session::select('id', 'code', 'title')->where('af_id', $af_id)->whereIn('id', $ids_sessions)->get();
            if (count($sessions) > 0) {
                $html = '<table border="1" style="border-collapse: collapse;width:18cm;font-size:12px;"><thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;"></th>
                        <th colspan="2" style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Séance 1</th>
                        <th colspan="2" style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Séance 2</th>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Dates</th>
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Début (h)</th>
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Fin (h)</th>
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Début (h)</th>
                        <th style="border: 1px solid black;border-collapse: collapse;padding: 4px;">Fin (h)</th>
                    </tr>
                </thead>
                <tbody>';
                foreach ($sessions as $s) {
                    $html .= '<tr style="border:1px solid #000;border-collapse: collapse;"><td colspan="5" style="border:1px solid #000;border-collapse: collapse;padding:5px;">Session : ' . $s->title . '</td></tr>';
                    $sessiondates = Sessiondate::where('session_id', $s->id)->whereIn('id', $ids_sessiondates)->orderBy('planning_date')->get();
                    if (count($sessiondates) > 0) {

                        foreach ($sessiondates as $sd) {
                            $html .= '<tr style="border:1px solid #000;border-collapse: collapse;">';
                            $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;

                            $date = ($planning_date) ? $planning_date->format('d-m-Y') : '';

                            $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $date . '</td>';

                            //Mooning
                            $rs_m_schedule = Schedule::where([['type', 'M'], ['sessiondate_id', $sd->id]])->get();
                            if (count($rs_m_schedule) > 0) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_m_schedule[0]->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_m_schedule[0]->end_hour);
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $start_hour->format('H') . 'h' . $start_hour->format('i') . '</td>';
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $end_hour->format('H') . 'h' . $end_hour->format('i') . '</td>';
                            } else {
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">-</td>';
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">-</td>';
                            }
                            //Afternoon
                            $rs_a_schedule = Schedule::where([['type', 'A'], ['sessiondate_id', $sd->id]])->get();
                            if (count($rs_a_schedule) > 0) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_a_schedule[0]->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $rs_a_schedule[0]->end_hour);
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $start_hour->format('H') . 'h' . $start_hour->format('i') . '</td>';
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">' . $end_hour->format('H') . 'h' . $end_hour->format('i') . '</td>';
                            } else {
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">-</td>';
                                $html .= '<td style="border: 1px solid black;border-collapse: collapse;padding:4px;">-</td>';
                            }

                            $html .= '</tr>';
                        }
                    }
                }
                $html .= '</tbody></table>';
            }
        }
        return $html;
    }
    public function manageAttachment($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Attachment();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Attachment::find($row_id);
                if (!$row) {
                    $row = new Attachment();
                }
            }
            $row->name = (isset($data['name'])) ? $data['name'] : null;
            $row->path = (isset($data['path'])) ? $data['path'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }

    /* public function getSubPfsRecursivly($pfp, $t_id) {
        $pfs = Formation::where('parent_id', $pfp->id)->get();
        $found = false;
        $datas = [];

        foreach($pfs as $pf){
            $is_eval = $pf->is_evaluation;
            $btnCheck = '<input type="hidden" name="af_sessions['.$pf->id.']" id="af_sessions'.$pf->id.'" class="">  ';

            $type_formation = '-';
            foreach ($pf->params as $pm) {
                if ($pm->param_code == 'PF_TYPE_FORMATION') {
                    $type_formation = $pm->name;
                }
            }
            
            $result = $this->getSubPfsRecursivly($pf, $t_id);
            $datas = array_merge($datas, $result['datas']);

            $found = $is_eval || $result['found'];
            //dd($result);
            
            if ($is_eval && $t_id == $pf->timestructure_id) {
            //if ($is_eval || $result['found']) {
                $datas [] = array (
                    "id" => $pf->id,
                    "text" => $btnCheck.$pf->title.'<span class="text-info">(ECTS: '.($pf->ects??'-').' ; Type: '.$type_formation.')</span>',
                    "state" => array('opened'=>false, 'checkbox_disabled' => !$is_eval),
                    "checkbox" => ($is_eval?$btnCheck:false),
                    "icon" => 'fa fa-file text-default',
                    "parent" => $pf->timestructure_id ? "S{$pf->timestructure_id}" : $pf->parent_id
                );
            }
        }

        return ['found'=>$found, 'datas'=>$datas]; 
        ///[is_eval, data] 
    } */
    public function getSubPfsRecursivly($pfp, $ts_id = false, $is_root = false)
    {
        $pfs = Formation::with('categorie')
            ->where('parent_id', $pfp->id);

        if ($ts_id && !$is_root) {
            $pfs->where('timestructure_id', $ts_id);
        }

        $pfs = $pfs->get();

        // dd($pfs->all());

        $found = false;
        $datas = [];

        foreach ($pfs as $pf) {
            $is_eval = $pf->is_evaluation;
            $btnCheck = '<input type="hidden" name="af_sessions[' . $pf->id . ']" id="af_sessions' . $pf->id . '" class="">  ';

            /* $type_formation = '-';
            foreach ($pf->params as $pm) {
                if ($pm->param_code == 'PF_TYPE_FORMATION') {
                    $type_formation = $pm->name;
                }
            } */

            $result = $this->getSubPfsRecursivly($pf, $ts_id);
            $datas = array_merge($datas, $result['datas']);

            $found = $found || $is_eval || $result['found'];
            // if ($found) {
            //     dump($pf->title);
            //     dump($pf->parent_id);
            //     die();
            // }
            if ($is_eval || $result['found']) {
                $datas[] = array(
                    "id" => $pf->id,
                    "text" => $btnCheck . $pf->title . '<span class="text-info">(ECTS: ' . ($pf->ects ?? '-') . ' ; Type: ' . $pf->evaluation_mode . ')</span>',
                    "state" => array('opened' => false, 'checkbox_disabled' => false/* !$is_eval */),
                    "checkbox" => ($is_eval ? $btnCheck : false),
                    "icon" => 'fa fa-file text-default',
                    "parent" => ($pf->timestructure_id == $ts_id && $pfp->timestructure_id !== $pf->timestructure_id) ? 'S' . $pf->timestructure_id : $pf->parent_id
                );
            }
        }

        return ['found' => $found, 'datas' => $datas]; /* [is_eval, data] */
    }
    public function getSchedulesContactsByScheduleId($schedule_id)
    {
        $datas = [];
        if ($schedule_id > 0) {
            $rs_schedulecontacts = Schedulecontact::select('id', 'member_id', 'price', 'price_type', 'contract_id', 'type_of_intervention', 'is_former')->where('schedule_id', $schedule_id)->get();
            if (count($rs_schedulecontacts) > 0) {
                foreach ($rs_schedulecontacts as $sc) {
                    if ($sc->is_former == 0) {
                        $datas['STUDENTS'][] = $sc;
                    }
                    if ($sc->is_former == 1) {
                        $datas['FORMERS'][] = $sc;
                    }
                }
            }
        }
        return $datas;
    }
    public function queryBuilderForTreeFrm($af_id, $session_id = 0, $group_id = 0, $member_id = 0, $member_former_id = 0, $start = null, $end = null)
    {
        $tab = [];

        $contactid = auth()->user()->contact_id;
        $roles = auth()->user()->roles;

        $enrollment_id = DB::table('af_members')->where('contact_id', $contactid)->pluck('enrollment_id');
        $member = DB::table('af_members')->where('contact_id', $contactid)->pluck('id');
        $schedules_contact = Schedulecontact::select('schedule_id')->whereIn('member_id', $member)->pluck('schedule_id');

        if ($af_id > 0) {
            $qb = DB::table('af_sessions')
                ->select(
                    'af_sessions.id as session_id',
                    'af_sessions.title as sessionTitle',
                    'af_sessions.code as sessionCode',
                )
                //->where('af_sessions.session_type','!=', 'AF_SESSION_TYPE_EVALUATION')
                ->where('af_sessions.is_internship_period', 0)->where('af_sessions.is_evaluation', 0)
                ->where('af_sessions.af_id', $af_id);
            if ($session_id > 0) {
                $qb->where('af_sessions.id', $session_id);
            }
            $rs = $qb->get();
            // dd($rs);
            foreach ($rs as $r) {
                $datesArray = $schedulesArray = $scheduleContactsArray = $groupsArray = [];
                //af_sessiondates
                $qb1 = DB::table('af_sessiondates')
                    ->select('af_sessiondates.id as sessiondate_id', 'af_sessiondates.planning_date')
                    ->where('af_sessiondates.session_id', $r->session_id);
                $dates = $qb1->get();
                //dd($dates);
                foreach ($dates as $d) {
                    $schedulesArray = [];
                    //af_schedules
                    if ($roles[0]->code == 'FORMATEUR') {
                        $qb2 = DB::table('af_schedules')
                            ->select('af_schedules.id as schedule_id', 'af_schedules.start_hour', 'af_schedules.end_hour', 'af_schedules.duration')
                            ->where('af_schedules.sessiondate_id', $d->sessiondate_id)
                            ->whereIn('af_schedules.id', $schedules_contact);
                    } else {
                        $qb2 = DB::table('af_schedules')
                            ->select('af_schedules.id as schedule_id', 'af_schedules.start_hour', 'af_schedules.end_hour', 'af_schedules.duration')
                            ->where('af_schedules.sessiondate_id', $d->sessiondate_id);
                    }
                    if (isset($start) && isset($end)) {
                        $qb2->whereBetween('af_sessiondates.planning_date', [$start, $end]);
                    }
                    $schedules = $qb2->get();
                    foreach ($schedules as $s) {
                        $scheduleContactsArray = [];
                        //af_schedulecontacts
                        $qb3 = DB::table('af_schedulecontacts')
                            ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                            ->leftJoin('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                            ->join('en_entities', 'en_entities.id', '=', 'af_enrollments.entitie_id')
                            ->leftJoin('en_contracts', 'en_contracts.id', '=', 'af_schedulecontacts.contract_id')
                            ->select(
                                'af_schedulecontacts.id',
                                'af_schedulecontacts.pointing',
                                'af_schedulecontacts.is_former',
                                'af_schedulecontacts.member_id',
                                'af_schedulecontacts.price',
                                'af_schedulecontacts.price_type',
                                'af_schedulecontacts.contract_id',
                                'af_schedulecontacts.type_of_intervention',
                                //af_members
                                'af_members.unknown_contact_name',
                                'af_members.contact_id',
                                'af_members.enrollment_id',
                                'af_members.group_id',
                                //af_enrollments
                                'af_enrollments.enrollment_type',
                                //en_contacts
                                'en_contacts.firstname',
                                'en_contacts.lastname',
                                'en_contacts.type_former_intervention',
                                //en_entities
                                'en_entities.ref',
                                'en_entities.name',
                                'en_entities.entity_type',
                                //en_contracts
                                'en_contracts.number as contractNumber',
                                'en_contracts.price as contractPrice',
                            )
                            ->where('af_schedulecontacts.schedule_id', $s->schedule_id);

                        if ($group_id > 0) {
                            $qb3->where('af_members.group_id', $group_id);
                        }

                        if ($member_id > 0) {
                            $qb3->where('af_schedulecontacts.member_id', $member_id);
                        }

                        if ($member_former_id > 0) {
                            $qb3->where('af_schedulecontacts.member_id', $member_former_id);
                        }

                        $schedulecontacts = $qb3->get();

                        foreach ($schedulecontacts as $sc) {
                            if ($sc->group_id > 0) {
                                if (!in_array($sc->group_id, $groupsArray))
                                    $groupsArray[] = $sc->group_id;
                            }
                            $scheduleContactsArray[$sc->id] = array(
                                'schedulecontact_id' => $sc->id,
                                'pointing' => $sc->pointing,
                                'is_former' => $sc->is_former,
                                'member_id' => $sc->member_id,
                                'price' => $sc->price,
                                'price_type' => $sc->price_type,
                                'contract_id' => $sc->contract_id,
                                'type_of_intervention' => $sc->type_of_intervention,
                                'group_id' => $sc->group_id,
                                'unknown_contact_name' => $sc->unknown_contact_name,
                                'contractNumber' => $sc->contractNumber,
                                'contractPrice' => $sc->contractPrice,
                                //entity
                                'entity_ref' => $sc->ref,
                                'entity_name' => $sc->name,
                                'entity_type' => $sc->entity_type,
                                //contact
                                'contact_id' => $sc->contact_id,
                                'contact_firstname' => $sc->firstname,
                                'contact_lastname' => $sc->lastname,
                                'contact_type_former_intervention' => $sc->type_former_intervention,
                            );
                            // dump($scheduleContactsArray); die;
                        }
                        //
                        // dump($scheduleContactsArray); die;

                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $s->start_hour);
                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $s->end_hour);
                        if (count($scheduleContactsArray) > 0) {
                            $schedulesArray[$s->schedule_id] = array(
                                'schedule_id' => $s->schedule_id,
                                'start_hour' => $start_hour,
                                'end_hour' => $end_hour,
                                'duration' => $s->duration,
                                'schedulecontacts' => $scheduleContactsArray,
                            );
                        }
                    }
                    if (count($schedulesArray) > 0) {
                        $datesArray[$d->sessiondate_id] = array(
                            'sessiondate_id' => $d->sessiondate_id,
                            'planning_date' => $d->planning_date,
                            'schedules' => $schedulesArray,
                        );
                    }
                }

                if (count($datesArray) > 0) {
                    $tab[$r->session_id] = array(
                        'id' => $r->session_id,
                        'title' => $r->sessionTitle,
                        'code' => $r->sessionCode,
                        'sessiondates' => $datesArray,
                        'groupsIds' => $groupsArray,
                    );
                }
            }
        }
        // dd($tab);
        return $tab;
    }

    public function queryBuilderForTree($af_id, $session_id = 0, $group_id = 0, $member_id = 0, $member_former_id = 0, $start = null, $end = null)
    {
        $tab = [];
        if ($af_id > 0) {
            $qb = DB::table('af_sessions')
                ->select(
                    'af_sessions.id as session_id',
                    'af_sessions.title as sessionTitle',
                    'af_sessions.code as sessionCode',
                )
                //->where('af_sessions.session_type','!=', 'AF_SESSION_TYPE_EVALUATION')
                ->where('af_sessions.is_internship_period', 0)->where('af_sessions.is_evaluation', 0)
                ->where('af_sessions.af_id', $af_id);
            if ($session_id > 0) {
                $qb->where('af_sessions.id', $session_id);
            }
            $rs = $qb->get();
            //dd($rs);
            foreach ($rs as $r) {
                $datesArray = $schedulesArray = $scheduleContactsArray = $groupsArray = [];
                //af_sessiondates
                $qb1 = DB::table('af_sessiondates')
                    ->select('af_sessiondates.id as sessiondate_id', 'af_sessiondates.planning_date')
                    ->where('af_sessiondates.session_id', $r->session_id);
                $dates = $qb1->get();
                //dd($dates);
                foreach ($dates as $d) {
                    $schedulesArray = [];
                    //af_schedules
                    $qb2 = DB::table('af_schedules')
                        ->select('af_schedules.id as schedule_id', 'af_schedules.start_hour', 'af_schedules.end_hour', 'af_schedules.duration')
                        ->where('af_schedules.sessiondate_id', $d->sessiondate_id);
                    if (isset($start) && isset($end)) {
                        $qb2->whereBetween('af_sessiondates.planning_date', [$start, $end]);
                    }
                    $schedules = $qb2->get();

                    foreach ($schedules as $s) {
                        $qb4 = AfSchedulegroup::select('group_id')->where('schedule_id',$s->schedule_id)->get();
                
                        foreach($qb4 as $group)
                        {
                            if (!in_array($group->group_id, $groupsArray))
                            {
                                    $groupsArray[] = $group->group_id;
                            }
                        }

                        $scheduleContactsArray = [];
                        //af_schedulecontacts
                        $qb3 = DB::table('af_schedulecontacts')
                            ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                            ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                            ->join('en_entities', 'en_entities.id', '=', 'af_enrollments.entitie_id')
                            ->leftJoin('en_contracts', 'en_contracts.id', '=', 'af_schedulecontacts.contract_id')
                            ->select(
                                'af_schedulecontacts.id',
                                'af_schedulecontacts.pointing',
                                'af_schedulecontacts.is_former',
                                'af_schedulecontacts.member_id',
                                'af_schedulecontacts.price',
                                'af_schedulecontacts.price_type',
                                'af_schedulecontacts.contract_id',
                                'af_schedulecontacts.type_of_intervention',
                                //af_members
                                'af_members.unknown_contact_name',
                                'af_members.contact_id',
                                'af_members.enrollment_id',
                                'af_members.group_id',
                                //af_enrollments
                                'af_enrollments.enrollment_type',
                                //en_contacts
                                'en_contacts.firstname',
                                'en_contacts.lastname',
                                'en_contacts.type_former_intervention',
                                //en_entities
                                'en_entities.ref',
                                'en_entities.name',
                                'en_entities.entity_type',
                                //en_contracts
                                'en_contracts.number as contractNumber',
                                'en_contracts.price as contractPrice',
                            )
                            ->where('af_schedulecontacts.schedule_id', $s->schedule_id);

                        if ($group_id > 0) {
                            $qb3->where('af_members.group_id', $group_id);
                        }
                        if ($member_id > 0) {
                            $qb3->where('af_schedulecontacts.member_id', $member_id);
                        }
                        if ($member_former_id > 0) {
                            $qb3->where('af_schedulecontacts.member_id', $member_former_id);
                        }

                        $schedulecontacts = $qb3->get();
                        foreach ($schedulecontacts as $sc) {
                            
                            $scheduleContactsArray[$sc->id] = array(
                                'schedulecontact_id' => $sc->id,
                                'pointing' => $sc->pointing,
                                'is_former' => $sc->is_former,
                                'member_id' => $sc->member_id,
                                'price' => $sc->price,
                                'price_type' => $sc->price_type,
                                'contract_id' => $sc->contract_id,
                                'type_of_intervention' => $sc->type_of_intervention,
                                'group_id' => $sc->group_id,
                                'unknown_contact_name' => $sc->unknown_contact_name,
                                'contractNumber' => $sc->contractNumber,
                                'contractPrice' => $sc->contractPrice,
                                //entity
                                'entity_ref' => $sc->ref,
                                'entity_name' => $sc->name,
                                'entity_type' => $sc->entity_type,
                                //contact
                                'contact_id' => $sc->contact_id,
                                'contact_firstname' => $sc->firstname,
                                'contact_lastname' => $sc->lastname,
                                'contact_type_former_intervention' => $sc->type_former_intervention,
                            );
                            //dump($scheduleContactsArray);
                        }
                        //
                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $s->start_hour);
                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $s->end_hour);
                        $schedulesArray[$s->schedule_id] = array(
                            'schedule_id' => $s->schedule_id,
                            'start_hour' => $start_hour,
                            'end_hour' => $end_hour,
                            'duration' => $s->duration,
                            'schedulecontacts' => $scheduleContactsArray,
                        );
                    }
                    $datesArray[$d->sessiondate_id] = array(
                        'sessiondate_id' => $d->sessiondate_id,
                        'planning_date' => $d->planning_date,
                        'schedules' => $schedulesArray,
                    );
                }
                $tab[$r->session_id] = array(
                    'id' => $r->session_id,
                    'title' => $r->sessionTitle,
                    'code' => $r->sessionCode,
                    'sessiondates' => $datesArray,
                    'groupsIds' => $groupsArray,
                );
            }
        }
        //dd($tab);
        return $tab;
        // $tab = [];
        // if ($af_id > 0) {
        //     $qb = DB::table('af_sessions')
        //         ->select(
        //             'af_sessions.id as session_id',
        //             'af_sessions.title as sessionTitle',
        //             'af_sessions.code as sessionCode',
        //         )
        //         //->where('af_sessions.session_type','!=', 'AF_SESSION_TYPE_EVALUATION')
        //         ->where('af_sessions.is_internship_period', 0)->where('af_sessions.is_evaluation', 0)
        //         ->where('af_sessions.af_id', 205);
        //     if ($session_id > 0) {
        //         $qb->where('af_sessions.id', $session_id);
        //     }
        //     $rs = $qb->first();
            
        //         $datesArray = $schedulesArray = $scheduleContactsArray = $groupsArray = [];

        //         $qb1 = DB::table('af_sessiondates')
        //             ->select('af_sessiondates.id as sessiondate_id', 'af_sessiondates.planning_date')
        //             ->where('af_sessiondates.session_id', $rs->session_id);
        //         $dates = $qb1->get();
        //         foreach ($dates as $d) {
        //             $schedulesArray = [];
        //             //af_schedules
        //             $qb2 = DB::table('af_schedules')
        //                 ->select('af_schedules.id as schedule_id', 'af_schedules.start_hour', 'af_schedules.end_hour', 'af_schedules.duration')
        //                 ->where('af_schedules.sessiondate_id', $d->sessiondate_id);
        //             if (isset($start) && isset($end)) {
        //                 $qb2->whereBetween('af_sessiondates.planning_date', [$start, $end]);
        //             }
        //             $schedules = $qb2->get();

        //             // foreach ($schedules as $s) {
        //             //     $qb4 = AfSchedulegroup::select('group_id')->where('schedule_id',$s->schedule_id)->get();

        //             //     foreach($qb4 as $grp)
        //             //     {
        //             //         if (!in_array($grp, $groupsArray))
        //             //         {
        //             //             $groupsArray[] = $grp;
        //             //         }
        //             //     }
        //             // }

        //             foreach ($schedules as $s) {
        //                 $scheduleContactsArray = [];
        //                 //af_schedulecontacts
        //                 $qb3 = DB::table('af_schedulecontacts')
        //                     ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
        //                     ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
        //                     ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
        //                     ->join('en_entities', 'en_entities.id', '=', 'af_enrollments.entitie_id')
        //                     ->leftJoin('en_contracts', 'en_contracts.id', '=', 'af_schedulecontacts.contract_id')
        //                     ->select(
        //                         'af_schedulecontacts.id',
        //                         'af_schedulecontacts.pointing',
        //                         'af_schedulecontacts.is_former',
        //                         'af_schedulecontacts.member_id',
        //                         'af_schedulecontacts.price',
        //                         'af_schedulecontacts.price_type',
        //                         'af_schedulecontacts.contract_id',
        //                         'af_schedulecontacts.type_of_intervention',
        //                         //af_members
        //                         'af_members.unknown_contact_name',
        //                         'af_members.contact_id',
        //                         'af_members.enrollment_id',
        //                         'af_members.group_id',
        //                         //af_enrollments
        //                         'af_enrollments.enrollment_type',
        //                         //en_contacts
        //                         'en_contacts.firstname',
        //                         'en_contacts.lastname',
        //                         'en_contacts.type_former_intervention',
        //                         //en_entities
        //                         'en_entities.ref',
        //                         'en_entities.name',
        //                         'en_entities.entity_type',
        //                         //en_contracts
        //                         'en_contracts.number as contractNumber',
        //                         'en_contracts.price as contractPrice',
        //                     )
        //                     ->where('af_schedulecontacts.schedule_id', $s->schedule_id);

        //                 if ($group_id > 0) {
        //                     $qb3->where('af_members.group_id', $group_id);
        //                 }
        //                 if ($member_id > 0) {
        //                     $qb3->where('af_schedulecontacts.member_id', $member_id);
        //                 }
        //                 if ($member_former_id > 0) {
        //                     $qb3->where('af_schedulecontacts.member_id', $member_former_id);
        //                 }

        //                 $qb4 = AfSchedulegroup::select('group_id')->where('schedule_id',$s->schedule_id)->get();

        //                 foreach($qb4 as $grp)
        //                 {
        //                     if (!in_array($grp, $groupsArray))
        //                     {
        //                         $groupsArray[] = $grp;
        //                     }
        //                 }

        //                 $schedulecontacts = $qb3->get();
        //                 foreach ($schedulecontacts as $sc) {
                            
        //                     $scheduleContactsArray[$sc->id] = array(
        //                         'schedulecontact_id' => $sc->id,
        //                         'pointing' => $sc->pointing,
        //                         'is_former' => $sc->is_former,
        //                         'member_id' => $sc->member_id,
        //                         'price' => $sc->price,
        //                         'price_type' => $sc->price_type,
        //                         'contract_id' => $sc->contract_id,
        //                         'type_of_intervention' => $sc->type_of_intervention,
        //                         'group_id' => $sc->group_id,
        //                         'unknown_contact_name' => $sc->unknown_contact_name,
        //                         'contractNumber' => $sc->contractNumber,
        //                         'contractPrice' => $sc->contractPrice,
        //                         //entity
        //                         'entity_ref' => $sc->ref,
        //                         'entity_name' => $sc->name,
        //                         'entity_type' => $sc->entity_type,
        //                         //contact
        //                         'contact_id' => $sc->contact_id,
        //                         'contact_firstname' => $sc->firstname,
        //                         'contact_lastname' => $sc->lastname,
        //                         'contact_type_former_intervention' => $sc->type_former_intervention,
        //                     );
        //                     //dump($scheduleContactsArray);
        //                 }
        //                 //
        //                 $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $s->start_hour);
        //                 $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $s->end_hour);
        //                 $schedulesArray[$s->schedule_id] = array(
        //                     'schedule_id' => $s->schedule_id,
        //                     'start_hour' => $start_hour,
        //                     'end_hour' => $end_hour,
        //                     'duration' => $s->duration,
        //                     'schedulecontacts' => $scheduleContactsArray,
        //                 );
        //             }
        //             $datesArray[$d->sessiondate_id] = array(
        //                 'sessiondate_id' => $d->sessiondate_id,
        //                 'planning_date' => $d->planning_date,
        //                 'schedules' => $schedulesArray,
        //             );

        //             // foreach ($schedules as $s) {
        //             //     $scheduleContactsArray = [];
        //             //     //af_schedulecontacts
        //             //     $qb3 = DB::table('af_schedulecontacts')
        //             //         ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
        //             //         ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
        //             //         ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
        //             //         ->join('en_entities', 'en_entities.id', '=', 'af_enrollments.entitie_id')
        //             //         ->leftJoin('en_contracts', 'en_contracts.id', '=', 'af_schedulecontacts.contract_id')
        //             //         ->select(
        //             //             'af_schedulecontacts.id',
        //             //             'af_schedulecontacts.pointing',
        //             //             'af_schedulecontacts.is_former',
        //             //             'af_schedulecontacts.member_id',
        //             //             'af_schedulecontacts.price',
        //             //             'af_schedulecontacts.price_type',
        //             //             'af_schedulecontacts.contract_id',
        //             //             'af_schedulecontacts.type_of_intervention',
        //             //             //af_members
        //             //             'af_members.unknown_contact_name',
        //             //             'af_members.contact_id',
        //             //             'af_members.enrollment_id',
        //             //             'af_members.group_id',
        //             //             //af_enrollments
        //             //             'af_enrollments.enrollment_type',
        //             //             //en_contacts
        //             //             'en_contacts.firstname',
        //             //             'en_contacts.lastname',
        //             //             'en_contacts.type_former_intervention',
        //             //             //en_entities
        //             //             'en_entities.ref',
        //             //             'en_entities.name',
        //             //             'en_entities.entity_type',
        //             //             //en_contracts
        //             //             'en_contracts.number as contractNumber',
        //             //             'en_contracts.price as contractPrice',
        //             //         )
        //             //         ->where('af_schedulecontacts.schedule_id', $s->schedule_id);

        //             //     if ($group_id > 0) {
        //             //         $qb3->where('af_members.group_id', $group_id);
        //             //     }
        //             //     if ($member_id > 0) {
        //             //         $qb3->where('af_schedulecontacts.member_id', $member_id);
        //             //     }
        //             //     if ($member_former_id > 0) {
        //             //         $qb3->where('af_schedulecontacts.member_id', $member_former_id);
        //             //     }

        //             //     $schedulecontacts = $qb3->get();

        //             //     foreach ($schedulecontacts as $sc) {
        //             //         if (isset($sc->group_id)) {
        //             //             if (!in_array($sc->group_id, $groupsArray))
        //             //                 $groupsArray[] = $sc->group_id;
        //             //         }

        //             //         $scheduleContactsArray[$sc->id] = array(
        //             //             'schedulecontact_id' => $sc->id,
        //             //             'pointing' => $sc->pointing,
        //             //             'is_former' => $sc->is_former,
        //             //             'member_id' => $sc->member_id,
        //             //             'price' => $sc->price,
        //             //             'price_type' => $sc->price_type,
        //             //             'contract_id' => $sc->contract_id,
        //             //             'type_of_intervention' => $sc->type_of_intervention,
        //             //             'group_id' => $sc->group_id,
        //             //             'unknown_contact_name' => $sc->unknown_contact_name,
        //             //             'contractNumber' => $sc->contractNumber,
        //             //             'contractPrice' => $sc->contractPrice,
        //             //             //entity
        //             //             'entity_ref' => $sc->ref,
        //             //             'entity_name' => $sc->name,
        //             //             'entity_type' => $sc->entity_type,
        //             //             //contact
        //             //             'contact_id' => $sc->contact_id,
        //             //             'contact_firstname' => $sc->firstname,
        //             //             'contact_lastname' => $sc->lastname,
        //             //             'contact_type_former_intervention' => $sc->type_former_intervention,
        //             //         );

        //             //     }


        //             // }
        //         }
            
            
            
        // }
        // //dd($tab);
        // return $tab;
    }

    

    /*public function queryBuilderForTree($af_id, $session_id = 0, $group_id = 0, $member_id = 0, $member_former_id = 0, $start = null, $end = null)
    {
        $tab = [];
        if ($af_id > 0) {
            $qb = DB::table('af_sessions')
                ->select(
                    'af_sessions.id as session_id',
                    'af_sessions.title as sessionTitle',
                    'af_sessions.code as sessionCode',
                )
                //->where('af_sessions.session_type','!=', 'AF_SESSION_TYPE_EVALUATION')
                ->where('af_sessions.is_internship_period', 0)->where('af_sessions.is_evaluation', 0)
                ->where('af_sessions.af_id', $af_id);
            if ($session_id > 0) {
                $qb->where('af_sessions.id', $session_id);
            }
            $rs = $qb->get();
            //dd($rs);
            foreach ($rs as $r) {
                $datesArray = $schedulesArray = $scheduleContactsArray = $groupsArray = [];
                //af_sessiondates
                $qb1 = DB::table('af_sessiondates')
                    ->select('af_sessiondates.id as sessiondate_id', 'af_sessiondates.planning_date')
                    ->where('af_sessiondates.session_id', $r->session_id);
                $dates = $qb1->get();
                //dd($dates);
                foreach ($dates as $d) {
                    $schedulesArray = [];
                    //af_schedules
                    $qb2 = DB::table('af_schedules')
                        ->select('af_schedules.id as schedule_id', 'af_schedules.start_hour', 'af_schedules.end_hour', 'af_schedules.duration')
                        ->where('af_schedules.sessiondate_id', $d->sessiondate_id);
                    if (isset($start) && isset($end)) {
                        $qb2->whereBetween('af_sessiondates.planning_date', [$start, $end]);
                    }
                    $schedules = $qb2->get();
                    foreach ($schedules as $s) {
                        $scheduleContactsArray = [];
                        //af_schedulecontacts
                        $qb3 = DB::table('af_schedulecontacts')
                            ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                            ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                            ->join('en_entities', 'en_entities.id', '=', 'af_enrollments.entitie_id')
                            ->leftJoin('en_contracts', 'en_contracts.id', '=', 'af_schedulecontacts.contract_id')
                            ->select(
                                'af_schedulecontacts.id',
                                'af_schedulecontacts.pointing',
                                'af_schedulecontacts.is_former',
                                'af_schedulecontacts.member_id',
                                'af_schedulecontacts.price',
                                'af_schedulecontacts.price_type',
                                'af_schedulecontacts.contract_id',
                                'af_schedulecontacts.type_of_intervention',
                                //af_members
                                'af_members.unknown_contact_name',
                                'af_members.contact_id',
                                'af_members.enrollment_id',
                                'af_members.group_id',
                                //af_enrollments
                                'af_enrollments.enrollment_type',
                                //en_contacts
                                'en_contacts.firstname',
                                'en_contacts.lastname',
                                'en_contacts.type_former_intervention',
                                //en_entities
                                'en_entities.ref',
                                'en_entities.name',
                                'en_entities.entity_type',
                                //en_contracts
                                'en_contracts.number as contractNumber',
                                'en_contracts.price as contractPrice',
                            )
                            ->where('af_schedulecontacts.schedule_id', $s->schedule_id);

                        if ($group_id > 0) {
                            $qb3->where('af_members.group_id', $group_id);
                        }
                        if ($member_id > 0) {
                            $qb3->where('af_schedulecontacts.member_id', $member_id);
                        }
                        if ($member_former_id > 0) {
                            $qb3->where('af_schedulecontacts.member_id', $member_former_id);
                        }

                        $schedulecontacts = $qb3->get();
                        foreach ($schedulecontacts as $sc) {
                            if ($sc->group_id > 0) {
                                if (!in_array($sc->group_id, $groupsArray))
                                    $groupsArray[] = $sc->group_id;
                            }
                            $scheduleContactsArray[$sc->id] = array(
                                'schedulecontact_id' => $sc->id,
                                'pointing' => $sc->pointing,
                                'is_former' => $sc->is_former,
                                'member_id' => $sc->member_id,
                                'price' => $sc->price,
                                'price_type' => $sc->price_type,
                                'contract_id' => $sc->contract_id,
                                'type_of_intervention' => $sc->type_of_intervention,
                                'group_id' => $sc->group_id,
                                'unknown_contact_name' => $sc->unknown_contact_name,
                                'contractNumber' => $sc->contractNumber,
                                'contractPrice' => $sc->contractPrice,
                                //entity
                                'entity_ref' => $sc->ref,
                                'entity_name' => $sc->name,
                                'entity_type' => $sc->entity_type,
                                //contact
                                'contact_id' => $sc->contact_id,
                                'contact_firstname' => $sc->firstname,
                                'contact_lastname' => $sc->lastname,
                                'contact_type_former_intervention' => $sc->type_former_intervention,
                            );
                            //dump($scheduleContactsArray);
                        }
                        //
                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $s->start_hour);
                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $s->end_hour);
                        $schedulesArray[$s->schedule_id] = array(
                            'schedule_id' => $s->schedule_id,
                            'start_hour' => $start_hour,
                            'end_hour' => $end_hour,
                            'duration' => $s->duration,
                            'schedulecontacts' => $scheduleContactsArray,
                        );
                    }
                    $datesArray[$d->sessiondate_id] = array(
                        'sessiondate_id' => $d->sessiondate_id,
                        'planning_date' => $d->planning_date,
                        'schedules' => $schedulesArray,
                    );
                }
                $tab[$r->session_id] = array(
                    'id' => $r->session_id,
                    'title' => $r->sessionTitle,
                    'code' => $r->sessionCode,
                    'sessiondates' => $datesArray,
                    'groupsIds' => $groupsArray,
                );
            }
        }
        //dd($tab);
        return $tab;
    }*/

    /* 
     * code auxiliaire
     */
    public function generateAuxiliaryAccountForEntity($entity_id)
    {
        $entity = Entitie::find($entity_id);
    
        if (!$entity) {
            return '';
        }
    
        $auxiliary_code = '';
    
        if ($entity->entity_type == 'P') {
            $contact = Contact::where('entitie_id', $entity_id)
                ->where(DB::raw("CONCAT(en_contacts.lastname, ' ', en_contacts.firstname)"), "D'HOUNDT LAURALEE")
                ->whereNull('en_contacts.deleted_at')
                ->limit(1)
                ->first();
    
            if ($contact) {
                $firstname = $this->removeAccentsAndSpecial($contact->firstname, false /* remain spaces */);
                $lastname = $this->removeAccentsAndSpecial($contact->lastname);
    
                $firstname_parts = explode(' ', $firstname);
                $firstname_parts = array_map(function ($part) {
                    return $part[0] ?? '';
                }, $firstname_parts);
    
                $firstname = implode('', $firstname_parts);
                $firstname = substr($firstname, 0, 12);
    
                $lastname = substr($lastname, 0, 13 - strlen($firstname));
                $auxiliary_code = strtoupper($lastname . $firstname);
            }
        } elseif ($entity->entity_type == 'S') {
            $address = Adresse::where('entitie_id', $entity_id)->first();
    
            if ($address) {
                $name = $this->removeAccentsAndSpecial($entity->name);
                $auxiliary_code = substr($name, 0, 8);
    
                $city = $this->removeAccentsAndSpecial($address->city, false /* remain spaces */);
                $city_parts = explode(' ', $city);
                $city_parts = array_map(function ($part) {
                    return $part[0] ?? '';
                }, $city_parts);
    
                $city = implode('', $city_parts);
                $city = substr($city, 0, 5);
    
                $auxiliary_code .= $city;
                $auxiliary_code = strtoupper($auxiliary_code);
            }
        }
    
        if ($auxiliary_code != '') {
            $same_code_entities = Entitie::select('auxiliary_customer_account')
                ->where('auxiliary_customer_account', 'like', $auxiliary_code . '%')
                ->where('id', '!=', $entity_id)
                ->groupBy('auxiliary_customer_account')
                ->get();
    
            $number = 0;
    
            while ($same_code_entities->contains('auxiliary_customer_account', $auxiliary_code)) {
                $number++;
                $suffix = sprintf("%02d", $number);
                $crop_limit = $number > 1 ? strlen($auxiliary_code) - 2 : 11;
                $auxiliary_code = substr($auxiliary_code, 0, $crop_limit) . $suffix;
            }
        }
    
        return $auxiliary_code;
    }
    


    


    public function formatString($string)
    {
        $string = preg_replace('/\s+/', '', $string);
        $string = preg_replace('/[^a-zA-Z0-9_ %\[\]\(\)&]/s', '', $string);
        $string = str_replace(array('_', '%', ',', ';', '<', '>'), '', $string);
        return strtoupper($string);
    }
    public function extractInvoiceNum($inv)
    {
        $num = $inv->code_facture;
        if (preg_match('/^(A|F)[0-9]{4}\-[0-9]{2}\-[0-9]{4}$/', $num)) {
            return explode('-', $num)[2];
        }
        return substr($num, 7);
    }
    public function generateCodeCollectifs($entity_id)
    {
        $entity = Entitie::select('id', 'entity_type')->where('id', $entity_id)->first();
        $code = 411;
        if ($entity->entity_type == 'P') {
            $code = 410;
        }
        return $code;
    }
    public function checkIfSessiondateChecked($sessiondate_id, $ids_schedules, $ids_members)
    {
        $checkbox_disabled = false;
        if ($sessiondate_id > 0) {
            $ids_sch = Schedule::select('id')->where('sessiondate_id', $sessiondate_id)->whereIn('id', $ids_schedules)->pluck('id');
            $rs_schedulecontacts_formers = Schedulecontact::where('is_former', 1)->whereIn('schedule_id', $ids_sch)->whereIn('member_id', $ids_members)->get();
            foreach ($rs_schedulecontacts_formers as $scf) {
                if ($scf->price < 1) {
                    $checkbox_disabled = true;
                } else {
                    $type_former_intervention = $scf->member->contact->type_former_intervention;
                    $scf_total_cost = $this->getCostScheduleContact($scf->schedule->duration, $scf->price, $type_former_intervention);
                    if ($scf_total_cost < 1) {
                        $checkbox_disabled = true;
                    }
                }
            }
        }
        return $checkbox_disabled;
    }
    public function getGroupsInSchedule($schedule_id)
    {
        $groups = [];
        if ($schedule_id > 0) {
            $qb3 = DB::table('af_schedulecontacts')
                ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                ->join('af_groups', 'af_groups.id', '=', 'af_members.group_id')
                ->select(
                    'af_schedulecontacts.id',
                    'af_schedulecontacts.member_id',
                    'af_members.group_id',
                    'af_groups.title',
                )->where('af_schedulecontacts.schedule_id', $schedule_id);
            $rs = $qb3->groupBy('af_members.group_id')->get();
            //dd($rs);
            if (count($rs) > 0) {
                foreach ($rs as $r) {
                    $groups[] = $r->title;
                }
            }
        }
        return $groups;
    }
    //intervenants sans contrat
    public function getFormersWithoutContracts()
    {

        //$ids_sch = Member::select('id','contact_id')->whereNotNull('contact_id')->pluck('id');
        $qb = DB::table('af_members')
            ->select('af_members.id', 'af_members.contact_id')
            ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
            ->whereNotNull('contact_id')
            ->where('en_contacts.type_former_intervention', 'Sur contrat');
        $ids_members = $qb->pluck('id');
        $arrMembersId = [];
        foreach ($ids_members as $member_id) {
            $qb2 = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                ->select(
                    'af_schedulecontacts.id',
                    'af_actions.id as af_id',
                    'af_actions.code',
                    'af_actions.title',
                    'af_schedulecontacts.is_former',
                    'af_schedulecontacts.member_id',
                    'af_schedulecontacts.contract_id',
                    'af_members.contact_id',
                    //en_contacts
                    'en_contacts.firstname',
                    'en_contacts.lastname',
                    'en_contacts.type_former_intervention'
                )->where('af_schedulecontacts.is_former', 1)
                ->where('en_contacts.type_former_intervention', 'Sur contrat')
                ->where('af_schedulecontacts.member_id', $member_id)
                ->whereNotNull('af_schedulecontacts.contract_id');
            $count = $qb2->count();
            //dd($count);
            // if ($count < 1) {
                $arrMembersId[] = $member_id;
            // }
        }
        $qb2 = DB::table('af_schedulecontacts')
            ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
            ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
            ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
            ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
            ->select(
                'af_schedulecontacts.id',
                'af_actions.id as af_id',
                'af_actions.code',
                'af_actions.title',
                'af_schedulecontacts.is_former',
                'af_schedulecontacts.member_id',
                'af_schedulecontacts.contract_id',
                'af_members.contact_id',
                //en_contacts
                'en_contacts.firstname',
                'en_contacts.lastname',
                'en_contacts.type_former_intervention'
            )->where('af_schedulecontacts.is_former', 1)
            ->where('en_contacts.type_former_intervention', 'Sur contrat')
            ->whereIn('af_schedulecontacts.member_id', $arrMembersId);
        //$schedulecontacts=$qb2->get();
        $schedulecontacts = $qb2->groupBy('af_members.contact_id')->get();
        //dd($schedulecontacts);
        /* $qb3 = DB::table('af_schedulecontacts')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                 ->select(
                    'af_schedulecontacts.id',
                    'af_actions.id as af_id',
                    'af_actions.code',
                    'af_actions.title',
                    'af_schedulecontacts.is_former',
                    'af_schedulecontacts.member_id',
                    'af_schedulecontacts.contract_id',
                    'af_members.contact_id',
                    //en_contacts
                    'en_contacts.firstname',
                    'en_contacts.lastname',
                    'en_contacts.type_former_intervention'
                    )->where('af_schedulecontacts.is_former', 1)
                    ->where('en_contacts.type_former_intervention', 'Sur contrat')
                    ->whereNull('af_schedulecontacts.contract_id'); */

        //$schedulecontacts=$qb3->groupBy('af_members.contact_id','af_schedulecontacts.contract_id')->get();
        //dd($schedulecontacts);
        return $schedulecontacts;
    }
    public function manageStudentStatus($data)
    {
        $id = 0;
        if (count($data) > 0) {
            $row = new Studentstatus();
            $row_id = (isset($data['id'])) ? $data['id'] : 0;
            if ($row_id > 0) {
                $row = Studentstatus::find($row_id);
                if (!$row) {
                    $row = new Studentstatus();
                }
            }
            $row->student_status = (isset($data['student_status'])) ? $data['student_status'] : null;
            $row->start_date = (isset($data['start_date'])) ? $data['start_date'] : null;
            $row->end_date = (isset($data['end_date'])) ? $data['end_date'] : null;
            $row->member_id = (isset($data['member_id'])) ? $data['member_id'] : null;
            $row->save();
            $id = $row->id;
        }
        return $id;
    }
    public function getAfInfosFromContract($contract_id)
    {
        //$af_array = [];
        $af_id = $code = $title = '';
        if ($contract_id > 0) {
            $qb = DB::table('en_contracts')
                ->join('af_schedulecontacts', 'en_contracts.id', '=', 'af_schedulecontacts.contract_id')
                ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                ->select('af_actions.id as af_id', 'af_actions.code', 'af_actions.title')
                ->where('en_contracts.id', $contract_id);
            $rs = $qb->first();
            //dd($rs);
            if ($rs) {
                $af_id = $rs->af_id;
                $code = $rs->code;
                $title = $rs->title;
            }
        }
        return array(
            'af_id' => $af_id,
            'code' => $code,
            'title' => $title,
        );
    }
    public function prepareMailContent($model_id, $view_id, $request = null)
    {
        $email_model = Emailmodel::where('id', $model_id)->orWhere('code', $model_id)->firstOrFail();
        $custom_content = $request ? $request->custom_content : $email_model->custom_content;
        $custom_header = $request ? $request->custom_header : $email_model->custom_header;
        $custom_footer = $request ? $request->custom_footer : $email_model->custom_footer;

        if ($email_model->view_table) {
            if($email_model->view_table="vw_email_tasks_details"){
                $task = Task::find($view_id);
                if($task){
                    // Get the values for date_debut and titre_pf
                    $date_debut = $task->start_date; // Example date value
                    $titre = $task->title;
                    $titre_af = ""; // Example titre_pf value
                    $titre_pf = ""; // Example titre_pf value
                    $type="";
                    $gender_sup="";
                    $nom_sup="";
                    $gender_resp="";
                    $nom_resp="";
                    $date_fin="";
                    $mod_rep="";
                    // Replace the placeholders with the actual values
                    $custom_content = str_replace('{date_debut}', $date_debut, $custom_content);
                    $custom_content = str_replace('{titre}', $titre, $custom_content);
                    $vars = DB::select('SHOW COLUMNS FROM ' . $email_model->view_table);
                    $vars = array_map(function ($v) {
                        return "{{$v->Field}}";
                    }, $vars);
    
                    $varsReplace = array_values((array) DB::table($email_model->view_table)->find($view_id));
    
                    $custom_content = str_replace($vars, $varsReplace, $custom_content);
                    $custom_header = str_replace($vars, $varsReplace, $custom_header);
                    $custom_footer = str_replace($vars, $varsReplace, $custom_footer);
                }
            }else{
                 $vars = DB::select('SHOW COLUMNS FROM ' . $email_model->view_table);
                $vars = array_map(function ($v) {
                    return "{{$v->Field}}";
                }, $vars);

                $varsReplace = array_values((array) DB::table($email_model->view_table)->find($view_id));

                $custom_content = str_replace($vars, $varsReplace, $custom_content);
                $custom_header = str_replace($vars, $varsReplace, $custom_header);
                $custom_footer = str_replace($vars, $varsReplace, $custom_footer);
            }
           
        }

        return [
            'content' => $custom_content,
            'header' => $custom_header,
            'footer' => $custom_footer,
            'subject' => $email_model->name,
        ];
    }


    public function storeUserAccountPersonne($request, $role, $host = 'http://solaris-crfpe.fr')
    {
        $id = "";
        $result = [];
        $datas = [];
        if (!empty($request->email)) {
            $datas = User::latest()->where('login', $request->email)
                ->orWhere('email', $request->email);
        }
        $response = is_array($datas) ? $datas : $datas->get();

        $data = $request;
        $bytes = random_bytes(5);
        $newPassword = bin2hex($bytes);
        $data["password"] = $newPassword;
        $data["login"] = $request->email;
        $data["active"] = 1;

        if (count($response) > 0) {
            $success = false;
            $msg = 'Cette adresse mail ' . $request->email . ' est déjà utilisé pour un autre compte';
        } else {
            if (!empty($request->email)) {
                $DbHelperTools = new DbHelperTools();
                try {
                    $data_array = $data->toArray();
                    $data_array['id'] = 0;
                    $user_id = $DbHelperTools->manageUserFromAf($data_array, $data->id);
                    //On supprime si exist
                    $DbHelperTools->detachRolesUser($user_id);
                    //Add roles
                    if (!empty($role)) {
                        $DbHelperTools->attachUserRoles($user_id, $role);
                    }
                    $success = true;
                    $msg = 'L\'utilisateur de cette adresse mail ' . $request->email . ' a été enregistrée avec succès';

                    /* Mail sending */
                    $user = User::find($user_id);
                    $fullname = ucfirst($user->name ?? '') . ' ' . ucfirst($user->lastname ?? '');
                    $content = "Bonjour $fullname,<br/>Votre nouveau compte a été créé sur notre plateforme CRFPE<br/>";
                    $content .= "Vous pouvez vous connecter dès maintenant sur <a href='$host/login'>CE SITE</a>.<br/>";
                    $content .= "<b>Identifiant: {$request->email}</b><br/>";
                    $content .= "<b>Mot de passe: $newPassword</b><br/><br/>";
                    $content .= "Une fois conneté, <b>nous vous recommandons de changer le mot de passe</b>, en allant sur <a href='$host/getnewpassword'>La page de réinitialisation</a><br/>";
                    $header = "Environnement de formation pour CRFPE";
                    $footer = "Plateforme de formation SOLARIS";
                    
                    Mail::send('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer], function ($m) use ($request, $fullname) {
                        $m->from(auth()->user()->email);
                        $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                        $m->to($request->email, $fullname)->subject('Création de votre compte sur CRFPE');
                    });
                } catch (Exception $e) {
                    $success = false;
                    $msg = $request->email . ': Erreur Inconnue.';
                }
            } else {
                $success = false;
                $msg = 'L\'adresse mail de ' . $request->firstname . ' ' . $request->lastname . ' n\'existe pas';
            }
        }


        $result = [
            'success' => $success,
            'msg' => $msg,
        ];
        return $result;
    }
}
