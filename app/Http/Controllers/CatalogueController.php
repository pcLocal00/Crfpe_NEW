<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Formation;
use Illuminate\Http\Request;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use Illuminate\Support\Facades\Validator;

class CatalogueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     */
    public function list()
    {
        $page_title = 'Paramétrage de l\'arborescence des produits de formation';
        $page_description = '';
        return view('pages.catalogue.list', compact('page_title', 'page_description'));
    }
    public function srcCatalogues($categorie_id,$with_trashed) {
        if($with_trashed==1){
            $categories = Categorie::withTrashed();
        }else{
            $categories=Categorie::select('*')->orderBy('order_show')->get();
        }
        //$categories->orderByDesc('order_show')->get();
        $datas = [];
            foreach($categories as $c){
                $fa = 'folder';
                $classCss = 'success';
                $labelIsActif = '';  
                if($c->is_active==0){
                    $classCss = 'danger';
                    $labelIsActif = '<span class="label label-sm label-'.$classCss.' label-inline mr-2">Non actif</span>';
                }
				
                $disabled_select=false;
                if($categorie_id==$c->id){
                    $disabled_select=true;
                }

				if ($c->categorie_id > 0) {
					$fa = 'file';
				}
                $icon="fa fa-".$fa." text-".$classCss;
                
                if($with_trashed == 1 && $c->deleted_at){
                    $disabled_select=true;
                }
                
                if($categorie_id>0){
                    $text=$c->name;
                }else {
                    $tools=new PublicTools();
                    $nb_formations = $c->formations->count();
                    
                    $nameText='<a style="cursor: pointer;" class="jstree-anchor mr-2" onclick="_formCategorie('.$c->id.')">'.$c->name.'</a>';
                    $btnEdit = '<a style="cursor: pointer;" class="mr-2" onclick="_formCategorie('.$c->id.')" title="Editer ce niveau"><i class="'.$tools->getIconeByAction('EDIT').' text-primary"></i></a>';

                    $btnDelete = $labelInfo = '';
                    if($nb_formations>0){
                        $labelInfo = '<span class="label label-sm label-info label-inline mr-2">'.$nb_formations.' PF</span>'; 
                    }else{
                        $btnDelete = '<a style="cursor: pointer;" class="mr-2" onclick="_deleteCategorie('.$c->id.')" title="Supprimer ce niveau"><i class="'.$tools->getIconeByAction('DELETE').' text-danger"></i></a>';
                    }

                    $btn_archive=$btn_unarchive='';
                    if($c->deleted_at!=null){
                        $btn_unarchive='<a style="cursor: pointer;" class="mr-2" onclick="_unarchiveCategorie('.$c->id.')" title="Désarchiver ce niveau"><i class="'.$tools->getIconeByAction('UNARCHIVE').' text-warning"></i></a>';
                        //un grain archivé ne devrait plus être modifiable.
                        $btnEdit = '';
                    }else{
                        $btn_archive='<a style="cursor: pointer;" class="mr-2" onclick="_archiveCategorie('.$c->id.')" title="Archiver ce niveau"><i class="'.$tools->getIconeByAction('ARCHIVE').' text-warning"></i></a>';
                    }
                    $text=$nameText.$btnEdit.$btnDelete.$btn_archive.$btn_unarchive.$labelIsActif.$labelInfo;
                }
                $datas [] = array (
                    "id" => $c->id,
                    "text" => $text,
                    "state" => array('opened'=>false,'disabled'=>$disabled_select),
                    "icon" => $icon,
                    "parent" => ($c->categorie_id>0)?$c->categorie_id:'#' 
                );
                //show pf
                if($with_trashed==2){
                    foreach ($c->formations as $pf) {
                        $datas [] = array (
                            "id" => 'pf'.$pf->id,
                            "text" => $pf->title.' ('.$pf->code.')',
                            "state" => array('opened'=>false,'disabled'=>false),
                            "icon" => "fa fa-check text-warning",
                            "parent" => $c->id 
                        );
                    }
                }        
            }
        
        return response()->json($datas);
	}
    public function formCategorie($row_id)
    {
        $row = null;
        $default_order_show = $parent_id = 0;
        if ($row_id > 0) {
          //$row = Categorie::findOrFail ( $row_id );
          $row = Categorie::withTrashed()->where('id',$row_id )->first();
          $parent_id = $row->categorie_id;
        }
        $DbHelperTools=new DbHelperTools();
        $default_order_show = $DbHelperTools->generateOrderShowForCategorie($parent_id);
        return view('pages.catalogue.form',['row'=>$row,'default_order_show'=>$default_order_show]);
    }
    public function storeFormCategorie(Request $request)
    {
        $success = false;
        $msg = '';
        $data=$request->all();
        $rules = [
            'code' => ($data['id']>0)?'required':'required|unique:App\Models\Categorie',
        ];
        $messages = [
            'code' => 'Le code est unique',
        ];
        $validator = Validator::make($request->all(),$rules,$messages);
        if ($validator->fails()) {
            $success = false;
            $msg = 'Veuillez vérifier tous les champs';
        }else {
            $DbHelperTools=new DbHelperTools();
            $row_id = $DbHelperTools->manageCategories($data);
            if($row_id>0){
                $success = true;
                $msg = 'La catégorie a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    public function deleteCategorie($categorie_id){
        /**
         * forceDelete
         */
        $success = false;
        $DbHelperTools=new DbHelperTools();
        if($categorie_id){
            $deletedRows = $DbHelperTools->massDeletes([$categorie_id],'categorie',1);
            if($deletedRows>0){
                $success = true; 
            }
        }
        return response()->json(['success'=>$success]);
    }
    public function archiveCategorie(Request $request){
        /**
         * softDelete
         */
        $success = false;
        if ($request->isMethod('post')) {
            if ($request->has(['categorie_id','motif_text'])) {
                $DbHelperTools=new DbHelperTools();
                if($request->categorie_id>0){
                    $success = $DbHelperTools->archiveCategorieProcess($request->categorie_id,$request->motif_text);
                }
            }
        }
        return response()->json(['success'=>$success]);
    }
    public function unarchiveCategorie($categorie_id){
        $success = false;
        $DbHelperTools=new DbHelperTools();
        if($categorie_id){
            $success = $DbHelperTools->unarchiveCategorieProcess($categorie_id);
        }
        return response()->json(['success'=>$success]);
    }
    public function generateCodeForCategorie(Request $request){
        $code = '';
        if ($request->isMethod('post')) {
            if ($request->has(['categorie_id', 'parent_categorie_id','categorie_name'])) {
                $DbHelperTools=new DbHelperTools();
                $code = $DbHelperTools->generateCodeForCategorieProcess($request->all());
            }
        }
        return response()->json(['code'=>$code]); 
    }
    public function jsonCataloguesForFilter() {
        $categories=Categorie::all();
        $datas = [];
        if(count($categories)>0){
            foreach($categories as $c){
                $fa = 'folder';
				$classCss = 'info';

				if ($c->categorie_id > 0) {
					$fa = 'file';
				}
                $icon="fa fa-".$fa." text-".$classCss;
                $datas [] = array (
                    "id" => $c->id,
                    "text" => $c->name,
                    "state" => array('opened'=>false),
                    "icon" => $icon,
                    "parent" => ($c->categorie_id>0)?$c->categorie_id:'#' 
                );
            }
        }
        return response()->json($datas);
	}
    public function moveCategorie(Request $request)
    {
        $success = false;
        if ($request->isMethod('post')) {
            if ($request->has('node_id') && $request->has('node_parent')) {
                $row = Categorie::find ( $request->node_id );
                $parent_id = ($request->node_parent>0)?$request->node_parent:null;
                //change order
                if($request->has('position')){
                    $row->order_show = $request->position;
                }
                $row->categorie_id = $parent_id;
                $row->save ();
                if($row->id>0){
                    $success = true;
                }
            }
        }
        return response()->json(['success'=>$success]);
    }
    public function generateOrderShowForCategorie($parent_id){
        $DbHelperTools=new DbHelperTools();
        $order_show = $DbHelperTools->generateOrderShowForCategorie($parent_id);
        return response()->json(['order_show'=>$order_show]); 
    }
}
