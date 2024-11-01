<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /*
    Plugin Name: ShareRoot Gallery
    Plugin URI: http://shareroot.co
    Description: Plugin for displaying ShareRoot galleries.
    Author: Shareroot Team
    Version: 1.0.0
    Author URI: http://shareroot.co
    */
?>
<?php 

class Shareroot_Galleries
{
	private $config;
	private $gallery_id;
	private $width;
	private $height;
	private $constraints_enabled = false;
	private $uploader_id;

	/**
	 * Initialize.
	 * 
	 * @param array $config
	 */
	public function __construct($config)
	{
		$this->config = $config;
		
		// Register the short code.
		add_shortcode( 'shrg_gallery', array($this, 'shrg_gallery_func' ));
		
		// Add the CSS script to the header
		add_action( 'wp_enqueue_scripts', array($this, 'shareroot_css_scripts' ));
	}

	/**
	 * This method is called when the shortcode is fired by wordpress.
	 * We will check for some of the members to set.
	 * 
	 * @param array $members
	 */
	public function initialize_members($members)
	{
		if (isset($members['height']) && is_numeric($members['height']))
		{
			$this->height = (int)$members['height'];
			$this->constraints_enabled = true;
		}
		if (isset($members['width']) && is_numeric($members['width']))
		{
			$this->width = (int)$members['width'];
			$this->constraints_enabled = true;
		}
		if (isset($members['gallery_id']) && is_numeric($members['gallery_id']))
		{
			$this->gallery_id = (int)$members['gallery_id'];
		}
		if (isset($members['uploader_id']) && is_numeric($members['uploader_id']))
		{
			$this->uploader_id = (int)$members['uploader_id'];
		}
	}

	/**
	 * Add CSS script.
	 */
	public function shareroot_css_scripts()
	{
		wp_enqueue_style('shareroot-gallery', $this->get_css());
	}

	/**
	 * This method will get the javascript, replace the gallery Id, and print the resulting code.
	 * 
	 * IMPORTANT!! This method must print (or echo) the string and *not* return the string.
	 */
	public function shareroot_js_scripts()
	{
		if ($this->uploader_id)
		{
			$js = $this->get_js('image');
			$tmp = sprintf($js, (int)$this->uploader_id, (int)$this->gallery_id);
		}
		else
		{
			$js = $this->get_js();
			$tmp = sprintf($js, (int)$this->gallery_id);
		}

		echo $tmp;
	}

	/**
	 * This method is called when WP hits the short code on the page.
	 * Example short code: [shrg_gallery gallery_id="GALLERY_ID" width="NUMBER" height="NUMBER"]
	 * 
	 * @param array $atts
	 */
	public function shrg_gallery_func( $atts ) {
		$a = shortcode_atts( array(
				'gallery_id' => false,
				'width' => '',
				'height' => '',
				'uploader_id' => ''
		), $atts );

		$this->initialize_members($a);

		if ($this->gallery_id) {
			
			// Add the javascript to the end of the page
			add_action('wp_footer', array($this, 'shareroot_js_scripts'));
			
			// Code to print to page.
			return $this->get_body();
		}
	}
	
	/**
	 * Get image uploader button.
	 * 
	 * @param string $type
	 */
	private function get_image($type='normal')
	{
		return $this->config['image'][$type];
	}
	
	/**
	 * Print the snippet to the page.
	 * 
	 * @param string $type
	 * @param boolean $with_upload Displays the upload button
	 * @param int $width
	 * @param int $height
	 */
	private function get_body()
	{
		if ($this->constraints_enabled)
		{
			$type='styled';
		}
		else
		{
			$type='normal';
		}
		
		// Get the values for the style tag.
		$style_value = $this->get_constraint_style_value();
		$str = '';
		if ($this->uploader_id)
		{
			$str .= $this->get_image();
		}
		$str .= sprintf($this->config['body'][$type], $style_value);
		
		return $str;
	}
	
	/**
	 * If constraints are defined, build the string here.
	 */
	private function get_constraint_style_value()
	{
		$str = '';
		if ($this->height)
		{
			$str .= 'height:'.$this->height.'px;';
		}
		if ($this->width)
		{
			$str .= 'width:'.$this->width.'px;';
		}
		return $str;
	}
	
	/**
	 * Get customized css styles.
	 * 
	 * @param string $type
	 */
	private function get_css($type='sdk')
	{
		return $this->config['css'][$type];
	}
	
	/**
	 * Get javascript snippet.
	 * 
	 * @param string $type
	 */
	private function get_js($type='normal')
	{
		return $this->config['js'][$type];
	}
}

// Check if plugin has been activaated
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'shareroot-gallery/shareroot_gallery.php' ) ) {	
	
	$shareroot_gallery_config = parse_ini_file(plugin_dir_path(__FILE__).'config.ini', true);
	$my_shareroot_class = new Shareroot_Galleries($shareroot_gallery_config);
}

/**
 * Admin Functions
 */
function sharerootgallery_admin()
{
	include('sharerootgallery_import_admin.php');
}
function sharerootgallery_admin_actions() {
	// Add menu item to admin config list
	if ( current_user_can( 'administrator' ) )
	{
		add_options_page('ShareRoot Gallery', 'ShareRoot Gallery', 1, 'ShareRoot Gallery', 'sharerootgallery_admin');
	}
}
add_action('admin_menu', 'sharerootgallery_admin_actions');