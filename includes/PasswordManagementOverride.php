<?php /**
 *
 */
class PasswordManagementOverride
{
    protected $auth0_service = null;
    private $modal_name = "open_modal_lost_password";
    function __construct($auth0_service,$login_logo_url)
    {
        $this->auth0_service = $auth0_service;
        add_filter('lostpassword_url', array( $this, 'override_password_reset_url'), 10, 2);
        add_action('init', array($this,'redirect_lost_password_page'));
        add_action('wp_ajax_nopriv_simple_auth0_login_password_reset', array($this,"get_new_password"));
        add_action('wp_ajax_simple_auth0_login_password_reset', array($this,"get_new_password"));
        add_action('show_user_profile', array($this,'add_password_reset_link'));
        add_action(
            'login_enqueue_scripts', function () {
                wp_enqueue_script('simple-auth0-login-modal',  plugins_url('lib/modal/modal.js', dirname(__FILE__)));
                wp_enqueue_style('simple-auth0-login-modal',  plugins_url('lib/modal/modal.css', dirname(__FILE__)));

            }, 10
        );

        add_filter(
            'login_footer', function () use ($login_logo_url) {
                ob_start();
                include "views/lostpassword.php";
                echo ob_get_clean();
            }
        );

        add_action(
            'init', function () {
                $show_password_fields = add_filter('show_password_fields', '__return_false');
            }, 10
        );
    }

    function add_password_reset_link( $user )
    {
        $email = $user->user_email;
        ob_start();
        include "views/profileadditions.php";
        echo ob_get_clean();
    }

    function override_password_reset_url( $lostpassword_url, $redirect )
    {
        return wp_login_url() . "#" . $this->modal_name;
    }

    function get_new_password()
    {
        $response = array("success"=>$this->auth0_service->request_new_password($_POST['email']));
        wp_send_json($response);
    }

    function redirect_lost_password_page()
    {
        if (isset($_GET['action']) && $_GET['action'] == 'lostpassword' ) {
            ob_start();
            wp_redirect(wp_login_url() . "#" . $this->modal_name);
            ob_clean();
        }
    }

}
?>
