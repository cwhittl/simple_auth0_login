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

    function __construct()
    {
        $this->domain = get_option($this->domain_name);
        $this->connection = get_option($this->connection_name);
        $this->client_id = get_option($this->client_id_name);
        $this->client_secret = get_option($this->client_secret_name);
        $this->scope="openid profile email";
    }

    public function reset_password( $user_email )
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
                    wp_set_password($password, $user_id);
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
}

    ?>
