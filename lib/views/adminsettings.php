<?php  ?>
<div class="wrap">
  <h1>Auth0 Simple Login Settings</h1>
  <form method="post" action="options.php">
    <?php settings_fields('simple-auth0-login-settings-group'); ?>
    <?php do_settings_sections('simple-auth0-login-settings-group'); ?>
    <table class="form-table">
        <tr valign="top">
          <th scope="row">Login Logo URL</th>
          <td><input type="text" name="<?php echo $login_logo_name; ?>" value="<?php echo esc_attr(get_option($login_logo_name)); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Support Email</th>
          <td><input required type="email" name="<?php echo $auth0_service->support_email_name; ?>" value="<?php echo esc_attr($auth0_service->support_email); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Auth0 Domain</th>
          <td><input required type="text" name="<?php echo $auth0_service->domain_name; ?>" value="<?php echo esc_attr($auth0_service->domain); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Auth0 Connection Name</th>
          <td><input required type="text" name="<?php echo $auth0_service->connection_name; ?>" value="<?php echo esc_attr($auth0_service->connection); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Auth0 Client ID</th>
          <td><input required type="text" name="<?php echo $auth0_service->client_id_name; ?>" value="<?php echo esc_attr($auth0_service->client_id); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Auth0 Client Secret</th>
          <td><input required type="password" name="<?php echo $auth0_service->client_secret_name; ?>" value="<?php echo esc_attr($auth0_service->client_secret); ?>" /></td>
        </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
