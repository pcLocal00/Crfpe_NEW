<!-- INTRA -->
<style>
    p {
        font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
        font-size: 12px;
    }

    .p-title {
        font-size: 12px;
    }

    .list {
        /*width: 16.8cm;*/
        font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
        font-size: 11px;
        border: 0.001em solid black;
        border-collapse: collapse;
        /* margin-bottom:50px; */
    }

    .bordered {
        border: 0.001em solid black;
        height: 26px;
        padding: 4px;
        font-weight: normal;
    }

    .font-body-table {
        font-size: 9px;
    }

    .page-break {
        page-break-after: always;
    }

    thead tr th:first-child,
    tbody tr td:first-child {
        width: 9.9em;
        min-width: 9.9em;
        max-width: 9.9em;
        word-break: break-all;
    }
</style>

@if ($type_af == 'INTRA')
    @if (count($intraArrayDatas) > 0)
        <div>
            <p style="text-transform: uppercase;text-align: center;font-size:18px;">
                <strong>{{ $PARAMS['COMPANY_NAME'] }}</strong>
            </p>
            <p style="font-size:20px;text-align: center;"><strong>FICHE D’EMARGEMENT INTRA-ENTREPRISES</strong></p>
        </div>
        <!-- <div>
            <p style="text-transform: uppercase;text-align: center;font-size:18px;"><strong>{{ $PARAMS['COMPANY_NAME'] }}</strong>
            </p>
            <p style="font-size:20px;text-align: center;"><strong>FICHE D’EMARGEMENT INTRA-ENTREPRISES</strong></p>
            <p>Intitulé de la formation : {{ $PARAMS['AF_TITLE'] }}</p>
            <p>Référence : {{ $PARAMS['AF_CODE'] }}</p>
            <p>Lieu de la formation : {{ $PARAMS['AF_LIEU_FORMATION'] }} / {{ $PARAMS['AF_ADRESSE_LIEU_FORMATION'] }}</p>
            <p>Formateur(s) : {{ $PARAMS['FORMERS'] }}</p>
            <p>Dates : du {{ $PARAMS['STARTED_AT'] }} au {{ $PARAMS['ENDED_AT'] }}</p>
            <p>Horaire : de {{ $PARAMS['FIRST_SCHEDULE_HOUR'] }} à {{ $PARAMS['LAST_SCHEDULE_HOUR'] }}</p>
            <p>Durée : {{ $PARAMS['NB_THEO_DAYS'] }} jours / {{ $PARAMS['NB_THEO_HOURS'] }} heures théoriques
            @if ($PARAMS['NB_PRACTICAL_HOURS'])
                et {{ $PARAMS['NB_PRACTICAL_DAYS'] }} jours / {{ $PARAMS['NB_PRACTICAL_HOURS'] }} heures de stage pratique
            @endif
            </p>
        </div> -->
        @foreach ($intraArrayDatas as $datas)
            @if (isset($datas['sessions']))
                @foreach ($datas['sessions'] as $d)
                    @foreach ($d['dates'] as $arrayDate)
                        <div class="nobreak">
                            <p>Intitulé de la formation : {{ $PARAMS['AF_TITLE'] }}</p>
                            <p>Référence : {{ $PARAMS['AF_CODE'] }}</p>
                            <p>Lieu de la formation : {{ $PARAMS['AF_LIEU_FORMATION'] }} /
                                {{ $PARAMS['AF_ADRESSE_LIEU_FORMATION'] }}</p>
                            <p>Formateur(s) : {{ $PARAMS['FORMERS'] }}</p>
                            <p>Dates : du {{ $PARAMS['STARTED_AT'] }} au {{ $PARAMS['ENDED_AT'] }}</p>
                            <p>Horaire : de {{ $PARAMS['FIRST_SCHEDULE_HOUR'] }} à {{ $PARAMS['LAST_SCHEDULE_HOUR'] }}
                            </p>
                            <p>Durée : {{ $PARAMS['NB_THEO_DAYS'] }} jours / {{ $PARAMS['NB_THEO_HOURS'] }} heures
                                théoriques
                                @if ($PARAMS['NB_PRACTICAL_HOURS'])
                                    et {{ $PARAMS['NB_PRACTICAL_DAYS'] }} jours / {{ $PARAMS['NB_PRACTICAL_HOURS'] }}
                                    heures de stage pratique
                                @endif
                            </p>
                            <p class="p-title" style="margin-left:2px;"> NOM de la STRUCTURE :
                                <strong>{{ $datas['name'] }}</strong>
                            </p>
                            <p class="p-title" style="margin-left:2px;"> SESSION : 
                                <strong>{{ $d['code'] }}{{ $d['title'] }}</strong>
                            </p>
                            <p class="p-title" style="margin-left:2px;"> DATE DE LA FORMATION :
                                <strong>{{ $arrayDate['planning_date'] }}</strong>
                            </p>
                            <table class="list" style="margin-bottom:20px;">
                                <thead>
                                    <tr style="background-color:#F1F1F1;">
                                        <th class="bordered">Participants</th>
                                        <th class="bordered">Emargement stagiaire <br>
                                            @if (array_key_exists('M', $arrayDate['schedules']))
                                                {{ $arrayDate['schedules']['M']['start_hour'] }} -
                                                {{ $arrayDate['schedules']['M']['end_hour'] }}
                                            @endif
                                        </th>

                                        <th class="bordered">Emargement stagiaire <br>
                                            @if (array_key_exists('A', $arrayDate['schedules']))
                                                {{ $arrayDate['schedules']['A']['start_hour'] }} -
                                                {{ $arrayDate['schedules']['A']['end_hour'] }}
                                            @endif
                                        </th>

                                        <th class="bordered">Retard ou départ anticipé</th>
                                        <th class="bordered" colspan="2">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th colspan="2" style="font-weight:normal;">Emargement
                                                            formateur</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="font-weight:normal;">
                                                            @if (array_key_exists('M', $arrayDate['schedules']))
                                                                {{ $arrayDate['schedules']['M']['start_hour'] }} -
                                                                {{ $arrayDate['schedules']['M']['end_hour'] }}
                                                            @endif
                                                        </th>
                                                        <th style="font-weight:normal;">
                                                            @if (array_key_exists('A', $arrayDate['schedules']))
                                                                {{ $arrayDate['schedules']['A']['start_hour'] }} -
                                                                {{ $arrayDate['schedules']['A']['end_hour'] }}
                                                            @endif
                                                        </th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($arrayDate['members']) > 0)
                                        @foreach ($arrayDate['members'] as $member)
                                            <tr>
                                                <td class="bordered">{{ $member['name'] }}</td>
                                                <td class="bordered"></td>
                                                <td class="bordered"></td>
                                                <td class="bordered"></td>
                                                <td class="bordered"></td>
                                                <td class="bordered"></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                
                            </table>
                        </div>
                    @endforeach
                @endforeach
            @endif
        @endforeach
    @endif
@endif
<!-- INTER -->
@if ($type_af == 'INTER')
    @if ($members)
        @foreach ($members as $k => $member)
            @php
                $member_name = $member->contact
                    ? $member->contact->firstname .' ' . $member->contact->lastname
                    : $member->unknown_contact_name;

                $entitie =
                    $member->contact && $member->contact->entitie->entity_type == 'S'
                        ? $member->contact->entitie->name
                        : $member->unknown_contact_name ?? '';
            @endphp

            <div>
                <p style="text-transform: uppercase;text-align: center;font-size:18px;">
                    <strong>{{ $PARAMS['COMPANY_NAME'] }}</strong>
                </p>
                <p style="font-size:20px;text-align: center;"><strong>FICHE D’EMARGEMENT INTER-ENTREPRISES</strong></p>
                <p>Intitulé de la formation : {{ $PARAMS['AF_TITLE'] }}</p>
                <p>Référence : {{ $PARAMS['AF_CODE'] }}</p>
                <p>Lieu de la formation : {{ $PARAMS['AF_LIEU_FORMATION'] }} /
                    {{ $PARAMS['AF_ADRESSE_LIEU_FORMATION'] }}</p>
                <p>Formateur(s) : {{ $PARAMS['FORMERS'] }}</p>
                <p>Dates : du {{ $PARAMS['STARTED_AT'] }} au {{ $PARAMS['ENDED_AT'] }}</p>
                <p>Horaire : de {{ $PARAMS['FIRST_SCHEDULE_HOUR'] }} à {{ $PARAMS['LAST_SCHEDULE_HOUR'] }}</p>
                <p>Durée : {{ $PARAMS['NB_THEO_DAYS'] }} jours / {{ $PARAMS['NB_THEO_HOURS'] }} heures heures
                    théoriques
                    @if ($PARAMS['NB_PRACTICAL_HOURS'])
                        et {{ $PARAMS['NB_PRACTICAL_DAYS'] }} jours /{{ $PARAMS['NB_PRACTICAL_HOURS'] }} heures de
                        stage pratique
                    @endif
                </p>
            </div>

            <p class="p-title">PARTICIPANT : <strong>{{ $member_name }} {{ $entitie }}</strong></p>
            <!-- <div class="nobreak"> -->
            <table class="list" id="table_{{ $k }}">
                <thead>
                    <tr style="background-color:#F1F1F1;">
                        <th class="bordered" colspan="8">PARTICIPANT : <strong>{{ $member_name }}
                                {{ $entitie }}</strong></th>
                    </tr>
                    <tr style="background-color:#F1F1F1;">
                        <th class="bordered">Date</th>
                        <th class="bordered" style="width: 7em;min-width: 6em;max-width: 7em;word-break: break-all;">
                            Horaire</th>
                        <th class="bordered">Emargement stagiaire</th>
                        <th class="bordered" style="width: 7em;min-width: 7em;max-width: 7em;word-break: break-all;">
                            Horaire</th>
                        <th class="bordered">Emargement stagiaire</th>
                        <th class="bordered">Retard ou départ anticipé</th>
                        <th class="bordered" colspan="2">
                            Emargement formateur
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($arraySessionPlanning[$member->id]) > 0)
                        @foreach ($arraySessionPlanning[$member->id] as $datas)
                            @foreach ($datas as $d)
                                <tr>
                                    <td class="bordered">
                                        <p class="font-body-table">{{ $d['planning_date'] }}</p>
                                        <!-- <p class="font-body-table">{{ $d['session_code'] }}</p> -->
                                        <p class="font-body-table">{{ $d['session_id'] }} -
                                            {{ substr($d['session_title'], 0, 10) }}</p>
                                    </td>
                                    <td class="bordered font-body-table">
                                        @if (array_key_exists('M', $d['schedules']))
                                            {{ $d['schedules']['M']['start_hour'] }} -
                                            {{ $d['schedules']['M']['end_hour'] }}
                                        @endif
                                    </td>
                                    <td class="bordered font-body-table"></td>
                                    <td class="bordered font-body-table">
                                        @if (array_key_exists('A', $d['schedules']))
                                            {{ $d['schedules']['A']['start_hour'] }} -
                                            {{ $d['schedules']['A']['end_hour'] }}
                                        @endif
                                    </td>
                                    <td class="bordered font-body-table"></td>
                                    <td class="bordered font-body-table"></td>
                                    <td class="bordered font-body-table">
                                        @php
                                            $formersMoorning = '';
                                            if (array_key_exists('M', $d['schedules'])) {
                                                $fms = $d['schedules']['M']['formers'];
                                                if (count($fms) > 0) {
                                                    $formersMoorning = implode(',', $fms);
                                                }
                                            }
                                        @endphp
                                        @if (!empty($formersMoorning))
                                            <p style="font-size:9px;">{{ $formersMoorning }}</p>
                                        @endif
                                    </td>
                                    <td class="bordered font-body-table">
                                        @php
                                            $formersAfter = '';
                                            if (array_key_exists('A', $d['schedules'])) {
                                                $fas = $d['schedules']['A']['formers'];
                                                if (count($fas) > 0) {
                                                    $formersAfter = implode(',', $fas);
                                                }
                                            }
                                        @endphp
                                        @if (!empty($formersAfter))
                                            <p style="font-size:9px;">{{ $formersAfter }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endif
                </tbody>
            </table>
            <!-- </div> -->

            @if (count($members) - 1 != $k)
                <div class="page-break"></div>
            @endif
        @endforeach
    @endif
@endif
