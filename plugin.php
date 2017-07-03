<?php
require 'vendor/autoload.php';
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

    private $realm = null;
    private $domain = null;
    private $client_id = null;
    private $client_secret = null;
    private $audience = null;
    private $scope = null;

    function __construct()
    {
        $this->domain = "";
        $this->realm = "";
        $this->client_id = '';
        $this->client_secret = "";
        $this->audience = '';
        $this->scope="openid profile email";

        add_action(
            'plugins_loaded', function () {
                remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
                add_filter('authenticate', array( $this, 'authenticate'), 20, 3);
            }
        );
        add_filter('lostpassword_url', array( $this, 'override_password_reset'), 10, 2);

        $this->fixLoginForms();
    }


    function override_password_reset( $lostpassword_url, $redirect )
    {
        return wp_login_url() . "#jsModal";
    }

    function reset_password( $user_email )
    {
        //die('got here');
        $authentication = $this->createAuthentication();
        $test = $authentication->dbconnections_change_password($user_email, $this->realm);
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
                $options = array("username"=>$user_email,"password"=>$password,"realm"=>$this->realm,"scope"=>$this->scope);
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
        add_filter(
            'login_footer', function () {
            ?>
            <div id="jsModal" class="modal">
              <div class="modal__overlay jsOverlay"></div>
              <div id="lostPassword" class="modal__container">
                <form action="#">
                  <h3>Need a new password?</h3>
                  <input required type="email" width="100%" class="input" id="possible_email" placeholder="Enter your password here"/>
                  <input type="submit" id="send_password" class="button button-primary button-large" value="Send new password"/>
                </form>
                <button class="modal__close jsModalClose">&#10005;</button>
              </div>
            </div>
            <?php
            }
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

}
new SimpleAuth0Login();

    ?>
