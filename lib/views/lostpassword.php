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
      <h4>Please enter or verify your email and hit send.</h4>
      <input required type="email" width="100%" class="input" name="possible_email" id="possible_email" placeholder="Enter your Email here"/>
      <input type="submit" id="send_password" class="button button-primary button-large" value="Send new password"/>
    </form>
    <button class="modal__close modal_close">&#10005;</button>
  </div>
</div>
