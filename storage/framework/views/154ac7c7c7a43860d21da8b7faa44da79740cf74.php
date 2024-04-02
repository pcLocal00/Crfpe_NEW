<style>
    #objet{
        padding: .5rem;
        border: 1px solid #b7b1b142;
        border-radius: .375rem;
        line-height: 1rem;
    }
</style>

<?php if(auth()->user()->roles[0]->code!='FORMATEUR'): ?>  
    <div class="accordion accordion-solid accordion-toggle-plus mb-4" id="accordionFiltrage<?php echo e($type); ?>">
        <div class="card">
            <?php if($type!='SessionsGrid'): ?>
            <div class="card-header" id="headingOne5">
                <div class="card-title" data-toggle="collapse" data-target="#collapseFiltrage<?php echo e($type); ?>">
                    <i class="flaticon-search"></i> Filtrages
                </div>
            </div>
            <?php endif; ?>
            <div id="collapseFiltrage<?php echo e($type); ?>" class="collapse show" data-parent="#accordionFiltrage<?php echo e($type); ?>">
                <div class="<?php echo e(($type!='SessionsGrid'?'card-body':'')); ?>">
                    <!--begin::form-->
                    <form id="formFilter<?php echo e($type); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="filter" value="1">
                        <?php
                        $dtNow = Carbon\Carbon::now();
                        $newDateTime = Carbon\Carbon::now()->subMonth();
                        //$start_date=$newDateTime->format('d/m/Y');
                        //$end_date =$dtNow->format('d/m/Y');
                        $start_date=$end_date='';
                        ?>
                        <!-- begin::filter Import -->
                        <?php if($type=='Import'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6 mb-lg-0 mb-6">
                                <label>Fichier:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="file_imported" id="file_imported">
                                    <option value="">Tous les fichier</option>
                                    <?php if($datafilter->files): ?>
                                    <?php foreach ($datafilter->files as $file): ?>
                                        <option value="<?php echo e($file->id); ?>"><?php echo e($file->name); ?></option>
                                    <?php endforeach ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::filter Import -->

                        <!-- begin::filter clients -->
                        <?php if($type=='Clients'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Référence, Nom, ..." data-col-index="0" />
                            </div>

                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        placeholder="Du" data-col-index="5" autocomplete="off" value="<?php echo e($start_date); ?>" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>

                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Type:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_type">
                                    <option value="">Tous</option>
                                    <option value="P">Particulier</option>
                                    <option value="S">Société</option>
                                </select>
                            </div>

                        </div>

                        <div class="row mb-6">
                            <div class="col-lg-2 mb-lg-0 mb-6">
                                <label>Activation:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_activation">
                                    <option value="">Tous les clients</option>
                                    <option value="a">Activés</option>
                                    <option value="n">Non activés</option>
                                </select>
                            </div>
                            <div class="col-lg-2 mb-lg-0 mb-6">
                                <div class="checkbox-inline">
                                    <label class="checkbox"><input type="checkbox" name="filter_is_client"
                                            value="1"><span></span>Client</label>
                                </div>
                            </div>
                            <div class="col-lg-2 mb-lg-0 mb-6">
                                <div class="checkbox-inline">
                                    <label class="checkbox"><input type="checkbox" name="filter_is_funder"
                                            value="1"><span></span>Financeur</label>
                                </div>
                            </div>
                            <div class="col-lg-2 mb-lg-0 mb-6">
                                <div class="checkbox-inline">
                                    <label class="checkbox"><input type="checkbox" name="filter_is_former"
                                            value="1"><span></span>Formateur</label>
                                </div>
                            </div>
                            <div class="col-lg-2 mb-lg-0 mb-6">
                                <div class="checkbox-inline">
                                    <label class="checkbox"><input type="checkbox" name="filter_is_stage_site"
                                            value="1"><span></span>Terrain de stage</label>
                                </div>
                            </div>
                            <div class="col-lg-2 mb-lg-0 mb-6">
                                <div class="checkbox-inline">
                                    <label class="checkbox"><input type="checkbox" name="filter_is_prospect"
                                            value="1"><span></span>Prospect</label>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::filter clients -->

                        <!-- begin::filter contacts -->
                        <?php if($type=='Contacts'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Référence, Nom, ..." data-col-index="0" />
                            </div>

                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        placeholder="Du" data-col-index="5" autocomplete="off" value="<?php echo e($start_date); ?>" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>

                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Type:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_type">
                                    <option value="">Tous</option>
                                    <option value="P">Particulier</option>
                                    <option value="S">Société</option>
                                </select>
                            </div>

                        </div>

                        <div class="row mb-6">
                            <div class="col-lg-2 mb-lg-0 mb-6">
                                <label>Activation:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_activation">
                                    <option value="">Tous les clients</option>
                                    <option value="a">Activés</option>
                                    <option value="n">Non activés</option>
                                </select>
                            </div>

                        </div>
                        <?php endif; ?>
                        <!-- end::filter contacts -->

                        <!--begin::users-->
                        <?php if($type=='Users'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Identifiant, Nom, Email ..." data-col-index="0" />
                            </div>

                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>

                            <div class="col-lg-4 mb-lg-0 mb-6">
                                <label>Activation:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_activation">
                                    <option value="">Tous les utilisateurs</option>
                                    <option value="a">Activés</option>
                                    <option value="n">Non activés</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--end::users-->

                        <!--begin::users-->
                        <?php if($type=='Stages'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6 mb-lg-0 mb-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Période ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6 mb-lg-0 mb-6">
                                <label>Période:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div> 
                        <?php endif; ?>
                        <!--end::users-->
                        <!--begin::users-->
                        <?php if($type=='Presences'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>AF <span id="LOADER_AFS"></span><span class="text-danger">*</span></label>
                                    <input type="hidden" id="selected_af_id" value = 0>
                                    <input type="hidden" id="default_af_id" value = 0>
                                    <select id="afsSelectEstimate" name="af_id" class="form-control form-control-sm select2" onchange="_loadDatasMembresForSelect()" required>
                                        <option value="">Sélectionnez</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label>Groupe :</label>
                                <select class="form-control" data-col-index="2" id="groupesSelectFilter"
                                    name="group_id" style="width:100%;" onchange="_loadDatasMembresForSelect()">
                                </select>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-6 mb-lg-0 mb-6">
                                <label>Étudiants :</label>
                                <select class="form-control" id="membersSelectFilter" name="member_id" style="width:100%;">
                                    <option value="0">Tous les étudiants</option>
                                    <?php $__currentLoopData = $datafilter->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($member->id); ?>"><?php echo e($member->contact->firstname. ' ' . $member->contact->lastname); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-lg-6 mb-lg-0 mb-6">
                                <label>Période:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--end::users-->
                        <!--begin::users-->
                        <?php if($type=='StageProposals'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-4 mb-6">
                                <label>Période de stage: <span id="LOADER_PERIODES_FILTER"></span></label>
                                <select class="form-control" data-col-index="2" id="sessionsSelectStagePeriodsFilter" name="filter_session_id">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4 mb-6">
                                <label>Stagiaires : <span id="LOADER_STAGIAIRES_FILTER"></span></label>
                                <select class="form-control" data-col-index="2" id="membersSelectFilter" name="filter_member_id">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4 mb-6">
                                <label>Etat:</label>
                                <select class="form-control" data-col-index="2" id="statesSelect" name="filter_state">
                                    <option value="">Tous</option>
                                    <option value="draft">Brouillant</option>
                                    <option value="approuved">A approuver</option>
                                    <option value="invalid">Stage non validée</option>
                                    <option value="validated">Stage validée</option>
                                    <option value="imposed">Stage imposée</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--end::users-->

                        <!--begin::Formations-->
                        <?php if($type=='Formations'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Titre, Desc ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-4">
                                <label>Statut:</label>
                                <select class="form-control datatable-input" data-col-index="2" id="statusSelect"
                                    name="filter_status">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Etat:</label>
                                <select class="form-control datatable-input" data-col-index="7" id="statesSelect"
                                    name="filter_state">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Type:</label>
                                <select class="form-control datatable-input" data-col-index="2" id="typesSelect"
                                    name="filter_type">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-8">
                            <div class="col-lg-12">
                                <div class="accordion accordion-solid accordion-toggle-plus" id="accordionCatalogue">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="card-title collapsed" data-toggle="collapse"
                                                data-target="#collapseCatalogue">
                                                <i class="flaticon-map"></i> Catalogue
                                            </div>
                                        </div>
                                        <div id="collapseCatalogue" class="collapse" data-parent="#accordionCatalogue">
                                            <div class="card-body">
                                                <!--begin::tree-->
                                                <div id="categories_tree" class="tree-demo"></div>
                                                <!--end::tree-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php endif; ?>
                        <!--begin::Formations-->

                        <!--begin::Params-->
                        <?php if($type=='Params'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Type, Code, Paramétrage ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Type de paramétrage</label>
                                    <?php
                                    $paramCodes = Config::get('params.params');
                                    ?>
                                    <select class="form-control" name="filter_param_code">
                                        <option value="">Tous les paramétrages</option>
                                        <?php if($paramCodes): ?>
                                        <?php $__currentLoopData = $paramCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($code['code']); ?>"><?php echo e($code['name']); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label>Activation:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_activation">
                                    <option value="">Tous les paramétrages</option>
                                    <option value="a">Activés</option>
                                    <option value="n">Non activés</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--end::Params-->

                        <!-- BEGIN: AFs -->
                        <?php if($type=='Afs'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Titre ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-4">
                                <label>Type dispositif:</label>
                                <select class="form-control datatable-input" data-col-index="2" id="typesDispositifSelect"
                                    name="filter_device_type">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Etat:</label>
                                <select class="form-control datatable-input" data-col-index="7" id="statesSelect"
                                    name="filter_state">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Statut:</label>
                                <select class="form-control datatable-input" data-col-index="2" id="statusSelect"
                                    name="filter_status">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-4">
                                <label>Activation:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_activation">
                                    <option value="">Tous</option>
                                    <option value="a">Activés</option>
                                    <option value="n">Non activés</option>
                                </select>
                            </div>
                            <div class="col-lg-8">
                                <label>Produit:</label>
                                <select class="form-control" data-col-index="2" id="pfFormationsSelect"
                                    name="filter_formation_id">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--END:AFs  -->

                        <!-- BEGIN: Sessions Grid-->

                        <?php if($type=='SessionsGrid'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-5">
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Titre , Description..." data-col-index="0" />
                            </div>
                            <div class="col-lg-5">
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <button type="submit" class="btn btn-sm btn-outline-primary btn-outline-primary--icon">
                                    <span>
                                        <i class="la la-search"></i>
                                        <?php if($type!='SessionsGrid'): ?><span>Filtrer</span><?php endif; ?>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--END:SessionsGrid -->


                        <!-- BEGIN: Sessions -->
                        <?php if($type=='Sessions'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Titre , Description..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de début:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>

                        
                        <?php endif; ?>
                        <!--END:Sessions  -->


                        <!-- BEGIN::PTemplates -->
                        <?php if($type=='PTemplates'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Titre ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-4">
                                <label>Activation:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_activation">
                                    <option value="">Tous</option>
                                    <option value="a">Activés</option>
                                    <option value="n">Non activés</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- END::PTemplates -->
                        <!-- begin::Prices -->
                        <?php if($type=='Prices'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Titre ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::Prices -->

                        <!-- begin::Convocations -->
                        <?php if($type=='Convocations'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="N°, Stagiaire, AF ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::Convocations -->

                        <!-- begin::Ressources -->
                        <?php if($type=='Ressources'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Nom ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-4">
                                <label>Type de ressource:</label>
                                <select class="form-control datatable-input" data-col-index="2" id="typesSelect"
                                    name="filter_type">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Activation:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_activation">
                                    <option value="">Tous</option>
                                    <option value="a">Activés</option>
                                    <option value="n">Non activés</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Disponibilité:</label>
                                <select class="form-control datatable-input" data-col-index="2" name="filter_dispo">
                                    <option value="">Tous</option>
                                    <option value="d">Disponible</option>
                                    <option value="n">Non disponible</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::Ressources -->

                        <!-- begin::Estimates -->
                        <?php if($type=='Estimates'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Nom ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::Estimates -->
                        <!-- begin::Estimates -->
                        <?php if($type=='task'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Résumé, description ..." data-col-index="0" />
                            </div>

                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>    
                        </div>

                        <div class="row mb-6">
                            <div class="col-lg-6">
                                    <label>Date de fin:</label>
                                    <div class="input-daterange input-group" id="filter_datepicker">
                                        <input type="text" class="form-control datatable-input" name="debut_end"
                                            value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-ellipsis-h"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control datatable-input" name="fin_end"
                                            value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                    </div>    
                                </div>

                                <div class="col-lg-6">
                                    <label>Date de rappel:</label>
                                    <div class="input-daterange input-group" id="filter_datepicker">
                                        <input type="text" class="form-control datatable-input" name="filter_rappel"
                                            value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-ellipsis-h"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control datatable-input" name="end_rappel"
                                            value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                    </div>    
                                </div>
                            </div>

                        <div class="row mb-6">
                            <div class="col-lg-4">
                                <label>État:</label>
                                <select class="form-control datatable-input" data-col-index="2" id="etatSelect"
                                    name="filter_etat">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Type:</label>
                                <select class="form-control datatable-input" data-col-index="7" id="typeSelect"
                                    name="filter_type">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Source:</label>
                                <select class="form-control datatable-input" data-col-index="2" id="sourceSelect"
                                    name="filter_source">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-4">
                                <label>Objet:</label>
                                <div class="checkbox-inline" id="objet">
										<label class="checkbox">
										<input type="checkbox"  name="filter_objet[]" value="af">
										<span></span>AF</label>
										<label class="checkbox">
										<input type="checkbox"  name="filter_objet[]" value="pf">
										<span></span>PF</label>
									</div>
                            </div>
                            <div class="col-lg-4">
                                <label>Responsable:</label>
                                <select class="form-control datatable-input" data-col-index="7" id="responsableSelect"
                                    name="filter_responsable">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Filtrage:</label>
								<span class="switch switch-success switch-sm">
                                    <span class="mr-2"><b>Toutes les tâches</b></span>
                                    <label>
                                        <input type="checkbox" name="only_my_tasks"/>
                                        <span></span>
									</label>
                                    <span class="ml-2"><b>Mes tâches</b></span>
								</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::Estimates -->
                        <!-- begin::Estimates -->
                        <?php if($type=='Agreements'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Code, Nom ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date de création:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::Estimates -->

                        <!-- begin::ControlePay -->
                        <?php if($type=='ControlePay'): ?>
                        <?php
                        $arrayDates=\App\Library\Helpers\Helper::getFilterControlePay();
                        $start_filter=$arrayDates['start'];
                        $end_filter=$arrayDates['end'];
                        ?>

                        <div class="row mb-6">
                            <div class="col-lg-12">
                                <label>Période:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        id="filter_start" value="<?php echo e($start_filter); ?>" placeholder="Du" data-col-index="5"
                                        autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        id="filter_end" value="<?php echo e($end_filter); ?>" placeholder="Au" data-col-index="5"
                                        autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::ControlePay -->

                        <!-- begin::ControlInvoices -->
                        <?php if($type=='ControlInvoices'): ?>
                        <?php
                        $arrayDates=\App\Library\Helpers\Helper::getFilterControleInvoices();
                        $start_filter=$arrayDates['start'];
                        $end_filter=$arrayDates['end'];
                        $acountingCodes=\App\Library\Helpers\Helper::getAcountingCodesInInvoices();
                        $clients=\App\Library\Helpers\Helper::getclients();
                        ?>

                        <div class="row mb-6">
                            <div class="col-lg-4">
                                <label>Code comptable:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text" placeholder="Code comptable" data-col-index="0" />
                            </div>
                            <div class="col-lg-8">
                                <label>Période:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        id="filter_start" value="<?php echo e($start_filter); ?>" placeholder="Du" data-col-index="5"
                                        autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        id="filter_end" value="<?php echo e($end_filter); ?>" placeholder="Au" data-col-index="5"
                                        autocomplete="off" />
                                </div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-lg-12">
                                <label>Client:</label>
                                <select class="form-control select2" data-col-index="2" id="clientsSelect"
                                    name="filter_entitie_id">
                                    <option value="">Tous</option>
                                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cl->id); ?>"><?php echo e($cl->name); ?> <?php echo e($cl->ref); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <p>Liste des codes comptables : </p>
                        <div class="form-group row mb-6">
                            <div class="col-12 col-form-label">
                                <div class="checkbox-inline">
                                    <?php $__currentLoopData = $acountingCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="checkbox-inline mr-2">
                                        <label class="checkbox checkbox-outline checkbox-success">
                                            <input type="checkbox" name="filter_accounting_codes[]" value="<?php echo e($code); ?>"/>
                                            <span></span>
                                            <?php echo e($code); ?>

                                        </label>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>                                
                                
                        <?php endif; ?>
                        <!-- end::ControlInvoices -->

                        <!-- begin::Invoices -->
                        <?php if($type=='Invoices'): ?>
                            <?php
                            $start_filter='';
                            $end_filter='';
                            $clients=\App\Library\Helpers\Helper::getclients();
                            ?>
                            <div class="row mb-6">
                                <div class="col-lg-4">
                                    <label>Filtre textuel:</label>
                                    <input type="text" class="form-control datatable-input" name="filter_text" placeholder="Numéro..." data-col-index="0" />
                                </div>
                                <div class="col-lg-8">
                                    <label>Date de facturation:</label>
                                    <div class="input-daterange input-group" id="filter_datepicker">
                                        <input type="text" class="form-control datatable-input" name="filter_start"
                                            id="filter_start" value="<?php echo e($start_filter); ?>" placeholder="Du" data-col-index="5"
                                            autocomplete="off" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-ellipsis-h"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control datatable-input" name="filter_end"
                                            id="filter_end" value="<?php echo e($end_filter); ?>" placeholder="Au" data-col-index="5"
                                            autocomplete="off" />
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <div class="col-lg-12">
                                    <label>Client:</label>
                                    <select class="form-control select2" data-col-index="2" id="clientsSelect"
                                        name="filter_entitie_id">
                                        <option value="">Tous</option>
                                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($cl->id); ?>"><?php echo e($cl->name); ?> <?php echo e($cl->ref); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- end::Invoices -->

                        <!-- begin::Avoirs -->
                        <?php if($type=='Avoirs'): ?>
                            <?php
                            $start_filter='';
                            $end_filter='';
                            $clients=\App\Library\Helpers\Helper::getclients();
                            ?>
                            <div class="row mb-6">
                                <div class="col-lg-4">
                                    <label>Filtre textuel:</label>
                                    <input type="text" class="form-control datatable-input" name="filter_text" placeholder="Numéro..." data-col-index="0" />
                                </div>
                                <div class="col-lg-8">
                                    <label>Date Avoirs:</label>
                                    <div class="input-daterange input-group" id="filter_datepicker">
                                        <input type="text" class="form-control datatable-input" name="filter_start"
                                            id="filter_start" value="<?php echo e($start_filter); ?>" placeholder="Du" data-col-index="5"
                                            autocomplete="off" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-ellipsis-h"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control datatable-input" name="filter_end"
                                            id="filter_end" value="<?php echo e($end_filter); ?>" placeholder="Au" data-col-index="5"
                                            autocomplete="off" />
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <div class="col-lg-12">
                                    <label>Client:</label>
                                    <select class="form-control select2" data-col-index="2" id="clientsSelect"
                                        name="filter_entitie_id">
                                        <option value="">Tous</option>
                                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($cl->id); ?>"><?php echo e($cl->name); ?> <?php echo e($cl->ref); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- end::Avoirs -->

                        <!--begin::Params-->
                        <?php if($type=='Helpindexes'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <label>Filtre textuel:</label>
                                <input type="text" class="form-control datatable-input" name="filter_text"
                                    placeholder="Type, Index ..." data-col-index="0" />
                            </div>
                            <div class="col-lg-6">
                                <label>Date :</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                        value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                        value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Type d'indexe :</label>
                                    <?php
                                    $typesCodes = Config::get('params.types_indexes');
                                    ?>
                                    <select class="form-control" name="filter_index_code">
                                        <option value="">Tous les types</option>
                                        <?php if($typesCodes): ?>
                                        <?php $__currentLoopData = $typesCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($code['code']); ?>"><?php echo e($code['name']); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--end::Params-->   
                        
                        <!-- begin::Certifications -->
                        <?php if($type=='Cert'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6 mb-lg-0 mb-6">
                                <label>Période:</label>
                                <select class="form-control" data-col-index="2" id="statesSelect" name="filter_periode">
                                    <option value="">Tous</option>
                                    <?php if($datafilter->periodes): ?>
                                    <?php foreach ($datafilter->periodes as $ts): ?>
                                        <option value="<?php echo e($ts->id); ?>"><?php echo e($ts->name); ?></option>
                                    <?php endforeach ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-lg-6 mb-lg-0 mb-6">
                                <label>Dates:</label>
                                <div class="input-daterange input-group" id="filter_datepicker">
                                    <input type="text" class="form-control datatable-input" name="filter_start"
                                    value="<?php echo e($start_date); ?>" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="filter_end"
                                    value="<?php echo e($end_date); ?>" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end::Certifications -->
                        <!--begin::users-->
                        <?php if($type=='CertGroups'): ?>
                        <div class="row mb-6">
                            <div class="col-lg-6 mb-6">
                                <label>Groupe: <span id="LOADER_PERIODES_FILTER"></span></label>
                                <select class="form-control" data-col-index="2" id="CertificationSelectGroupFilter" name="filter_group_id">
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--end::users-->
                        
                        <?php if($type!='SessionsGrid'): ?>
                        <div class="row">
                            <div class="col-lg-8"></div>
                            <div class="col-lg-4">
                                <div class="float-right">
                                    <!-- <button type="submit" onclick="_export()" class="btn btn-sm btn-outline-primary btn-outline-primary--icon">
                                        <span>
                                            <i class="la la-search"></i>
                                            <span>Exporter</span>
                                        </span>
                                    </button> -->
                                    <div class="dropdown dropdown-inline mr-2">
                                        <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="la la-download"></i> Télécharger</button>
                                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                            <ul class="navi flex-column navi-hover py-2">
                                                <li class="navi-item">
                                                    <a href="#" onclick="downloadExcel()" class="navi-link" >
                                                        <span class="navi-icon">
                                                            <i class="la la-file-excel-o"></i>
                                                        </span>
                                                        <span class="navi-text">Excel</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <button type="reset" onclick="_reset()"
                                        class="btn btn-sm btn-outline-danger btn-outline-danger--icon">
                                        <span>
                                            <i class="la la-close"></i>
                                            <span>Réinitialiser</span>
                                        </span>
                                    </button>
                                    <button type="submit" id="filtrecontrole" class="btn btn-sm btn-outline-primary btn-outline-primary--icon">
                                        <span>
                                            <i class="la la-search"></i>
                                            <span>Filtrer</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <?php if($type=='Presences'): ?>
                                <div class="col-lg-12">
                                    <div class="membre-state-edit" style="width: fit-content; margin:0 auto;">
                                        <button type="button" class="btn btn-success" onclick='editState("present")'><i class="far fa-check-square"></i>Present</button>
                                        <button style="margin-left: 15px" type="button" class="btn btn-danger" onclick='editState("Absent non justifié")'><i class="fas fa-times"></i>Absent non justifié</button>
                                        <button style="margin-left: 15px" type="button" class="btn btn-warning" onclick='editState("Absent justifié")'><i class="fas fa-battery-quarter"></i>Absent justifié</button>
                                        <button style="margin-left: 15px" type="button" class="btn btn-light" onclick='editState("not_pointed")'><i class="fas fa-ban"></i>Non renseigné</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </form>
                    <!--end::form-->
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<script>


function downloadExcel() {

    // const selectedLis = document.querySelectorAll('li a[aria-selected="true"]');
    // const selectedIds = Array.from(selectedLis).map(li => li.id);
    // console.log(selectedIds);

    // const selectedAs = document.querySelectorAll('li > a[aria-selected="true"]');
    // const selectedIds = Array.from(selectedAs).map(a => a.parentNode.id);
    // console.log(selectedIds);

    // const selectedAs = document.querySelectorAll('li > a[aria-selected="true"]');
    // const selectedLis = Array.from(selectedAs).map(a => {
    // let parentLi = a.parentNode;
    // while (parentLi && parentLi.nodeName !== 'LI') {
    //     parentLi = parentLi.parentNode;
    // }
    // return parentLi;
    // });
    // const selectedIds = selectedLis.map(li => li.id);
    // console.log(selectedIds);




    // const selectedAs = document.querySelectorAll('li > a.jstree-clicked');
    // const selectedIds = Array.from(selectedAs).map(a => a.parentNode.id);
    // console.log(selectedIds);



    // const selectedAs = document.querySelectorAll('li > a.jstree-clicked');
    // const selectedIds = Array.from(selectedAs).map(a => {
    // const icon = a.querySelector('i.jstree-undetermined');
    // return {id: a.parentNode.id, icon: icon};
    // });
    // console.log(selectedIds);



    // const selectedLis = document.querySelectorAll('li[aria-selected="true"]');
    // const selectedIds = Array.from(selectedLis).map(li => {
    // const rootNode = li.closest('li');
    // return rootNode ? rootNode.id : li.id;
    // });
    // console.log(selectedIds);


    // const selectedNodes = document.querySelectorAll('li.jstree-node > ul > li[aria-selected="true"], li.jstree-node > ul > li.jstree-clicked');
    // const selectedIds = Array.from(selectedNodes).map(li => li.parentNode.parentNode.id);
    // console.log(selectedIds);


    // const selectedNodes = document.querySelectorAll('li.jstree-node > ul > li[aria-selected="true"], li.jstree-node > ul > li.jstree-clicked');
    // const selectedIds = Array.from(selectedNodes).map(li => {
    //     console.log(li);
    // let parentId = li.closest('li.jstree-node').id;
    // let childId = li.id;
    // return { parentId, childId };
    // });
    // console.log(selectedIds);


    // Select the parent <ul> element
    var parentUl = $('ul.jstree-container-ul');

    // Find all child <li> elements
    var childLis = parentUl.find('li');

    // Loop through each child <li> element
    childLis.each(function() {
    // Select the parent <li> element of the child
    var parentLi = $(this).parent().closest('li');

    // Do something with the parent and child <li> elements
    console.log('Parent:', parentLi.text(), 'Child:', $(this).text());
    });




    var formData = $('#formFilterPresences').serializeArray();
    // console.log(formData);


    function _initJsTreePlanning(api_url, data) {
        var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
        $("#tree_schedulecontacts").html(spinner);
        $.ajax({
            type: 'POST',
            url: api_url + data[3]["value"],
            data: data,
            //async: false,
            dataType: 'json',
            success: function(json, status) {
                _createJSTree(json);
            },
            error: function(error) {},
            complete: function(resultat, statut) {

            }
        });
    }
    function _initJsTreePlanning1(api_url, data) {
        var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
        $("#tree_schedulecontacts").html(spinner);
        $.ajax({
            type: 'POST',
            url: api_url + data[3]["value"],
            data: data,
            //async: false,
            dataType: 'json',
            success: function(json, status) {
                _createJSTree(json);
                $.ajax({
                    type: 'GET',
                    url: '/api/presences/schedules/exportget',
                    xhrFields: {
                    responseType: 'blob'
                    },
                    success: function(blob) {
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'list.xlsx';
                    link.click();
                    },
                    error: function(error) {},
                    complete: function(resultat, statut) {}
                });
            },
            error: function(error) {},
            complete: function(resultat, statut) {

            }
        });
    }

    _initJsTreePlanning('/api/presences/schedules/', formData);
    _initJsTreePlanning1('/api/presences/schedules/export/', formData); 

    }
</script>
<?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/components/filter-form.blade.php ENDPATH**/ ?>