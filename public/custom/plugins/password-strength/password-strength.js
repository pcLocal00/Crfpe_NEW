const testPasswRegexp = (passw, regexp) => {
    return regexp.test(passw);
};

const testPassw = passw => {
    let strength = "none";
    const moderate = /(?=.*[A-Z])(?=.*[a-z]).{5,}|(?=.*[\d])(?=.*[a-z]).{5,}|(?=.*[\d])(?=.*[A-Z])(?=.*[a-z]).{5,}/g;
    const strong = /(?=.*[A-Z])(?=.*[a-z])(?=.*[\d]).{7,}|(?=.*[\!@#$%^&*()\\[\]{}\-_+=~`|:;"'<>,./?])(?=.*[a-z])(?=.*[\d]).{7,}/g;
    const extraStrong = /(?=.*[A-Z])(?=.*[a-z])(?=.*[\d])(?=.*[\!@#$%^&*()\\[\]{}\-_+=~`|:;"'<>,./?]).{9,}/g;
    if (testPasswRegexp(passw, extraStrong)) {
        strength = "extra"; //bg-success
    } else if (testPasswRegexp(passw, strong)) {
        strength = "strong"; //bg-info
    } else if (testPasswRegexp(passw, moderate)) {
        strength = "moderate"; //bg-warning
    } else if (passw.length > 0) {
        strength = "weak"; //bg-danger
    }
    return strength;
};

const testPasswError = passw => {
    const errorSymbols = /\s/g;
    return testPasswRegexp(passw, errorSymbols);
};

function calculate_strength_password(password_id) {
    var passd = $("#" + password_id).val();
    var strength = "none";
    var percent = 0;
    if(passd){
      var strength = testPassw(passd);
      if (strength == "extra") {
          percent = 100;
      } else if (strength == "strong") {
          percent = 75;
      } else if (strength == "moderate") {
          percent = 50;
      } else if (strength == "weak") {
          percent = 25;
      }
    }
    return percent;
}

function get_progress_bar_strength_password(percent, lang,progressbar_id) {
    var cssClass = "bg-danger";
    if (percent == 100) {
        cssClass = "bg-success";
        //percent=100;
        strength = lang == "fr" ? "Très Fort" : "Extra";
    } else if (percent == 75) {
        cssClass = "bg-info";
        //percent=75;
        strength = lang == "fr" ? "Fort" : "Strong";
    } else if (percent == 50) {
        cssClass = "bg-warning";
        //percent=50;
        strength = lang == "fr" ? "Normal" : "Moderate";
    } else if (percent == 25) {
        cssClass = "bg-danger";
        //percent=25;
        strength = lang == "fr" ? "Faible" : "Weak";
    }
    var htmlProgressBar =
        '<div data-toggle="tooltip" data-placement="top" title="' +strength +'" class="progress"><div class="progress-bar '+cssClass+'" role="progressbar" aria-valuenow="' +
        percent +
        '" aria-valuemin="' +
        percent +
        '" aria-valuemax="100" style="width:' +
        percent +
        '%">' +
        percent +
        "% " +
        strength +
        "</div></div>";
      $("#"+progressbar_id).html(htmlProgressBar);
}
