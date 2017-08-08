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
        $support_email = $this->auth0_service->support_email;

        add_action(
            'init', function () {
                if (isset($_GET['action']) && $_GET['action'] == 'lostpassword' ) {
                    ob_start();
                    wp_redirect(wp_login_url() . "#" . $this->modal_name);
                    ob_clean();
                }
            }
        );

        add_filter(
            'lostpassword_url', function ( $lostpassword_url, $redirect ) {
                return wp_login_url() . "#" . $this->modal_name;
            }, 10, 2
        );

        add_filter(
            'login_footer', function () use ($login_logo_url, $support_email) {
                ob_start();
                include "views/lostpassword.php";
                echo ob_get_clean();
            }
        );

        add_action('wp_ajax_nopriv_simple_auth0_login_password_reset', array($this,"get_new_password"));
        add_action('wp_ajax_simple_auth0_login_password_reset', array($this,"get_new_password"));
    }

    function get_new_password()
    {
        $response = array("success"=>$this->auth0_service->request_new_password($_POST['email']));
        wp_send_json($response);
    }

}
?>
