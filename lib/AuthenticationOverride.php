<?php
/**
 *
 */
class AuthenticationOverride
{
    private $auth0_service = null;
    function __construct($auth0_service)
    {
        $this->auth0_service = $auth0_service;
        add_action(
            'plugins_loaded', function () {
                if(!isset($_POST["wle"])) {
                    remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
                    remove_filter('authenticate', 'wp_authenticate_email_password', 20, 3);
                    add_filter(
                        'authenticate', function ( $user, $user_email, $password ) {
                            if($user_email && $password) {
                                return $this->auth0_service->authenticate($user, $user_email, $password);
                            }else{
                                return false;
                            }
                        }, 20, 3
                    );
                }
            }
        );
        
        add_action(
            "login_form", function () {
                if(isset($_GET["wle"])) {
                    echo "<input type='hidden' name='wle' value='true' />";
                }
            }
        );
    }
}
    ?>
