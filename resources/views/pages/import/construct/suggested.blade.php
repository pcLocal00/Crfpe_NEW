<div class="modal-header">
    <h5 class="modal-title" id="modal_logs_title"><i class="flaticon-edit"></i> {{ $title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_logs_body">
    <table id="suggested-table" class="table table-stripped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Numéro</th>
                <th>Nom</th>
                <th>Naissance</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th>Code AUX</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contacts as $c)
                @php
                    $adresse_line = '';
                    if ($c->entitie && !$c->entitie->adresses->isEmpty()) {
                        $adresse = $c->entitie->adresses->first();
                        $adresse_line = "{$adresse->line_1} {$adresse->line_2} {$adresse->line_3} {$adresse->postal_code} {$adresse->city}";
                    }
                @endphp
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->firstname }} {{ $c->lastname }}</td>
                    <td>{{ $c->birth_date ? date_create_from_format('Y-m-d', $c->birth_date)->format('d.m.Y') : '' }}
                    </td>
                    <td>{{ $adresse_line }}</td>
                    <td>{{ $c->pro_phone ?? $c->pro_mobile }}</td>
                    <td>{{ $c->entitie->auxiliary_customer_account ?? '' }}</td>
                    <td>
                        <button class="btn btn-success btn-sm btn-edit btn-icon"
                            onclick="_selectSuggestedContact({{ $c->id }}, {{ $attachment }})"
                            title="Valider le contact"><i class="fas fa-check"></i></button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Aunune donnée pour le moment.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-primary" onclick="_selectSuggestedContact(0, {{ $attachment }});"><i
            class="fa fa-plus"></i>
        Créer le contact</button>
    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><i class="fa fa-times"></i>
        Fermer</button>
</div>
