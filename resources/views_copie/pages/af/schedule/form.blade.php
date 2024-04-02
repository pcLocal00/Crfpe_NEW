<style>
  #modal_form_afintervenant .modal-dialog-scrollable .modal-content{
    overflow : auto !important;
  }
</style>
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_afintervenant_title"><i class="flaticon-edit"></i>Listes des séances de l'intervenent</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<?php if($temoin == 0) {?>
<form id="formAfintervenant" class="form">
    <div class="modal-body" id="modal_form_afintervenant_body">
       @csrf
       <table class="table">
        <thead>
          <tr>
            <th scope="col"></th>
            <th scope="col">Séances</th>
            <th scope="col">Date début/heure</th>
            <th scope="col">Date fin/heure</th>
            <th scope="col">Nombre d'heure</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($schedules as $ss_schedules){ ?>
          <tr>
            <th scope="row"><input type="checkbox" class="checksche" value="<?= $ss_schedules->id ?>"></th>
            <th scope="row"><?= "(".$ss_schedules->sessiondate->session->code.")"." ".$ss_schedules->sessiondate->session->title ?></th>
            <td><?=$ss_schedules->start_hour?></td>
            <td><?=$ss_schedules->end_hour?></td>
            <td><?=$ss_schedules->duration." h"?></td>
            <td><button class="btn btn-sm btn-clean btn-icon test-class" data-id-schedule="<?php echo $ss_schedules->id ?>" data-id-member="<?php echo $member_id ?>"  data-toggle="tooltip" data-original-title="Demande de devis"> <i class="flaticon2-black-back-closed-envelope-shape"></i></button></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <center><input type="hidden" id="hid_member" value="<?=$member_id?>"><button class="btn btn-sm btn-clean btn-icon" id="btn_alotreq" data-toggle="tooltip" data-original-title="Demande de devis"><i class="flaticon2-black-back-closed-envelope-shape"></i></button></center>
    </div>
</form>
<?php } else {?>
  <form id="formAfintervenant" class="form">
    <div class="modal-body" id="modal_form_afintervenant_body">
       @csrf
       <strong>Toutes les séances on été envoyé à l'intervenant .</strong>
    </div>
</form>
<?php } ?>


<script>
function af_call(member_id) {

  var modal_id = 'modal_form_afintervenant';
  var modal_content_id = 'modal_form_afintervenant_content';
  var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
  $('#' + modal_content_id).html(spinner);
  $.ajax({
      url: '/form/selectionafintervenant/' + member_id,
      type: 'GET',
      dataType: 'html',
      success: function(html, status) {
          $('#' + modal_content_id).html(html);
      }
  });

}

</script>
<script>

  function _formSendDevis(id_schedule,member_id) {
      var modal_id = 'modal_form_mail';
      var modal_content_id = 'modal_form_mail_content';
      var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
      $('#' + modal_id).modal('show');
      $('#' + modal_content_id).html(spinner);

      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      
       $.ajax({
         url: "/form/mail/devis/" + id_schedule + '/' + member_id,
          type: "POST",
          dataType: "html",
          success: function(html, status) {
              $("#" + modal_content_id).html(html);
          },
      });
  };

  $('.test-class').on('click',function(e){
    e.preventDefault();
    var id_schedule =  $(this).attr('data-id-schedule');
    var member_id =  $(this).attr('data-id-member');
    _formSendDevis(id_schedule,member_id);
    $.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
		});

    $.ajax({     
            url: '/form/envoyerdemande/' + id_schedule + '/' + member_id,
            type: 'POST',
            dataType: 'html',
            success: function(html, status) {
              Swal.fire("succès", "Demande de devis envoyé avec succès!", "success");
              af_call(member_id);
            },
            error :  function(resp) {
              console.log(resp);
            },
        });
    });
</script>

<script>

$( "#btn_alotreq" ).on('click',function(e) {
  e.preventDefault();
 
  if ($('.checksche').is(':checked')){

    member_id = $("#hid_member").val();
    var tabidsche = new Array();
				var j = 0;
				$('.checksche').each(function() {
					var checked = $(this).is(":checked");
					if (checked) {
						tabidsche[j] = $(this).val();
						j++;
					}
				});

                $.ajaxSetup({
                    headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/form/envoyerdemandealot/' + member_id,
                    type: "POST",
                    dataType: 'html',
                    data:{
                      tabidsche : tabidsche
                    },
                    success: function(response) {
                      Swal.fire("succès", "Demande de devis envoyé avec succès!", "success");
                      af_call(member_id);
                    }
              });
    }
    else{
      Swal.fire("Avertissement", "Veuiller cocher les séances!", "warning");
    }
});

</script>