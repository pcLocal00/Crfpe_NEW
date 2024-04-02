@php
    use Carbon\Carbon;
    $dateNow = Carbon::now()->format('Y-m-d');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modal_depot_devis_title"><i class="flaticon-edit"></i> Depot de devis</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="depotDevis" class="form" enctype="multipart/form-data">
    @csrf
    <div class="modal-body" id="modal_depot_devis_body">
        <div data-scroll="true" data-height="550">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="max_nb_trainees">Date de devis : </label>
                        <label for="max_nb_trainees"> {{ $dateNow }} </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="max_nb_trainees">Action Formation : </label>
                        <label for="max_nb_trainees">{{ $af->title }} </label>
                        <input class="form-control " type="text" name="af_id" value="{{ $af->id }}" hidden />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="max_nb_trainees">Montant de devis HT : </label>
                        <input class="form-control " type="number" min="1" name="montant_ht" value=""
                            id="montant_ht" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="max_nb_trainees">TVA : </label>
                        <input class="form-control " type="number" min="1" name="tva" value=""
                            id="v" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="max_nb_trainees">Montant de devis TTC : </label>
                        <input class="form-control " type="number" min="1" name="montant_ttc" value=""
                            id="montant_ttc" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div id="uploader">
                        <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler
        </button>
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-check"></i> Valider <span id="BTN_SAVE_POINTAGE"></span>
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('#depotDevis').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $('#uploader').plupload('getFiles').forEach(file => {
                formData.append('attachments[]', file.getNative());
            });
            $.ajax({
                url: "{{ route('depot_devis') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        _showResponseMessage('success', response.msg);
                        $('#modal_depot_devis').modal('hide');
                    } else {
                        _showResponseMessage('error', response.msg);
                    }
                },
                error: function(xhr, status, error) {
                    _showResponseMessage('error','Veuillez vérifier les champs du formulaire...');
                }
            });
        });
    });

    $(function() {
        $("#uploader").plupload({
            runtimes: 'html5,flash,silverlight,html4',
            url: '../upload.php',
            max_file_count: 20,

            chunk_size: '1mb',
            resize: {
                width: 200,
                height: 200,
                quality: 90,
            },

            filters: {
                max_file_size: '1000mb',
                mime_types: [{
                        title: "Image files",
                        extensions: "jpg,gif,png"
                    },
                    {
                        title: "Zip files",
                        extensions: "zip"
                    },
                    {
                        title: "Pdf files",
                        extensions: "pdf"
                    }
                ]
            },
            rename: true,
            sortable: true,
            dragdrop: true,
            views: {
                list: true,
                active: 'thumbs'
            },
            flash_swf_url: '../../js/Moxie.swf',
            silverlight_xap_url: '../../js/Moxie.xap',
            init: {
                FileFiltered: function(up, file) {
                    console.log(file.getNative());
                }
            }
        });
    });
</script>
