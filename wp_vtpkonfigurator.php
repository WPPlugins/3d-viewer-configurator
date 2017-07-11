<?php
/*
Plugin Name: 3D Produkt Viewer
Plugin URI: http://3d-produkt-viewer.eu/software/
Description: Wordpress 3D Produkt Viewer
Author: Visualtektur and ProNego
Version: 1.7.3
Author URI: http://www.visualtektur.net/
*/

// Huge file sizes lead to a massive execution time...
set_time_limit(86400);

// Pre-2.6 compatibility
if (!defined('WP_CONTENT_URL'))
{
	define('WP_CONTENT_URL', get_option( 'siteurl' ).'/wp-content');
}
if (!defined('WP_CONTENT_DIR'))
{
	define('WP_CONTENT_DIR', ABSPATH.'wp-content');
}
if (!defined('WP_PLUGIN_URL'))
{
	define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
}
if (!defined( 'WP_PLUGIN_DIR'))
{
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
}

// license class
require_once("licensefactory.inc.php");
 
// main plugin class
if (!class_exists('wp_vtpkonfigurator'))
{
	class wp_vtpkonfigurator
	{
		public $localizationDomain = "3d-viewer-configurator";

		protected $plugin_url;
		protected $plugin_path;
		protected $table_name;
		
		protected $debug = FALSE; // set to true to include non-compressed JS
		protected $licensed;
		protected $registered_to = '';
		
		// configuration
		protected $allowed_variants_free = 5;
		protected $allowed_products_free = 1;
		protected $items_per_page = 20;

		protected $db;
		protected $licensefactory; // needed for license key validation#
		protected $licensekeyfile = 'regkey.txt';

		/**
		 * Maintain compatibility with old constructor.
		 */
		function wp_vtpkonfigurator()
		{
			$this->__construct();
		}

		/**
		 * Default constructor.
		 */
		function __construct()
		{
			// paths
			$this->plugin_url = WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__));
			$this->plugin_path = WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__));
			
			// check license
			$this->licensefactory = new License_factory;
			
			// read user_id (line 1) and licensekey (line 2) from file "regkey.txt"
			$regkey = NULL;
			if (file_exists($this->plugin_path.'/'.$this->licensekeyfile))
			{
				$regkey = file($this->plugin_path.'/'.$this->licensekeyfile);
			}
			
			// check reg file
			if (is_array($regkey) AND count($regkey) == 2)
			{
				$this->registered_to = $regkey[0];
				$this->licensed = $this->licensefactory->validate_licensekey($this->registered_to, $regkey[1]);
			}
			else
			{
				$this->licensed = FALSE;
			}
			
			// language setup
 			load_plugin_textdomain($this->localizationDomain, false, basename(dirname(__FILE__)).'/languages/');
			 
			global $wpdb;
			$this->db = &$wpdb;

			// database table name
			$this->table_name = $this->db->prefix.'vtpkonfigurator';

			// add actions
			if (is_admin())
			{
				add_action("admin_menu", array(&$this, "admin_menu_link")); // prepare admin menu
				add_action('init', array(&$this, "init_sessions")); // init session (for paypal payment)
				add_action('wp_ajax_register', array(&$this, "register_ajax_callback")); // activate ajax handler to enter serial
			}
			else
			{
				// frontend scripts

				// script
				add_action('wp_print_scripts', array(&$this, 'add_js'));

				// style
				add_action('wp_print_styles', array(&$this, 'add_css'));

				// content filter
				add_filter('the_content', array(&$this, 'the_content'), 0);
			}
			 

			// install hook
			register_activation_hook(__FILE__, array(&$this, "install"));
			// Uninstallation is done using suggested uninstall.php
			
			// for auto update
			add_filter('upgrader_pre_install', array(&$this, "create_backup"), 10, 2);
			add_filter('upgrader_post_install', array(&$this, "restore_backup"), 10, 2);
		}

		/**
		 * Install function.
		 */
		function install()
		{
			$sql = "CREATE TABLE ".$this->table_name. " (
				v_id int(12) unsigned NOT NULL AUTO_INCREMENT,
				v_name varchar(255) COLLATE utf8_bin NOT NULL,
				v_options varchar(255) COLLATE utf8_bin NOT NULL,
				v_rpm varchar(255) COLLATE utf8_bin NOT NULL,
				v_configurationlabels varchar(255) COLLATE utf8_bin NOT NULL,
				PRIMARY KEY  (v_id)
				);";
			
			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			dbDelta($sql);

			// restore backup
			$this->restore_backup();
		}
		
		/**
		 * Create a backup of the configuration data and license key file.
		 */
		function create_backup()
		{
			$working_dir = WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)); 
			
			// Backup potential configurations
			rename($working_dir.'/data/', WP_PLUGIN_DIR.'/3d_viewer_configurator-databackup');
			
			// Backup reg file
			if (file_exists($working_dir.'/regkey.txt'))
				copy($working_dir.'/regkey.txt', WP_PLUGIN_DIR.'/3d_viewer_configurator-databackup/regkey.txt');
		}
		
		/**
		 * Restore a backup if it exists.
		 */
		function restore_backup()
		{
			// Move back backup data (moved during uninstall)
			$backup_dir = WP_PLUGIN_DIR.'/3d_viewer_configurator-databackup';
			if (file_exists($backup_dir)) {
				// 1. try to restore regkey file if exists
				if (file_exists($backup_dir.'/'.$this->licensekeyfile))
					rename($backup_dir.'/'.$this->licensekeyfile, WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)).'/'.$this->licensekeyfile);
			
				// 2. move configurations (dir itself)
				rename($backup_dir, WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)).'/data/');
			}
		}


		/**
		 * Filter content.
		 */
		function the_content($content)
		{
			$wp_tag = '/(\[wp_vtpkonfigurator)[^\]]*\]/';
			// get wp_vtpviewer elements
			if (preg_match_all($wp_tag, $content, $a))
			{
				while(list(, $block) = @each($a[0]))
				{
					// get parameters from block
					preg_match_all('#([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*")#', $block, $matches, PREG_SET_ORDER);
					$params = array();
					foreach($matches as $attr)
					{
						$params[$attr[1]] = str_replace(array('"', "'"), array('', ''), $attr[2]);
					}

					// get object by ID
					if ($params['id'])
					{
						$item = $this->db->get_row("
							SELECT * FROM ".$this->table_name."
							WHERE v_id = '".$this->db->escape($params['id'])."'
							", ARRAY_A);

						if ($item['v_id'])
						{
							$dir = $this->plugin_path.'/data/'.$item['v_id'];
							$url = $this->plugin_url.'/data/'.$item['v_id'];
							$options = $item['v_options'];
							$rpm = $item['v_rpm'];
							$configuration_labels = $item['v_configurationlabels'];
						}
						else
						{
							$content = preg_replace($wp_tag, "WP_VTPKONFIGURATOR ERROR: Invalid ID.", $content, 1);
							continue;
						}
					}
					else
						if ($params['path'])
						{
							$dir = $params['path'];
							$url = home_url($params['path']);
							$options = $params['options'];
							$rpm = $params['rpm'];
							$configuration_labels = $params['configurationlabels'];
						}
						else
						{
							$content = preg_replace($wp_tag, "WP_VTPKONFIGURATOR ERROR: Invalid options.", $content, 1);
							continue;
						}

						if (!is_dir($dir))
						{
							$content = preg_replace($wp_tag, "WP_VTPKONFIGURATOR ERROR: Directory not found.", $content, 1);
							continue;
						}

						// Get Data (data/id/<folderN>/<images>)
						$data = $this->get_data($dir);
						
						$variants = $data['variants'];
						$pagetitle = $data['pagetitle'];
						unset($data['variants']); // remove variants+pagetitle from data array (was used only temporary)
						unset($data['pagetitle']);
						
						// prepare option group labels
						
						$configuration_labels = split("\+", $configuration_labels);
						foreach ($configuration_labels as $key => $c) {
							$configuration_labels[$key] = strtr($c, "_", " ");
						}
						
						// Output vtpconfigurator
/*
 						// Only product configurator
 						$el = '<div class="vtpkonfigurator" '
							.'data="'.htmlspecialchars(json_encode($data)).'" '
							.'variants="'.htmlspecialchars(json_encode($variants)).'" '
							.'pagetitle="'.htmlspecialchars($pagetitle).'" '
							.'configurationlabels="'.htmlspecialchars(json_encode($configuration_labels)).'"';
*/

						// Output vtpconfigurator
						$el = '<div class="vtpkonfigurator" data="'.htmlspecialchars(json_encode($data)).'"';

						if ($options || $rpm)
						{
							$el.= ' options="'.$options.($options !== '' ? ',' : '').'rpm:'.$rpm.'"';
						}
						$el.= '><img src="'.$files[0].'" /></div>';

						$content = preg_replace($wp_tag, $el, $content, 1);
				}
			}
			return $content;
		}

		/**
		 * Read all image files located under a given path.
		 */
		function get_images($path)
		{
			$imgs = array();
			$relpath = str_replace(ABSPATH.'/', '', $path);
			foreach(preg_grep('/\.(jpe?g|gif|png)$/i', glob($path.'*')) as $l)
				$imgs[] = get_option('siteurl').'/'.$relpath.'/'.basename($l);

			return $imgs;
		}

		/**
		 * Read configurations and the corresponding pieces.
		 */
		function get_data($dir)
		{
			if( ! is_dir($dir))
				return array();

			// Make sure we got a relative path
			$dir = str_replace(ABSPATH, '', $dir);

			$dh = opendir(ABSPATH.'/'.$dir);
			$data = array();
			
			// ========== 3D Produkt viewer ============
			while($file = readdir($dh))
			{
				if($file{0} == '.')
					continue;

				$file = $dir.'/'.$file;
				$view_path = $file.'/view/';

				// Entry is a dir, fetch its name, put it into our data array and fetch its images
				if(is_dir(ABSPATH.'/'.$file) && is_dir(ABSPATH.'/'.$view_path))
				{
					$images = $this->get_images(ABSPATH.'/'.$view_path);

					// No images found, skip configuration
					if(count($images) == 0)
						continue;

					$thumb = $this->get_thumb($file);

					if($thumb === FALSE)
						$thumb = $images[0];

					$data[basename($file)] = array(
						'imgs'  => $images,
						'thumb' => $thumb,
					);
				}
			}

			// limit configurations to 5 for free version
			if ( ! $this->licensed)
				return array_slice($data, 0, $this->allowed_variants_free, TRUE);
			else
				return $data;
			// =========/ 3D Produkt Viewer ============
			
/*
 // 3D Produkt configurator
			$variants = array();

			while($file = readdir($dh))
			{
				if($file{0} == '.')
					continue;

				$file = $dir.'/'.$file;
				$view_path = $file.'/view/';

				// Entry is a dir, fetch its name, put it into our data array and fetch its images
				if(is_dir(ABSPATH.'/'.$file) && is_dir(ABSPATH.'/'.$view_path))
				{
					$images = $this->get_images(ABSPATH.'/'.$view_path);

					// No images found, skip configuration
					if(count($images) == 0)
						continue;

					$thumb = $this->get_thumb($file);

					if($thumb === FALSE)
						$thumb = $images[0];

					// configurator variant name
					$full_variant_name = basename($file);
					// split to obtain variant layers: e.g. 2door+drawers+825+olive
					$single_variant_arr = split("\+", $full_variant_name);

					foreach ($single_variant_arr AS $layer => $variant_opt_name)
					{
						// add spaces -> this is done by client side JS
						//$variant_opt_name = strtr($variant_opt_name, "_", " ");

						$variants[$layer][] = $variant_opt_name;
					}
					// remove duplicates from layers
					foreach ($variants AS $layer => $var_opts)
					{
						$variants[$layer] = array_unique($variants[$layer]);
						sort($variants[$layer]); // sort options alphabetically
					}

					// Put image data into array -> for JS
					$data[$full_variant_name] = array(
						'imgs'  => $images,
						'thumb' => $thumb,
						'name' => $full_variant_name
					);
				}
			} // end while

			// reset keys
			foreach ($variants AS $num => $var_opts_arr)
			{
				$variants[$num] = array_values($var_opts_arr);
			}
			// add variant information and title
			$data['variants'] = array_values($variants); // array_values -> reset keys
			$data['pagetitle'] = get_the_title();
*/
		}

		/**
		 * Determine thumbnail picture filename located under a given path.
		 */
		function get_thumb($path)
		{
			foreach(array('jpg', 'png', 'gif', 'jpeg') AS $ext)
			{
				if(file_exists($path.'/thumb.'.$ext))
					return get_option('siteurl').'/'.$path.'/thumb.'.$ext;
			}

			return FALSE;
		}

		/**
		 * Add required JS scripts.
		 */
		function add_js()
		{
			wp_enqueue_script('mootools', $this->plugin_url.'/js/mootools.js');
			wp_enqueue_script('wp_vtpkonfigurator_pviewer', $this->plugin_url.'/js/pviewer'.($this->debug ? '' : '.min').'.js', array('mootools'));
			wp_enqueue_script('wp_vtpkonfigurator', $this->plugin_url.'/js/vtpkonfigurator'.($this->debug ? '' : '.min').'.js', array('mootools'));
		}

		/**
		 * Add required CSS.
		 */
		function add_css()
		{
			wp_enqueue_style('wp_vtpkonfigurator', $this->plugin_url.'/css/vtpkonfigurator.css');
			wp_enqueue_style('wp_vtpkonfigurator_wpfix', $this->plugin_url.'/css/vtpkonfigurator_wpfix.css');
		}
		
		/**
		 * Activates a session that is needed for Paypal payment.
		 */
		function init_sessions() {
 			if (!session_id()) {
        		session_start();
    		}
		}

		/**
		 * Adds the link to the admin menu (inside the backend).
		 */
		function admin_menu_link()
		{
			//add_options_page('WP-VTP Viewer', 'WP-VTP Viewer', 10, basename(__FILE__), array(&$this, 'admin_options_page'));
			//add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2);
			$icon = $this->plugin_url.'/img/visualtektur_16.png';
			add_menu_page('3D Produkt Viewer', '3D Produkt Viewer', 10, basename(__FILE__), array(&$this, 'admin_page'), $icon);
			add_filter('contextual_help', array(&$this, 'admin_help'), 10, 3);
		}
		
		/**
		 * Handles the AJAX call register: verifies the license data
		 * and creates the corresponding license data files.
		 * Is activated by add_action('wp_ajax_register', array(&$this, "register_ajax_callback"));
		 */
		function register_ajax_callback() {
			
			$success = FALSE;
			if ($_GET['action'] == 'register') {
				$user_id = $_POST['user_id'];
				$serial = $_POST['serial'];
				if ((bool) $this->licensefactory->validate_licensekey($user_id, $serial))
				{
					// license data is correct, save it to file
					$fh = fopen($this->plugin_path.'/'.$this->licensekeyfile, 'w');
					fwrite($fh, $user_id."\n");
					fwrite($fh, $serial);
					fclose($fh);
					$success = TRUE;
				}
			}
			die(json_encode(array('success' => $success))); // this is required to return a proper result
		}

		// plugins page - plugin actions
		function filter_plugin_actions($links, $file)
		{
			$settings_link = '<a href="options-general.php?page='.basename(__FILE__) . '">' . __('Settings') . '</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		// plugin options page
		function admin_options_page()
		{
			//require_once $this->thispluginpath.'admin_settings.php';
		}


		// plugin admin page
		function admin_page()
		{
			$main_link = admin_url('admin.php?page='.basename(__FILE__));
			$main_link_ajax = admin_url('admin-ajax.php?page='.basename(__FILE__));

			if ($_GET['action'] == 'delete')
			{
				$this->db->query("
					DELETE FROM ".$this->table_name." WHERE v_id = ".$this->db->escape($_GET['vid'])."
					");

				$path = $this->plugin_path.'/data/'.$_GET['vid'];
				if (is_dir($path))
				{
					rec_rmdir($path);
				}
			}

			if ($_POST['f_ok'])
			{
				if (!$_POST['f_id'])
				{
					$this->db->query("
						INSERT INTO ".$this->table_name."
						SET v_name = '".$this->db->escape($_POST['f_name'])."',
						v_options = '".$this->db->escape(@implode(',', $_POST['f_options']))."',
						v_rpm = '".$this->db->escape($_POST['f_rpm'])."',
						v_configurationlabels = '".$this->db->escape($_POST['f_configurationlabels'])."' 
						");

					$id = $this->db->insert_id;

					die('<script type="text/javascript">location.href="'.$main_link.'&action=edit&vid='.$id.'";</script>');
				}
				else
				{
					$this->db->query("
						UPDATE ".$this->table_name."
						SET v_name = '".$this->db->escape($_POST['f_name'])."',
						v_options = '".$this->db->escape(@implode(',', $_POST['f_options']))."',
						v_rpm = '".$this->db->escape($_POST['f_rpm'])."',
						v_configurationlabels = '".$this->db->escape($_POST['f_configurationlabels'])."'
						WHERE v_id = ".$this->db->escape($_POST['f_id'])."
						");
					$id = $_POST['f_id'];
				}

				// prepare path
				$path = $this->plugin_path.'/data/'.$id;
				$files = $_FILES['f_files'];

				if ($files['size'][0] > 0)
				{
					// remove if exists
					if (is_dir($path))
					{
						// rec_rmdir($path);
					}
					else
						mkdir($path);
				}

				// one zip file
				if ((count($files['name']) == 1)&&($files['size'][0] > 0)&&(stripos($files['name'][0], '.zip') !== false))
				{
					$zip = new ZipArchive;
					if ($zip->open($files['tmp_name'][0]))
					{
						$zip->extractTo($path.'/');
						$zip->close();

						// Remove hidden dirs / mac things
						$dh = opendir($path.'/');

						if($dh)
						{
							while($file = readdir($dh))
							{
								if(is_dir($path.'/'.$file) && $file != '..' && $file != '.' && ($file{0} == '.' || $file == '__MACOSX'))
									rec_rmdir($path.'/'.$file);
							}
							closedir($dh);
						}
					}
				}
				/*else
				 { // more images
				for($i=0,$n=count($files['name']);$i<$n;$i++)
				{
				if ($files['size'][$i] > 0)
				{
				move_uploaded_file($files['tmp_name'][$i], $path.'/'.$files['name'][$i]);
				}
				}
				}*/
			}
			
			
			if ($_GET['action'] == 'paypal')
			{
				require_once('paypal.php');
			}


			if (($_GET['action'] == 'create') || ($_GET['action'] == 'edit'))
			{
				if ($_GET['action'] == 'edit')
				{
					$subact = isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : 'edit';

					$item = $this->db->get_row("
						SELECT * FROM ".$this->table_name."
						WHERE v_id = '".$this->db->escape($_GET['vid'])."'
						", ARRAY_A);

					if($subact == 'edit')
					{
						$item['options'] = explode(',', $item['v_options']);

						$dir = $this->plugin_path.'/data/'.$_GET['vid'];
						$url = $this->plugin_url.'/data/'.$_GET['vid'];

						$files = $this->get_data($dir);

						if($files === array())
							$files = FALSE;
					}
					elseif($subact == 'create_configuration')
					{
						if($item !== NULL)
						{
							// check limits
							if ( ! $this->licensed)
							{
								$confs = $this->_get_configurations($_GET['vid']);
								if (count($confs) >= $this->allowed_variants_free) 
								{
									print('<h3 class="red">');
									printf( __('The FREE version allows only %d configuration variants. Please upgrade to the PRO version.' ), $this->allowed_variants_free);
									print('</h3>');
									include('admin_buy.php');
									die;
								}
							}
							
							// Validation
							if(preg_match('/^[a-z0-9\-_\+]+$/', $_POST['confname']))
							{
								mkdir($this->plugin_path.'/data/'.$_GET['vid'].'/'.$_POST['confname'].'/view/zoom', 0777, true);
							}
						}
					}
					elseif($subact == 'delete_configuration')
					{
						rec_rmdir($this->plugin_path.'/data/'.$_GET['vid'].'/'.$_GET['confname']);
						echo '<script type="text/javascript">location.href="'.$main_link.'&action=edit&vid='.$_GET['vid'].'";</script>';
					}
					elseif($subact == 'upload_zoom')
					{
						$errors = $this->_handle_partly_upload($this->plugin_path.'/data/'.$_POST['vid'].'/'.$_POST['confname'].'/view/zoom/');
					}
					elseif($subact == 'upload_normal')
					{
						$errors = $this->_handle_partly_upload($this->plugin_path.'/data/'.$_POST['vid'].'/'.$_POST['confname'].'/view/');
					}
					elseif($subact == 'upload_thumbnail')
					{
						$errors = $this->_handle_partly_upload($this->plugin_path.'/data/'.$_POST['vid'].'/'.$_POST['confname'], FALSE);
					}
					elseif($subact == 'delete_thumbnail')
					{
						$path = $this->plugin_path.'/data/'.$_GET['vid'].'/'.$_GET['confname'].'/';
						$thumb = $this->_get_thumbnail($path);

						if($thumb)
							unlink($path.$thumb);

						//echo '<script type="text/javascript">location.href="'.$main_link.'&action=edit&vid='.$_GET['vid'].'";</script>';
					}
					elseif($subact == 'delete_normal_picture' || $subact == 'delete_zoom_picture')
					{
						$path = $this->plugin_path.'/data/'.$_GET['vid'].'/'.$_GET['confname'].'/view/';

						if($subact == 'delete_zoom_picture')
							$path .= 'zoom/';

						if(isset($_GET['picture']))
						{
							if(file_exists($path.$_GET['picture']))
								unlink($path.$_GET['picture']);
						}
						elseif(isset($_GET['all']) && $_GET['all'] == '1')
						{
							$files = scandir($path);
							 
							foreach($files AS $file)
							{
								if( ! is_dir($path.$file))
									unlink($path.$file);
							}
						}
						 
						//echo '<script type="text/javascript">location.href="'.$main_link.'&action=edit&vid='.$_GET['vid'].'";</script>';
					}
				}
				else
				{
					$item = array();

					// Check limit of configurations (for free version)
					if ( ! $this->licensed)
					{
						$count = $this->db->get_var($this->db->prepare( "SELECT COUNT(v_id) FROM ".$this->table_name));
						if ($count >= 1)
						{
							print('<h3 class="red">');
							printf( __('The FREE version allows only %d product. Please upgrade to the PRO version.' ), $this->allowed_products_free);
							print('</h3>');
							include('admin_buy.php');
							die;
						}
					}
				}
			}
			else
			{
				$create_konfigurator_link = $main_link.'&action=form';

				/*
				 // get items
				$page = $_GET['pg'];
				if (!$page) $page = 1;

				// get viewers count
				$count = $this->db->get_var($this->db->prepare( "SELECT COUNT(v_id) FROM ".$this->table_name));

				$it = ceil($count/$this->items_per_page);
				if ($page > $it) $page = 1;
				LIMIT ".(($page-1)*$this->items_per_page).",".$this->items_per_page."
				*/
				$list = $this->db->get_results("
					SELECT * FROM ".$this->table_name."
					", ARRAY_A);

			}

			$configurations = $this->_get_configurations($_GET['vid']);
			
			// limit configurations to 5 for free version
			if ( ! $this->licensed)
			{
				$configurations = array_slice($configurations, 0, $this->allowed_variants_free, TRUE);
			}

			require_once $this->plugin_path.'/admin.php';

		}

		function _handle_partly_upload($target, $multiple=TRUE)
		{
			if( ! is_dir($target))
				mkdir($target, 0777, true);

			if(substr($target, -1) != '/' && substr($target, -1) != '\\')
				$target .= '/';

			$errors = array();

			if( ! isset($_FILES['files']) || ! is_array($_FILES['files']['error']))
			{
				$errors[] = 'No files were uploaded.';
				return $errors;
			}

			foreach($_FILES['files']['error'] AS $key => $err)
			{
				if($err ==  UPLOAD_ERR_OK)
				{
					$name = $_FILES['files']['name'][$key];

					if( ! $multiple)
					{
						if( ! preg_match('/(\.jpe?g|\.gif|\.png)$/i', $name, $matches))
						{
							$errors[] = 'Invalid file format for single upload: '.$name;
							continue;
						}
						else
						{
							// If single upload is required, we got a thumb.xxx
							// Remove a previous thumb in case we got a thumb with a different extension
							$contents = scandir($target);

							foreach($contents AS $content)
							{
								if(in_array(strtolower($content), array('thumb.jpg', 'thumb.png', 'thumb.gif', 'thumb.jpeg')))
									unlink($target.$content);
							}

							move_uploaded_file($_FILES['files']['tmp_name'][$key], $target.'thumb'.$matches[1]);
							return array();
						}
					}
					else
					{
						// Simple image file?
						if(preg_match('/\.jpe?g|\.gif|\.png$/i', $name))
						{
							move_uploaded_file($_FILES['files']['tmp_name'][$key], $target.$name);
						}
						elseif(preg_match('/\.zip$/i', $name))
						$errors = array_merge($errors, $this->_handle_zip_upload($_FILES['files']['tmp_name'][$key], $target));
						else
							$errors[] = 'Invalid file format: '.$name;
					}
				}
			}

			return $errors;
		}

		function _handle_zip_upload($file, $target)
		{
			$errors = array();

			// Create tmp file
			$tmpfile = tempnam('asdfg', ''); // if asdfg doesnt exist, it will fetch the tmpdir

			if($tmpfile === FALSE)
			{
				$errors[] = 'Could not create temporary directory to unzip the files.';
				return $errors;
			}

			// Delete tmp file and create a folder with its name
			unlink($tmpfile);

			mkdir($tmpfile, 0777, true);

			$zip = new ZipArchive;
			if ($zip->open($file))
			{
				// Unzip the zip to our tmp dir
				$zip->extractTo($tmpfile);
				$zip->close();
				 
				// Move all image files in this zip to the target destination
				$errros = $this->_move_unzipped_images($tmpfile, $target, $errors);
				 
				// Remove tmp folder
				rec_rmdir($tmpfile);
			}
			else
				$errors[] = 'Could not extract zip archive.';

			return $errors;
		}

		function _move_unzipped_images($tmpfile, $target, $errors=array())
		{
			if( ! is_dir($tmpfile))
				return $errors;

			if(substr($tmpfile, -1) != '/' && substr($tmpfile, -1) != '\\')
				$tmpfile .= '/';

			if(substr($target, -1) != '/' && substr($target, -1) != '\\')
				$target .= '/';

			$dh = opendir($tmpfile);

			if( ! $dh)
			{
				$errors[] = 'Could not open temp folder to read the unzipped images';
				return $errors;
			}

			while($file = readdir($dh))
			{
				if($file{0} == '.')
					continue;

				$path = $tmpfile.$file;

				if(is_dir($path))
				{
					$errors = $this->_move_unzipped_images($path, $target, $errors);
					continue;
				}

				if( ! preg_match('/\.jpe?g|\.gif|\.png$/i', $file))
				{
					$errors[] = 'Invalid file format in Zip archive: '.$file;
					continue;
				}

				if(file_exists($target.$file))
					unlink($target.$file);

				rename($path, $target.$file);
			}

			return $errors;
		}

		function _get_configurations($id)
		{
			$confs = array();

			$dir = $this->plugin_path.'/data/'.$_GET['vid'];

			if( ! file_exists($dir))
				return $confs;

			$dh = opendir($dir);
			while($file = readdir($dh))
			{
				$path = $dir.'/'.$file;

				if($file{0} == '.')
					continue;

				if(is_dir($path))
				{
					$conf = array(
						'name' => $file,
						'zoom_pictures' => $this->_get_image_names($path.'/view/zoom'),
						'normal_pictures' => $this->_get_image_names($path.'/view'),
						'thumbnail'	=> $this->_get_thumbnail($path),
					);

					$confs[] = $conf;
				}
			}

			closedir($dh);
			
			return $confs;
		}

		function _get_thumbnail($path)
		{
			if( ! is_dir($path))
				return null;

			$dh = opendir($path);

			while($file = readdir($dh))
			{
				if(preg_match('/^thumb\.jpe?g|\.gif|\.png$/i', $file))
				{
					closedir($dh);
					return $file;
				}
			}

			closedir($dh);
			return null;
		}

		function _get_image_names($path)
		{
			if( ! is_dir($path))
				return array();

			$files = scandir($path);

			$arr = array();

			foreach($files AS $file)
			{
				if(preg_match('/\.jpe?g|\.png|\.gif$/i', $file))
					$arr[] = $file;
			}

			return $arr;
		}

		function admin_help($contextual_help, $screen_id, $screen)
		{
			global $my_plugin_hook;
			if ($screen_id == 'toplevel_page_wp_vtpkonfigurator')
			{
				require_once $this->plugin_path.'/help.php';
			}
			return $contextual_help;
		}
		 
	}
}


if (class_exists('wp_vtpkonfigurator'))
{
	$wp_vtpkonfigurator_var = new wp_vtpkonfigurator();
}

/**
 * Source: http://aktuell.de.selfhtml.org/artikel/php/verzeichnisse/
 * The one implemented in the class above doesnt work for some reason in all cases
 */
function rec_rmdir ($path) {
	// schau' nach, ob das ueberhaupt ein Verzeichnis ist
	if (!is_dir ($path)) {
		return -1;
	}
	// oeffne das Verzeichnis
	$dir = @opendir ($path);

	// Fehler?
	if (!$dir) {
		return -2;
	}

	// gehe durch das Verzeichnis
	while (($entry = @readdir($dir)) !== false) {
		// wenn der Eintrag das aktuelle Verzeichnis oder das Elternverzeichnis
		// ist, ignoriere es
		if ($entry == '.' || $entry == '..') continue;
		// wenn der Eintrag ein Verzeichnis ist, dann
		if (is_dir ($path.'/'.$entry)) {
			// rufe mich selbst auf
			$res = rec_rmdir ($path.'/'.$entry);
			// wenn ein Fehler aufgetreten ist
			if ($res == -1) {
				// dies duerfte gar nicht passieren
				@closedir ($dir); // Verzeichnis schliessen
				return -2; // normalen Fehler melden
			} else if ($res == -2) {
				// Fehler?
				@closedir ($dir); // Verzeichnis schliessen
				return -2; // Fehler weitergeben
			} else if ($res == -3) {
				// nicht unterstuetzer Dateityp?
				@closedir ($dir); // Verzeichnis schliessen
				return -3; // Fehler weitergeben
			} else if ($res != 0) {
				// das duerfe auch nicht passieren...
				@closedir ($dir); // Verzeichnis schliessen
				return -2; // Fehler zurueck
			}
		} else if (is_file ($path.'/'.$entry) || is_link ($path.'/'.$entry)) {
			// ansonsten loesche diese Datei / diesen Link
			$res = @unlink ($path.'/'.$entry);
			// Fehler?
			if (!$res) {
				@closedir ($dir); // Verzeichnis schliessen
				return -2; // melde ihn
			}
		} else {
			// ein nicht unterstuetzer Dateityp
			@closedir ($dir); // Verzeichnis schliessen
			return -3; // tut mir schrecklich leid...
		}
	}

	// schliesse nun das Verzeichnis
	@closedir ($dir);

	// versuche nun, das Verzeichnis zu loeschen
	$res = @rmdir ($path);

	// gab's einen Fehler?
	if (!$res) {
		return -2; // melde ihn
	}

	// alles ok
	return 0;
}
?>