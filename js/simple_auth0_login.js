document.addEventListener("DOMContentLoaded", function(event){
    var user_login = document.getElementById("user_login")
    var nav_link = document.getElementById("nav").getElementsByTagName("A")[0]
    var password_reset_form = document.getElementById("lostPassword").getElementsByTagName("form")[0]
    var possible_email = document.getElementById("possible_email")
    var send_password = document.getElementById("send_password")


    user_login.setAttribute('type', 'email');

    nav_link.className = "modal_trigger";
    // nav_link.dataset.toggle = 'modal';
    // nav_link.dataset.target = 'lostPassword';
    nav_link.addEventListener('click', function(e) {
      //e.preventDefault();
      possible_email.value = user_login.value
    });

    send_password.addEventListener('click', function(e) {
      e.preventDefault();
      if (password_reset_form.checkValidity()) {
      //  form.submit();
        alert("go!");
      }

    });

});
