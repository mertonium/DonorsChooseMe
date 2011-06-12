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

class DonorsChoosePlugin {
    
    public static function get_projects() {
        // SF   174.253.235.90
        // GOOG 74.125.224.82 
        $ip = '174.253.235.90'; //gethostbyname($_SERVER['SERVER_NAME']);
    echo '<h1>' . $ip . '</h1>';
        $ip_data = file_get_contents('http://api.ipinfodb.com/v3/ip-city/?key=cf74017054f12362f1ddecbf26ca61d8ab8acc5253573cdfe1a7f7255a4fdcc2&ip='.$ip);
        if($ip_data) {
            $ip_info = explode(';', $ip_data);
            print_r($ip_info);
        }
        $ret = '';
        $data = file_get_contents('http://api.donorschoose.org/common/json_feed.html?APIKey=DONORSCHOOSE&centerLat='.$ip_info[8].'&centerLng='.$ip_info[9]);
        if($data) {
            $json_data = json_decode($data);
            $projects = array_slice($json_data->proposals, 0, 3);
            foreach($projects as $proj) {
                $ret .= self::render_project_html($proj);
            }
            echo $ret;
        } else {
            echo 'Could not connect to page.';
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