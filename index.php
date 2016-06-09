<?php
	/*
  Plugin Name: Location Click Map
  Description: Location Click Map plugin is useful to display marker on clicking of location, use shortcode [location_map].
  Version: 1.0

 */
require('settings-page-locations.php');
// Add the google maps api to header
add_action('wp_head', 'LC_map_header');

// Create custom taxonomy for map 


function LC_map_header() {
    ?>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <?php
}

// generate script
function LC_map_build_map($short_attribute) {
    $markertext = '';
    $short_attribute = shortcode_atts(array(
        'lat' => (get_option('LC_map_initial_latitude') !='') ? sanitize_text_field(get_option('LC_map_initial_latitude')) : 0 ,
        'lon' => (get_option('LC_map_initial_longitude') !='') ? sanitize_text_field(get_option('LC_map_initial_longitude')): 0,
        'id' => 'map',
        'z' => (get_option('LC_map_zoom') !='') ? sanitize_text_field(get_option('LC_map_zoom')) : 16 ,
        'w' => (get_option('LC_map_width') !='') ? sanitize_text_field(get_option('LC_map_width')) : 500 ,
        'h' => (get_option('LC_map_height') !='') ? sanitize_text_field(get_option('LC_map_height')) : 500,
        'maptype' => (get_option('LC_map_maptype') !='') ? sanitize_text_field(get_option('LC_map_maptype')): 'ROADMAP',
		'hide_locations' => '0',
        'marker' => ''
            ), $short_attribute);
			
		if($short_attribute['hide_locations'] == 0){
				$front_locations = '';
		    if (!isset($wpdb))
			$wpdb = $GLOBALS['wpdb'];
				$markers = $wpdb->get_results("SELECT id,title, lattitude, longitude,html,icon,postdate FROM " . $wpdb->prefix . "location_click_map_data");
				$front_locations = '<div class="location-map-div"><ul style="cursor:pointer" class="location-maps-ul">';
				foreach ($markers as $marker) {
					$front_locations .= '<li  onclick="lc_location_map_filterMarkers(this.id)" id="'. $marker->id .'" class="location-map-li">
							<h3 class="location-map-title">'. $marker->title .'</h3>
							<p class="location-map-address">'. $marker->html .'</p>
					</li>';
				}
				$front_locations .= '</ul></div>';
				$generateScript = '  '.$front_locations.' ';
			
		}
		else{
			$generateScript = '';
		}
		
	$generateScript .=' 
    <div id="' . $short_attribute['id'] . '" style="width:' . $short_attribute['w'] . 'px;height:' . $short_attribute['h'] . 'px;border:1px solid gray;"></div><br>
    <script type="text/javascript">
    var infowindow = null;
		var latlng = new google.maps.LatLng(' . $short_attribute['lat'] . ', ' . $short_attribute['lon'] . ');
		var myOptions = {
			zoom: ' . $short_attribute['z'] . ',
			center: latlng,
			mapTypeId: google.maps.MapTypeId.' . $short_attribute['maptype'] . '
		};
		var ' . $short_attribute['id'] . ' = new google.maps.Map(document.getElementById("' . $short_attribute['id'] . '"),
		myOptions);
		';

    $generateScript .=' 
	var gmarkers = [];
	var locations = [';
    if (!isset($wpdb))
        $wpdb = $GLOBALS['wpdb'];
    $markers = $wpdb->get_results("SELECT id,title, lattitude, longitude,html,icon,postdate FROM " . $wpdb->prefix . "location_click_map_data");
    foreach ($markers as $marker) {
        if (isset($marker->icon) && @GetImageSize($marker->icon)) {
            $markertext .='[' . $marker->lattitude . ',' . $marker->longitude . ',\'' . $marker->html . '\',\'' . $marker->icon . '\',\'' . $marker->id . '\'],';
        } else {
            $markertext .='[' . $marker->lattitude . ',' . $marker->longitude . ',\'' . $marker->html . '\',null,\'' . $marker->id . '\'],';
        }
    }
    $markertext = substr($markertext, 0, strlen($markertext) - 1);
    $generateScript .=$markertext;
    $generateScript .='];';
    $generateScript .='
	 ';
    $generateScript .=' 
	 var bounds = new google.maps.LatLngBounds();
	 for (var i = 0; i < locations.length; i++) {';
    $generateScript .=' var loc = locations[i];
	 ';
    $generateScript .=' var siteLatLng = new google.maps.LatLng(loc[0], loc[1]);
   ';
    $generateScript .=' if(loc[3]!=null) { 
   ';
    $generateScript .=' var markerimage  = loc[3];
   ';
    $generateScript .=' var marker = new google.maps.Marker({
   ';
    $generateScript .=' position: siteLatLng,
   ';
    $generateScript .= ' map: ' . $short_attribute['id'] . ',
   ';
	$generateScript .= ' category: loc[4],
   ';
    $generateScript .= ' icon: markerimage,
   ';
    $generateScript .= ' html: \'<div class="custom-location-info">\'+ loc[2] +\'</div>\'});
   ';
    $generateScript .=' } else {
   ';
    $generateScript .=' var marker = new google.maps.Marker({
   ';
    $generateScript .=' position: siteLatLng,
   ';
    $generateScript .= ' map: ' . $short_attribute['id'] . ',
   ';
	$generateScript .= ' category: loc[4],
   ';
    $generateScript .= ' html: \'<div class="custom-infowindow">\'+ loc[2] +\'</div>\' });';
    $generateScript .=' } gmarkers.push(marker); 
   ';
    $generateScript .= ' var contentString = "Some content";';
    $generateScript .= 'google.maps.event.addListener(marker, "click", function () {
   ';
    $generateScript .= 'infowindow.setContent(this.html);
   ';
    $generateScript .= ' infowindow.open(' . $short_attribute['id'] . ', this); 
   ';
    $generateScript .= '});
   ';
    $generateScript .= 'bounds.extend(marker.position); }
	if(locations.length == 1){
			map.setCenter( bounds.getCenter() );
			map.setZoom( 12 );
	}
	else{
		 map.fitBounds(bounds)
	}
	;
   ';
    $generateScript .=' infowindow = new google.maps.InfoWindow({
                content: "loading.."
            });
    ';
    $generateScript .= '
		lc_location_map_filterMarkers = function (category) {
		for (i = 0; i < locations.length; i++) {

        marker1 = gmarkers[i];
		
        // If is same category or category not picked
        if (marker1.category == category || category.length === 0) {
            marker1.setVisible(true);
        }
        else {
            marker1.setVisible(false);
        }
		if(category == "0")
			marker1.setVisible(true);
    }
		
	if(locations.length == 1){
			map.setCenter( bounds.getCenter() );
			map.setZoom( 12 );
	}
	else{
		 map.fitBounds(bounds)
	}
}
		</script>';
    return $generateScript;
    ?>



    <?php
}

add_shortcode('location_map', 'LC_map_build_map');

// loading js files 
function LC_map_load_scripts() {
    wp_enqueue_script('slider-validation-js', plugins_url('js/validation.js', __FILE__));
    //wordpress nonce check

    if (isset($_POST['map_form_nonce_field']) && wp_verify_nonce($_POST['map_form_nonce_field'], 'map_form_action')) {
		
        // process form data
        LC_map_post_map_form();
    }
    //wordpress nonce check
    if (isset($_POST['map_del_nonce_field']) && wp_verify_nonce($_POST['map_del_nonce_field'], 'map_del_action')) {
        // process form data
        LC_map_delete_map();
    }
}

add_action('admin_init', 'LC_map_load_scripts');

function LC_map_post_map_form() {

    // Validate user role/permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    // extract to variables
		extract($_POST);
        $date = date('Y-m-d H:i:s');
		if (!isset($wpdb))
            $wpdb = $GLOBALS['wpdb'];
        $wpdb->insert($wpdb->prefix . 'location_click_map_data', array('title' => sanitize_text_field($title), 'lattitude' => sanitize_text_field($lattitude), 'longitude' => sanitize_text_field($longitude),  'html' => sanitize_text_field($infohtml), 'icon' => sanitize_text_field($icon), 'postdate' => $date), array('%s', '%s'));
}

function LC_map_delete_map() {
    // only if numeric values 
    // Validate user role/permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (isset($_REQUEST['deleteval']) && is_numeric($_REQUEST['deleteval'])) {
        $id = $_REQUEST['deleteval'];
        if (!isset($wpdb))
            $wpdb = $GLOBALS['wpdb'];
        $LC_map_table_name = $wpdb->prefix . 'location_click_map_data';
        $wpdb->query("DELETE FROM $LC_map_table_name WHERE ID = $id ");
    }
}

function LC_map_admin_menu() {

    add_menu_page('Locations Map', 'Locations Map', 'manage_options', 'map-locations', 'LC_map_menu_plugin_options');
}

//
add_action('admin_menu', 'LC_map_admin_menu');

//	

function LC_map_add_submenu_page() {
    add_submenu_page(
            'map-locations', 'New Location', 'New Location', 'manage_options', 'addnew_location', 'LC_map_add_options_function'
    );
}

add_action('admin_menu', 'LC_map_add_submenu_page');

function LC_map_add_options_function() {

    // Validate user role/permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>

    <div class="wrap">
        <h2><?php echo esc_html('Add New Location'); ?></h2>
        <form method="post" name="map_form" id="map_form" action="" >

            <?php
            // WordPress nonce field
            wp_nonce_field('map_form_action', 'map_form_nonce_field');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Title'); ?></th>
                    <td><input required type="text" name="title" id="title" class="" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Lattitude'); ?></th>
                    <td><input required type="text" name="lattitude" id="lattitude" class="" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Longitude'); ?></th>
                    <td><input required type="text" name="longitude" id="longitude" class="" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Address'); ?></th>
                    <td><input required type="text" name="infohtml" id="infohtml" class="" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html('Map Icon URL'); ?></th>
                    <td><input required type="url" name="icon" id="icon" class="" value="" /></td>
                </tr>
            </table>

            <?php submit_button(); ?>

        </form>

    </div>
    <?php
}

// display map list 
function LC_map_menu_plugin_options() {
    $cat_string = '';
    if (!isset($wpdb))
        $wpdb = $GLOBALS['wpdb'];
// Validate user role/permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html('Locations Map'); ?>
            <a class="page-title-action" href="<?php echo admin_url(); ?>admin.php?page=addnew_location"><?php echo esc_html('Add New Location'); ?></a>
        </h1>
    </div>
    <table class="wp-list-table widefat fixed striped pages">	
        <thead>
            <tr >
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Title'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Lattitude'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Longitude'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Icon'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Address'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Date Posted'); ?></th>
                <th class="manage-column column-author" id="author" scope="col"><?php echo esc_html('Action'); ?></th>
            </tr>
        </thead> 
        <?php
        $all_maps = $wpdb->get_results("SELECT id,title, lattitude, longitude,html,icon, postdate FROM " . $wpdb->prefix . "location_click_map_data");

        foreach ($all_maps as $map) {
            $cat_string = '';
            ?>	
            <tr class="row-title">
                <th><?php echo esc_html($map->title); ?></th>
                <th><?php echo esc_html($map->lattitude); ?></th>
                <th><?php echo esc_html($map->longitude); ?></th>
                <th><?php echo esc_html($map->icon); ?></th>
                <th><?php echo esc_html($map->html); ?></th>
                <th><?php echo esc_html($map->postdate); ?></th>
				<th>
            <form action="" id="delfrm<?php echo $map->id; ?>" name="delfrm<?php echo $map->id; ?>" method="post">
                <?php
                // WordPress nonce field
                wp_nonce_field('map_del_action', 'map_del_nonce_field');
                ?>
                <a href="javascript:;"onclick="javascript:confirm('Do you really want to delete') ? location_validate(event, <?php echo $map->id; ?>) : 0"  /><?php echo esc_html('Delete'); ?> </a>
                <input type="hidden" name="deleteval" id="deleteval" value="<?php echo esc_html($map->id); ?>" />
            </form>
        </th>

        <tr>
        <?php }
        ?>

    </tr>

    <tbody id="the-list">

    </tbody>
    </table>
    <?php
}

/* Plugin Activation Hook
 * 
 */

function LC_map_plugin_options_install() {
    if (!isset($wpdb))
        $wpdb = $GLOBALS['wpdb'];
    $LC_map_table_name = $wpdb->prefix . 'location_click_map_data';

    if ($wpdb->get_var("show tables like '$LC_map_table_name'") != $LC_map_table_name) {
        $sql = "CREATE TABLE " . $LC_map_table_name . " (
		id INT NOT NULL AUTO_INCREMENT,
		lattitude double NOT NULL,
		title text,
		longitude double NOT NULL,
		html text,
		icon text,
		postdate datetime , 
		PRIMARY KEY (id)
		);";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'LC_map_plugin_options_install');

// Plugin deactivation hook
function LC_map_hook_uninstall() {
    if (!isset($wpdb))
     $wpdb = $GLOBALS['wpdb'];
    $LC_map_table_name = $wpdb->prefix . 'location_click_map_data';
    $wpdb->query("DROP TABLE IF EXISTS $LC_map_table_name");
}

register_uninstall_hook(__FILE__, 'LC_map_hook_uninstall');
?>