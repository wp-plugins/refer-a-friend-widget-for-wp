<?php
/*
  Plugin Name: Wordpress InviteBox Plugin
  Description: Add InviteBox-powered referral program to your WordPress blog
  Version: 1.2.1
  Plugin URI: http://invitebox.com/
 */

/*
ini_set('display_errors',1);
error_reporting(E_ALL);
 */

if( !function_exists('wp_ib_add_icon') ) {
    function wp_ib_add_icon() {
        global $typenow;
        // check user permissions
        if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
            return;
        }
        // verify the post type
        if( ! in_array( $typenow, array( 'post', 'page' ) ) )
            return;
        // check if WYSIWYG is enabled
        if ( get_user_option('rich_editing') == 'true') {
            add_filter("mce_external_plugins", "wp_ib_plugin");
            add_filter('mce_buttons', 'wp_ib_button');
        }
    }
}
if(!function_exists('wp_ib_style')) {
    function wp_ib_style() {
        wp_enqueue_style('wp_ib', plugins_url('/style.css', __FILE__));
    }
}

if( !function_exists('wp_ib_plugin') ) {
    function wp_ib_plugin($plugin_array) {
        $plugin_array['invitebox_button'] = plugins_url( '/invitebox_button.js', __FILE__ ); // CHANGE THE BUTTON SCRIPT HERE
        return $plugin_array;
    }
}

if( !function_exists('wp_ib_button') ) {
    function wp_ib_button($buttons) {
        array_push($buttons, "invitebox_button");
        return $buttons;
    }
}

if( !function_exists('wp_ib_settings') ) {
    function wp_ib_settings () {
        add_menu_page("Invitebox", "Invitebox", 8, basename(__FILE__), "wp_ib_opt");
    }
}

if ( !function_exists('wp_ib_opt') ) {
    function wp_ib_opt()
    {
?>
            <div class="wrap">
            <div id="icon-themes" class="icon32"></div>
            <h2>
            <strong>Invitebox Settings</strong>
            </h2>

<?php
        if(isset($_POST['wp_ib_form_submit'])) {
            if ((get_option("wp_ib_isresponse")) && (get_option("wp_ib_errorcode") == 0)) {
                echo '<div style="color:green;font-weight:bold;background:#FFC;padding:4px;margin:2px 0;">Your Invitebox Settings was saved successfully!</div>';
            } else {
                if (get_option("wp_ib_errorcode") == 0) {
                    echo '<div style="color:red;font-weight:bold;background:#FFC;padding:4px;margin:2px 0;">Error! Invalid secret key.</div>';
                } else {
                    echo '<div style="color:red;font-weight:bold;background:#FFC;padding:4px;margin:2px 0;">Server error! </div>';
                }
            }
        }
?>

            <fieldset>
            <form name="wp_ib_option_form" id="id-form" method="post">

<?php
        $wp_ib_secret_key = get_option("wp_ib_secret_key");
        if($wp_ib_secret_key == '') { $wp_ib_secret_key = ''; }
        $wp_ib_show_options = get_option("wp_ib_show_options");
?>

            <h2>Secret key</h2>
            <input type="text" name="wp_ib_secret_key" id="wp_ib_secret_key" style="width:300px;" value="<?= $wp_ib_secret_key ?>">
            <p> The secret key can be found in your InviteBox account under "Campaign -> Integration -> Show Advanced Settings". (Screenshot attached)</p>
            <hr />
            <h3>View options:</h3>
            <label for="wp_ib_show_options_all"><input type="radio" id="wp_ib_show_options_all" <?=$wp_ib_show_options == "all" || !$wp_ib_show_options ? 'checked="checked"' : ''  ?> name="wp_ib_show_options" value="all" /> - Show widget on all pages</label><br />
            <label for="wp_ib_show_options_page"><input type="radio" id="wp_ib_show_options_page" <?=$wp_ib_show_options == "one_page"  ? 'checked="checked"' : '' ?> name="wp_ib_show_options" value="one_page" /> - Show widget on one page</label>
            <hr />
            <br /> <input type="submit" id="id-save" value="Save" class="button-primary" /> 
            <input type="hidden" name="wp_ib_form_submit" value="true" />
            </form>
            <br /> <br />

            </fieldset>

            </div>

<?php
    }
}

if( !function_exists('wp_ib_update') ) {
    function wp_ib_update() {
        if(isset($_POST['wp_ib_form_submit'])) {
            $skey = $_POST['wp_ib_secret_key']; 
            $show_options = $_POST['wp_ib_show_options']; 
            update_option("wp_ib_secret_key", ($skey));
            update_option("wp_ib_show_options", ($show_options));

            $ch = curl_init(); 

            if ($_POST['wp_ib_show_options'] == 'one_page')
                curl_setopt ($ch, CURLOPT_URL, "http://invitebox.com/invitation-camp/wordpress/?one_page=1&skey=" . $skey);
            else
                curl_setopt ($ch, CURLOPT_URL, "http://invitebox.com/invitation-camp/wordpress/?skey=" . $skey);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch, CURLOPT_HTTPGET, true);

            $result = curl_exec($ch);
            $response = json_decode($result);
            $error_code = curl_errno($ch);
            update_option("wp_ib_errorcode", ($error_code));
            update_option("wp_ib_isresponse", (false));
            if (($response->success == true) && ($error_code == 0)) {
                update_option("wp_ib_isresponse", (true));
                update_option("wp_ib_key", ($response->pkey));
                update_option("wp_ib_url", ($response->id));
            }
            curl_close($ch);
        }
    }
}

if( !function_exists('wp_ib_for_button') ) {
    function wp_ib_for_button() {
        $key = get_option("wp_ib_key");
        $url = get_option("wp_ib_url");
        $output = "<script type='text/javascript'> var ib_key='".$key."'; var ib_url='".$url."'; </script>";

        return $output;
    }
}

if( !function_exists('wp_ib_format') )
{
    function wp_ib_format( $align )
    {
        $key = get_option("wp_ib_key");
        $url = get_option("wp_ib_url");
        $post_title = get_the_title();
        $post_url = get_permalink();
        $output = "<script id='invitebox-script' type='text/javascript'>
            (function() {
                var ib = document.createElement('script');
                ib.type = 'text/javascript';
                ib.async = true;
                ib.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'invitebox.com/invitation-camp/" . $url . "/invitebox.js?key=" . $key . "&jquery='+(typeof(jQuery)=='undefined');
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ib, s);
            })();
            </script>";

        return $output;
    }
}

if ( !function_exists('wp_ib_fbutton') ) {
    function wp_ib_fbutton( $content ) {
        echo wp_ib_for_button();
    }
}

if ( !function_exists('wp_ib') ) {
    function wp_ib( $content ) {
        echo wp_ib_format('none');
    }
}

$show_option = get_option("wp_ib_show_options");

add_action('admin_menu', 'wp_ib_settings');
add_action('init', 'wp_ib_update');

if($show_option == "all" || !$show_option) {
    add_action('wp_footer', 'wp_ib', 100);
}

if($show_option == "one_page") {
    add_action('admin_head', 'wp_ib_fbutton');
    add_action('admin_head', 'wp_ib_add_icon');
    add_action('admin_enqueue_scripts', 'wp_ib_style');
}

?>
