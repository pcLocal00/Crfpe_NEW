var _showLoader = function(id_element) {
    $('#'+id_element).html('<i class="fas fa-spinner fa-spin"></i>');
}
var _hideLoader = function(id_element) {
    $('#'+id_element).html('');
}
var _showResponseMessage = function(type,msgText,duration=1500) {
    swal.fire({
        html: msgText,
        icon: type,
        buttonsStyling: false,
        confirmButtonText: '<i class="far fa-times-circle"></i> Fermer',
        customClass: {
            confirmButton: "btn btn-light-primary"
        },
        timer: duration
    }).then(function() {});
}
var _loadDatasForSelectOptions = function(select_id,param_code,selected_value = 0,use_code = 0) {
    //if ($('#'+select_id).find("option").size() == 1) {
        $('#'+select_id).empty();
        if(select_id==='statusSelect' || select_id==='statesSelect' || select_id==='typesSelect' || select_id==='typesDispositifSelect' || select_id==='typesRessourcesSelect'){
            $('#'+select_id).empty().append('<option value="">Sélectionner</option>');
            if(select_id==='typesRessourcesSelect'){
              $('#'+select_id).empty().append('<option value="">Toutes les types</option>');
            }
        }
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var url = '/api/select/options';
        $.ajax({
            url: url,
            dataType: 'json',
            type: 'POST',
            data: {_token:CSRF_TOKEN,param_code: param_code,use_code: use_code},
            success: function(response) {
              var array = response;
              if (array != '')
              {
                for (i in array) {
                 $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
               }
              }
            },
            error: function(x, e) {

            }
        }).done(function() {
            if(selected_value!=0 && selected_value!=''){
                $('#'+select_id+' option[value="'+selected_value+'"]').attr('selected', 'selected');
            }
          });
    //}
}
var _loadDatasEntitiesForSelectOptions = function(select_id,entity_id,entity_type,$is_former,selected_value = 0) {
        //$('#'+select_id).empty();
        $.ajax({
            url: '/api/select/options/entities/'+entity_id+'/'+entity_type+'/'+$is_former,
            dataType: 'json',
            success: function(response) {
              var array = response;
              if (array != '')
              {
                for (i in array) {
                 $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
               }
              }
            },
            error: function(x, e) {

            }
        }).done(function() {
            if(selected_value!=0 && selected_value!=''){
                $('#'+select_id+' option[value="'+selected_value+'"]').attr('selected', 'selected');
            }
          });
}

var _loadDatasFormationsForSelectOptions = function(select_id,selected_value = 0,autorize_af=1) {
    $.ajax({
        url: '/api/select/options/formations/'+autorize_af,
        dataType: 'json',
        success: function(response) {
          var array = response;
          if (array != '')
          {
            for (i in array) {
             $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
           }
          }
        },
        error: function(x, e) {

        }
    }).done(function() {
        if(selected_value!=0 && selected_value!=''){
            $('#'+select_id+' option[value="'+selected_value+'"]').attr('selected', 'selected');
        }
      });
}
var _loadDatasPricesForSelectOptions = function(select_id,af_id,entity_type,selected_value = 0) {
    $('#'+select_id).empty();
    $.ajax({
        url: '/api/select/options/prices/'+af_id+'/'+entity_type,
        dataType: 'json',
        success: function(response) {
          var array = response;
          if (array != '')
          {
        //      alert("yes");
            $('#'+select_id).html("<option>Sélectionnez un tarif</option>");
            for (i in array) {
             $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
           }
          }
        },
        error: function(x, e) {

        }
    }).done(function() {
        if(selected_value!=0 && selected_value!=''){
            $('#'+select_id+' option[value="'+selected_value+'"]').attr('selected', 'selected');
        }
      });
}
var _loadDatasPricesForFormersOptions = function(select_id,selected_value = 0) {
  $('#'+select_id).empty();
  $.ajax({
      url: '/api/select/options/prices/formers',
      dataType: 'json',
      success: function(response) {
        var array = response;
        if (array != '')
        {
          for (i in array) {
           $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
         }
        }
      }
  }).done(function() {
      if(selected_value!=0 && selected_value!=''){
          $('#'+select_id+' option[value="'+selected_value+'"]').attr('selected', 'selected');
      }
    });
}
var _loadDatasSessionsForSelectOptions = function(select_id,af_id,selected_value = 0) {
    $.ajax({
        url: '/api/select/options/sessions/'+af_id,
        dataType: 'json',
        success: function(response) {
          var array = response;
          if (array != '')
          {
            for (i in array) {
             $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
           }
          }
        },
        error: function(x, e) {

        }
    }).done(function() {
        if(selected_value!=0 && selected_value!=''){
            $('#'+select_id+' option[value="'+selected_value+'"]').attr('selected', 'selected');
        }
      });
}
function calculateHours(nb_days,input_hours_id){
  nb_hours = nb_days*7;
  $("#"+input_hours_id).val(nb_hours);
}
function timeStringToFloat(time) {
  var hoursMinutes = time.split(/[.:]/);
  var hours = parseInt(hoursMinutes[0], 10);
  var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
  return hours + minutes / 60;
}
function convertFloatToTime(number) {
  // Check sign of given number
  var sign = (number >= 0) ? 1 : -1;
  // Set positive value of number of sign negative
  number = number * sign;
  // Separate the int from the decimal part
  var hour = Math.floor(number);
  var decpart = number - hour;
  var min = 1 / 60;
  // Round to nearest minute
  decpart = min * Math.round(decpart / min);
  var minute = Math.floor(decpart * 60) + '';
  // Add padding if need
  if (minute.length < 2) {
      minute = '0' + minute;
  }
  // Add Sign in final result
  sign = sign == 1 ? '' : '-';
  // Concate hours and minutes
  time = sign + hour + 'h' + minute + 'min';
  return time;
}
var _loadDatasEntitiesForSelectEnrollmentsOptions = function(select_id, entity_id, entity_type,is_former, selected_value = 0) {
  $.ajax({
      url: '/api/select/options/entities/' + entity_id + '/' + entity_type+'/'+is_former,
      dataType: 'json',
      success: function(response) {
          var array = response;
          if (array != '') {
              for (i in array) {
                  $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                      "</option>");
              }
          }
      },
      error: function(x, e) {

      }
  }).done(function() {
      if (selected_value != 0 && selected_value != '') {
          $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
      }
      //_loadContacts();
  });
}
var _loadDatasIntervenantsForSelectOptions = function(select_id, af_id,selected_value) {
  $.ajax({
      url: '/api/select/options/members/' + af_id ,
      dataType: 'json',
      success: function(response) {
          var array = response;
          if (array != '') {
              for (i in array) {
                  $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +"</option>");
              }
          }
      },
      error: function(x, e) {

      }
  }).done(function() {
      if (selected_value != 0 && selected_value != '') {
          $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
      }
  });
}
var _loadDatasRessourcesForSelectOptions = function(select_id,res_id,type,selected_value = 0) {
  $.ajax({
      url: '/api/select/options/ressources/'+res_id+'/'+type,
      dataType: 'json',
      success: function(response) {
        var array = response;
        if (array != '')
        {
          for (i in array) {
           $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
         }
        }
      },
      error: function(x, e) {

      }
  }).done(function() {
      if(selected_value!=0 && selected_value!=''){
          $('#'+select_id+' option[value="'+selected_value+'"]').attr('selected', 'selected');
      }
    });
}
