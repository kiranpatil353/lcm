<?php

if (is_admin()) {
  add_action('admin_menu', 'LC_map_menu');
  add_action('admin_init', 'LC_map_register_settings');
}

function LC_map_menu() {
	add_options_page('Location Map Settings','Location Map Settings','manage_options','LC_map_settings','LC_map_settings_view');
}

function LC_map_settings() {
	$LC_map = array();
	$LC_map[] = array('name'=>'LC_map_maptype','label'=>'Map Type','value'=>'ROADMAP/ SATELLITE / HYBRID / TERRAIN ');
	$LC_map[] = array('name'=>'LC_map_initial_latitude','label'=>' Latitude','value'=>'0');
	$LC_map[] = array('name'=>'LC_map_initial_longitude','label'=>' Longitude','value'=>'0');
	$LC_map[] = array('name'=>'LC_map_height','label'=>'Map Height','value'=>'500');
	$LC_map[] = array('name'=>'LC_map_width','label'=>'Map Width','value'=>'500');
	$LC_map[] = array('name'=>'LC_map_zoom','label'=>'Map Zoom','value'=>'1');
	return $LC_map;
}

function LC_map_register_settings() {
	$settings = LC_map_settings();
	foreach($settings as $setting) {
		register_setting('LC_map_settings',$setting['name']);
	}
}
// Settings page display
function LC_map_settings_view() {
	$settings = LC_map_settings();
	
	echo '<div class="wrap">';
	
		echo '<h2>Location Map Settings</h2>';
		echo '<form method="post" action="options.php">';
		
    settings_fields('LC_map_settings');
		
		echo '<table>';
			foreach($settings as $setting) {
					echo '<tr>';
					echo '<td>'.$setting['label'].'</td>';
					echo '<td><input type="text" value="'.get_option($setting['name']).'"  placeholder="'.$setting['value'].'" style="width: 400px" name="'.$setting['name'].'"  /></td>';
				echo '</tr>';
			}
		echo '</table>';
		
		submit_button();
		
		echo '</form>';
		
		echo '<hr />';
	echo '</div>';
	}