<?php
$logo_css = "";
if($login_logo_url) {
    $logo_css = "#login h1 a, .login h1 a {
    background-image: url($login_logo_url);
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;
  }";
}
?>
<script>
document.addEventListener("DOMContentLoaded", function(event){
    var user_login = document.getElementById("user_login")
    var nav_links = document.getElementById("nav").getElementsByTagName("A")
    var password_reset_form = document.getElementById("lostPassword").getElementsByTagName("form")[0]
    var possible_email = document.getElementById("possible_email")
    var send_password = document.getElementById("send_password")

    user_login.setAttribute('type', 'email');
    for (i = 0; i < nav_links.length; ++i) {
      var nav_link = nav_links[i];
      nav_link.className = "modal_trigger";
      nav_link.addEventListener('click', function(e) {
        possible_email.value = user_login.value
      });
    }

    send_password.addEventListener('click', function(e) {
      e.preventDefault();
      if (password_reset_form.checkValidity()) {
        simpleAuth0LoginShared.resetPassword(possible_email.value).then(function(data){
          if(data.success && (data.success === "true" || data.success === true )){
            alert("A password request has been sent to your email.");
          }else{
            console.log(data);
            alert("There was an unknown error, please contact <?php echo $support_email; ?> if this continues");
          }
        },function(data){
          console.log(data);
          alert("There was an unknown error, please contact <?php echo $support_email; ?> if this continues");
        });
      }else{
        alert("Please include a valid email address");
      }
    });
});
</script>
<style>
<?php echo $logo_css; ?>
.login #nav {
  text-align: center;
}
#lostPassword #possible_email {
  font-size: 24px;
  width: 100%;
  padding: 3px;
  margin: 2px 6px 16px 0;
}
</style>

<div id="<?php echo $this->modal_name; ?>" class="modal">
  <div class="modal__overlay modal_overlay"></div>
  <div id="lostPassword" class="modal__container">
    <form action="#">
      <h3>Need a new password?</h3>
      <h4>Please verify your email and hit send.</h4>
      <input required type="email" width="100%" class="input" name="possible_email" id="possible_email" placeholder="Enter your Email here"/>
      <input type="submit" id="send_password" class="button button-primary button-large" value="Send new password"/>
    </form>
    <button class="modal__close modal_close">&#10005;</button>
  </div>
</div>
