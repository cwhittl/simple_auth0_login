<?php /**
 *
 */
class LostPasswordOverride
{
    private $modal_name = "open_modal_lost_password";
    function __construct()
    {
        add_filter('lostpassword_url', array( $this, 'override_password_reset'), 10, 2);
        add_action('init', array($this,'redirect_lost_password_page'));
        add_filter(
            'login_footer', function () {
            ?>
            <div id="<?php echo $this->modal_name; ?>" class="modal">
              <div class="modal__overlay modal_overlay"></div>
              <div id="lostPassword" class="modal__container">
                <form action="#">
                  <h3>Need a new password?</h3>
                  <input required type="email" width="100%" class="input" id="possible_email" placeholder="Enter your password here"/>
                  <input type="submit" id="send_password" class="button button-primary button-large" value="Send new password"/>
                </form>
                <button class="modal__close modal_close">&#10005;</button>
              </div>
            </div>
            <?php
            }
        );
    }

    function override_password_reset( $lostpassword_url, $redirect )
    {
        return wp_login_url() . "#" . $this->modal_name;
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
