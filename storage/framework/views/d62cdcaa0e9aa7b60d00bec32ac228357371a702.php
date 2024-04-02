
<div class="topbar" style="display:flex;align-items: center;gap:20px;">
<?php
        $nbtasksnoread = 0;
        $alltasks = App\Models\Task::all();
        foreach($alltasks as $tasks){
                if($tasks->responsable_id == auth()->user()->contact_id)
                {
                    if($tasks->is_read == 0){
                        $nbtasksnoread++;
                    }
                }
            }
?>
<?php echo '<strong><span style="color:red;font-size:20px;">'.$nbtasksnoread.'</span></strong>'; ?>
    
    <?php if(config('layout.extras.search.display')): ?>
        <?php if(config('layout.extras.search.layout') == 'offcanvas'): ?>
            <div class="topbar-item">
                <div class="btn btn-icon btn-clean btn-lg mr-1" id="kt_quick_search_toggle">
                    <?php echo e(Metronic::getSVG("media/svg/icons/General/Search.svg", "svg-icon-xl svg-icon-primary")); ?>

                </div>
            </div>
        <?php else: ?>
            <?php if(auth()->user()->roles[0]->code!='FORMATEUR' || auth()->user()->roles[0]->code!='APPRENANT'): ?>  
            <div class="navi navi-spacer-x-0 p-0" data-container="body" data-toggle="tooltip" data-placement="top" title="Ajouter une tâche" >
            
		    
                <form  action="<?php echo e(url('/addtask')); ?>">
                    <!-- <?php echo csrf_field(); ?> -->
                    <?php echo method_field('put'); ?>
                    <?php echo csrf_field(); ?>
                    <a href="<?php echo e(url('/addtask')); ?>" class="navi-item">
                        <div class="navi-link">
                            <div class="topbar-item">
                                <div class="btn btn-icon btn-clean btn-lg mr-1" id="kt_quick_panel_toggle">
                                    <!-- <img style="width: 27px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAABmJLR0QA/wD/AP+gvaeTAAADB0lEQVRoge2azUtUURTAf2eUGZpxkYZQQ8s2IS6bNCv6cBcIhbVTCNIxg2hVGpRQ/0GLYhKhsk1GC0Mwwj7AyDTaJO2Gdir04bTQ8CPfaTEOMwzzdN7c98ZB57canvede37v3PPm+t7ANkHcCNLwUFvWlAcCYSfnKcxWCJc/dcpL0xx8pgEACpEAEAhbyn03crCtSN2Q+kMJ7gJtwL58gk1FxVGFIzHVfMYpzIowuLib298uyEquMZV2J4f+cAe47iQxrxAIo9yoSqBAb64xtiKqtAmgQtPnTvm40UT5Xlk7Nqvk4ZgeVRi3oB0bEdseSa35zSSKwWRUPkA6p1zYViQb06teaOx8+86Vu1YpkHdFUuS6QodiOiMQLrBqM1NR2Z990GksVyriU7qBmQJOnfEJ3W7k4LgiuZjskmFg2I1YhbJteqQsUmqURUoNV+5aTjhwTwM1AXqBK4AoDCaW6YlflWWTuEUVWZd4AZxJHRO4tifAUtxmM5gvRVtaWRKLmX9TuGQavygiWRK/xMcRVW5lDLFM5/BcJIfE6ckO+VopDJOuzCPTeTwVqRtSf3WAIZISP1MSDf16cA1eAyHgVdBPn+lcnonUDak/mOC5QAtJieaUhGXxFthLUuLs+4uyZDqfJyLFlgAPRLZCAlwWyZaoIN0TXkqAiyK5JCaiMp0pITDqhQS4JJKvxC4/57yQAJe2KKEET8noiYkOmW6Maf2axRugFmXk9wqt8ajZfmojjCvSNKBhoBX4m+qJxpjWr5GWmF+h1XRTuBnGIqurNJN8hvwutZzWv+xqBUaDAc57LQFuLC2hGUCFUCSmI5bFSSBYjOWUiRs9cgpAlBOpAwLPFmpoj9s8OfcCcxHluwoiwpjA2D9l7EtU5lzIzRHGIlNdctyNREzZNv+zl0VKjbJIqeH4ruXlmysTdl5FnL5DLza2FVGYheSr4eKlk5tIvx5b/zhvN8a2Ij54otCjMB6JbXFbpB/fDdgNsRVZqKavKgEWtBfyOxOX+QE8Ds5xc4vzKLPz+A8LnzkXUbeiTgAAAABJRU5ErkJggg=="> -->
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24"></rect>
                                            <path d="M4.875,20.75 C4.63541667,20.75 4.39583333,20.6541667 4.20416667,20.4625 L2.2875,18.5458333 C1.90416667,18.1625 1.90416667,17.5875 2.2875,17.2041667 C2.67083333,16.8208333 3.29375,16.8208333 3.62916667,17.2041667 L4.875,18.45 L8.0375,15.2875 C8.42083333,14.9041667 8.99583333,14.9041667 9.37916667,15.2875 C9.7625,15.6708333 9.7625,16.2458333 9.37916667,16.6291667 L5.54583333,20.4625 C5.35416667,20.6541667 5.11458333,20.75 4.875,20.75 Z" fill="#3699FF " fill-rule="nonzero" opacity="0.3"></path>
                                            <path d="M2,11.8650466 L2,6 C2,4.34314575 3.34314575,3 5,3 L19,3 C20.6568542,3 22,4.34314575 22,6 L22,15 C22,15.0032706 21.9999948,15.0065399 21.9999843,15.009808 L22.0249378,15 L22.0249378,19.5857864 C22.0249378,20.1380712 21.5772226,20.5857864 21.0249378,20.5857864 C20.7597213,20.5857864 20.5053674,20.4804296 20.317831,20.2928932 L18.0249378,18 L12.9835977,18 C12.7263047,14.0909841 9.47412135,11 5.5,11 C4.23590829,11 3.04485894,11.3127315 2,11.8650466 Z M6,7 C5.44771525,7 5,7.44771525 5,8 C5,8.55228475 5.44771525,9 6,9 L15,9 C15.5522847,9 16,8.55228475 16,8 C16,7.44771525 15.5522847,7 15,7 L6,7 Z" fill="#3699FF "></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                </form>
            </div>
            <?php endif; ?>
            
        <?php endif; ?>
    <?php endif; ?>


    
    <?php if(config('layout.extras.cart.display')): ?>
        <div class="dropdown">
            
            <div class="topbar-item"  data-toggle="dropdown" data-offset="10px,0px">
                <div class="btn btn-icon btn-clean btn-dropdown btn-lg mr-1">
                    <?php echo e(Metronic::getSVG("media/svg/icons/Shopping/Cart3.svg", "svg-icon-xl svg-icon-primary")); ?>

                </div>
            </div>

            
            <div class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-xl dropdown-menu-anim-up">
                <form>
                    <?php echo $__env->make('layout.partials.extras.dropdown._cart', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </form>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(config('layout.header.topbar.quick-panel.display')): ?>
        <div class="topbar-item">
            <div class="btn btn-icon btn-clean btn-lg mr-1" id="kt_quick_panel_toggle">
                <?php echo e(Metronic::getSVG("media/svg/icons/Layout/Layout-4-blocks.svg", "svg-icon-xl svg-icon-primary")); ?>

            </div>
        </div>
    <?php endif; ?>
    
    <?php if(config('layout.extras.languages.display')): ?>
        <div class="dropdown">
            <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                <div class="btn btn-icon btn-clean btn-dropdown btn-lg mr-1">
                    <img class="h-20px w-20px rounded-sm" src="<?php echo e(asset('media/svg/flags/226-united-states.svg')); ?>" alt=""/>
                </div>
            </div>

            <div class="dropdown-menu p-0 m-0 dropdown-menu-anim-up dropdown-menu-sm dropdown-menu-right">
                <?php echo $__env->make('layout.partials.extras.dropdown._languages', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(config('layout.extras.user.display')): ?>
        <?php if(config('layout.extras.user.layout') == 'offcanvas'): ?>
            <div class="topbar-item">
                <div class="btn btn-icon w-auto btn-clean d-flex align-items-center btn-lg px-2" id="kt_quick_user_toggle">
                    <span class="text-muted font-weight-bold font-size-base d-none d-md-inline mr-1">Bonjour,</span>
                    <span class="text-dark-50 font-weight-bolder font-size-base d-none d-md-inline mr-3"><?php echo e(Auth::user()->name); ?></span>
                    <span class="symbol symbol-35 symbol-light-success">
                        <span class="symbol-label font-size-h5 font-weight-bold"><?php echo e(substr(Auth::user()->name, 0, 1)); ?></span>
                    </span>
                </div>
            </div>
        <?php else: ?>
            <div class="dropdown">
                
                <div class="topbar-item" data-toggle="dropdown" data-offset="0px,0px">
                    <div class="btn btn-icon w-auto btn-clean d-flex align-items-center btn-lg px-2">
                        <span class="text-muted font-weight-bold font-size-base d-none d-md-inline mr-1">Bonjour,</span>
                        <span class="text-dark-50 font-weight-bolder font-size-base d-none d-md-inline mr-3"><?php echo e(Auth::user()->name); ?></span>
                        <span class="symbol symbol-35 symbol-light-success">
                            <span class="symbol-label font-size-h5 font-weight-bold"><?php echo e(substr(Auth::user()->name, 0, 1)); ?></span>
                        </span>
                    </div>
                </div>

                
                <div class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg p-0">
                    <?php echo $__env->make('layout.partials.extras.dropdown._user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/layout/partials/extras/_topbar.blade.php ENDPATH**/ ?>