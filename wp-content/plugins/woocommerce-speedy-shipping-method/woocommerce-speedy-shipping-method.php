<?php
/*
Plugin Name: WooCommerce Speedy Shipping Method
Plugin URI: http://extensa.bg
Description: Speedy shipping method plugin
Version: 2.7.5
Author: EXTENSA WEB DEVELOPMENT
Author URI: http://extensa.bg
*/
 
/**
 * Check if WooCommerce is active
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! defined( 'SPEEDY_TEXT_DOMAIN' ) )
	define( 'SPEEDY_TEXT_DOMAIN', 'woocommerce-speedy-shipping-method' );

if ( ! defined( 'MIN_PHP_VERSION_REQUIRED' ) ) {
	define( 'MIN_PHP_VERSION_REQUIRED', '5.6' );
}

if ( ! defined( 'MIN_MySQL_VERSION_REQUIRED' ) ) {
	define( 'MIN_MySQL_VERSION_REQUIRED', '5.0' );
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function speedy_shipping_method_init() {
		if ( !class_exists( 'WC_Shipping_Method' ) ) return;

		/**
		 * Shipping class
		 */
		if ( class_exists( 'WC_Speedy_Shipping_Method' ) ) return;
		require_once(dirname(__FILE__) . '/class-speedy-shipping-method.php');

		// Setup translations
		if (get_locale() == 'bg_BG') {

		} elseif (get_locale() == 'el') {
			load_textdomain( 'woocommerce-speedy-shipping-method', plugin_dir_path(__FILE__) . 'languages/speedy-gr_GR.mo' );
		} else {
			load_textdomain( 'woocommerce-speedy-shipping-method', plugin_dir_path(__FILE__) . 'languages/speedy-en_US.mo' );
		}
	}

	add_action( 'init', 'speedy_shipping_method_init' );

	function add_speedy_shipping_method( $methods ) {
		$methods[] = 'WC_Speedy_Shipping_Method';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'add_speedy_shipping_method' );

	// Display product Fields
	add_action( 'woocommerce_product_options_shipping', 'speedy_quantity_dimentions_fields' );

	function get_mysql_version() {
		global $wpdb;
		$query = $wpdb->get_var( "SELECT VERSION() as mysql_version" );
		return preg_replace('/^([0-9\.]+).*/', '$1', $query);
	}

	function speedy_quantity_dimentions_fields() {
		global $wpdb;

		$table_name = $wpdb->prefix . "speedy_product_settings";

		$query = $wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'");

		if ($query) {
			$query = $wpdb->prepare( "SELECT * FROM ". $table_name . " WHERE product_id = '%d'", $_GET['post'] );
			$dimentions = $wpdb->get_row($query, ARRAY_A);
			if($dimentions) {
				$dimentions = unserialize($dimentions['quantity_dimentions']);
			} else {
				$dimentions = array();
			}

			wc_get_template( 'admin/html-speedy-product-shipping-fields.php',
				$dimentions,
				'',
				plugin_dir_path(__FILE__) . '/templates/'
			);
		}
	}

	add_action( 'woocommerce_order_details_after_customer_details', 'speedy_shipping_number' );

	function speedy_shipping_number($order) {
		global $wpdb;

		$table_name = $wpdb->prefix . "speedy_order";

		$query = $wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'");

		if ($query) {
			$query = $wpdb->prepare( "SELECT * FROM ". $table_name . " WHERE order_id = '%d'", $order->id );
			$speedy_order = $wpdb->get_row($query, ARRAY_A);

			if($speedy_order) {
				wc_get_template( 'front/html-speedy-shipping-number.php',
					$speedy_order,
					'',
					plugin_dir_path(__FILE__) . '/templates/'
				);
			}
		}
	}

	add_action( 'woocommerce_process_product_meta', 'speedy_quantity_dimentions_fields_save' );

	function speedy_quantity_dimentions_fields_save() {
		global $wpdb;

		$table_name = $wpdb->prefix . "speedy_product_settings";

		$query = $wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'");

		if ($query) {
			$wpdb->delete($table_name, array('product_id' => $_POST['ID']));

			if($_POST['speedy']['quantity_dimentions']['XS']
			|| $_POST['speedy']['quantity_dimentions']['S']
			|| $_POST['speedy']['quantity_dimentions']['M']
			|| $_POST['speedy']['quantity_dimentions']['L']
			|| $_POST['speedy']['quantity_dimentions']['XL']) {
				$wpdb->insert(
					$table_name,
					array(
						'product_id'                   => $_POST['ID'],
						'quantity_dimentions'          => serialize( $_POST['speedy']['quantity_dimentions'] ),
					),
					array(
						'%d',
						'%s',
					)
				);
			}
		}
	}

	/**
	* Add Settings link to the plugin entry in the plugins menu for WC below 2.1
	**/
	add_filter('plugin_action_links', 'speedy_shipping_method_action_links', 10, 2);

	function speedy_shipping_method_action_links($links, $file) {
		static $this_plugin;

		if (!$this_plugin) {
			$this_plugin = plugin_basename(__FILE__);
		}

		if (get_locale() == 'bg_BG') {
			$text_settings = 'Настройки';
		} else {
			$text_settings = 'Settings';
		}

		if ($file == $this_plugin) {
			if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) <= 0 ) {
				$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=woocommerce_settings&tab=shipping&section=WC_Speedy_Shipping_Method">' . __( $text_settings, SPEEDY_TEXT_DOMAIN ) . '</a>';
			} else {
				$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-settings&tab=shipping&section=wc_speedy_shipping_method">' . __( $text_settings, SPEEDY_TEXT_DOMAIN ) . '</a>';
			}
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	 /**
	 * Add action in admin panel
	 */
	$post_id = false;
	if (!empty($_POST['post_ID'])) {
		$post_id = $_POST['post_ID'];
	} else if (!empty($_GET['post'])) {
		$post_id = $_GET['post'];
	}

	if ( 'shop_order' == get_post_type($post_id) ) {
		$query = "SELECT woim.meta_value FROM `" .  $wpdb->prefix . "woocommerce_order_items` woi LEFT JOIN `" .  $wpdb->prefix . "woocommerce_order_itemmeta` woim ON woi.order_item_id = woim.order_item_id WHERE woi. order_id = '" . intval( $post_id ) . "' AND woi.order_item_type = 'shipping' AND woim.meta_key = 'method_id'";
		$shipping_method_id = $wpdb->get_var($query);

		if ( 'speedy_shipping_method' == $shipping_method_id ) { // add meta boxes if shipping method is speedy
			// Add meta box speedy form
			add_action('add_meta_boxes', function() {
				add_meta_box( 'woocommerce-speedy-data', __( 'Спиди', SPEEDY_TEXT_DOMAIN ), array( 'WC_Speedy_Shipping_Method', 'speedy_admin_order_meta' ), 'shop_order', 'normal', 'high' );
			});
		}
	}

	/**
	 * AJAX function for getting allowed methods
	 */
	add_action ( 'wp_ajax_get_allowed_methods', 'get_allowed_methods' );

	function get_allowed_methods() {
		$response = array();
		$services = array();

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		$speedy = SpeedyEpsLib::getInstance();

		$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';

		$services = $speedy->getServices($lang);

		if ($services) {
			$response['status'] = true;

			foreach ($services as $service_id => $service) {
				$response['services'][] = array(
					'service_id' => $service_id,
					'name'       => $service
				);
			}
		} else {
			$response['status'] = false;
			$response['error'] = __( 'Грешка при взимането на позволените методи!', SPEEDY_TEXT_DOMAIN );
		}

		wp_send_json( $response );
	}

	/**
	 * AJAX function for submitting form
	 */
	add_action ( 'wp_ajax_validate', array( 'WC_Speedy_Shipping_Method', 'validateSpeedyForm' ) );
	add_action ( 'wp_ajax_nopriv_validate', array( 'WC_Speedy_Shipping_Method', 'validateSpeedyForm' ) );

	register_activation_hook( __FILE__, 'activate' );
	function activate() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "speedy_address` (
			`customer_id` INT(11) NOT NULL,
			`postcode` VARCHAR(10) NOT NULL DEFAULT '',
			`city` VARCHAR(255) NOT NULL DEFAULT '',
			`city_id` INT(11) NOT NULL DEFAULT '0',
			`city_nomenclature` VARCHAR(15) NOT NULL DEFAULT '',
			`to_office` TINYINT(1) NOT NULL DEFAULT '0',
			`office_id` INT(11) NOT NULL DEFAULT '0',
			`quarter` VARCHAR(255) NOT NULL DEFAULT '',
			`quarter_id` INT(11) NOT NULL DEFAULT '0',
			`street` VARCHAR(255) NOT NULL DEFAULT '',
			`street_id` INT(11) NOT NULL DEFAULT '0',
			`street_no` VARCHAR(255) NOT NULL DEFAULT '',
			`block_no` VARCHAR(255) NOT NULL DEFAULT '',
			`entrance_no` VARCHAR(255) NOT NULL DEFAULT '',
			`floor_no` VARCHAR(255) NOT NULL DEFAULT '',
			`apartment_no` VARCHAR(255) NOT NULL DEFAULT '',
			`note` VARCHAR(255) NOT NULL DEFAULT '',
			KEY `customer_id` (`customer_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "speedy_order` (
			`speedy_order_id` INT(11) NOT NULL AUTO_INCREMENT,
			`order_id` INT(11) NOT NULL DEFAULT '0',
			`bol_id` VARCHAR(15) NOT NULL,
			`data` TEXT NOT NULL,
			`date_created` DATETIME NOT NULL,
			`courier` TINYINT(1) NOT NULL DEFAULT '0',
			`is_final` TINYINT(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`speedy_order_id`),
			KEY `order_id` (`order_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";

		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'usermeta';

		$wpdb->query("DELETE FROM `" . $table_name . "` WHERE meta_key = 'manageedit-shop_ordercolumnshidden'");
		$results = $wpdb->get_results("SELECT * FROM `" . $table_name . "` WHERE meta_key = '" . $wpdb->prefix . "capabilities' AND meta_value LIKE '%administrator%'", ARRAY_A);

		foreach($results as $result) {
			$wpdb->insert(
				$table_name,
				array(
					'user_id'    => $result['user_id'],
					'meta_key'   => 'manageedit-shop_ordercolumnshidden',
					'meta_value' => 'a:2:{i:0;s:15:"billing_address";i:1;s:10:"wc_actions";}',
				),
				array(
					'%d',
					'%s',
					'%s',
				)
			);
		}

		speedy_update_plugin(true);
	}

	register_deactivation_hook( __FILE__, 'deactivate' );
	function deactivate() {
		global $wpdb;

		$options = get_option('woocommerce_speedy_shipping_method_settings');  
		// update it
		$options['enabled'] = 'no';
		// store updated data  
		update_option('woocommerce_speedy_shipping_method_settings', $options); 

		$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "speedy_address`;");
		$wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "speedy_order`;");
	}

	// Adding actions
	add_action( 'woocommerce_review_order_after_shipping', array( 'WC_Speedy_Shipping_Method', 'speedy_add_form' ) );

	add_action ( 'wp_ajax_speedy_submit_form', array( 'WC_Speedy_Shipping_Method', 'speedy_submit_form' ) );
	add_action ( 'wp_ajax_nopriv_speedy_submit_form', array( 'WC_Speedy_Shipping_Method', 'speedy_submit_form' ) );

	add_action ( 'wp_ajax_speedy_save_data_form', array( 'WC_Speedy_Shipping_Method', 'speedy_save_data_form' ) );
	add_action ( 'wp_ajax_nopriv_speedy_save_data_form', array( 'WC_Speedy_Shipping_Method', 'speedy_save_data_form' ) );

	add_action ( 'wp_ajax_set_speedy_method', array( 'WC_Speedy_Shipping_Method', 'set_speedy_method' ) );
	add_action ( 'wp_ajax_nopriv_set_speedy_method', array( 'WC_Speedy_Shipping_Method', 'set_speedy_method' ) );

	add_action ( 'wp_ajax_speedy_compare_address', array( 'WC_Speedy_Shipping_Method', 'speedy_compare_address' ) );
	add_action ( 'wp_ajax_nopriv_speedy_compare_address', array( 'WC_Speedy_Shipping_Method', 'speedy_compare_address' ) );

	add_action ( 'woocommerce_checkout_update_order_review', array( 'WC_Speedy_Shipping_Method', 'speedy_update_order_review' ), 10, 1 );

	// Update plugin
	add_action ( 'plugins_loaded', 'speedy_update_plugin' );

	/**
	 * Check for updates and change DB
	 */
	function speedy_update_plugin( $activate = false ) {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$plugin_data = get_plugin_data( __FILE__ );

		if (get_option('extensa_speedy_shipping_method_version') !== $plugin_data['Version'] || $activate) {
			$table_name = $wpdb->prefix . 'speedy_address';

			$query = $wpdb->get_row( "SHOW TABLES LIKE '" . $table_name . "'" );

			if (!is_null($query)) {
				$columns = array();
				$speedy_address_columns = $wpdb->get_results("SHOW COLUMNS FROM `" . $table_name . "`");

				foreach ($speedy_address_columns as $speedy_address_column) {
					$columns[$speedy_address_column->Field] = $speedy_address_column->Field;
				}

				if (!isset($columns['country'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `country` VARCHAR(255) NOT NULL DEFAULT '' AFTER `note`;");
				}

				if (!isset($columns['country_id'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `country_id` INT(11) NOT NULL DEFAULT '0' AFTER `country`;");
				}

				if (!isset($columns['country_nomenclature'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `country_nomenclature` VARCHAR(15) NOT NULL DEFAULT '' AFTER `country_id`;");
				}

				if (!isset($columns['state'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `state` VARCHAR(255) NOT NULL DEFAULT '' AFTER `country_nomenclature`;");
				}

				if (!isset($columns['state_id'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `state_id` VARCHAR(50) NOT NULL DEFAULT '' AFTER `state`;");
				}

				if (!isset($columns['required_state'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `required_state` TINYINT(1) NOT NULL DEFAULT '0' AFTER `state_id`;");
				}

				if (!isset($columns['required_postcode'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `required_postcode` TINYINT(1) NOT NULL DEFAULT '0' AFTER `required_state`;");
				}

				if (!isset($columns['address_1'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `address_1` VARCHAR(255) NOT NULL DEFAULT '' AFTER `required_postcode`;");
				}

				if (!isset($columns['address_2'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `address_2` VARCHAR(255) NOT NULL DEFAULT '' AFTER `address_1`;");
				}

				if (!isset($columns['abroad'])) {
					$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `abroad` TINYINT(1) NOT NULL DEFAULT '0' AFTER `address_2`;");
				}
			}

			$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "speedy_table_rate` (
				`service_id` INT(11) NOT NULL,
				`take_from_office` TINYINT(1) NOT NULL,
				`weight` DECIMAL(15,4) NOT NULL,
				`order_total` DECIMAL(15,4) NOT NULL,
				`price_without_vat` DECIMAL(15,4) NOT NULL,
				`fixed_time_delivery` TINYINT(1) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";

			dbDelta( $sql );

			$speedy_table_rate_name = $wpdb->prefix . 'speedy_table_rate';

			$speedy_table_rate_query = $wpdb->get_row( "SHOW TABLES LIKE '" . $speedy_table_rate_name . "'" );

			if (!is_null($speedy_table_rate_query)) {
				$table_rate_columns = array();
				$speedy_table_rate_columns = $wpdb->get_results("SHOW COLUMNS FROM `" . $speedy_table_rate_name . "`");

				foreach ($speedy_table_rate_columns as $speedy_table_rate_column) {
					$table_rate_columns[$speedy_table_rate_column->Field] = $speedy_table_rate_column->Field;
				}

				if (!isset($table_rate_columns['fixed_time_delivery'])) {
					$wpdb->query("ALTER TABLE `" . $speedy_table_rate_name . "` ADD `fixed_time_delivery` TINYINT(1) NOT NULL;");
				}
			}

			$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "speedy_weight_dimensions` (
			`speedy_weight_dimension_id` INT(11) NOT NULL AUTO_INCREMENT,
			`WEIGHT` VARCHAR(255) NOT NULL DEFAULT '',
			`XS` VARCHAR(255) NOT NULL DEFAULT '',
			`S` VARCHAR(255) NOT NULL DEFAULT '',
			`M` VARCHAR(255) NOT NULL DEFAULT '',
			`L` VARCHAR(255) NOT NULL DEFAULT '',
			`XL` VARCHAR(255) NOT NULL DEFAULT '',
			PRIMARY KEY (`speedy_weight_dimension_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";

			dbDelta( $sql );

			$speedy_weight_dimensions_name = $wpdb->prefix . 'speedy_weight_dimensions';

			$speedy_weight_dimensions_query = $wpdb->get_row( "SHOW TABLES LIKE '" . $speedy_weight_dimensions_name . "'" );

			if (!is_null($speedy_weight_dimensions_query)) {
				$speedy_dimensions_columns = array();
				$speedy_weight_dimensions_columns = $wpdb->get_results("SHOW COLUMNS FROM `" . $speedy_weight_dimensions_name . "`");

				foreach ($speedy_weight_dimensions_columns as $speedy_weight_dimensions_column) {
					$speedy_dimensions_columns[$speedy_weight_dimensions_column->Field] = $speedy_weight_dimensions_column->Field;
				}

				if (!isset($speedy_dimensions_columns['XL'])) {
					$wpdb->query("ALTER TABLE `" . $speedy_weight_dimensions_name . "` ADD `XL` VARCHAR(255) NOT NULL DEFAULT '';");
				}
			}

			$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "speedy_product_settings` (
			`speedy_quantity_dimension_id` INT(11) NOT NULL AUTO_INCREMENT,
			`product_id` int(11) NOT NULL DEFAULT '0',
			`quantity_dimentions` varchar(255) NOT NULL DEFAULT '',
			PRIMARY KEY (`speedy_quantity_dimension_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";

			dbDelta( $sql );

			update_option('extensa_speedy_shipping_method_version', $plugin_data['Version']);
		}
	}

	/**
	 * Including js files for autocomplete
	 */
	add_action ( 'wp_enqueue_scripts', 'add_autocomplete' );
	function add_autocomplete() {
		if ( ! is_admin() ) {
			wp_enqueue_script( 'jquery' );
		}
		global $wp_scripts;

		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.css', array(), $jquery_version );
	}

	// Add custom css
	add_action ( 'wp_enqueue_scripts', 'add_custom_css' );
	function add_custom_css() {
		wp_enqueue_style( 'speedyStyle', plugins_url('styles/style.css', __FILE__) );
	}

	/**
	 * Get Cities from speedy API
	 */
	function get_cities() {
		if (isset( $_GET['term'] )) {
			$name = $_GET['term'];
		} else {
			$name = '';
		}

		if (isset( $_GET['country_id'] )) {
			$country_id = $_GET['country_id'];
		} else {
			$country_id = '';
		}

		if (isset( $_GET['abroad'] ) && $_GET['abroad']) {
			$lang = 'en';
		} else {
			$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';
		}

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		$speedy = SpeedyEpsLib::getInstance();

		$data = $speedy->getCities( $name, null, $country_id, $lang );

		if ($speedy->getError()) {
			$data = array('error' => $speedy->getError());
		}

		$response = json_encode( $data );
		echo $response;
		exit();
	}
	add_action( 'wp_ajax_get_cities', 'get_cities' );
	add_action( 'wp_ajax_nopriv_get_cities', 'get_cities' );

	/**
	 * Get Offices from speedy API
	 */
	function get_offices() {
		if (isset( $_GET['term'] )) {
			$name = $_GET['term'];
		} else {
			$name = '';
		}

		if (isset( $_GET['city_id'] )) {
			$city_id = $_GET['city_id'];
		} else {
			$city_id = '';
		}

		if (isset( $_GET['country_id'] )) {
			$country_id = $_GET['country_id'];
		} else {
			$country_id = '';
		}

		if (isset( $_GET['abroad'] ) && $_GET['abroad']) {
			$lang = 'en';
		} else {
			$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';
		}

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		$speedy = SpeedyEpsLib::getInstance();

		if ($city_id && $country_id) {
			$data = $speedy->getOffices( $name, $city_id, $lang, $country_id);

			if ($speedy->getError()) {
				$data = array('error' => $speedy->getError());
			}
		} else {
			$data = array('error' => __( 'Моля, въведете населено място!', SPEEDY_TEXT_DOMAIN ));
		}

		$response = json_encode( $data );
		echo $response;
		exit();
	}
	add_action( 'wp_ajax_get_offices', 'get_offices' );
	add_action( 'wp_ajax_nopriv_get_offices', 'get_offices' );

	/**
	 * Get Quarters from speedy API
	 */
	function get_quarters() {
		if (isset( $_GET['term'] )) {
			$name = $_GET['term'];
		} else {
			$name = '';
		}

		if (isset( $_GET['city_id'] )) {
			$city_id = $_GET['city_id'];
		} else {
			$city_id = '';
		}

		if (isset( $_GET['abroad'] ) && $_GET['abroad']) {
			$lang = 'en';
		} else {
			$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';
		}

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		$speedy = SpeedyEpsLib::getInstance();

		if ($city_id) {
			$data = $speedy->getQuarters( $name, $city_id, $lang );

			if ($speedy->getError()) {
				$data = array('error' => $speedy->getError());
			}
		} else {
			$data = array('error' => __( 'Моля, въведете населено място!', SPEEDY_TEXT_DOMAIN ));
		}

		$response = json_encode( $data );
		echo $response;
		exit();
	}
	add_action( 'wp_ajax_get_quarters', 'get_quarters' );
	add_action( 'wp_ajax_nopriv_get_quarters', 'get_quarters' );

	/**
	 * Get Streets from speedy API
	 */
	function get_streets() {
		if (isset( $_GET['term'] )) {
			$name = $_GET['term'];
		} else {
			$name = '';
		}

		if (isset( $_GET['city_id'] )) {
			$city_id = $_GET['city_id'];
		} else {
			$city_id = '';
		}

		if (isset( $_GET['abroad'] ) && $_GET['abroad']) {
			$lang = 'en';
		} else {
			$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';
		}

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		$speedy = SpeedyEpsLib::getInstance();

		if ($city_id) {
			$data = $speedy->getStreets( $name, $city_id, $lang );

			if ($speedy->getError()) {
				$data = array('error' => $speedy->getError());
			}
		} else {
			$data = array('error' => __( 'Моля, въведете населено място!', SPEEDY_TEXT_DOMAIN ));
		}

		$response = json_encode( $data );
		echo $response;
		exit();
	}
	add_action( 'wp_ajax_get_streets', 'get_streets' );
	add_action( 'wp_ajax_nopriv_get_streets', 'get_streets' );

	/**
	 * Get Blocks from speedy API
	 */
	function get_blocks() {
		if (isset( $_GET['term'] )) {
			$name = $_GET['term'];
		} else {
			$name = '';
		}

		if (isset( $_GET['city_id'] )) {
			$city_id = $_GET['city_id'];
		} else {
			$city_id = '';
		}

		if (isset( $_GET['abroad'] ) && $_GET['abroad']) {
			$lang = 'en';
		} else {
			$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';
		}

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		$speedy = SpeedyEpsLib::getInstance();

		if ($city_id) {
			$data = $speedy->getBlocks( $name, $city_id, $lang );

			if ($speedy->getError()) {
				$data = array('error' => $speedy->getError());
			}
		} else {
			$data = array('error' => __( 'Моля, въведете населено място!', SPEEDY_TEXT_DOMAIN ));
		}

		$response = json_encode( $data );
		echo $response;
		exit();
	}
	add_action( 'wp_ajax_get_blocks', 'get_blocks' );
	add_action( 'wp_ajax_nopriv_get_blocks', 'get_blocks' );

	/**
	 * Get Countries from speedy API
	 */
	function get_countries() {
		if (isset( $_GET['term'] )) {
			$name = $_GET['term'];
		} else {
			$name = '';
		}

		if (isset( $_GET['abroad'] ) && $_GET['abroad']) {
			$lang = 'en';
		} else {
			$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';
		}

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		$speedy = SpeedyEpsLib::getInstance();

		$data = $speedy->getCountries( $name, $lang );

		if ($speedy->getError()) {
			$data = array('error' => $speedy->getError());
		}

		$response = json_encode( $data );
		echo $response;
		exit();
	}
	add_action( 'wp_ajax_get_countries', 'get_countries' );
	add_action( 'wp_ajax_nopriv_get_countries', 'get_countries' );

	/**
	 * Get States from speedy API
	 */
	function get_states() {
		if (isset( $_GET['term'] )) {
			$name = $_GET['term'];
		} else {
			$name = '';
		}

		if (isset( $_GET['country_id'] )) {
			$country_id = $_GET['country_id'];
		} else {
			$country_id = '';
		}

		if (isset( $_GET['abroad'] ) && $_GET['abroad']) {
			$lang = 'en';
		} else {
			$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';
		}

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		$speedy = SpeedyEpsLib::getInstance();

		if ($country_id) {
			$data = $speedy->getStates( $country_id, $name, $lang );

			if ($speedy->getError()) {
				$data = array('error' => $speedy->getError());
			}
		} else {
			$data = array('error' => __( 'Моля, въведете държава!', SPEEDY_TEXT_DOMAIN ));
		}

		$response = json_encode( $data );
		echo $response;
		exit();
	}
	add_action( 'wp_ajax_get_states', 'get_states' );
	add_action( 'wp_ajax_nopriv_get_states', 'get_states' );

	// Admin menu
	add_action( 'admin_menu', array( 'WC_Speedy_Shipping_Method', 'speedy_orders_menu' ), 20 );

	 /**
	 * Admin ajax hooks
	 */
	add_action( 'wp_ajax_speedy_validate_bill_of_lading', array( 'WC_Speedy_Shipping_Method', 'validate_bill_of_lading' ) );

	add_action( 'wp_ajax_speedy_generate_loading', array( 'WC_Speedy_Shipping_Method', 'generate_loading' ) );

	add_action( 'wp_ajax_speedy_cancel_loading', array( 'WC_Speedy_Shipping_Method', 'cancel_loading' ) );

	add_action( 'wp_ajax_speedy_calculate_price', array( 'WC_Speedy_Shipping_Method', 'speedy_calculate_price' ) );

	 /**
	 * After save order items
	 */
	add_action( 'wp_ajax_speedy_after_save_order_items', array( 'WC_Speedy_Shipping_Method', 'after_save_order_items' ) );

	 /**
	 * Function speedy_before_checkout_process()
	 */
	add_action( 'woocommerce_before_checkout_process', array( 'WC_Speedy_Shipping_Method', 'speedy_before_checkout_process' ), 10, 1 );

	 /**
	 * Function speedy_checkout_order_processed()
	 */
	add_action( 'woocommerce_checkout_order_processed', array( 'WC_Speedy_Shipping_Method', 'speedy_checkout_order_processed' ), 10, 2 );

	add_filter( 'woocommerce_shipping_packages', 'modify_package_rates', 10, 2 );
	function modify_package_rates($packages) {
		$rates = array();

		foreach ($packages as $key => $package) {
			if (isset($package['rates'])) {
				$rates = $package['rates'];
				break;
			}
		}

		if (empty($rates['speedy_shipping_method'])) {
			return $packages;
		}

		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method_rates = $rates['speedy_shipping_method'];

		$rate = $speedy_shipping_method->calculate_shipping();

		$totals = WC()->cart->get_totals();

		if (empty($speedy_shipping_method->free_method_international)) {
			$speedy_shipping_method->free_method_international = array();
		}

		$free_shipping_metods = array_merge(array($speedy_shipping_method->free_method_city, $speedy_shipping_method->free_method_intercity), $speedy_shipping_method->free_method_international);

		$is_free = $speedy_shipping_method->pricing == 'free' && $totals['cart_contents_total'] >= $speedy_shipping_method->free_shipping_total && isset(WC()->session->speedy['shipping_method_id']) && in_array(WC()->session->speedy['shipping_method_id'], $free_shipping_metods);

		if ($speedy_shipping_method->pricing == 'fixed' || $is_free) {
			if ($is_free) {
				$rate['cost'] = 0;
			}

			$speedy_shipping_method_rates->set_cost($rate['cost']);
			return $packages;
		}

		$pricing_is_different = !isset(WC()->session->speedy['pricing']) || $speedy_shipping_method->pricing != WC()->session->speedy['pricing'];
		$price_is_different = !isset(WC()->session->speedy['shipping_method_cost']) || !isset(WC()->session->speedy['total']) || $totals['cart_contents_total'] != WC()->session->speedy['total'];
		$weight_is_different = !isset(WC()->session->speedy['weight_cart']) || wc_get_weight(WC()->cart->get_cart_contents_weight(), 'kg') != WC()->session->speedy['weight_cart'];

		if ($speedy_shipping_method->pricing == 'calculator_fixed') {
			$price_is_different = !isset(WC()->session->speedy['shipping_method_cost']) || !isset(WC()->session->speedy['total']) || $totals['cart_contents_total'] != (WC()->session->speedy['total'] - $speedy_shipping_method->fixed_price);
		}

		if ($price_is_different || $weight_is_different || $pricing_is_different) {
			$rate['cost'] = 0;
		}

		$speedy_shipping_method_rates->set_cost($rate['cost']);
		return $packages;
	}

	add_filter( 'woocommerce_cart_shipping_method_full_label', 'remove_speedy_shipping_free_label', 10, 2 );
	function remove_speedy_shipping_free_label($full_label, $method){
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();

		if ( $speedy_shipping_method->id == $method->id ) {
			$full_label = str_replace('(Free)','',$full_label);

			$totals = WC()->cart->get_totals();

			if (empty($speedy_shipping_method->free_method_international)) {
				$speedy_shipping_method->free_method_international = array();
			}

			$free_shipping_metods = array_merge(array($speedy_shipping_method->free_method_city, $speedy_shipping_method->free_method_intercity), $speedy_shipping_method->free_method_international);

			$is_free = $speedy_shipping_method->pricing == 'free' && $totals['cart_contents_total'] >= $speedy_shipping_method->free_shipping_total && isset(WC()->session->speedy['shipping_method_id']) && in_array(WC()->session->speedy['shipping_method_id'], $free_shipping_metods);

			if ($speedy_shipping_method->pricing == 'fixed' || $is_free) {
				return $full_label;
			}

			$pricing_is_different = !isset(WC()->session->speedy['pricing']) || $speedy_shipping_method->pricing != WC()->session->speedy['pricing'];
			$price_is_different = !isset(WC()->session->speedy['shipping_method_cost']) || !isset(WC()->session->speedy['total']) || $totals['cart_contents_total'] != WC()->session->speedy['total'];
			$weight_is_different = !isset(WC()->session->speedy['weight_cart']) || wc_get_weight(WC()->cart->get_cart_contents_weight(), 'kg') != WC()->session->speedy['weight_cart'];

			if ($speedy_shipping_method->pricing == 'calculator_fixed') {
				$price_is_different = !isset(WC()->session->speedy['shipping_method_cost']) || !isset(WC()->session->speedy['total']) || $totals['cart_contents_total'] != (WC()->session->speedy['total'] - $speedy_shipping_method->fixed_price);
			}

			if ($price_is_different || $weight_is_different || $pricing_is_different) {
				$full_label .= '<span id="price-not-calculated">' . __( ' (цената за доставка не е калкулирана)', SPEEDY_TEXT_DOMAIN ) . '</span>';
			}
		}

		return $full_label;
	}

	add_filter( 'woocommerce_order_formatted_shipping_address', 'speedy_is_to_office', 10, 2 );
	function speedy_is_to_office( $address, $order ){
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$is_speedy_order = $order->has_shipping_method($speedy_shipping_method->id);

		if ($is_speedy_order) {
			$speedy_order = $speedy_shipping_method->_getOrderByOrderId($order->get_id());

			if ($speedy_order) {
				$speedy_order_data = maybe_unserialize( $speedy_order['data'] );
				$to_office = $speedy_order_data['to_office'];
			} else {
				$to_office = (int)$order->get_shipping_address_1();
			}

			if ($to_office) {
				$prefix = __( 'Доставка до офис', SPEEDY_TEXT_DOMAIN ) . ': ';
			} else {
				$prefix = __( 'Доставка до адрес', SPEEDY_TEXT_DOMAIN ) . ': ';
			}

			switch ($address) {
				case !empty($address['address_1']):
					$address['address_1'] = $prefix . $address['address_1'];
					break;
				case !empty($address['address_2']):
					$address['address_2'] = $prefix . $address['address_2'];
					break;
				case !empty($address['city']):
					$address['city'] = $prefix . $address['city'];
					break;
			}

		}

		return $address;
	}
}