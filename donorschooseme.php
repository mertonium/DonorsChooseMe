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

function get_donors_choose_projects() {
    $ret = '';
    $data = file_get_contents('http://api.donorschoose.org/common/json_feed.html?APIKey=DONORSCHOOSE&centerLat=40.776104&centerLng=-73.920822');
    if($data) {
        $json_data = json_decode($data);
        $projects = array_slice($json_data->proposals, 0, 3);
        foreach($projects as $proj) {
            $ret .= render_dc_project_html($proj);
        }
        echo $ret;
    } else {
        echo 'Could not connect to page.';
    }
}

function render_dc_project_html($project) {
    $html = '<div class="dc-project">'.
            '<img src="'.$project->imageURL.'" />'.
            '<span class="dc-project-title"><a class="dc-project-link" href="'.$project->proposalURL.'">'.$project->title.'</a></span>'.
            '<span class="dc-project-location">'.$project->zone->name.', '.$project->state.'</span>'.
            '<span class="dc-project-funding-appeal"><a class="dc-project-funding-link" href="'.$project->fundURL.'">'.$project->costToComplete.'</a></span>'.
            '</div>';
    return $html;
}
?>