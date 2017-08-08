<?php
require 'vendor/autoload.php';
require 'lib/Auth0Service.php';
require 'lib/PasswordManagementOverride.php';
require 'lib/AuthenticationOverride.php';
require 'lib/RegistrationOverride.php';

/*
Plugin Name: SimpleAuth0Login
Description: Plugin using the wordpress' authentication flow to authenticate with auth0 instead of overriding it.
Author: cwhittl
Version: 0.1
*/

//TODO Need to check if user exists when user tries to reset password, if user doesn't exist and sign up is enabled can you send them to sign up?
//TODO email and profile change?
class SimpleAuth0Login
{
    protected $auth0_service = null;
    public $login_logo_name = "simple_auth0_login_logo";

    function __construct()
    {
        $this->auth0_service = new Auth0Service();

        add_action('login_enqueue_scripts', array($this,"enqueue_scripts"));
        add_action('admin_enqueue_scripts', array($this,"enqueue_scripts"));
        add_action('enqueue_scripts', array($this,"enqueue_scripts"));

        add_action('login_footer', array($this,"init_shared_javascript"));
        add_action('admin_footer', array($this,"init_shared_javascript"));
        add_action('wp_footer', array($this,"init_shared_javascript"));

        add_action('admin_menu', array($this,'create_admin_menu'));

        new PasswordManagementOverride($this->auth0_service, esc_attr(get_option($this->login_logo_name)));
        new AuthenticationOverride($this->auth0_service);
        new RegistrationOverride($this->auth0_service);
        $this->fixLoginForms();
    }

    function enqueue_scripts()
    {
        wp_enqueue_script('simple-auth0-promise-polyfill',  plugins_url('includes/polyfills/promise.min.js', __FILE__));
        wp_enqueue_script('simple-auth0-fetch-polyfill',  plugins_url('includes/polyfills/fetch.js', __FILE__));
        wp_enqueue_script('simple-auth0-shared',  plugins_url('includes/SimpleAuth0LoginShared.js', __FILE__));
    }

    function init_shared_javascript()
    {
        ob_start();
        include "lib/views/shared.php";
        echo ob_get_clean();
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

        register_setting('simple-auth0-login-settings-group', $this->login_logo_name);
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
          <th scope="row">Login Logo URL</th>
          <td><input type="text" name="<?php echo $this->login_logo_name; ?>" value="<?php echo esc_attr(get_option($this->login_logo_name)); ?>" /></td>
          </tr>
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
