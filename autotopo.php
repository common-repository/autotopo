<?php
/*
Plugin Name: AutoTopo
Plugin URI: http://www.wandererllc.com/company/plugins/autotopo/
Description: Display USGS topographical maps on your site with a simple shortcode that is placed in a page or a post.
Author: Wanderer LLC Dev Team
Version: 0.82
*/

// This fantastic class does coordinate system conversions
if ( !class_exists( 'gPoint' ) )
{
	require_once 'inc/gPoint.php';
}

define( 'AT_DEFAULT_MAP_SIZE', 'at_default_map_size' );
define( 'AT_DEFAULT_ANCHOR_TILE', 'at_default_anchor_tile' );
define( 'AT_DEBUG_INFO', 0 );


// Various hooks and actions for this plug-in
add_shortcode( 'autotopo', AT_GenerateMap );

//
//	Actions to hook to allow AutoTopo to do it's work
//
//	See:  http://codex.wordpress.org/Plugin_API/Action_Reference
//
add_action( 'admin_menu', 'AT_OnPluginMenu' );				// Sets up the menu and admin page
add_action( 'wp_print_styles', 'AT_AddStyles' );			// Needed for adding a custom style for topo tables

//
//	Filters to hook
//
add_filter( 'plugin_row_meta', 'AT_AddAutoTopoPluginLinks', 10, 2 ); // Expand the links on the plugins page

//	Supported dimensions
$MAP_SIZES = array( '2x2', '3x2', '3x3' );

//
//	Function to create the menu and admin page handler
//
function AT_OnPluginMenu()
{
	add_submenu_page('options-general.php', 'AutoTopo Options', 'AutoTopo', 'add_users', basename(__FILE__), AT_AutoTopoOptions );
}

//
//	AT_AddStyles will add custom style for tables which is needed to ensure that tables
//	which host topos are as minimalistic as possible.  There can be no borders or spaces
//	otherwise the map will look bad.
//
function AT_AddStyles()
{
	$url = WP_PLUGIN_URL . '/autotopo/topo_style.css';
	$styleFile = WP_PLUGIN_DIR . '/autotopo/topo_style.css';
	if ( file_exists( $styleFile ) )
	{
		wp_register_style('TopoStyleSheets', $url);
		wp_enqueue_style( 'TopoStyleSheets');
	}
}

//
//	This function just adds in some extra links on the Plugins page.
//
function AT_AddAutoTopoPluginLinks($links, $file)
{
	if ( $file == plugin_basename(__FILE__) )
	{
		$links[] = '<a href="http://wordpress.org/extend/plugins/autotopo/">' . __('Overview', 'autotopo') . '</a>';
		$links[] = '<a href="http://wordpress.org/extend/plugins/autotopo/">' . __('Donate', 'autotopo') . '</a>';
	}
	return $links;
}

//
//	This function is responsible for displaying the AutoTopo admin panel.  That
//	happens at the very bottom, with the require statement.  The rest of the code
//	is for saving the options.
//
function AT_AutoTopoOptions()
{
	// Stop the user if they don't have permission
	if (!current_user_can('add_users'))
	{
    	wp_die( __('You do not have sufficient permissions to access this page.') );
  	}

	// Save off the autotopo options here
	if ( isset( $_POST['save_autotopo_options'] ) )
	{
		// Security check
		check_admin_referer( 'autotopoz-nonce' );

		// Save the options now
		$mapSize = $_POST['mapsizegroup'];
		$tileAnchor = $_POST['anchortile'];

		// Write them to the DB
		update_option( AT_DEFAULT_MAP_SIZE, $mapSize );
		update_option( AT_DEFAULT_ANCHOR_TILE, $tileAnchor );

		print '<div id="message" class="updated fade"><p>Successfully saved your AutoTopo options!</p></div>';
	}

	// The file that will handle uploads is this one (see the "if" above)
	$action_url = $_SERVER['REQUEST_URI'];
	require_once 'autotopo_settings.php';
}

//
//	This is where most of the works happens.  Give the shortcode in a page or
//	post, this function converts that shortcode into a borderless/no-padding
//	table which holds the tiles that form the map that the user has requested.
//
function AT_GenerateMap( $params )
{
	// Store the various options values in an array.  Set default values here too.
	$values = shortcode_atts( array( 	'lat' => '',
									 	'long' => '',
										'mapsize' => get_option( AT_DEFAULT_MAP_SIZE ),
										'anchortile' => get_option( AT_DEFAULT_ANCHOR_TILE ),
										'label' => '',
									), $params );

	$lat = $values['lat'];
	$long = $values['long'];
	$mapSize = $values['mapsize'];
	$anchorTile = $values['anchortile'];
	$label = $values['label'];

	// Verify the map size variable
	global $MAP_SIZES;
	if ( !in_array( $mapSize, $MAP_SIZES ) )
	{
		return "<p>Invalid mapsize argument: '$mapSize'.</p>";
	}

	if ( AT_DEBUG_INFO )
		print "<p>MapSize = $mapSize, Anchor = $anchorTile</p>";

	// Create an empty point with the default datum
	$coordinates =& new gPoint();

	// Set the point's Longitude & Latitude.
	$coordinates->setLongLat( $long, $lat );

	// Switch to UTM and save coordinates
	$coordinates->convertLLtoTM();
	$northing = $coordinates->N();
	$easting = $coordinates->E();
	$zone = $coordinates->Z();

	// Convert to tiles now
	$northing = floor( $northing / 800 );
	$easting = floor( $easting / 800 );

	// Strip off letters in the zone.
	$zone = ereg_replace("[^0-9]", "", $zone);

	// Save off the number of rows and columns for the final image
	$dimensions = explode( 'x', $mapSize );
	$numColumns = $dimensions[0]; 	// columns = x
	$numRows = $dimensions[1]; 		// rows = y

	// Verify the anchor tile
	if ( $anchorTile > $numColumns * $numRows )
	{
		return "<p>Invalid anchortile argument: '$anchorTile'.</p>";
	}

	// Anchor the northing and easting points based on the anchor point
	$dims = AnchorTileToColumnRow( $anchorTile, $numColumns, $numRows );
	$row = $dims[0];
	$col = $dims[1];
	if ( AT_DEBUG_INFO )
		print "<p>Anchor Point row/col = $row,$col</p>";

	// Calculate the minimum northing and maximum easting (because images are
	// constructed from left to right, top to bottom.  UTM's origin is lower
	// left.
	$startEasting	= $easting - ( $col - 1 );
	$endEasting		= $easting + ( $numColumns - $col );
	$startNorthing 	= $northing + ( $row - 1 );
	$endNorthing 	= $northing - ( $numRows - $row );

	if ( AT_DEBUG_INFO )
		print "<p>Northing: $northing,Easting: $easting</p>";

	// Generate the table
	$output = PHP_EOL . '<table class="autotopo">' . PHP_EOL;
	for ( $y = $startNorthing; $y >= $endNorthing; $y-- )
	{
		$output .= '<tr>' . PHP_EOL;
		for ( $x = $startEasting; $x <= $endEasting; $x++ )
		{
			$debugInfo = '';
			if ( AT_DEBUG_INFO )
				$debugInfo = "<p>$x, $y, $zone</p>";
			$output .= '<td><img src="http://MSRMaps.com/tile.ashx?T=2&S=12&X=' . $x . '&Y=' . $y . '&Z=' . $zone . '" width=200 height=200>' . $debugInfo . '</td>' . PHP_EOL;
		}
		$output .= '</tr>' . PHP_EOL;
	}
	$output .= '</table>' . PHP_EOL;
	if ( 0 < strlen( $label ) )
		$output .= '<label>(above) ' . $label . '</label>' . PHP_EOL;
	return $output;
}

//
//	Given the anchor tile number and the number of rows and columns in the map,
//	returns the (row,column) in array format where the anchor row appears.  For
//	example, in a 2x3 map, an anchor tile of "4" returns (2,1)...second row, first
//	column.  In a 3x4 map, an anchor tile of "4" returns (1,4)...first row, fourth
//	column.
//
function AnchorTileToColumnRow( $anchorTile, $numColumns, $numRows )
{
	$row = ceil( $anchorTile /  $numColumns );
	$column = $anchorTile % $numColumns;
	if ( 0 === $column )
		$column = $numColumns;

	return array( $row, $column );
}

?>