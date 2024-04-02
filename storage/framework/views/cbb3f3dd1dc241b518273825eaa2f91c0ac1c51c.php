



<?php $__env->startSection('content'); ?>



<div class="row">
    <div class="col-lg-12 col-xxl-12">
        <div class="row s_nb_column_fixed" style="width: 100%;padding-right: 15px;padding-left: 15px;margin-right: auto;margin-left: auto;margin-top: 250px;">
            <div class="col-lg-12 s_title" data-name="Title">
                <h2 class="s_title_thin" style="text-align: center;">
                    <font style="font-size: 48px;">
                        <?php if(auth()->user()->roles[0]->code=='APPRENANT'): ?>  
                            <b>Bienvenue sur l'Espace Etudiant</b>
                        <?php endif; ?>
                        <?php if(auth()->user()->roles[0]->code=='FORMATEUR'): ?>  
                            <b>Bienvenue sur l'Espace Formateur</b>
                        <?php endif; ?>
                    </font>
                </h2>
            </div>
            <div class="col-lg-12 s_text pt16 pb16" data-name="Text">
                <p class="lead" style="text-align: center;">
                    <span style="text-align: left;">Cet espace offre un accès centralisé aux différents services de la platforme de formation pour CRFPE.</span>

                    <br>
                </p>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layout.default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/pages/dashboard1.blade.php ENDPATH**/ ?>