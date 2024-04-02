@php
$readOnly='';
if($row){
    $readOnly=($row->is_synced_to_sage==1)?'readonly':'';
}
@endphp
<!-- begin::entity form -->
<input type="hidden" name="id" value="{{ ($row)?$row->id:0 }}" />
<input type="hidden" name="c_id" value="{{ ($mainContact)?$mainContact->id:0 }}" />
<input type="hidden" name="a_id" value="{{ ($mainAdresse)?$mainAdresse->id:0 }}" />

<!-- Les roles -->
<div class="form-group row">
    <div class="col-lg-3">
        <div class="checkbox-inline">
            @php
            $checked = ($row && $row->is_client===1)?'checked="checked"':'';
            @endphp
            <label class="checkbox">
                <input type="checkbox" value="1" name="is_client" {{ $checked }}>
                <span></span>Client</label>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="checkbox-inline">
            @php
            $checked = ($row && $row->is_funder===1)?'checked="checked"':'';
            @endphp
            <label class="checkbox">
                <input type="checkbox" value="1" name="is_funder" {{ $checked }}>
                <span></span>Financeur</label>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="checkbox-inline">
            @php
            $checked = ($row && $row->is_former===1)?'checked="checked"':'';
            @endphp
            <label class="checkbox">
                <input type="checkbox" value="1" name="is_former" {{ $checked }}>
                <span></span>Formateur</label>
        </div>
    </div>
    @if($entityType=="S")
    <div class="col-lg-3">
        <div class="checkbox-inline">
            @php
            $checked = ($row && $row->is_stage_site===1)?'checked="checked"':'';
            @endphp
            <label class="checkbox">
                <input type="checkbox" value="1" name="is_stage_site" {{ $checked }}>
                <span></span>Terrain de stage</label>
        </div>
    </div>
    @endif
    <div class="col-lg-3">
        <div class="checkbox-inline">
            @php
            $checked = ($row && $row->is_prospect===1)?'checked="checked"':'';
            @endphp
            <label class="checkbox">
                <input type="checkbox" value="1" name="is_prospect" {{ $checked }}>
                <span></span>Prospect</label>
        </div>
    </div>
</div>
<!-- Les roles -->

<div class="form-group row" id="BLOCK_TYPE_INTERVENSION_FORMER">
    <label class="col-6 col-form-label">Type d’intervention du formateur <span class="text-danger">*</span></label>
    <div class="col-6">
        <select class="form-control" name="c_type_former_intervention">
            <option value="">Sélectionner un type</option>
            @if($entityType=="S")
            <option value="Sur facture"
                {{ (($mainContact && $mainContact->type_former_intervention==='Sur facture')?'selected':'') }}>
                Sur facture</option>
            @endif
            @if($entityType=="P")
            <option value="Sur contrat"
                {{ (($mainContact && $mainContact->type_former_intervention==='Sur contrat')?'selected':'') }}>
                Sur contrat</option>
            <option value="Interne"
                {{ (($mainContact && $mainContact->type_former_intervention==='Interne')?'selected':'') }}>
                Interne</option>
            @endif
        </select>
    </div>
</div>


<div class="separator separator-dashed my-5"></div>

@if($entityType=="P")
<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label>Référence</label>
            <input type="text" class="form-control form-control-solid" name="ref" value="{{ ($row)?$row->ref:$ref }}"
                readonly />
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            @php
            $checkedIsActive = ($row && $row->is_active==1)?'checked="checked"':'';
            @endphp
            <div class="checkbox-inline">
                <label class="checkbox">
                    <input type="checkbox" value="1" name="is_active" id="c_is_active" {{ $checkedIsActive }}>
                    <span></span>Activé</label>
            </div>
        </div>
    </div>
</div>
<div class="separator separator-dashed my-5"></div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="genderSelects">Civilité</label>
            <select class="form-control " name="c_gender" id="genderSelects">
                <option {{ (($mainContact && $mainContact->gender==='M')?'selected':'') }} value="M">M</option>
                <option {{ (($mainContact && $mainContact->gender==='Mme')?'selected':'') }} value="Mme">Mme
                </option>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" class="form-control" name="c_firstname"
                value="{{ ($mainContact)?$mainContact->firstname:'' }}" />
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" name="c_lastname" id="c_lastname"
                value="{{ ($mainContact)?$mainContact->lastname:'' }}" />
        </div>
    </div>
</div>
<div class="separator separator-dashed my-5"></div>
<!-- Begin adresse -->

<div class="row">
    <div class="col-lg-12">
        <div class="form-group mb-2">
            <label for="line_1">Ligne 1<span class="text-danger">*</span></label>
            <textarea class="form-control" name="a_line_1" id="line_1" rows="3"
                required>{{ ($mainAdresse)?$mainAdresse->line_1:'' }}</textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="form-group mb-2">
            <label for="line_2">Ligne 2</label>
            <textarea class="form-control" name="a_line_2" id="line_2"
                rows="3">{{ ($mainAdresse)?$mainAdresse->line_2:'' }}</textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="form-group mb-2">
            <label for="line_3">Ligne 3</label>
            <textarea class="form-control" name="a_line_3" id="line_3"
                rows="3">{{ ($mainAdresse)?$mainAdresse->line_3:'' }}</textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Code postal </label>
            <div class="input-group">
                <input type="text" class="form-control" id="ZIPCODE" name="a_postal_code" value="{{ ($mainAdresse)?$mainAdresse->postal_code:'' }}" />
                <div class="input-group-append">
                    <button type="button" onclick="_call_api_to_search_cities()" data-toggle="tooltip"
                        title="Rechercher les villes" class="btn btn-icon btn-outline-primary"><span
                            id="BTN_SEARCH_CITIES"><i class="flaticon2-search"></i></span></button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Ville <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="a_city" value="{{ ($mainAdresse)?$mainAdresse->city:'' }}"
                required />
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="paysSelects">Pays</label>
            @php
            //$selected_country = ($mainAdresse && $mainAdresse->country==='fr')?'selected':'';
            $selected_country = ($mainAdresse)?$mainAdresse->country:'';
            @endphp
            <select class="form-control " name="a_country" id="paysSelects">
                <option value="0">Pays</option>
                @if(count($countriesDatas)>0)
                    @foreach($countriesDatas as $dt)
                        <option value="{{$dt['code']}}" {{($selected_country == $dt['code'])? 'selected' : '' }}>{{$dt['country']}}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
</div>
<!-- Begin adresse -->
<div class="separator separator-dashed my-5"></div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>email</label>
            <input type="email" class="form-control" name="c_email" id="c_email" value="{{ ($mainContact)?$mainContact->email:'' }}"
                />
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="functionSelects">Fonction</label>
            <input type="text" class="form-control" name="c_function"
                value="{{ ($mainContact)?$mainContact->function:'' }}" />
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Téléphone pro </label>
            <input type="tel" class="form-control" name="c_pro_phone"
                value="{{ ($mainContact)?$mainContact->pro_phone:'' }}" />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Portable pro </label>
            <input type="tel" class="form-control" name="c_pro_mobile"
                value="{{ ($mainContact)?$mainContact->pro_mobile:'' }}" />
        </div>
    </div>
    <div class="col-lg-8">
        <div class="form-group">
            <label>Date de naissance</label>
            <div class="input-group date">
                @php
                $birth_date ='';
                if($mainContact){
                if($mainContact->birth_date!=null){
                $dt = Carbon\Carbon::createFromFormat('Y-m-d',$mainContact->birth_date);
                $birth_date = $dt->format('d/m/Y');
                }
                }
                @endphp
                <input type="text" class="form-control" name="birth_date" id="dateofbirth_datepicker"
                    placeholder="Sélectionner une date" value="{{ $birth_date }}" autocomplete="off" />
                <div class="input-group-append">
                    <span class="input-group-text">
                        <i class="la la-calendar-check-o"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="form-group">
            <label>Zone de prospection</label>
            <input type="text" class="form-control" name="prospecting_area"
                value="{{ ($row)?$row->prospecting_area:'' }}" />
        </div>
    </div>
</div>
<div class="separator separator-dashed my-5"></div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="collective_customer_account">Compte collectif client </label>
            <input class="form-control " type="text" name="collective_customer_account"
                value="{{ ($row)?$row->collective_customer_account:'' }}" id="collective_customer_account" readonly/>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="auxiliary_customer_account">Compte client auxiliaire </label>
            <input class="form-control " type="text" name="auxiliary_customer_account"
                value="{{ ($row)?$row->auxiliary_customer_account:'' }}" id="auxiliary_customer_account" {{ $readOnly }}/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Code Matricule</label>
            <input type="text" class="form-control" name="matricule_code"
                value="{{ ($row)?$row->matricule_code:'' }}" />
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Code Tiers personnel</label>
            <input type="text" class="form-control" name="personal_thirdparty_code"
                value="{{ ($row)?$row->personal_thirdparty_code:'' }}" />
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Code Fournisseur</label>
            <input type="text" class="form-control" name="vendor_code" value="{{ ($row)?$row->vendor_code:'' }}" />
        </div>
    </div>
</div>


@elseif($entityType=="S")
<div class="form-group row">
    <div class="col-lg-12">
        <div class="accordion  accordion-toggle-arrow" id="accordionformEntitie">
            <div class="card">
                <div class="card-header">
                    <div class="card-title" data-toggle="collapse" data-target="#collapseEntitie">
                        <i class="flaticon-file-1"></i> Informations
                    </div>
                </div>

                <div id="collapseEntitie" class="collapse show" data-parent="#accordionformEntitie">
                    <div class="card-body" id="ENTITY_FORM_BLOCK">

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Société parente : </label>
                                    <input type="hidden" id="selected_entitie_parent"
                                        value="{{ ($row)?$row->entitie_id:0 }}">
                                    <select class="form-control select2" id="entitiesParentSelect" name="entitie_id">
                                        <option value="">Pas de société parente</option>
                                    </select>
                                    <span class="form-text text-muted">Cas d'un groupe de sociétés.</span>
                                    <script>
                                    var selected_entitie_parent = $('#selected_entitie_parent').val();
                                    var entity_id = $("input[name='id']").val();
                                    _loadDatasEntitiesForSelectOptions('entitiesParentSelect', entity_id, 'S', 2,
                                        selected_entitie_parent);
                                    $('#entitiesParentSelect').select2();
                                    </script>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Référence</label>
                                    <input type="text" class="form-control form-control-solid" name="ref"
                                        value="{{ ($row)?$row->ref:$ref }}" readonly />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Nom complet</label>
                                    <input type="text" class="form-control " id="ID_NAME" name="name"
                                        value="{{ ($row)?$row->name:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="checkbox-inline mt-3">
                                    @php
                                    $checked = ($row && $row->is_active==1)?'checked="checked"':'';
                                    @endphp
                                    <label class="checkbox">
                                        <input type="checkbox" value="1" name="is_active" {{ $checked }}>
                                        <span></span>Activé</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <input type="hidden" id="selected_type_of_societe" value="{{ ($row)?$row->type:'' }}">
                                <div class="form-group">
                                    <label for="typesSelects">Forme</label>
                                    <select class="form-control " name="type" id="typesSelects"></select>
                                    <script>
                                    var selected_type_of_societe = $('#selected_type_of_societe').val();
                                    _loadDatasForSelectOptions('typesSelects', 'TYPE_OF_SOCIETE',
                                        selected_type_of_societe, 1);
                                    </script>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Sigle</label>
                                    <input type="text" class="form-control " name="acronym"
                                        value="{{ ($row)?$row->acronym:'' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <input type="hidden" id="selected_type_of_establishment"
                                    value="{{ ($row)?$row->type_establishment:'' }}">
                                <div class="form-group">
                                    <label for="establishmentTypesSelects">Type d’établissement</label>
                                    <select class="form-control " name="type_establishment"
                                        id="establishmentTypesSelects"></select>
                                    <script>
                                    var selected_type_of_establishment = $('#selected_type_of_establishment').val();
                                    _loadDatasForSelectOptions('establishmentTypesSelects', 'TYPE_OF_ESTABLISHMENT',
                                        selected_type_of_establishment, 1);
                                    </script>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Code NAF</label>
                                    <input type="text" class="form-control " name="naf_code"
                                        value="{{ ($row)?$row->naf_code:'' }}" />
                                </div>
                            </div>
                            <!-- <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Numéro TVA</label>
                                    <input type="text" class="form-control " name="tva"
                                        value="{{ ($row)?$row->tva:'' }}" />
                                </div>
                            </div> -->
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>SIREN</label>
                                    <input type="text" class="form-control " name="siren"
                                        value="{{ ($row)?$row->siren:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>SIRET</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control " name="siret"
                                            value="{{ ($row)?$row->siret:'' }}" />
                                        <div class="input-group-append">
                                            <button type="button" onclick="_call_insee_api_for_siret_siren()"
                                                data-toggle="tooltip" title="Rechercher siret et siren"
                                                class="btn btn-icon btn-outline-primary"><span
                                                    id="BTN_SEARCH_SIRET_SIREN"><i
                                                        class="flaticon2-reload"></i></span></button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Téléphone</label>
                                    <input type="tel" class="form-control " name="pro_phone"
                                        value="{{ ($row)?$row->pro_phone:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Mobile</label>
                                    <input type="tel" class="form-control " name="pro_mobile"
                                        value="{{ ($row)?$row->pro_mobile:'' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Email général</label>
                                    <input type="email" class="form-control " name="email"
                                        value="{{ ($row)?$row->email:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Fax</label>
                                    <input type="tel" class="form-control " name="fax"
                                        value="{{ ($row)?$row->fax:'' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Zone de prospection</label>
                                    <input type="text" class="form-control" name="prospecting_area"
                                        value="{{ ($row)?$row->prospecting_area:'' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="separator separator-dashed my-5"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="collective_customer_account">Compte collectif client </label>
                                    <input class="form-control " type="text" name="collective_customer_account"
                                        value="{{ ($row)?$row->collective_customer_account:'' }}" id="collective_customer_account" readonly/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="auxiliary_customer_account">Compte client auxiliaire </label>
                                    <input class="form-control " type="text" name="auxiliary_customer_account"
                                        value="{{ ($row)?$row->auxiliary_customer_account:'' }}" id="auxiliary_customer_account" {{ $readOnly }}/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Code Matricule</label>
                                    <input type="text" class="form-control" name="matricule_code"
                                        value="{{ ($row)?$row->matricule_code:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Code Tiers personnel</label>
                                    <input type="text" class="form-control" name="personal_thirdparty_code"
                                        value="{{ ($row)?$row->personal_thirdparty_code:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Code Fournisseur</label>
                                    <input type="text" class="form-control" name="vendor_code"
                                        value="{{ ($row)?$row->vendor_code:'' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="separator separator-dashed my-5"></div>
                        <p class="text-primary">Coordonnées banquaires :</p>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>IBAN</label>
                                    <input type="text" class="form-control" name="iban"
                                        value="{{ ($row)?$row->iban:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>BIC – Code SWIFT</label>
                                    <input type="text" class="form-control" name="bic"
                                        value="{{ ($row)?$row->bic:'' }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="form-group row">
    <div class="col-lg-12">
        <div class="accordion  accordion-toggle-arrow" id="accordionformContactPrincipal">
            <div class="card">
                <div class="card-header">
                    <div class="card-title" data-toggle="collapse" data-target="#collapseContactPrincipal">
                        <i class="flaticon-user-ok"></i> Contact
                    </div>
                </div>
                <div id="collapseContactPrincipal" class="collapse show" data-parent="#accordionformContactPrincipal">
                    <div class="card-body" id="CONTACT_FORM_BLOCK">

                        <!-- begin::contact form -->
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <div class="checkbox-inline">
                                        @php
                                        $checkedTraineeContact = ($mainContact &&
                                        $mainContact->is_trainee_contact===1)?'checked="checked"':'';
                                        @endphp
                                        <label class="checkbox">
                                            <input type="checkbox" value="1" name="is_trainee_contact"
                                                {{ $checkedTraineeContact }}>
                                            <span></span>Stagiaire</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    @php
                                    $checkedBillingContact = ($mainContact &&
                                    $mainContact->is_billing_contact===1)?'checked="checked"':'';
                                    @endphp
                                    <div class="checkbox-inline">
                                        <label class="checkbox">
                                            <input type="checkbox" value="1" name="c_is_billing_contact"
                                                {{ $checkedBillingContact }}>
                                            <span></span>Facturation</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="genderSelects">Civilité</label>
                                    <select class="form-control " name="c_gender" id="genderSelects">
                                        <option
                                            {{ (($mainContact && $mainContact->gender==='M')?'selected':'') }}
                                            value="M">M
                                        </option>
                                        <option
                                            {{ (($mainContact && $mainContact->gender==='Mme')?'selected':'') }}
                                            value="Mme">Mme
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Prénom</label>
                                    <input type="text" class="form-control" name="c_firstname" id="c_firstname"
                                        value="{{ ($mainContact)?$mainContact->firstname:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Nom</label>
                                    <input type="text" class="form-control" name="c_lastname"
                                        value="{{ ($mainContact)?$mainContact->lastname:'' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>email</label>
                                    <input type="email" class="form-control" name="c_email"
                                        value="{{ ($mainContact)?$mainContact->email:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="functionSelects">Fonction</label>
                                    <input type="text" class="form-control" name="c_function"
                                        value="{{ ($mainContact)?$mainContact->function:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Téléphone pro </label>
                                    <input type="tel" class="form-control" name="c_pro_phone"
                                        value="{{ ($mainContact)?$mainContact->pro_phone:'' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Portable pro </label>
                                    <input type="tel" class="form-control" name="c_pro_mobile"
                                        value="{{ ($mainContact)?$mainContact->pro_mobile:'' }}" />
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label>Date de naissance</label>
                                    <div class="input-group date">
                                        @php
                                        $birth_date ='';
                                        if($mainContact){
                                        if($mainContact->birth_date!=null){
                                        $dt = Carbon\Carbon::createFromFormat('Y-m-d',$mainContact->birth_date);
                                        $birth_date = $dt->format('d/m/Y');
                                        }
                                        }
                                        @endphp
                                        <input type="text" class="form-control" name="birth_date"
                                            id="dateofbirth_datepicker" placeholder="Sélectionner une date"
                                            value="{{ $birth_date }}" autocomplete="off"/>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-calendar-check-o"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::contact form-->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group row">
    <div class="col-lg-12">
        <div class="accordion  accordion-toggle-arrow" id="accordionformAdresse">
            <div class="card">
                <div class="card-header">
                    <div class="card-title" data-toggle="collapse" data-target="#collapseAdresse">
                        <i class="flaticon-map-location"></i> Adresse
                    </div>
                </div>
                <div id="collapseAdresse" class="collapse show" data-parent="#accordionformAdresse">
                    <div class="card-body" id="ADRESSE_FORM_BLOCK">

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    @php
                                    $checkedBilling = ($mainAdresse &&
                                    $mainAdresse->is_billing===1)?'checked="checked"':'';
                                    @endphp
                                    <div class="checkbox-inline">
                                        <label class="checkbox">
                                            <input type="checkbox" value="1" name="a_is_billing" {{ $checkedBilling }}>
                                            <span></span>Facturation</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="checkbox-inline">
                                    @php
                                    $checked = ($mainAdresse && $mainAdresse->is_stage_site===1)?'checked="checked"':'';
                                    @endphp
                                    <label class="checkbox">
                                        <input type="checkbox" value="1" name="a_is_stage_site" {{ $checked }}>
                                        <span></span>Terrain de stage</label>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    @php
                                    $checkedFS = ($mainAdresse &&
                                    $mainAdresse->is_formation_site===1)?'checked="checked"':'';
                                    @endphp
                                    <div class="checkbox-inline">
                                        <label class="checkbox">
                                            <input type="checkbox" value="1" name="a_is_formation_site"
                                                {{ $checkedFS }}>
                                            <span></span>Lieu de formation</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group mb-2">
                                    <label for="line_1">Ligne 1<span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="a_line_1" id="line_1" rows="3"
                                        required>{{ ($mainAdresse)?$mainAdresse->line_1:'' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group mb-2">
                                    <label for="line_2">Ligne 2</label>
                                    <textarea class="form-control" name="a_line_2" id="line_2"
                                        rows="3">{{ ($mainAdresse)?$mainAdresse->line_2:'' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group mb-2">
                                    <label for="line_3">Ligne 3</label>
                                    <textarea class="form-control" name="a_line_3" id="line_3"
                                        rows="3">{{ ($mainAdresse)?$mainAdresse->line_3:'' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Code postal </label>
                                    <!-- <input type="text" class="form-control" name="a_postal_code" value="{{ ($mainAdresse)?$mainAdresse->postal_code:'' }}" /> -->
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="ZIPCODE" name="a_postal_code"
                                            value="{{ ($mainAdresse)?$mainAdresse->postal_code:'' }}" />
                                        <div class="input-group-append">
                                            <button type="button" onclick="_call_api_to_search_cities()"
                                                data-toggle="tooltip" title="Rechercher les villes"
                                                class="btn btn-icon btn-outline-primary"><span id="BTN_SEARCH_CITIES"><i
                                                        class="flaticon2-search"></i></span></button>
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Ville <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="a_city"
                                        value="{{ ($mainAdresse)?$mainAdresse->city:'' }}" required />
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="paysSelects">Pays</label>
                                    @php
                                        $selected_country = ($mainAdresse)?$mainAdresse->country:'';
                                    @endphp
                                    <select class="form-control " name="a_country" id="paysSelects">
                                        <option value="0">Pays</option>
                                        @if(count($countriesDatas)>0)
                                            @foreach($countriesDatas as $dt)
                                                <option value="{{$dt['code']}}" {{($selected_country == $dt['code'])? 'selected' : '' }}>{{$dt['country']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- end::adresse form -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif
<script>
var auxCodes = `{!! $auxCodes !!}`;
var aux_codes = JSON.parse(auxCodes);

$('#dateofbirth_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
var _constructRadio = function(siret, siren, name, l) {
    var label = '<label class="radio"><input type="radio" name="RSAPI" data-siret="' + siret + '" data-siren="' +
        siren + '" data-name="' + name + '" value="' + siret + '" /><span></span>' + l +
        '</label>';
    return label;
}
var _choice_rs_api = function() {
    var siret = $("input[name='RSAPI']:checked").val();
    if (!siret) {
        _showResponseMessage('error', 'Veuillez sélectionner une valeur parmi la liste !.');
        return false;
    }
    var siren = $("input[name='RSAPI']:checked").attr("data-siren");
    var name = $("input[name='RSAPI']:checked").attr("data-name");
    $("input[name='siret']").val(siret);
    $("input[name='siren']").val(siren);
    $("input[name='name']").val(name);
}
var _call_insee_api_for_siret_siren = function() {
    var name = $('#ID_NAME').val();
    if (name) {
        _showLoader('BTN_SEARCH_SIRET_SIREN');
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: "/api/insee/siret",
            type: "POST",
            data: {
                _token: CSRF_TOKEN,
                name: name,
            },
            dataType: "JSON",
            success: function(result, status) {
                if (result.length > 0) {
                    var blockHtml = '<div class="radio-list" id="RADIO_LIST_API">';
                    $.each(result, function(key, value) {
                        var l = value.denomination + ' - siret : ' + value.siret +
                            ' - siren : ' + value.siren;
                        var html = _constructRadio(value.siret, value.siren, value.denomination,
                            l);
                        blockHtml = blockHtml.concat(html);
                    });
                    blockHtml = blockHtml.concat('</div>');
                    Swal.fire({
                        title: 'Veuillez sélectionner une ligne :',
                        icon: 'success',
                        html: blockHtml,
                        showCloseButton: true,
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: '<i class="fa fa-check"></i> Choisir',
                        cancelButtonText: '<i class="fa fa-times"></i>',
                    }).then(function(result) {
                        if (result.value) {
                            _choice_rs_api();
                        }
                    });
                } else {
                    _showResponseMessage('error', 'Aucun résultat ! ');
                }
                $('#BTN_SEARCH_SIRET_SIREN').html('<i class="flaticon2-reload"></i>');
            },
            error: function(result, status, error) {
                $('#BTN_SEARCH_SIRET_SIREN').html('<i class="flaticon2-reload"></i>');
            },
            complete: function(result, status) {
                $('#BTN_SEARCH_SIRET_SIREN').html('<i class="flaticon2-reload"></i>');
            }
        });

    } else {
        _showResponseMessage('error',
            'Veuillez renseigner le nom de l\'entité pour pouvoir chercher SIRET et SIREN');
    }

};
//Block type d'intervention formateur
$("input[name='is_former']").change(function() {
    _showTypeFormerInterventionBlock();
});
/* compte aux client : nom du client/société tronqué et sans espace */
$("input[name='c_firstname'], input[name='c_lastname']").on('change paste keyup', function() {
    const f_name = $("input[name='c_firstname']").val(),
        l_name = $("input[name='c_lastname']").val();
    _createAuxCode(f_name, l_name);
});
$("input[name='name']").on('change paste keyup', function() {
    const name = $(this).val();
    _createAuxCode(name);
});

var _createAuxCode = function (f_name, l_name = false) {
    var aux_account = '';
    f_name = f_name.normalize("NFD").replace(/[\u0300-\u036f]/g, "");/* remove accented chars */
    f_name = f_name.replace(/[^A-Za-z0-9]/g, "");/* remove special chars */
    if (!l_name) {
        /* société */
        aux_account = f_name.replaceAll(' ', '').toUpperCase().slice(0, 13);
        while (aux_codes.indexOf(aux_account) >= 0) {
            var num = parseInt(aux_account.slice(-2));
            if (isNaN(num)) {
                num = '01';
            } else {
                num = ('0' + (num + 1)).slice(-2);
            }
            aux_account = aux_account.slice(0, 11) + num;
        }
    } else {
        /* particulier */
        l_name = l_name.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        aux_account = f_name.replaceAll(' ', '').toUpperCase().slice(0, 10);
        aux_account += l_name.replaceAll(' ', '').toUpperCase().slice(0, 3);
        while (aux_codes.indexOf(aux_account) >= 0) {
            var num = parseInt(aux_account.slice(-2));
            if (isNaN(num)) {
                num = '01';
            } else {
                num = ('0' + (num + 1)).slice(-2);
            }
            
            aux_account = f_name.slice(0, 9) + l_name.slice(0, 2) + num;
        }
    }

    $('input[name=auxiliary_customer_account]').val(aux_account);
}

var _showTypeFormerInterventionBlock = function() {
    var rs = $("input[name='is_former']").is(":checked");
    if (rs === true) {
        $('#BLOCK_TYPE_INTERVENSION_FORMER').show();
        $("select[name='c_type_former_intervention']").prop('required', true);
    } else {
        $('#BLOCK_TYPE_INTERVENSION_FORMER').hide();
        $("select[name='c_type_former_intervention']").removeAttr('required');
        $("select[name='c_type_former_intervention']").prop('selectedIndex', 0);
    }
}
//default
_showTypeFormerInterventionBlock();

var _constructRadioCity = function(city) {
    var label = '<label class="radio"><input type="radio" name="RSAPI_CITY"' + '" value="' + city +
        '" /><span></span>' + city + '</label>';
    return label;
}
var _choice_city_api = function() {
    var city = $("input[name='RSAPI_CITY']:checked").val();
    if (!city) {
        _showResponseMessage('error', 'Veuillez sélectionner une ville parmi la liste !.');
        return false;
    }
    $("input[name='a_city']").val(city);
}
var _call_api_to_search_cities = function() {
    var codePostal = $('#ZIPCODE').val();
    if (codePostal > 0) {
        _showLoader('BTN_SEARCH_CITIES');
        $.ajax({
            url: "/api/geo/cities/" + codePostal,
            type: "GET",
            dataType: "JSON",
            success: function(result, status) {
                if (result.length > 0) {
                    var blockHtml = '<div class="radio-list" id="RADIO_LIST_CITIES_API">';
                    $.each(result, function(key, value) {
                        var html = _constructRadioCity(value.city);
                        blockHtml = blockHtml.concat(html);
                    });
                    blockHtml = blockHtml.concat('</div>');
                    Swal.fire({
                        title: 'Veuillez sélectionner une ville :',
                        icon: 'success',
                        html: blockHtml,
                        showCloseButton: true,
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: '<i class="fa fa-check"></i> Choisir',
                        cancelButtonText: '<i class="fa fa-times"></i>',
                    }).then(function(result) {
                        if (result.value) {
                            _choice_city_api();
                        }
                    });
                } else {
                    _showResponseMessage('error', 'Aucun résultat ! ');
                }
                $('#BTN_SEARCH_CITIES').html('<i class="flaticon2-search"></i>');
            },
            error: function(result, status, error) {
                $('#BTN_SEARCH_CITIES').html('<i class="flaticon2-search"></i>');
            },
            complete: function(result, status) {
                $('#BTN_SEARCH_CITIES').html('<i class="flaticon2-search"></i>');
            }
        });

    } else {
        _showResponseMessage('error', 'Veuillez renseigner le code postal pour pouvoir chercher la ville');
    }

};
</script>
<script type="text/javascript">
    checkRequerement();
    $('#c_is_active').on('change', function(e){
        checkRequerement();
    });

    function checkRequerement(){
        $("#c_firstname").attr("required", $('#c_is_active').prop('checked'));
        $("#c_lastname").attr("required", $('#c_is_active').prop('checked'));
        $("#c_email").attr("required", $('#c_is_active').prop('checked'));
    }
</script>
<!-- end::entity form -->