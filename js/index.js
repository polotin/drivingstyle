function login_success() {
    var my_form =document.getElementById("my_form");
    var login_form =document.getElementById("login_form");
    my_form.style.visibility = "visible";
    login_form.style.display = "none";
}

function login_failed() {
    alert("login failed. invalid id or pwd");
}
