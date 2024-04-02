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

<?php if($type_af == 'INTRA'): ?>
    <?php if(count($intraArrayDatas) > 0): ?>
        <div>
            <p style="text-transform: uppercase;text-align: center;font-size:18px;">
                <strong><?php echo e($PARAMS['COMPANY_NAME']); ?></strong>
            </p>
            <p style="font-size:20px;text-align: center;"><strong>FICHE D’EMARGEMENT INTRA-ENTREPRISES</strong></p>
        </div>
        <!-- <div>
            <p style="text-transform: uppercase;text-align: center;font-size:18px;"><strong><?php echo e($PARAMS['COMPANY_NAME']); ?></strong>
            </p>
            <p style="font-size:20px;text-align: center;"><strong>FICHE D’EMARGEMENT INTRA-ENTREPRISES</strong></p>
            <p>Intitulé de la formation : <?php echo e($PARAMS['AF_TITLE']); ?></p>
            <p>Référence : <?php echo e($PARAMS['AF_CODE']); ?></p>
            <p>Lieu de la formation : <?php echo e($PARAMS['AF_LIEU_FORMATION']); ?> / <?php echo e($PARAMS['AF_ADRESSE_LIEU_FORMATION']); ?></p>
            <p>Formateur(s) : <?php echo e($PARAMS['FORMERS']); ?></p>
            <p>Dates : du <?php echo e($PARAMS['STARTED_AT']); ?> au <?php echo e($PARAMS['ENDED_AT']); ?></p>
            <p>Horaire : de <?php echo e($PARAMS['FIRST_SCHEDULE_HOUR']); ?> à <?php echo e($PARAMS['LAST_SCHEDULE_HOUR']); ?></p>
            <p>Durée : <?php echo e($PARAMS['NB_THEO_DAYS']); ?> jours / <?php echo e($PARAMS['NB_THEO_HOURS']); ?> heures théoriques
            <?php if($PARAMS['NB_PRACTICAL_HOURS']): ?>
                et <?php echo e($PARAMS['NB_PRACTICAL_DAYS']); ?> jours / <?php echo e($PARAMS['NB_PRACTICAL_HOURS']); ?> heures de stage pratique
            <?php endif; ?>
            </p>
        </div> -->
        <?php $__currentLoopData = $intraArrayDatas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $datas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(isset($datas['sessions'])): ?>
                <?php $__currentLoopData = $datas['sessions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $d['dates']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arrayDate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="nobreak">
                            <p>Intitulé de la formation : <?php echo e($PARAMS['AF_TITLE']); ?></p>
                            <p>Référence : <?php echo e($PARAMS['AF_CODE']); ?></p>
                            <p>Lieu de la formation : <?php echo e($PARAMS['AF_LIEU_FORMATION']); ?> /
                                <?php echo e($PARAMS['AF_ADRESSE_LIEU_FORMATION']); ?></p>
                            <p>Formateur(s) : <?php echo e($PARAMS['FORMERS']); ?></p>
                            <p>Dates : du <?php echo e($PARAMS['STARTED_AT']); ?> au <?php echo e($PARAMS['ENDED_AT']); ?></p>
                            <p>Horaire : de <?php echo e($PARAMS['FIRST_SCHEDULE_HOUR']); ?> à <?php echo e($PARAMS['LAST_SCHEDULE_HOUR']); ?>

                            </p>
                            <p>Durée : <?php echo e($PARAMS['NB_THEO_DAYS']); ?> jours / <?php echo e($PARAMS['NB_THEO_HOURS']); ?> heures
                                théoriques
                                <?php if($PARAMS['NB_PRACTICAL_HOURS']): ?>
                                    et <?php echo e($PARAMS['NB_PRACTICAL_DAYS']); ?> jours / <?php echo e($PARAMS['NB_PRACTICAL_HOURS']); ?>

                                    heures de stage pratique
                                <?php endif; ?>
                            </p>
                            <p class="p-title" style="margin-left:2px;"> NOM de la STRUCTURE :
                                <strong><?php echo e($datas['name']); ?></strong>
                            </p>
                            <p class="p-title" style="margin-left:2px;"> SESSION : 
                                <strong><?php echo e($d['code']); ?><?php echo e($d['title']); ?></strong>
                            </p>
                            <p class="p-title" style="margin-left:2px;"> DATE DE LA FORMATION :
                                <strong><?php echo e($arrayDate['planning_date']); ?></strong>
                            </p>
                            <table class="list" style="margin-bottom:20px;">
                                <thead>
                                    <tr style="background-color:#F1F1F1;">
                                        <th class="bordered">Participants</th>
                                        <th class="bordered">Emargement stagiaire <br>
                                            <?php if(array_key_exists('M', $arrayDate['schedules'])): ?>
                                                <?php echo e($arrayDate['schedules']['M']['start_hour']); ?> -
                                                <?php echo e($arrayDate['schedules']['M']['end_hour']); ?>

                                            <?php endif; ?>
                                        </th>

                                        <th class="bordered">Emargement stagiaire <br>
                                            <?php if(array_key_exists('A', $arrayDate['schedules'])): ?>
                                                <?php echo e($arrayDate['schedules']['A']['start_hour']); ?> -
                                                <?php echo e($arrayDate['schedules']['A']['end_hour']); ?>

                                            <?php endif; ?>
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
                                                            <?php if(array_key_exists('M', $arrayDate['schedules'])): ?>
                                                                <?php echo e($arrayDate['schedules']['M']['start_hour']); ?> -
                                                                <?php echo e($arrayDate['schedules']['M']['end_hour']); ?>

                                                            <?php endif; ?>
                                                        </th>
                                                        <th style="font-weight:normal;">
                                                            <?php if(array_key_exists('A', $arrayDate['schedules'])): ?>
                                                                <?php echo e($arrayDate['schedules']['A']['start_hour']); ?> -
                                                                <?php echo e($arrayDate['schedules']['A']['end_hour']); ?>

                                                            <?php endif; ?>
                                                        </th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($arrayDate['members']) > 0): ?>
                                        <?php $__currentLoopData = $arrayDate['members']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td class="bordered"><?php echo e($member['name']); ?></td>
                                                <td class="bordered"></td>
                                                <td class="bordered"></td>
                                                <td class="bordered"></td>
                                                <td class="bordered"></td>
                                                <td class="bordered"></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </tbody>
                                
                            </table>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
<?php endif; ?>
<!-- INTER -->
<?php if($type_af == 'INTER'): ?>
    <?php if($members): ?>
        <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $member_name = $member->contact
                    ? $member->contact->firstname .' ' . $member->contact->lastname
                    : $member->unknown_contact_name;

                $entitie =
                    $member->contact && $member->contact->entitie->entity_type == 'S'
                        ? $member->contact->entitie->name
                        : $member->unknown_contact_name ?? '';
            ?>

            <div>
                <p style="text-transform: uppercase;text-align: center;font-size:18px;">
                    <strong><?php echo e($PARAMS['COMPANY_NAME']); ?></strong>
                </p>
                <p style="font-size:20px;text-align: center;"><strong>FICHE D’EMARGEMENT INTER-ENTREPRISES</strong></p>
                <p>Intitulé de la formation : <?php echo e($PARAMS['AF_TITLE']); ?></p>
                <p>Référence : <?php echo e($PARAMS['AF_CODE']); ?></p>
                <p>Lieu de la formation : <?php echo e($PARAMS['AF_LIEU_FORMATION']); ?> /
                    <?php echo e($PARAMS['AF_ADRESSE_LIEU_FORMATION']); ?></p>
                <p>Formateur(s) : <?php echo e($PARAMS['FORMERS']); ?></p>
                <p>Dates : du <?php echo e($PARAMS['STARTED_AT']); ?> au <?php echo e($PARAMS['ENDED_AT']); ?></p>
                <p>Horaire : de <?php echo e($PARAMS['FIRST_SCHEDULE_HOUR']); ?> à <?php echo e($PARAMS['LAST_SCHEDULE_HOUR']); ?></p>
                <p>Durée : <?php echo e($PARAMS['NB_THEO_DAYS']); ?> jours / <?php echo e($PARAMS['NB_THEO_HOURS']); ?> heures heures
                    théoriques
                    <?php if($PARAMS['NB_PRACTICAL_HOURS']): ?>
                        et <?php echo e($PARAMS['NB_PRACTICAL_DAYS']); ?> jours /<?php echo e($PARAMS['NB_PRACTICAL_HOURS']); ?> heures de
                        stage pratique
                    <?php endif; ?>
                </p>
            </div>

            <p class="p-title">PARTICIPANT : <strong><?php echo e($member_name); ?> <?php echo e($entitie); ?></strong></p>
            <!-- <div class="nobreak"> -->
            <table class="list" id="table_<?php echo e($k); ?>">
                <thead>
                    <tr style="background-color:#F1F1F1;">
                        <th class="bordered" colspan="8">PARTICIPANT : <strong><?php echo e($member_name); ?>

                                <?php echo e($entitie); ?></strong></th>
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
                    <?php if(count($arraySessionPlanning[$member->id]) > 0): ?>
                        <?php $__currentLoopData = $arraySessionPlanning[$member->id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $datas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $datas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="bordered">
                                        <p class="font-body-table"><?php echo e($d['planning_date']); ?></p>
                                        <!-- <p class="font-body-table"><?php echo e($d['session_code']); ?></p> -->
                                        <p class="font-body-table"><?php echo e($d['session_id']); ?> -
                                            <?php echo e(substr($d['session_title'], 0, 10)); ?></p>
                                    </td>
                                    <td class="bordered font-body-table">
                                        <?php if(array_key_exists('M', $d['schedules'])): ?>
                                            <?php echo e($d['schedules']['M']['start_hour']); ?> -
                                            <?php echo e($d['schedules']['M']['end_hour']); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td class="bordered font-body-table"></td>
                                    <td class="bordered font-body-table">
                                        <?php if(array_key_exists('A', $d['schedules'])): ?>
                                            <?php echo e($d['schedules']['A']['start_hour']); ?> -
                                            <?php echo e($d['schedules']['A']['end_hour']); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td class="bordered font-body-table"></td>
                                    <td class="bordered font-body-table"></td>
                                    <td class="bordered font-body-table">
                                        <?php
                                            $formersMoorning = '';
                                            if (array_key_exists('M', $d['schedules'])) {
                                                $fms = $d['schedules']['M']['formers'];
                                                if (count($fms) > 0) {
                                                    $formersMoorning = implode(',', $fms);
                                                }
                                            }
                                        ?>
                                        <?php if(!empty($formersMoorning)): ?>
                                            <p style="font-size:9px;"><?php echo e($formersMoorning); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="bordered font-body-table">
                                        <?php
                                            $formersAfter = '';
                                            if (array_key_exists('A', $d['schedules'])) {
                                                $fas = $d['schedules']['A']['formers'];
                                                if (count($fas) > 0) {
                                                    $formersAfter = implode(',', $fas);
                                                }
                                            }
                                        ?>
                                        <?php if(!empty($formersAfter)): ?>
                                            <p style="font-size:9px;"><?php echo e($formersAfter); ?></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <!-- </div> -->

            <?php if(count($members) - 1 != $k): ?>
                <div class="page-break"></div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/pages/pdf/fiche-emargement.blade.php ENDPATH**/ ?>