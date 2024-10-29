<div class="wrap" style="max-width:950px !important;">
<h2>AutoTopo</h2>
<div id="poststuff" style="margin-top:10px;">
<div id="mainblock" style="width:710px">
<div class="dbx-content">

<form enctype="multipart/form-data" action="<?php echo $action_url ?>" method="POST">

<?php
wp_nonce_field('autotopoz-nonce');

$pluginFolder = get_bloginfo('wpurl') . '/wp-content/plugins/' . dirname( plugin_basename( __FILE__ ) ) . '/';
?>

<div style="float:right;width:220px;margin-left:10px;border: 1px solid #ddd;background: #fdffee; padding: 10px 0 10px 10px;">
 	<h2 style="margin: 0 0 5px 0 !important;">Information</h2>
 	<ul id="dbx-content" style="text-decoration:none;">
    	<li><img src="<?php echo $pluginFolder;?>help.png"><a style="text-decoration:none;" href="http://www.wandererllc.com/company/plugins/autochimp" target="_blank"> Support and Help</a></li>
		<li><a style="text-decoration:none;" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JGET7U7Z9685Q&lc=US" target="_blank"><img src="<?php echo $pluginFolder;?>paypal.gif"></a></li>
    	<li><table border="0">
    		<tr>
    			<td><a href="http://member.wishlistproducts.com/wlp.php?af=1080050" target="_blank"><img src="http://www.wishlistproducts.com/affiliatetools/images/WLM_120X60.gif" border="0"></a></td>
    			<td>Want a membership site? Try <a style="text-decoration:none;" href="http://member.wishlistproducts.com/wlp.php?af=1080050" target="_blank">Wishlist</a></td>
    		</tr>
    	</table></li>
    	<li><table border="0">
    		<tr>
    			<td><a href="http://www.woothemes.com/amember/go.php?r=39127&i=b18" target="_blank"><img src="http://woothemes.com/ads/120x90c.jpg" border=0 alt="WooThemes - WordPress themes for everyone" width=120 height=90></a></td>
    			<td>Make your site <em>stunning</em> with <a style="text-decoration:none;" href="http://www.woothemes.com/amember/go.php?r=39127&i=b18" target="_blank">WooThemes for WordPress</a></td>
    		</tr>
    	</table></li>
    	<li>Contact <a href="http://www.wandererllc.com/company/contact/" target="_blank">Wanderer LLC</a> to sponsor a feature or write a plugin just for you.</li>
    	<li>Leave a good rating or comments for <a href="http://wordpress.org/extend/plugins/autotopo/" target="_blank">AutoTopo</a>.</li>
	</ul>
</div>

<div id="autotopo_options_key" class="postbox" style="width:450px;height:530px">
<h3 class='hndle'><span>AutoTopo Options and Usage Guide</span></h3>
<div class="inside">

<p><strong>Choose a default map size:</strong></p>
<p>Each map can override this value with the 'mapsize' shortcode.  The value passed must be one of the values in parentheses below.</p>

<?php
	$mapSize = get_option( AT_DEFAULT_MAP_SIZE );
	if ( empty( $mapSize ) )
		$mapSize = '2x2';
	$anchorTile = get_option( AT_DEFAULT_ANCHOR_TILE );
	if ( empty( $anchorTile ) )
		$anchorTile = '1';

	$checked = '';
	$mapSizes = array( 	'2x2' => '400 x 400 pixels (2x2)',
						'3x2' => '600 x 400 pixels (3x2)',
						'4x3' => '800 x 600 pixels (4x2)' );
	foreach( $mapSizes as $size => $text )
	{
		if ( 0 === strcmp( $mapSize, $size ) )
			$checked = ' checked';
		else
			$checked = '';
		print '<input type="radio" name="mapsizegroup" value="' . $size . '"' . $checked . '> ' . $text . '<br />';
	}
?>

<p><strong>Choose a default anchor tile:</strong></p>
<p>Each map can override this value with the 'anchortile' shortcode.  Set 'anchortile' when you want the map to grow in a certain direction.  Each tile in your image is labeled with a number, starting at '1', working left to right, top to bottom.  So, a mapsize of '2x2' would be numbered 1-4, with 1 and 2 being on the top row, and 3 and 4 being on the bottom row.  If you set 'anchortile' to '4' (the lower right corner), then the lat/long that you specified would correspond to the 4th tile, and the other tiles would be north and west of your anchor tile.  Your anchor tile is always the tile that corresponds to the lat/long you've specified.</p>
<input type="text" name="anchortile" size="15" value="<?php echo $anchorTile;?>"/>

<div class="submit"><input type="submit" name="save_autotopo_options" class="button-primary" value="Save Options" /></div>
<div class="clear"></div>

<p><strong>Shortcode Usage:</strong></p>
<p>Typical shortcode usage:  [autotopo lat='39.74223' long='-106.33518' anchortile='4' mapsize='3x2' label='Bubble Lake']</p>
<p>'lat' and 'long' are the only required options.  You can set default values for 'anchortile' and 'mapsize', though you'll probably often override 'anchortile'.  'label' is completely optional.</p>

</div>

</form>

</div>
</div>
</div>
</div>

