<?php
/**
 *
 */
class RegistrationOverride
{
    protected $auth0_service = null;
    function __construct($auth0_service)
    {
        $this->auth0_service = $auth0_service;
        remove_action('register_new_user',   'wp_send_new_user_notifications');
        add_action(
            'register_form', function () {
                ob_start();
                include "views/registration.php";
                echo ob_get_clean();
            }
        );

        add_filter(
            'sanitize_user', function ($username, $raw_username, $strict) {
                $username = wp_strip_all_tags($raw_username);
                return $username;
            }, 10, 3
        );


        add_filter(
            'registration_errors', function ( $errors, $sanitized_user_login, $user_email ) {
                if (empty($_POST['reg_pass1']) || ! empty($_POST['reg_pass1']) && trim($_POST['reg_pass1']) == '' ) {
                    $errors->add('reg_pass1_error', __('<strong>ERROR</strong>: You must include a password.', 'mydomain'));
                }else if (empty($_POST['reg_pass2']) || ! empty($_POST['reg_pass2']) && trim($_POST['reg_pass2']) == '' ) {
                    $errors->add('reg_pass2_error', __('<strong>ERROR</strong>: You must confirm your password.', 'mydomain'));
                }else if (trim($_POST['reg_pass1']) !== trim($_POST['reg_pass2']) ) {
                    $errors->add('reg_pass_match_error', __('<strong>ERROR</strong>: You passwords must match.', 'mydomain'));
                }else {
                    $signed_up = $this->auth0_service->signup($user_email, $_POST['reg_pass']);
                    if(!$signed_up === true) {
                        $errors->add('reg_signed_up_error', __('<strong>ERROR</strong>: There was an issue with your sign up.($signed_up)', 'mydomain'));
                    }
                }
                return $errors;
            }, 10, 3
        );

        add_action(
            'user_register',
            function ( $user_id ) {
                if (!empty($_POST['reg_pass1']) && !empty($user_id)) {
                    wp_set_password($_POST['reg_pass1'], $user_id);
                }
            }
        );
    }
}

    ?>
