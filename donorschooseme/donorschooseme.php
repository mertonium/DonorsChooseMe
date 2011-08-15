<?php
/*
Plugin Name: Donors Choose Me
Plugin URI: http://mertonium.com/donorschoose-projects-near-me-wordpress-plugin/
Description: A brief description of the Plugin.
Version: 0.2
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
                    <label for="dcm_donorschoose_stuff[num_projects]">Number of projects to display:</label>
                    <input type="text" name="dcm_donorschoose_stuff[num_projects]" value="<?php echo $options['num_projects']; ?>" size="2" /><br />
                    <em>This is the number of DonorsChoose.org projects to display.</em>
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
 * Main plugin class
 */
class DonorsChooseMePlugin {
    
    public static function set_head() {
        $siteurl = get_option('siteurl');
        $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/dcm_styles.css';
        echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
    }
    
    public static function widget($args) {
        extract($args);
        echo $before_widget;
        echo $before_title . 'DonorsChoose.org Projects' . $after_title;
        echo self::get_projects();
        echo $after_widget;
    }
    
    /**
     * Retrieve all of the relevant projects from DonorsChoose.org
     * 
     * @return A string of HTML which represents all of the projects returned from the DonorsChoose.org API
     */
    public static function get_projects() {
    	$options = get_option('dcm_donorschoose_stuff');
    	$ip_latlng = array();
    	
    	/** 
    	 * Some IPs for testing
         * SF   174.253.235.90
         * GOOG 74.125.224.82
         * UK   64.34.193.13 
         */
        
        $ip = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1') ? '174.253.235.90' : $_SERVER['REMOTE_ADDR'];

        $ip_url = 'http://www.geoplugin.net/php.gp?ip='.$ip;
        $ip_data = self::curl_this($ip_url);

        $ip_info = unserialize($ip_data);

        if($ip_info[0] === "ERROR") {
            $ip_latlng[0] = '40.96797434499278';
            $ip_latlng[1] = '-91.55096054077148';
        } else {
            $ip_latlng[0] = $ip_info['geoplugin_latitude'];
            $ip_latlng[1] = $ip_info['geoplugin_longitude'];
        }
        $ret = '';
        $dc_url = 'http://api.donorschoose.org/common/json_feed.html?APIKey='.$options['dc_api_key'].'&centerLat='.$ip_latlng[0].'&centerLng='.$ip_latlng[1];
        $data = self::curl_this($dc_url);

        if($data) {
            $json_data = json_decode($data);
            $num_projects = (intval($options['num_projects']) > 0) ? intval($options['num_projects']) : 3;
            
            // Slice off the number of projects that we want
            $projects = array_slice($json_data->proposals, 0, $num_projects);
            foreach($projects as $proj) {
                // Build our html
                $ret .= self::render_project_html($proj);
            }
            return $ret;
        } else {
            return 'Could not connect to DonorsChoose.org.';
        }
    }
    
    /**
     * Build a single project html block
     * 
     * @param $project A project (proposal) object from the DonorsChoose.org API
     * @return A string of HTML which represents the given project
     */
    private function render_project_html($project) {
        $html = '<div class="dc-project">'.
                '<img src="'.$project->imageURL.'" />'.
                '<div class="dc-project-info">'.
                '<div class="dc-project-title"><a class="dc-project-link" href="'.$project->proposalURL.'">'.$project->title.'</a></div>'.
                '<div class="dc-project-location">'.$project->city.', '.$project->state.'</div>'.
                '<div class="dc-project-funding-appeal"><a class="dc-project-funding-link" href="'.$project->fundURL.'">'.$project->percentFunded.'% funded</a></div>'.
                '</div>'.
                '</div>';
        return $html;
    }
    
    /**
     * Internal cURL wrapper
     * 
     * @param $url The URL to be cURL'ed
     * @return The result of the cURL operation
     */
    private function curl_this($url) {
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER,0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret_val = curl_exec($ch);
        curl_close($ch);
        return $ret_val;
    }
    
}

/**
 * Class for the widgetizing of the plugin
 */
class DonorsChooseMeWidget extends WP_Widget {
 
    // Constructor
    function DonorsChooseMeWidget() {
        $widget_ops = array('classname' => 'widget_donorschooseme', 'description' => __('Displays projects from DonorsChoose.org that your readers may find interesting.') );
        $this->WP_Widget('donorschooseme-widget', __('DonorsChoose Me'), $widget_ops); 
    }
    
    /**
     * Outputs the options form on admin in Appearance => Widgets (backend).
     */
    function form($instance) {
        //  Assigns values
        $instance = wp_parse_args( (array) $instance, array( 'title' => 'Classroom Project Near You' ) );
        $title = strip_tags($instance['title']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
        <?php

    }
    
    /**
     * Processes widget options to be saved.
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);

        return $instance;
    }
    
    /**
     *  Outputs the content of the widget
     */
    function widget($args,$instance) {

        extract($args);

        //  Get the title of the widget and the specified width of the image
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        //  Outputs the widget in its standard ul li format.
        echo $before_widget;
        if (!empty( $title )) {
            echo $before_title . $title . $after_title;
        };
        
        echo DonorsChooseMePlugin::get_projects();
        echo $after_widget;
    }
}

/**
 * Activate the widget
 */
add_action('widgets_init', create_function('', 'return register_widget("DonorsChooseMeWidget");'));
?>