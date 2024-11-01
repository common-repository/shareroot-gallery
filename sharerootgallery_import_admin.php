<div class="wrap">
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Check access
	$has_access = current_user_can( 'administrator' );
	if (!$has_access) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	/**
	 * Handle form submit.
	 */
    if($_POST['shrg_hidden'] == 'Y') {
    	// Check nonce
    	check_admin_referer( 'set-shareroot-account' );
    	
        //Form data sent
        $gallery_id = $_POST['shrg_gallery_id'];
        $additional_constraints = $_POST['shrg_additional_constraints'];
        if ($additional_constraints == '1')
        {
        	$gallery_height = !empty($_POST['shrg_gallery_height']) ? $_POST['shrg_gallery_height'] : '';
        	$gallery_width = !empty($_POST['shrg_gallery_width']) ? $_POST['shrg_gallery_width'] : '';
        	$uploader_id = !empty($_POST['shrg_uploader_id']) ? $_POST['shrg_uploader_id'] : '';
        }
        else {
        	$additional_constraints = false;
        	$gallery_height = '';
        	$gallery_width = '';
        	$uploader_id = '';
        }
        
        
        // Do some basic error checking
        $errors = array();
        if ($gallery_id && ! is_numeric($gallery_id)) {
        	$errors[] = _e('Gallery Id can only be a numeric value.' );
        }
        if ( strlen( $gallery_id ) > 10 ) {
        	$gallery_id = substr( $gallery_id, 0, 10 );
        }
        if ($gallery_height && ! is_numeric($gallery_height))
        {
        	$errors[] = _e('Please enter a valid numeric value for height.' );
        }
        if ( strlen( $gallery_height ) > 10 ) {
        	$gallery_height = substr( $gallery_height, 0, 10 );
        }
        if ($gallery_width && ! is_numeric($gallery_width))
        {
        	$errors[] = _e('Please enter a valid numeric value for width.' );
        }
        if ( strlen( $gallery_width ) > 10 ) {
        	$gallery_width = substr( $gallery_width, 0, 10 );
        }
        if ($uploader_id && ! is_numeric($uploader_id))
        {
        	$errors[] = _e('Please enter a valid numeric value for uploader id.' );
        }
    	if ( strlen( $uploader_id ) > 10 ) {
        	$uploader_id = substr( $uploader_id, 0, 10 );
        }
        
        // Do some verification of entered data
        if (count($errors)) {
			?>
			<div class="error">
			<?php foreach ($errors as $error): ?>
			<p><strong><?php echo $error; ?></strong></p>
			<?php endforeach; ?>
			</div>
		<?php 
        }
        else 
        {
        	// All good. Let's save.
        	update_option('shrg_gallery_id', ($gallery_id) ? (int)$gallery_id : '');
        	update_option('shrg_additional_constraints', (int)$additional_constraints);
        	if ($additional_constraints)
        	{
        		update_option('shrg_gallery_height', ($gallery_height) ? (int)$gallery_height : '');
        		update_option('shrg_gallery_width', ($gallery_width) ? (int)$gallery_width : '');
        		update_option('shrg_uploader_id', ($uploader_id) ? (int)$uploader_id : '');
        	}
			?>
			<div class="updated"><?php _e('Gallery configuration updated.' ); ?></div>
			<?php  
    	}
    }
    else {
        $gallery_id = get_option('shrg_gallery_id', false);
        $additional_constraints = get_option('shrg_additional_constraints', false);
        $gallery_height = get_option('shrg_gallery_height', '');
        $gallery_width = get_option('shrg_gallery_width', '');
        $uploader_id = get_option('shrg_uploader_id','');
    }
?>
	<?php 
    // If gallery has been set, then we display a shortcode to the user for them to copy paste onto their page.
    if ($gallery_id) 
    {
    	$additional_style = '';
    	if ($additional_constraints)
    	{
    		if ($gallery_height)
    		{
    			$additional_style .= ' height="'.esc_attr($gallery_height).'"';
    		}
    		if ($gallery_width)
    		{
    			$additional_style .= ' width="'.esc_attr($gallery_width).'"';
    		}
    		if ($uploader_id)
    		{
    			$additional_style .= ' uploader_id="'.esc_attr($uploader_id).'"';
    		}
    	}
    	?>
    	<div>
	    	<h3>The Short Code</h3>
	    	<p class="big"><strong>Copy &amp; paste the following short code onto the page you want to display your gallery.</strong></p>
	    	<pre style="background-color:#fff;padding:8px;" class="big">[shrg_gallery gallery_id="<?php echo esc_attr($gallery_id); ?>"<?php echo $additional_style; ?>]</pre>	
    	</div>
    	<hr />
    	<?php 
    }
    ?>

	<p style="font-size: 16px">Don't have a ShareRoot account? No problem!<br><a href="http://shareroot.co/contact/" target="_blank">Contact us now!</a></p>
    <?php    echo "<h2>" . __( 'ShareRoot Gallery Options', 'shrg_trdom' ) . "</h2>"; ?>
    <form name="shrg_form" method="post" action="<?php echo str_replace( '%7E', '~', esc_url($_SERVER['REQUEST_URI'])); ?>">
    	<?php wp_nonce_field( 'set-shareroot-account' ); ?>
        <input type="hidden" name="shrg_hidden" value="Y">
        <table class="form-table">
        	<tbody>
        		<tr>
        			<th scope="row">
        				<label for="shrg_gallery_id"><?php _e("* Gallery Id:"); ?></label>
        			</th>
        			<td>
        				<input type="text" name="shrg_gallery_id" id="shrg_gallery_id" value="<?php echo esc_attr($gallery_id); ?>" size="10">
        			</td>
        		</tr>
        		<tr>
        			<th scope="row">
        				<label for="shrg_additional_constraints"><?php _e( 'Additional constraints:' ); ?></label>
        			</th>
        			<td>
        				<input type="checkbox" name="shrg_additional_constraints" id="shrg_additional_constraints" value="1" <?php ($additional_constraints) ? print 'checked="checked"' : '' ; ?> onclick="shrg_enableConstraints()"> 
        				<?php _e("Check to enable additional constraints" ); ?>
        			</td>
        		</tr>
        	</tbody>
        </table>
        <div id="constraint_area" class="hidden">
        	<table class="form-table">
        		<tbody>
        			<tr>
        				<th scope="row">
        					<label for="shrg_gallery_height"><?php _e("Height:"); ?></label>
        				</th>
        				<td>
        					<input type="text" name="shrg_gallery_height" id="shrg_gallery_height" value="<?php echo esc_attr($gallery_height); ?>" size="10"/>px
        				</td>
        			</tr>
        			<tr>
        				<th scope="row">
        					<label for="shrg_gallery_width"><?php _e("Width:"); ?></label>
        				</th>
        				<td>
        					<input type="text" name="shrg_gallery_width" id="shrg_gallery_width" value="<?php echo esc_attr($gallery_width); ?>" size="10"/>px
        				</td>
        			</tr>
        			<tr>
        				<th scope="row">
        					<label for="shrg_uploader_id"><?php _e("* Uploader Id:"); ?></label>
        				</th>
        				<td>
        					<input type="text" name="shrg_uploader_id" id="shrg_uploader_id" value="<?php echo esc_attr($uploader_id); ?>" size="10">
        					Add the uploader ID here to include an upload button for your gallery.
        				</td>
        			</tr>
        		</tbody>
        	</table>
        </div>
        <p>
        	<strong>*</strong> &nbsp;The gallery ID and Uploader ID can be found in the Embed Code section of your ShareRoot Gallery Configuration Dashboard.<br />
        </p>
        <hr />
        <p class="submit">
	        <input type="submit" name="shrg_Submit" class="button button-primary" value="<?php _e('Update', 'shrg_trdom' ) ?>" />
        </p>
    </form>
</div>
<script type="text/javascript">
function shrg_enableConstraints(selector)
{
	if(document.getElementById('shrg_additional_constraints').checked==true) {
		document.getElementById('constraint_area').className = '';
	}
	else {
		document.getElementById('constraint_area').className = 'hidden';
	}
}
<?php if ($additional_constraints): ?>
document.getElementById('constraint_area').className = '';
<?php endif; ?>
</script>