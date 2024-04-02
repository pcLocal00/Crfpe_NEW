<?php
$currentDate=Carbon\Carbon::now();
$currentDay=$currentDate->dayOfWeek;
$arrayDays=[0=>'Dimanche',1=>'Lundi',2=>'Mardi',3=>'Mercredi',4=>'Jeudi',5=>'Vendredi',6=>'Samedi'];
?>
<style>
    .current-day{
        background-color: #E1F0FF !important;
    }
</style>
<!--begin: Datatable-->
<div class="table-responsive">
    <table class="table table-bordered" id="dt_schedulerooms">
        <thead class="thead-light">
            <tr>
                <th><strong>Salle</strong></th>
                <?php if($dates): ?>
                <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $newDate=Carbon\Carbon::createFromFormat('Y-m-d', $d);
                    $day=$newDate->dayOfWeek;
                ?>
                <th class="<?php echo e(($currentDay==$day)?'current-day':''); ?>"><p class="text-center"><?php echo e($arrayDays[$day]); ?></p><p class="text-center"><strong><?php echo e($newDate->format('d-m-Y')); ?></strong></p></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
<div>
<!--end: Datatable-->
<script>
var dtUrl = '/api/sdt/agenda/<?php echo e($start_date); ?>/<?php echo e($nb_days); ?>';
//console.log(dtUrl);
var table = $('#dt_schedulerooms');
// begin first table
table.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
    ajax: {
        url: dtUrl,
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
            af_id : <?php echo e($af_id); ?>,
        },
    },
    lengthMenu: [5, 10, 25, 50],
    pageLength: 25,
    columnDefs: [{}],
});
</script><?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/pages/ressource/agenda/headtable.blade.php ENDPATH**/ ?>