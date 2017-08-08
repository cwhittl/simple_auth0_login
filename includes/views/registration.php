<?php  ?>
<script>
document.addEventListener("DOMContentLoaded", function(event){
  var registerform = document.getElementById("registerform");
  if(registerform){
    var user_email = registerform.querySelector("#user_email");
    var user_login = registerform.querySelector("#user_login");
    user_email.onkeyup = function(e){
      user_login.value = e.target.value;
    }
  }
});
</script>
<style>
#registerform label[for="user_login"] {
  display: none;
}
</style>
