<?php
/*
  Plugin Name: Wordpress InviteBox Plugin
  Description: Add InviteBox-powered referral program to your WordPress blog
  Plugin URI: http://invitebox.com/
*/

/*
ini_set('display_errors',1);
error_reporting(E_ALL);
*/

if( !function_exists('wp_ib_settings') )
  {
    function wp_ib_settings ()
    {
      add_menu_page("Invitebox", "Invitebox", 8, basename(__FILE__), "wp_ib_opt");
    }
  }

if ( !function_exists('wp_ib_opt') )
  {
    function wp_ib_opt()
    {
      ?>
      <div class="wrap">
	<div id="icon-themes" class="icon32"></div>
	<h2>
	<strong>Invitebox Settings</strong>
	</h2>

	<?php
	if(isset($_POST['wp_ib_form_submit']))
	  {
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
      ?>

      <h2>Secret key</h2>
	 <input type="text" name="wp_ib_secret_key" id="wp_ib_secret_key" style="width:300px;" value="<?= $wp_ib_secret_key ?>">
	 <p>The secret key can be found in your InviteBox account under "Widget Settings -> Integration -> Show Advanced Settings".</p>
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

if( !function_exists('wp_ib_update') )
  {
    function wp_ib_update()
    {
      if(isset($_POST['wp_ib_form_submit']))
	{
	  $skey = $_POST['wp_ib_secret_key']; 
	  update_option("wp_ib_secret_key", ($skey));
	
	  $ch = curl_init(); 
	  curl_setopt ($ch, CURLOPT_URL, "http://invitebox.com/invitation-camp/wordpress/?skey=" . $skey);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  curl_setopt ($ch, CURLOPT_GET, true);
			
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
      $output .= "<script type='text/javascript'>
var __ibq = __ibq || [];
__ibq.push(['bind', 'preInit',
function(){invitebox.setMessage('$post_title $post_url');}]);
</script>";

      return $output;
    }
  }

if ( !function_exists('wp_ib') )
  {
    function wp_ib( $content )
    {
      if( !is_feed() && !is_page() && !is_archive() && !is_search() && !is_404() )
	{
	  return $content . wp_ib_format('none');
	}
      else
	{
	  return $content;
	}
    }
  }

add_filter('the_content', 'wp_ib');
add_action('admin_menu', 'wp_ib_settings');
add_action('init', 'wp_ib_update');
?>
