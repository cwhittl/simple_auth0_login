<?php
use Auth0\SDK\API\Authentication;
use Firebase\JWT\JWT;
/**
 *
 */
class Auth0Service
{
    public $connection_name = "connection";
    public $connection = null;
    public $domain_name = "auth0_domain";
    public $domain = null;
    public $client_id_name = "client_id";
    public $client_id = null;
    public $client_secret_name = "client_secret";
    public $client_secret = null;
    public $scope = null;
    public $support_email_name = "simple_auth0_support_email";
    public $support_email = "support";

    function __construct()
    {
        $this->domain = get_option($this->domain_name);
        $this->connection = get_option($this->connection_name);
        $this->client_id = get_option($this->client_id_name);
        $this->client_secret = get_option($this->client_secret_name);
        $this->support_email = get_option($this->support_email_name);
        $this->scope="openid profile email email_verified";
    }

    private function createAuthentication()
    {
        return new Authentication(
            $this->domain,
            $this->client_id,
            $this->client_secret,
            null,
            $this->scope,
            ['http_errors' => false]
        );
    }

    public function request_new_password( $user_email )
    {
        $authentication = $this->createAuthentication();
        $response = $authentication->dbconnections_change_password($user_email, $this->connection);
        add_filter(
            'allow_password_reset', function ($allow, $user_id) {
                return new WP_Error('no_password_reset', __(json_encode($response)));
            }, 99, 2
        );
        return true;
    }

    public function getUserIDFromAuth0ID($auth0_id)
    {
        global $wpdb;
        $user_id_object = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value = %s",
                $wpdb->prefix.'auth0_id', $auth0_id
            )
        );
        return count($user_id_object) === 1 ? $user_id_object[0]->user_id : null;
    }

    public function signup( $user_email, $password )
    {
        $authentication = $this->createAuthentication();
        $response = $authentication->dbconnections_signup($user_email, $password, $this->connection);
        add_filter(
            'allow_password_reset', function ($allow, $user_id) {
                return new WP_Error('no_password_reset', __(json_encode($response)));
            }, 99, 2
        );
        return true;
    }

    public function authenticate( $user, $user_email, $password )
    {
        if ($user instanceof WP_User ) {
            return $user;
        }

        if (empty($user_email) || empty($password) ) {
            if (is_wp_error($user) ) {
                return $user;
            }
            $error = new WP_Error();
            if (empty($user_email) ) {
                $error->add('empty_username', __('<strong>ERROR</strong>: The email field is empty.')); // Uses 'empty_username' for back-compat with wp_signon()
            }
            if (empty($password) ) {
                $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));
            }
            return $error;
        }

        if (!is_email($user_email) ) {
            return $user;
        }

        $authentication = $this->createAuthentication();
        if($user_email) {
            $options = array("username"=>$user_email,"password"=>$password,"realm"=>$this->connection,"scope"=>$this->scope);
            $result = null;
            try {
                $result = (object) $authentication->login($options);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }

            if(!$result) {
                $error = new WP_Error();
                $error->add("unknown", __("<strong>ERROR</strong>: I'm sorry there was an unknown error, please try again and if it continues, please contact ".$this->support_email.".  Thank you"));
                return $error;
            }else if(isset($result->error)) {
                $error = new WP_Error();
                $error->add($result->error, __('<strong>ERROR</strong>: ' . $result->error_description));
                return $error;
            }else{
                try {
                    $user_profile = JWT::decode($result->id_token, $this->client_secret, array('HS256', 'RS256'));
                    if(!$user_profile->email_verified) {
                        $error = new WP_Error();
                        $error->add("not_validation", __('<strong>ERROR</strong>: Check your inbox for an verfication email, once verfied you can login.'));
                        return $error;
                    }
                    $is_new = false;
                    $user = get_user_by('email', $user_email);
                    if($user) {
                        $user_id = $user->ID;
                    }else{
                        $user_id = $this->getUserIDFromAuth0ID($user_profile->sub);
                        if(!$user_id) {
                            $is_new = true;
                            $user_id = wp_create_user($user_email, $password, $user_email);
                        }
                    }

                    wp_update_user(array( 'ID' => $user_id, 'display_name' => $user_profile->nickname, "user_email" => $user_profile->email));
                    global $wpdb;
                    update_user_meta($user_id, $wpdb->prefix.'auth0_id', ( isset($user_profile->user_id) ? $user_profile->user_id : $user_profile->sub ));
                    update_user_meta($user_id, $wpdb->prefix.'auth0_obj', json_encode($user_profile));
                    update_user_meta($user_id, $wpdb->prefix.'last_update', date('c'));
                    
                    if(!wp_check_password($password, $user->data->user_pass, $user_id)) {
                        wp_set_password($password, $user_id);
                    }

                    do_action('auth0_user_login', $user_id, $user_profile, $is_new, $result->id_token, $result->access_token);
                    return $user;
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $error = new WP_Error();
                    $error->add("unknown", __("<strong>ERROR</strong>: I'm sorry there was an unknown error, please try again and if it continues, please contact ".$this->support_email.".  Thank you"));
                    return $error;
                }
            }
            //Should never get here but if it does....
            return false;
        }
    }
}

    ?>
