<?php
/*
Plugin Name: Donors Choose Me
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 0.0
Author: John Mertens
Author URI: http://mertonium.com
License: GPL2
*/
/*  Copyright 2011  John Mertens  (email : john@mertonium.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define('WP_DEBUG', true);
/**
 * Admin section
 */
add_action('admin_menu', 'dcm_projects_menu');

function dcm_projects_menu() {
	add_options_page('Donors Choose Me Options', 'DonorsChoose Me', 'manage_options', 'dcm-options', 'dcm_plugin_options');
}

function dcm_options_init() {
    register_setting('dcm-options','dcm_donorschoose_stuff');
}

add_action('admin_init', 'dcm_options_init' );


function dcm_plugin_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	$options = get_option('dcm_donorschoose_stuff');
	?>
	    <div class="wrap">
            <h2>DonorsChoose Me Options</h2>
            <form method="post" action="options.php">
                <?php settings_fields('dcm-options'); ?>
                <div class="dcm-admin-options">
                    <label for="dcm_donorschoose_stuff[dc_api_key]">DonorsChoose.org API Key:</label>
                    <input type="text" name="dcm_donorschoose_stuff[dc_api_key]" value="<?php echo $options['dc_api_key']; ?>" /><br />
                    <a href="http://developer.donorschoose.org/help-contact" target="_blank">Register for key here.</a>
                </div>
                <div class="dcm-admin-options">
                    <label for="dcm_donorschoose_stuff[ipinfodb_key]">IPInfoDB API Key:</label>
                    <input type="text" name="dcm_donorschoose_stuff[ipinfodb_key]" value="<?php echo $options['ipinfodb_key']; ?>" size="70" /><br />
                    <a href="http://ipinfodb.com/register.php" target="_blank">Register for key here.</a>
                </div>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
                </p>
            </form>
        </div>
        <style type="text/css">
            .dcm-admin-options {
                margin-bottom: 1em;
            }
        </style>
    <?php
}

/**
 * Add the action to inject the stylesheet/js files into the header
 */
add_action('wp_head', 'DonorsChooseMePlugin::set_head');

/**
 * Add shortcode
 */
function dcm_shortcode( $atts ) {
    // http://codex.wordpress.org/Shortcode_API
	return DonorsChooseMePlugin::get_projects();
}
add_shortcode( 'donorschooseme', 'dcm_shortcode' );

/**
 * Widgetize the plugin
 */
register_sidebar_widget('DonorsChoose Me', 'DonorsChooseMePlugin::widget');

/**
 * Main class
 */
class DonorsChooseMePlugin {
    
    public static function set_head() {
        $siteurl = get_option('siteurl');
        $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/dcm_styles.css';
        echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
    }
    
    public static function widget($args) {
        error_log(print_r($args, 1));
        extract($args);
        echo $before_widget;
        echo $before_title . 'DonorsChoose.org Projects' . $after_title;
        echo self::get_projects();
        echo $after_widget;
    }
    
    public static function get_projects() {
    	$options = get_option('dcm_donorschoose_stuff');
    	$ip_latlng = array();
        // SF   174.253.235.90
        // GOOG 74.125.224.82
        // ALIEN 64.34.193.13 
        $ip = '174.253.235.90'; //gethostbyname($_SERVER['SERVER_NAME']);

        $ip_data = file_get_contents('http://api.ipinfodb.com/v3/ip-city/?key='.$options['ipinfodb_key'].'&ip='.$ip);
        $ip_info = explode(';', $ip_data);
        $ip_info = explode(';', $ip_data);

        if($ip_info[0] === "ERROR") {
            $ip_latlng[0] = '40.96797434499278';
            $ip_latlng[1] = '-91.55096054077148';
        } else {
            $ip_latlng[0] = $ip_info[8];
            $ip_latlng[1] = $ip_info[9];
        }
        $ret = '';
        $data = file_get_contents('http://api.donorschoose.org/common/json_feed.html?APIKey='.$options['dc_api_key'].'&centerLat='.$ip_latlng[0].'&centerLng='.$ip_latlng[1]);
        if($data) {
            $json_data = json_decode($data);
            $projects = array_slice($json_data->proposals, 0, 3);
            foreach($projects as $proj) {
                $ret .= self::render_project_html($proj);
            }
            return $ret;
        } else {
            return 'Could not connect to page.';
        }
    }

    private function render_project_html($project) {
        $html = '<div class="dc-project">'.
                '<img src="'.$project->imageURL.'" />'.
                '<span class="dc-project-title"><a class="dc-project-link" href="'.$project->proposalURL.'">'.$project->title.'</a></span>'.
                '<span class="dc-project-location">'.$project->zone->name.', '.$project->state.'</span>'.
                '<span class="dc-project-funding-appeal"><a class="dc-project-funding-link" href="'.$project->fundURL.'">'.$project->costToComplete.'</a></span>'.
                '</div>';
        return $html;
    }
    
}
?>