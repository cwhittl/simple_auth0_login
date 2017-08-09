<?php
  $reg_pass1 = ( ! empty($_POST['reg_pass1']) ) ? trim($_POST['reg_pass1']) : '';
  $reg_pass2 = ( ! empty($_POST['reg_pass2']) ) ? trim($_POST['reg_pass2']) : '';
?>
<style>
#registerform label[for="user_login"] {
  display: none;
}
</style>
<p>
  <label for="reg_pass"><?php _e('Password', 'mydomain') ?><br />
    <input type="password" name="reg_pass1" id="reg_pass1" class="input" value="<?php echo esc_attr(wp_unslash($reg_pass1)); ?>" />
  </label>
  <label for="reg_pass"><?php _e('Password Again', 'mydomain') ?><br />
    <input type="password" name="reg_pass2" id="reg_pass2" class="input" value="<?php echo esc_attr(wp_unslash($reg_pass2)); ?>" />
  </label>
</p>
