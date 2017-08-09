<?php
/**
 *
 */
class ProfileOverride
{
    private $auth0_service = null;
    function __construct($auth0_service)
    {
        $this->auth0_service = $auth0_service;
        $support_email = $this->auth0_service->support_email;

        add_action(
            'init', function () {
                $show_password_fields = add_filter('show_password_fields', '__return_false');
            }, 10
        );

        add_action(
            'show_user_profile', function ( $user ) {
                $email = $user->user_email;
                ob_start();
                include "views/profileadditions.php";
                echo ob_get_clean();
            }
        );

        add_action(
            'user_profile_update_errors', function ( $errors, $update, $user ) use ($support_email) {
                $old = get_user_by('id', $user->ID);
                if($user->user_email != $old->user_email) {
                    $user->user_email = $old->user_email;
                    $errors->add('cannot_change_email', __('<strong>ERROR</strong>: Unfortunately you cannot currently change your email address, please contact '.$support_email.' and they can change it for you.', 'mydomain'));
                }
            }, 10, 3
        );
    }


}
    ?>
