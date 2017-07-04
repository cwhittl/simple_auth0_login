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
        add_action(
            'login_enqueue_scripts', function () {
                wp_enqueue_script('simple-auth0-login-modal',  plugins_url('lib/modal/modal.js', dirname(__FILE__)));
                wp_enqueue_style('simple-auth0-login-modal',  plugins_url('lib/modal/modal.css', dirname(__FILE__)));
                ?>
                <script>
                document.addEventListener("DOMContentLoaded", function(event){
                    var user_login = document.getElementById("user_login")
                    var nav_link = document.getElementById("nav").getElementsByTagName("A")[0]
                    var password_reset_form = document.getElementById("lostPassword").getElementsByTagName("form")[0]
                    var possible_email = document.getElementById("possible_email")
                    var send_password = document.getElementById("send_password")


                    user_login.setAttribute('type', 'email');

                    nav_link.className = "modal_trigger";
                    nav_link.addEventListener('click', function(e) {
                      possible_email.value = user_login.value
                    });

                    send_password.addEventListener('click', function(e) {
                      e.preventDefault();
                      if (password_reset_form.checkValidity()) {
                      //  form.submit();
                        alert("go!");
                      }else{
                        //TODO validation
                      }
                    });
                });
                </script>
                <style>
                #lostPassword #possible_email {
                  font-size: 24px;
                  width: 100%;
                  padding: 3px;
                  margin: 2px 6px 16px 0;
                }
                </style>
                <?php
            }, 10
        );
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
