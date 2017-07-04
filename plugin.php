<?php
require 'vendor/autoload.php';
require 'lib/Auth0Service.php';
require 'includes/LostPasswordOverride.php';
require 'includes/AuthenticationOverride.php';
require 'includes/RegistrationOverride.php';

/*
Plugin Name: SimpleAuth0Login
Description: Plugin using the wordpress' authentication flow to authenticate with auth0 instead of overriding it.
Author: cwhittl
Version: 0.1
*/

//TODO Need to check if user exists when user tries to reset password, if user doesn't exist and sign up is enabled can you send them to sign up?
//TODO Need to add user auth0 create to sign up or short circuit and add your own
class SimpleAuth0Login
{
    protected $auth0_service = null;
    function __construct()
    {
        $this->auth0_service = new Auth0Service();
        new LostPasswordOverride($this->auth0_service);
        new AuthenticationOverride($this->auth0_service);
        new RegistrationOverride($this->auth0_service);
        add_action('admin_menu', array($this,'create_admin_menu'));
        $this->fixLoginForms();
    }


    function fixLoginForms()
    {
        add_filter('gettext',  array($this,'register_text'));
        add_filter('ngettext',  array($this,'register_text'));

    }

    function register_text( $translated )
    {
         $translated = str_ireplace('Username or Email Address',  'Email Address',  $translated);
         $translated = str_ireplace('username, email address',  'email address',  $translated);
         return $translated;
    }
    function create_admin_menu()
    {
            add_menu_page('Simple Auth0 Login', 'Simple Auth0 Login', 'administrator', "simple_auth0_login",  array($this,'admin_settings_page'),  "dashicons-lock");
            add_action('admin_init', array($this,'register_admin_settings'));
    }

    function register_admin_settings()
    {
        register_setting('simple-auth0-login-settings-group', $this->auth0_service->client_id_name);
        register_setting('simple-auth0-login-settings-group', $this->auth0_service->client_secret_name);
        register_setting('simple-auth0-login-settings-group', $this->auth0_service->connection_name);
        register_setting('simple-auth0-login-settings-group', $this->auth0_service->domain_name);
    }

    function admin_settings_page()
    {
        ?>
      <div class="wrap">
        <h1>Auth0 Simple Login Settings</h1>

        <form method="post" action="options.php">
        <?php settings_fields('simple-auth0-login-settings-group'); ?>
        <?php do_settings_sections('simple-auth0-login-settings-group'); ?>
      <table class="form-table">
          <tr valign="top">
          <th scope="row">Auth0 Domain</th>
          <td><input type="text" name="<?php echo $this->auth0_service->domain_name; ?>" value="<?php echo esc_attr(get_option($this->auth0_service->domain_name)); ?>" /></td>
          </tr>
          <tr valign="top">
          <th scope="row">Auth0 Connection Name</th>
          <td><input type="text" name="<?php echo $this->auth0_service->connection_name; ?>" value="<?php echo esc_attr(get_option($this->auth0_service->connection_name)); ?>" /></td>
          </tr>
          <tr valign="top">
          <th scope="row">Auth0 Client ID</th>
          <td><input type="text" name="<?php echo $this->auth0_service->client_id_name; ?>" value="<?php echo esc_attr(get_option($this->auth0_service->client_id_name)); ?>" /></td>
          </tr>
          <tr valign="top">
          <th scope="row">Auth0 Client Secret</th>
          <td><input type="password" name="<?php echo $this->auth0_service->client_secret_name; ?>" value="<?php echo esc_attr(get_option($this->auth0_service->client_secret_name)); ?>" /></td>
          </tr>
      </table>
        <?php submit_button(); ?>
      </form>
        </div>
    <?php }

}
new SimpleAuth0Login();

    ?>
