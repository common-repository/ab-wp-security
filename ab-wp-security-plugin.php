<?php
   /*
   Plugin Name: AB WP Security
   Plugin URI: http://aleksandar.bjelosevic.info/abwps
   Description: Security plugin that stop User Enumeration in WordPress,check for admin and administrator usernames, Disable XML-RPC,Remove WordPress Version Number and disable directory browsing
   Version: 1.51
   Author: Aleksandar Bjelosevic
   Author URI: http://aleksandar.bjelosevic.info
   License: GPL3
   */




function ab_wp_security_menu_item()
{
  add_submenu_page("options-general.php", "AB WP Security", "AB WP Security", "manage_options", "abwps", "abwpstools_page"); 
}

//create page
function abwpstools_page()
{

  ?>
      <div class="wrap">
         <!-- Add the icon to the page -->
        <div id="icon-themes" class="icon32"></div>
         <h2>AB WP Security</h2>
         <!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
        <?php settings_errors(); ?>
 
        <!-- Create the form that will be used to render our options -->  
         <form method="post" action="options.php">
            <?php
               settings_fields("section");
  
               do_settings_sections("abwps");
                 
               submit_button(); 
            ?>
         </form>
         <?php  
           //check users
           abwp_check_users();
           ?>
      </div>
   <?php
}

function abwps_settings()
{
    add_settings_section("section", "Settings", null, "abwps");
    add_settings_field("ab-wp-security-userenumeration", "Stop User Enumeration in WordPress", "abwps_userenumeration_display", "abwps", "section");  
    add_settings_field("ab-wp-security-wp-version", "Remove WordPress Version Number", "abwps_wpversion_display", "abwps", "section"); 
    add_settings_field("ab-wp-security-xml-rpc", "Disable XML-RPC", "abwps_xmlrpc_display", "abwps", "section"); 
 add_settings_field("ab-wp-security-dir-browse", "Disable directory browsing", "abwps_dir_browse_display", "abwps", "section");
    register_setting("section", "ab-wp-security-user-enumeration");
    register_setting("section", "ab-wp-security-wp-version");
    register_setting("section", "ab-wp-security-xml-rpc");
    register_setting("section", "ab-wp-security-dir-browse");
}

function abwps_userenumeration_display()
{
   ?>
        <input type="checkbox" name="ab-wp-security-user-enumeration" value="1" <?php checked(1, get_option('ab-wp-security-user-enumeration'), true); ?>>
        
       
   <?php
}

function abwps_wpversion_display()
{
   ?>
        <input type="checkbox" name="ab-wp-security-wp-version" value="1" <?php checked(1, get_option('ab-wp-security-wp-version'), true); ?>>
   <?php
}

function abwps_xmlrpc_display()
{
   ?>
         <input type="checkbox" name="ab-wp-security-xml-rpc" value="1" <?php checked(1, get_option('ab-wp-security-xml-rpc'), true); ?>>
   <?php
}

function abwps_dir_browse_display()
{
   ?>
         <input type="checkbox" name="ab-wp-security-dir-browse" value="1" <?php checked(1, get_option('ab-wp-security-dir-browse'), true); ?>>
   <?php
}



// block WP enum scans


function abwp_check_enum($redirect, $request) {
	// permalink URL format
	if (preg_match('/\?author=([0-9]*)(\/*)/i', $request)) die();
	else return $redirect;
}

//check username for security!
function abwp_check_users()
{
         $username = array("admin","administrator");
         
         for($x=0;$x<count($username);$x++)
  {
       if ( username_exists( $username[$x] ) )
           echo "Username: <b>".$username[$x]."</b> In Use!<b>THIS IS MAJOR SECURITY RISK!</b><br>";
         
   }      

}
 // Remove WordPress Version Number
function ab_wp_security_remove_version() {
return '';
}



function ab_wp_security_clean() {
           
// Remove WordPress Version Number
        if (get_option('ab-wp-security-wp-version'))
           {
           add_filter('the_generator', 'ab_wp_security_remove_version');
           add_filter( 'script_loader_src', 'ab_wp_security_remove_wp_version_strings' );
           add_filter( 'style_loader_src', 'ab_wp_security_remove_wp_version_strings' );
           }
           
        // disable xmlrpc in WordPress    
       if (get_option('ab-wp-security-xml-rpc'))
           {
            add_filter('xmlrpc_enabled', '__return_false');
           }    
              
       if (get_option('ab-wp-security-user-enumeration'))
           {
            if (!is_admin()) {
	// default URL format
	if (preg_match('/author=([0-9]*)/i', $_SERVER['QUERY_STRING'])) die();
	add_filter('redirect_canonical', 'abwp_check_enum', 10, 2);
}         
            
            
            
           }  
           
           if (get_option('ab-wp-security-dir-browse'))
           {
           
           if (! is_admin())
           { 
           
           //get wordpress directory
           if ( !defined('ABSPATH') )
               define('ABSPATH', dirname(__FILE__) . '/');
                      
           $filename = ABSPATH.'.htaccess'; //get original .htaccess
           $filenameCopy = ABSPATH.'.htaccessBackupCopy'; //get changed .htaccess

          if (file_exists($filename)) {
               
                //make copy of original $filename
                if (copy(".htaccess",".htaccessCopy")) //
                {
					 $txt = "Options All -Indexes";
                   $contents = file_get_contents($filename);
				   $pattern = preg_quote($txt, '/');
                   $pattern = "/^.*$pattern.*\$/m";

				   if(preg_match_all($pattern, $contents, $matches)){
                      fclose ($filename); 
                      }
                    else{
						$myfile = fopen($filename, "a") or die("Unable to open file!");
                  
                   fwrite($myfile, "\n". $txt);
                   fclose($myfile);
						
						}
				   
				   
                   
                }
           
           
          }            
           
           
} 
}          

}
// Remove WordPress Version Number
function ab_wp_security_remove_wp_version_strings( $src ) {
     global $wp_version;
     parse_str(parse_url($src, PHP_URL_QUERY), $query);
     if ( !empty($query['ver']) && $query['ver'] === $wp_version ) {
          $src = remove_query_arg('ver', $src);
     }
     return $src;
}

add_action("admin_menu", "ab_wp_security_menu_item");
add_action("admin_init", "abwps_settings");

add_action('init', 'ab_wp_security_clean');

?>