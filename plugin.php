<?php
require 'vendor/autoload.php';
require 'includes/LostPasswordOverride.php';

use Auth0\SDK\API\Authentication;
use Firebase\JWT\JWT;
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

    private $connection = null;
    private $domain = null;
    private $client_id = null;
    private $client_secret = null;
    private $scope = null;

    function __construct()
    {
        new LostPasswordOverride();
        $this->domain_name = "auth0_domain";
        $this->domain = get_option($this->domain_name);
        $this->connection_name = "connection";
        $this->connection = get_option($this->connection_name);
        $this->client_id_name = "client_id";
        $this->client_id = get_option($this->client_id_name);
        $this->client_secret_name = "client_secret";
        $this->client_secret = get_option($this->client_secret_name);
        $this->scope="openid profile email";

        add_action('admin_menu', array($this,'create_admin_menu'));

        add_action(
            'plugins_loaded', function () {
                remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
                add_filter('authenticate', array( $this, 'authenticate'), 20, 3);
            }
        );



        $this->fixLoginForms();
    }




    function reset_password( $user_email )
    {
        //die('got here');
        $authentication = $this->createAuthentication();
        $test = $authentication->dbconnections_change_password($user_email, $this->connection);
        add_filter(
            'allow_password_reset', function ($allow, $user_id) {
                return new WP_Error('no_password_reset', __(json_encode($test)));
            }, 99, 2
        );
        return true;
    }

    public function authenticate( $user, $user_email, $password )
    {
        if (is_a($user, 'WP_User') ) {
            return $user;
        }
        if (empty($user_email) || empty($password) ) {
            if (is_wp_error($user) ) {
                return $user;
            }
            return false;
        }
        if (!empty($user_email) && is_email($user_email) ) {
            $authentication = $this->createAuthentication();
            if($user_email) {
                $options = array("username"=>$user_email,"password"=>$password,"realm"=>$this->connection,"scope"=>$this->scope);
                $result = (object) $authentication->login($options);
                //echo json_encode($result);
                if(isset($result->error)) {
                    $error = new WP_Error();
                    $error->add($result->error, __('<strong>ERROR</strong>: ' . $result->error_description));
                    return $error;
                }else{
                    $user_profile = JWT::decode($result->id_token, $this->client_secret, array('HS256', 'RS256'));
                    $is_new = false;
                    $user = get_user_by('email', $user_email);
                    if(!$user) {
                        $is_new = true;
                        $user_id = wp_create_user($user_email, $password, $user_email);
                        $user = get_user_by('ID', $user_id);
                    }else{
                        $user_id = $user->user_email;

                    }
                    do_action('auth0_user_login', $user_id, $user_profile, $is_new, $result->id_token, $result->access_token);
                    //TODO validate above works
                    return $user;
                }
                die(json_encode($test));
            }
        }
        if (!empty($user_email) || !empty($password) ) {
            return false;
        }
    }

    function fixLoginForms()
    {
        add_filter('gettext',  array($this,'register_text'));
        add_filter('ngettext',  array($this,'register_text'));
        add_action(
            'login_enqueue_scripts', function () {
                wp_enqueue_script('simple-auth0-login',  plugins_url('js/simple_auth0_login.js', __FILE__));
                wp_enqueue_style('simple-auth0-login',  plugins_url('css/simple_auth0_login.css', __FILE__));
                wp_enqueue_script('simple-auth0-login-modal',  plugins_url('lib/modal/modal.js', __FILE__));
                wp_enqueue_style('simple-auth0-login-modal',  plugins_url('lib/modal/modal.css', __FILE__));
            }, 10
        );


    }

    function register_text( $translated )
    {
         $translated = str_ireplace('Username or Email Address',  'Email Address',  $translated);
         $translated = str_ireplace('username, email address',  'email address',  $translated);
         return $translated;
    }

    private function createAuthentication()
    {
        return new Authentication(
            $this->domain,
            $this->client_id,
            $this->client_secret,
            $this->scope,
            ['http_errors' => false]
        );
    }

    function create_admin_menu()
    {
            add_menu_page('Simple Auth0 Login', 'Simple Auth0 Login', 'administrator', "simple_auth0_login",  array($this,'admin_settings_page'),  "dashicons-lock");
            add_action('admin_init', array($this,'register_admin_settings'));
    }
    function register_admin_settings()
    {
        register_setting('simple-auth0-login-settings-group', $this->client_id_name);
        register_setting('simple-auth0-login-settings-group', $this->client_secret_name);
        register_setting('simple-auth0-login-settings-group', $this->connection_name);
        register_setting('simple-auth0-login-settings-group', $this->domain_name);
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
          <td><input type="text" name="<?php echo $this->domain_name; ?>" value="<?php echo esc_attr(get_option($this->domain_name)); ?>" /></td>
          </tr>
          <tr valign="top">
          <th scope="row">Auth0 Connection Name</th>
          <td><input type="text" name="<?php echo $this->connection_name; ?>" value="<?php echo esc_attr(get_option($this->connection_name)); ?>" /></td>
          </tr>
          <tr valign="top">
          <th scope="row">Auth0 Client ID</th>
          <td><input type="text" name="<?php echo $this->client_id_name; ?>" value="<?php echo esc_attr(get_option($this->client_id_name)); ?>" /></td>
          </tr>
          <tr valign="top">
          <th scope="row">Auth0 Client Secret</th>
          <td><input type="password" name="<?php echo $this->client_secret_name; ?>" value="<?php echo esc_attr(get_option($this->client_secret_name)); ?>" /></td>
          </tr>
      </table>
        <?php submit_button(); ?>
      </form>
        </div>
    <?php }

}
new SimpleAuth0Login();

    ?>
