var _login;
var _errorServer ="Désolé, il semble que des erreurs ont été détectées, veuillez réessayer plus tard.";
var validation;

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

validation = FormValidation.formValidation(
    KTUtil.getById("kt_login_signin_form"),
    {
        fields: {
            email: {
                validators: {
                    notEmpty: {
                        message: "L'email est obligatoire."
                    }
                }
            },
            password: {
                validators: {
                    notEmpty: {
                        message: "Le mot de passe est obligatoire."
                    }
                }
            }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            submitButton: new FormValidation.plugins.SubmitButton(),
            bootstrap: new FormValidation.plugins.Bootstrap()
        }
    }
);

$("#kt_login_signin_submit").on("click", function(e) {
    e.preventDefault();
    validation.validate().then(function(status) {
        if (status == "Valid") {
            _showLoader("ID_LOGIN_LOADER");
            var formData = $('#kt_login_signin_form').serializeArray();
            $.ajax({
                type: 'post',
                url: '/login',
                data: formData,
                dataType: 'json',
                cache : false,
                success: function(result) {
                        if(result.success){
                            _hideLoader("ID_LOGIN_LOADER");
                            window.location = "/";
                        }else {
                            _hideLoader("ID_LOGIN_LOADER");
                            _showResponseMessage('error', result.msg);
                        }
                },
                error: function(error) {
                    var msg ="Oups !! quelque chose ne va pas. veuillez réessayer plus tard.";
                    _hideLoader("ID_LOGIN_LOADER");
                    _showResponseMessage('error', msg);
                },
                complete: function(resultat, statut) {
                    
                }
            });    

        } else {
            _showResponseMessage('error', _errorServer);
        }
    });
});
