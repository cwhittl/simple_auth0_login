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
                remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
                add_filter('authenticate', array( $this, 'authenticate'), 20, 3);
            }
        );
    }

    public function authenticate( $user, $user_email, $password )
    {
        return $this->auth0_service->authenticate($user, $user_email, $password);
    }
}
    ?>
