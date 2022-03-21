<?php
class WC_Speedy_Shipping_Method extends WC_Shipping_Method {

	// Error array
	public $errors = array();

	public $speedy;

	public $version = '2.7.5';

	private $parcel_sizes = array(
		1 => 'XS',
		2 => 'S',
		3 => 'M',
		4 => 'L',
		5 => 'XL',
	);

	/**
	 * Constructor for speedy shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->id                                  = 'speedy_shipping_method'; // Id for speedy shipping method. Should be uunique.
		$this->method_title                        = __( 'Спиди', SPEEDY_TEXT_DOMAIN );  // Title shown in admin
		$this->title                               = $this->get_option('title');
		$this->method_description                  = __( 'Description of speedy shipping method', SPEEDY_TEXT_DOMAIN ); // Description shown in admin

		$this->default_weight                      = (float)str_replace( ',', '.', $this->get_option('default_weight') );
		$this->documents                           = (bool)$this->get_option('documents');
		$this->taking_date                         = $this->get_option('taking_date');
		$this->pricing                             = $this->get_option('pricing');
		$this->fixed_price                         = $this->get_option('fixed_price');
		$this->from_office                         = $this->get_option('from_office');
		$this->office_id                           = $this->get_option('office_id');
		$this->insurance                           = $this->get_option('insurance');
		$this->fragile                             = $this->get_option('fragile');
		$this->allowed_methods                     = $this->get_option('allowed_methods');
		$this->client_id                           = $this->get_option('client_id');
		$this->fixed_time                          = $this->get_option('fixed_time');
		$this->check_office_work_day               = (bool)$this->get_option('check_office_work_day', 1);
		$this->free_shipping_total                 = $this->get_option('free_shipping_total');
		$this->free_method_city                    = $this->get_option('free_method_city');
		$this->free_method_intercity               = $this->get_option('free_method_intercity');
		$this->free_method_international           = $this->get_option('free_method_international');
		$this->packing                             = $this->get_option('packing');
		$this->option_before_payment               = $this->get_option('option_before_payment');
		$this->return_payer_type                   = $this->get_option('return_payer_type');
		$this->return_package_city_service_id      = $this->get_option('return_package_city_service_id');
		$this->return_package_intercity_service_id = $this->get_option('return_package_intercity_service_id');
		$this->ignore_obp                          = $this->get_option('ignore_obp');
		$this->return_voucher                      = $this->get_option('return_voucher');
		$this->return_voucher_city_service_id      = $this->get_option('return_voucher_city_service_id');
		$this->return_voucher_intercity_service_id = $this->get_option('return_voucher_intercity_service_id');
		$this->return_voucher_payer_type           = $this->get_option('return_voucher_payer_type');
		$this->order_status_id                     = $this->get_option('order_status_id');
		$this->order_status_update                 = $this->get_option('order_status_update');
		$this->final_statuses                      = maybe_unserialize($this->get_option('final_statuses', array()));
		$this->speedy_statuses                     = array(
			-14 => __( 'Доставена', SPEEDY_TEXT_DOMAIN ),
			124 => __( 'Доставена обратно към подателя', SPEEDY_TEXT_DOMAIN ),
			125 => __( 'Унищожена', SPEEDY_TEXT_DOMAIN ),
			127 => __( 'Кражба', SPEEDY_TEXT_DOMAIN ),
			128 => __( 'Отменена', SPEEDY_TEXT_DOMAIN ),
			129 => __( 'Административно приключване', SPEEDY_TEXT_DOMAIN ),
		);
		$this->money_transfer                      = $this->get_option('money_transfer', 0);
		$this->currency                            = $this->get_option('currency');
		$this->currency_rate                       = $this->get_option('currency_rate', array());
		$this->min_package_dimention               = $this->get_option('min_package_dimention', 0);
		$this->convert_to_win_1251                 = (bool)$this->get_option('convert_to_win_1251', 0);
		$this->additional_copy_for_sender          = (bool)$this->get_option('additional_copy_for_sender', 0);
		$this->weight_dimensions                   = $this->getWeightDimensions();
		$this->invoice_courrier_sevice_as_text     = (bool)$this->get_option('invoice_courrier_sevice_as_text', 0);

		$this->enabled            = $this->get_option('enabled'); // This can be added as an setting but for this example its forced enabled

		require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
		// $this->speedy = SpeedyEpsLib::getInstance();

		$this->init();
	}

	/**
	 * Including js files
	 */
	public function add_scripts() {
		wp_enqueue_script ( 'speedy_javascript', plugin_dir_url(__FILE__) . 'js/javascript.js', array('jquery'), '1.0.0', true );
		wp_enqueue_script ( 'speedy_javascript', plugin_dir_url(__FILE__) . 'js/speedyAutocomplete.js', array('jquery'), '1.0.0', true );
		wp_localize_script ( 'speedy_javascript', 'php_vars', array(
			'error_get_allowed_methods' => __( 'За да вземете методите, моля попълнете Адрес на сървъра, Потребителско име и Парола!', SPEEDY_TEXT_DOMAIN ),
			'error_get_offices' => __( 'Моля попълнете Адрес на сървъра, Потребителско име и Парола, за да се генерират офисите!', SPEEDY_TEXT_DOMAIN ),
			'prefix' => 'woocommerce_' . $this->id,
		) );
	}

	/**
	 * Init speedy settings
	 *
	 * @access public
	 * @return void
	 */
	function init() {
		wp_register_style( 'speedyStyle', plugins_url('styles/style.css', __FILE__) );

		if (!empty($_GET['page']) && !empty($_GET['section']) && $_GET['page'] == 'wc-settings' && ($_GET['section'] == 'wc_speedy_shipping_method' || $_GET['section'] == 'speedy_shipping_method')) {
			$this->init_form_fields(); // This is part of the settings API. Override the method to add speedy own settings
			$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

			wp_enqueue_style( 'speedyStyle' );
		}

		$this->availability = $this->get_option( 'availability' );
		$this->countries 	= $this->get_option( 'countries' );

		// Actions
		add_action( 'admin_enqueue_scripts', array( $this,'add_scripts' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'speedy_process_admin_options' ) );
	}

	public function speedy_process_admin_options() {
		$this->addWeightDimensions($this->weight_dimensions);

		parent::process_admin_options();

		if ( $this->settings['money_transfer'] && !$this->speedy->isAvailableMoneyTransfer() ) {
			$this->settings['money_transfer'] = 0;

			update_option( 'woocommerce_speedy_shipping_method_settings', $this->settings );
		}
	}

	/*
	 WooCommerce Version 3.4.1 bug fix
	*/
	public function get_option($option, $default = '') {
		$result = parent::get_option($option, $default);
		global $woocommerce;

		if ($option == 'allowed_methods') {
			if (is_array($result)) {
				foreach ($result as $key => $value) {
					if (is_numeric($value)) {
						if (version_compare( $woocommerce->version, '3.4.2', "<=" )) {
							$result[$key] = $value + 0;
						} else {
							$result[$key] = (string)$value;
						}
					}
				}
			}
		}

		return $result;
	}

	static function speedy_orders_menu() {
		add_submenu_page( 'woocommerce', __( 'Спиди поръчки', SPEEDY_TEXT_DOMAIN ), __( 'Спиди поръчки', SPEEDY_TEXT_DOMAIN ) , 'manage_woocommerce', 'speedy-orders', array( 'WC_Speedy_Shipping_Method', 'speedy_orders_page' ) );
	}

	static function speedy_orders_page(){
		require_once('class-speedy-orders-table.php');
		$speedy_orders_table = new Speedy_Orders_Table();

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );

		$speedy_orders_table->prepare_items();

		echo '<div class="wrap"><h2>' . __( 'Спиди поръчки', SPEEDY_TEXT_DOMAIN ) . '</h2>'; 
		echo '<form id="events-filter" method="get">';
		echo '<input type="hidden" name="page" value="' . $page . '" />';
		$speedy_orders_table->search_box(__( 'Търсене', SPEEDY_TEXT_DOMAIN ), 'search_id');

		$speedy_orders_table->display();
		echo '</form>';
		echo '</div>'; 
	}

	/**
	 * Add row with speedy form in review-order.
	 */
	static function speedy_add_form() {
		global $woocommerce;
		$wc_speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$wc_speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		wp_enqueue_style( 'speedyStyle' );

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping = $chosen_methods[0];

		if (isset($chosen_methods['undefined'])) {
			unset($chosen_methods['undefined']);
			WC()->session->set( 'chosen_shipping_methods', $chosen_methods );
		}

		if ($chosen_shipping == $wc_speedy_shipping_method->id) {
			$data = array();

			if (WC()->customer->get_shipping_country() == 'BG') {
				$data['abroad'] = false;
			} else {
				$data['abroad'] = true;
			}

			$data['chosen_shipping'] = $chosen_shipping;

			if (is_user_logged_in()){
				$speedy_address = $wc_speedy_shipping_method->_getSpeedyAddress(get_current_user_id( ));
			} else {
				$speedy_address = array();
			}

			if ( WC()->session->get( 'speedy' ) ) {
				$speedy_data = WC()->session->get( 'speedy' );
			} else {
				$speedy_data = array();
			}

			$checkout_address = array();
			if (isset($_POST) && !empty($_POST)) {
				$checkout_address = array(
					'country'   => $_POST['s_country'],
					'address'   => $_POST['s_address'],
					'address_2' => $_POST['s_address_2'],
					'city'      => $_POST['s_city'],
					'state'     => $_POST['s_state'],
					'postcode'  => $_POST['s_postcode'],
				);

				if ( WC()->session->get( 'speedy_checkout_address' ) != md5( serialize( $checkout_address ) ) ) {
					$speedy_address = array();
					$speedy_data = array();
				}

				WC()->session->set( 'speedy_checkout_address', md5( serialize( $checkout_address ) ) );
			}


			if ( isset( $speedy_data['shipping_method_id'] ) ) {
				$results_speedy = $wc_speedy_shipping_method->getQuotePublic();

				if (isset($results_speedy['speedy_error'])) {
					$results['error']['warning'] = $results_speedy['speedy_error'];
				}

				if (isset($results_speedy['quote'])) {
					$data['speedy_methods'] = $results_speedy['quote'];
					$data['speedy_shipping_method_id'] = $speedy_data['shipping_method_id'];
				}
			} else {
				$data['speedy_methods'] = array();
				$data['speedy_shipping_method_id'] = '';
			}

			if ( empty( $data['speedy_methods'] ) ) {
				unset( $speedy_data['shipping_method_id'] );
				WC()->session->set( 'speedy', $speedy_data);
			}

			if (isset($speedy_data['to_office'])) {
				$data['to_office'] = $speedy_data['to_office'];
			} elseif ( isset( $speedy_address['to_office'] ) ) {
				$data['to_office'] = $speedy_address['to_office'];
			}

			if (isset($speedy_data['postcode'])) {
				$data['postcode'] = $speedy_data['postcode'];
			} elseif ( isset( $speedy_address['postcode'] ) ) {
				$data['postcode'] = $speedy_address['postcode'];
			} else {
				$data['postcode'] = '';
			}

			if (isset($speedy_data['city'])) {
				$data['city'] = $speedy_data['city'];
			} elseif ( isset( $speedy_address['city'] ) ) {
				$data['city'] = $speedy_address['city'];
			} else {
				$data['city'] = '';
			}

			if (isset($speedy_data['city_id'])) {
				$data['city_id'] = $speedy_data['city_id'];
			} elseif ( isset( $speedy_address['city_id'] ) ) {
				$data['city_id'] = $speedy_address['city_id'];
			} else {
				$data['city_id'] = 0;
			}

			if (isset($speedy_data['city_nomenclature'])) {
				$data['city_nomenclature'] = $speedy_data['city_nomenclature'];
			} elseif ( isset( $speedy_address['city_nomenclature'] ) ) {
				$data['city_nomenclature'] = $speedy_address['city_nomenclature'];
			} else {
				$data['city_nomenclature'] = '';
			}

			if (isset($speedy_data['quarter'])) {
				$data['quarter'] = $speedy_data['quarter'];
			} elseif ( isset( $speedy_address['quarter'] ) ) {
				$data['quarter'] = $speedy_address['quarter'];
			} else {
				$data['quarter'] = '';
			}

			if (isset($speedy_data['quarter_id'])) {
				$data['quarter_id'] = $speedy_data['quarter_id'];
			} elseif ( isset( $speedy_address['quarter_id'] ) ) {
				$data['quarter_id'] = $speedy_address['quarter_id'];
			} else {
				$data['quarter_id'] = 0;
			}

			if (isset($speedy_data['street'])) {
				$data['street'] = $speedy_data['street'];
			} elseif ( isset( $speedy_address['street'] ) ) {
				$data['street'] = $speedy_address['street'];
			} else {
				$data['street'] = '';
			}

			if (isset($speedy_data['street_id'])) {
				$data['street_id'] = $speedy_data['street_id'];
			} elseif ( isset( $speedy_address['street_id'] ) ) {
				$data['street_id'] = $speedy_address['street_id'];
			} else {
				$data['street_id'] = 0;
			}

			if (isset($speedy_data['street_no'])) {
				$data['street_no'] = $speedy_data['street_no'];
			} elseif ( isset( $speedy_address['street_no'] ) ) {
				$data['street_no'] = $speedy_address['street_no'];
			} else {
				$data['street_no'] = '';
			}

			if (isset($speedy_data['block_no'])) {
				$data['block_no'] = $speedy_data['block_no'];
			} elseif ( isset( $speedy_address['block_no'] ) ) {
				$data['block_no'] = $speedy_address['block_no'];
			} else {
				$data['block_no'] = '';
			}

			if (isset($speedy_data['entrance_no'])) {
				$data['entrance_no'] = $speedy_data['entrance_no'];
			} elseif ( isset( $speedy_address['entrance_no'] ) ) {
				$data['entrance_no'] = $speedy_address['entrance_no'];
			} else {
				$data['entrance_no'] = '';
			}

			if (isset($speedy_data['floor_no'])) {
				$data['floor_no'] = $speedy_data['floor_no'];
			} elseif ( isset( $speedy_address['floor_no'] ) ) {
				$data['floor_no'] = $speedy_address['floor_no'];
			} else {
				$data['floor_no'] = '';
			}

			if (isset($speedy_data['apartment_no'])) {
				$data['apartment_no'] = $speedy_data['apartment_no'];
			} elseif ( isset( $speedy_address['apartment_no'] ) ) {
				$data['apartment_no'] = $speedy_address['apartment_no'];
			} else {
				$data['apartment_no'] = '';
			}

			if (isset($speedy_data['office_id'])) {
				$data['office_id'] = $speedy_data['office_id'];
			} elseif ( isset( $speedy_address['office_id'] ) ) {
				$data['office_id'] = $speedy_address['office_id'];
			} else {
				$data['office_id'] = 0;
			}

			if (isset($speedy_data['is_apt'])) {
				$data['is_apt'] = $speedy_data['is_apt'];
			} elseif (isset($speedy_address['is_apt'])) {
				$data['is_apt'] = $speedy_address['is_apt'];
			}

			if (isset($speedy_data['to_office'])) {
				$data['to_office'] = $speedy_data['to_office'];
			} elseif (isset($speedy_address['to_office'])) {
				$data['to_office'] = $speedy_address['to_office'];
			}

			if (isset($speedy_data['note'])) {
				$data['note'] = $speedy_data['note'];
			} elseif (isset($speedy_address['note'])) {
				$data['note'] = $speedy_address['note'];
			} else {
				$data['note'] = '';
			}

			if (isset($speedy_data['country'])) {
				$data['country'] = $speedy_data['country'];
			} elseif (isset($speedy_address['country'])) {
				$data['country'] = $speedy_address['country'];
			} else {
				$data['country'] = '';
			}

			if (isset($speedy_data['country_id'])) {
				$data['country_id'] = $speedy_data['country_id'];
			} elseif (isset($speedy_address['country_id'])) {
				$data['country_id'] = $speedy_address['country_id'];
			} else {
				$data['country_id'] = '';
			}

			if (isset($speedy_data['country_nomenclature'])) {
				$data['country_nomenclature'] = $speedy_data['country_nomenclature'];
			} elseif (isset($speedy_address['country_nomenclature'])) {
				$data['country_nomenclature'] = $speedy_address['country_nomenclature'];
			} else {
				$data['country_nomenclature'] = '';
			}

			if (isset($speedy_data['country_address_nomenclature'])) {
				$data['country_address_nomenclature'] = $speedy_data['country_address_nomenclature'];
			} elseif (isset($speedy_address['country_address_nomenclature'])) {
				$data['country_address_nomenclature'] = $speedy_address['country_address_nomenclature'];
			} else {
				$data['country_address_nomenclature'] = '';
			}

			if (isset($speedy_data['required_state'])) {
				$data['required_state'] = $speedy_data['required_state'];
			} elseif (isset($speedy_address['required_state'])) {
				$data['required_state'] = $speedy_address['required_state'];
			} else {
				$data['required_state'] = '';
			}

			if (isset($speedy_data['required_postcode'])) {
				$data['required_postcode'] = $speedy_data['required_postcode'];
			} elseif (isset($speedy_address['required_postcode'])) {
				$data['required_postcode'] = $speedy_address['required_postcode'];
			} else {
				$data['required_postcode'] = '';
			}

			if (isset($speedy_data['active_currency_code'])) {
				$data['active_currency_code'] = $speedy_data['active_currency_code'];
			} else {
				$data['active_currency_code'] = get_option('woocommerce_currency');
			}

			if (isset($speedy_data['state'])) {
				$data['state'] = $speedy_data['state'];
			} elseif (isset($speedy_address['state'])) {
				$data['state'] = $speedy_address['state'];
			} else {
				$data['state'] = '';
			}

			if (isset($speedy_data['state_id'])) {
				$data['state_id'] = $speedy_data['state_id'];
			} elseif (isset($speedy_address['state_id'])) {
				$data['state_id'] = $speedy_address['state_id'];
			} else {
				$data['state_id'] = '';
			}

			if (isset($speedy_data['address_1'])) {
				$data['address_1'] = $speedy_data['address_1'];
			} elseif (isset($speedy_address['address_1'])) {
				$data['address_1'] = $speedy_address['address_1'];
			} elseif ( isset( $checkout_address['address'] ) ) {
				$data['address_1'] = $checkout_address['address'];
			} else {
				$data['address_1'] = '';
			}

			if (isset($speedy_data['address_2'])) {
				$data['address_2'] = $speedy_data['address_2'];
			} elseif (isset($speedy_address['address_2'])) {
				$data['address_2'] = $speedy_address['address_2'];
			} elseif ( isset( $checkout_address['address_2'] ) ) {
				$data['address_2'] = $checkout_address['address_2'];
			} else {
				$data['address_2'] = '';
			}

			if (!empty($speedy_data['fixed_time_cb'])) {
				$data['fixed_time_cb'] = $speedy_data['fixed_time_cb'];
			} else {
				$data['fixed_time_cb'] = false;
			}

			if (isset($speedy_data['fixed_time_hour'])) {
				$data['fixed_time_hour'] = $speedy_data['fixed_time_hour'];
			} else {
				$data['fixed_time_hour'] = '';
			}

			if (isset($speedy_data['fixed_time_min'])) {
				$data['fixed_time_min'] = $speedy_data['fixed_time_min'];
			} else {
				$data['fixed_time_min'] = '';
			}

			if ( WC()->session->get( 'speedy' ) || $speedy_address) {
				$data['speedy_precalculate'] = true;
			} else {
				$data['speedy_precalculate'] = false;
			}

			$data['fixed_time'] = $wc_speedy_shipping_method->fixed_time;
			$data['option_before_payment'] = $wc_speedy_shipping_method->option_before_payment;
			$data['ignore_obp'] = $wc_speedy_shipping_method->ignore_obp;

			$avalable_gateways = 0;
			$hasCod = false;
			$gateways = WC()->payment_gateways;

			if (!empty($gateways)) {
				foreach ($gateways->payment_gateways as $payment_gateway) {
					if ( 'yes' == $payment_gateway->enabled ) {
						if ( !empty( $payment_gateway->enable_for_methods ) ) {
							foreach ( $payment_gateway->enable_for_methods as $enabled_method ) {
								if ( $enabled_method == $wc_speedy_shipping_method->id ) {
									$avalable_gateways++;
									$hasCod = true;
								}
							}
						} else {
							$avalable_gateways++;
						}
					}
				}
			}

			if ($avalable_gateways > 1) {
				$data['cod_status'] = true;
			} else {
				$data['cod_status'] = false;
			}

			if (!$data['cod_status']) {
				$data['cod'] = $hasCod;
			} elseif (isset($speedy_data['cod'])) {
				$data['cod'] = $speedy_data['cod'];
			} else {
				$data['cod'] = 1;
			}

			$lang = ($data['abroad']) ? 'en' : ((get_locale() == 'bg_BG') ? 'bg' : 'en');

			$data['country_disabled'] = false;
			$data['state_disabled'] = false;

			$country_filter = array();

			if (!empty($woocommerce->customer->get_shipping_country())) {
				$country_filter['iso_code_2'] = $woocommerce->customer->get_shipping_country();
			} elseif (!empty($data['country_id'])) {
				$country_filter['country_id'] = $data['country_id'];
			} else {
				$country_filter['name'] = $data['country'];
			}

			$countryCache = wp_cache_get('speedy.countries.' . md5(json_encode($country_filter)));

			if ($countryCache) {
				$countries = $countryCache;
			} else {
				$countries = $wc_speedy_shipping_method->speedy->getCountries($country_filter, $lang);
				wp_cache_set('speedy.countries.' . md5(json_encode($country_filter)), $countries);
			}

			$countries = $wc_speedy_shipping_method->speedy->getCountries($country_filter, $lang);

			if (!$wc_speedy_shipping_method->speedy->getError()) {
				if (count($countries) == 1) {
					$country = $countries[0];

					$data['country'] = $country['name'];
					$data['country_id'] = $country['id'];
					$data['country_nomenclature'] = $country['nomenclature'];
					$data['country_address_nomenclature'] = $country['address_nomenclature'];
					$data['required_state'] = $country['required_state'];
					$data['required_postcode'] = $country['required_postcode'];
					$data['active_currency_code'] = $country['active_currency_code'];

					if (!$country['active_currency_code']) {
						$data['cod_status'] = false;
						$data['cod'] = 0;
					}

					if ($data['abroad']) {
						$stateCache = wp_cache_get('speedy.states.' . md5($country['id'] . $woocommerce->customer->get_shipping_state()));

						if ($stateCache) {
							$states = $stateCache;
						} else {
							$states = $wc_speedy_shipping_method->speedy->getStates($country['id'], $woocommerce->customer->get_shipping_state());
							wp_cache_set('speedy.states.' . md5($country['id'] . $woocommerce->customer->get_shipping_state()), $states);
						}

						if (!$wc_speedy_shipping_method->speedy->getError()) {
							if (count($states) == 1) {
								$state = $states[0];
								$data['state'] = $state['name'];
								$data['state_id'] = $state['id'];
							} else {
								foreach ($states as $state) {
									if ($woocommerce->customer->get_shipping_state() == $state['code']) {
										$data['state'] = $state['name'];
										$data['state_id'] = $state['id'];
									}
								}
								$data['state_disabled'] = false;
							}
						} else {
							$data['error_address'] = $wc_speedy_shipping_method->speedy->getError();
						}
					}
				}
			} else {
				$data['error_address'] = $wc_speedy_shipping_method->speedy->getError();
			}

			if ($data['cod']) {
				$gateways->payment_gateways()['cod']->set_current();
			}

			$data['offices'] = array();

			if (!$data['city_id']) {
				$cities = $wc_speedy_shipping_method->speedy->getCities( $woocommerce->customer->get_shipping_city(), $woocommerce->customer->get_shipping_postcode(), $data['country_id'], $lang );

				if (!$wc_speedy_shipping_method->speedy->getError()) {
					if (count($cities) == 1) {
						$data['postcode'] = $cities[0]['postcode'] ? $cities[0]['postcode'] : ( isset( $checkout_address['postcode'] ) ? $checkout_address['postcode'] : '' );
						$data['city'] = $cities[0]['value'];
						$data['city_id'] = $cities[0]['id'];
						$data['city_nomenclature'] = $cities[0]['nomenclature'];
					} elseif ($data['country_nomenclature'] != 'FULL') {
						if ( isset( $checkout_address['city'] ) && isset( $checkout_address['postcode'] ) ) {
							$data['city'] = $checkout_address['city'];
							$data['postcode'] = $checkout_address['postcode'];
						} elseif ( isset( $speedy_data['city'] ) && isset( $speedy_data['postcode'] ) ) {
							$data['city'] = $speedy_data['city'];
							$data['postcode'] = $speedy_data['postcode'];
						} elseif ( isset( $shipping_address['city'] ) && isset( $shipping_address['postcode'] ) ) {
							$data['city'] = $shipping_address['city'];
							$data['postcode'] = $shipping_address['postcode'];
						} else {
							$data['city'] = '';
							$data['postcode'] = '';
						}
					}
				} else {
					$data['error_address'] = $wc_speedy_shipping_method->speedy->getError();
				}
			}

			if ($data['city_id'] || $data['postcode']) {
				$data['speedy_precalculate'] = true;
			}


			if ($data['city_id'] && !empty($countries[0]['id'])) {
				$officeCache = wp_cache_get('speedy.offices.' . md5($data['city_id'] . $lang . $countries[0]['id']));

				if ($officeCache) {
					$data['offices'] = $officeCache;
				} else {
					$data['offices'] = $wc_speedy_shipping_method->speedy->getOffices( null, $data['city_id'], $lang, $countries[0]['id']);
					wp_cache_set('speedy.offices.' . md5($data['city_id'] . $lang . $countries[0]['id']), $data['offices']);
				}

				if ($data['offices'] && !isset($data['to_office'])) {
					$data['to_office'] = 1;

					if (!isset($data['is_apt'])) {
						foreach ($data['offices'] as $office) {
							if (!empty($office['is_apt'])) {
								$data['is_apt'] = 1;
							}
						}
					}
				}

				if ($wc_speedy_shipping_method->speedy->getError()) {
					$data['error_office'] = $wc_speedy_shipping_method->speedy->getError();
				}
			}

			$data['wc_speedy_shipping_method_id'] = $wc_speedy_shipping_method->id;

			wc_get_template( 'speedy-form.php',
				$data,
				'',
				plugin_dir_path(__FILE__) . '/templates/'
			);
		}
	}

	/**
	 * Generate speedy methods
	 */
	static function speedy_submit_form() {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$results = array();

		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty( $errors ) ) {
			if (isset( $_POST['data'] )) {
				parse_str($_POST['data'], $_POST);
			}

			if (!isset($_POST['postcode'])) {
				$_POST['postcode'] = '';
			}
			if (!isset($_POST['city'])) {
				$_POST['city'] = '';
			}
			if (!isset($_POST['city_id'])) {
				$_POST['city_id'] = 0;
			}
			if (!isset($_POST['city_nomenclature'])) {
				$_POST['city_nomenclature'] = '';
			}
			if (!isset($_POST['to_office'])) {
				$_POST['to_office'] = 0;
			}
			if (!isset($_POST['office_id'])) {
				$_POST['office_id'] = 0;
			}
			if (!isset($_POST['quarter'])) {
				$_POST['quarter'] = '';
			}
			if (!isset($_POST['quarter_id'])) {
				$_POST['quarter_id'] = 0;
			}
			if (!isset($_POST['street'])) {
				$_POST['street'] = '';
			}
			if (!isset($_POST['street_id'])) {
				$_POST['street_id'] = 0;
			}
			if (!isset($_POST['street_no'])) {
				$_POST['street_no'] = '';
			}
			if (!isset($_POST['block_no'])) {
				$_POST['block_no'] = '';
			}
			if (!isset($_POST['entrance_no'])) {
				$_POST['entrance_no'] = '';
			}
			if (!isset($_POST['floor_no'])) {
				$_POST['floor_no'] = '';
			}
			if (!isset($_POST['apartment_no'])) {
				$_POST['apartment_no'] = '';
			}
			if (!isset($_POST['note'])) {
				$_POST['note'] = '';
			}
			if (!isset($_POST['country'])) {
				$_POST['country'] = '';
			}
			if (!isset($_POST['country_id'])) {
				$_POST['country_id'] = 0;
			}
			if (!isset($_POST['country_nomenclature'])) {
				$_POST['country_nomenclature'] = '';
			}
			if (!isset($_POST['country_address_nomenclature'])) {
				$_POST['country_address_nomenclature'] = '';
			}
			if (!isset($_POST['state'])) {
				$_POST['state'] = '';
			}
			if (!isset($_POST['state_id'])) {
				$_POST['state_id'] = '';
			}
			if (!isset($_POST['required_state'])) {
				$_POST['required_state'] = 0;
			}
			if (!isset($_POST['required_postcode'])) {
				$_POST['required_postcode'] = 0;
			}
			if (!isset($_POST['address_1'])) {
				$_POST['address_1'] = '';
			}
			if (!isset($_POST['address_2'])) {
				$_POST['address_2'] = '';
			}
			if (!isset($_POST['abroad'])) {
				$_POST['abroad'] = 0;
			}

			if (!empty($_POST['speedy_payment_method'])) {
				WC()->session->set('chosen_payment_method', $_POST['speedy_payment_method']);
				$gateways = WC()->payment_gateways;
				$gateways->set_current_gateway($gateways->payment_gateways());
				$results['gateway'] = $_POST['speedy_payment_method'];
			} else {
				$results['gateway'] = WC()->session->chosen_payment_method;
			}

			if (is_user_logged_in()) {
				$speedy_shipping_method->_addSpeedyAddress( $_POST );
			}

			if ( isset( WC()->session->speedy['shipping_method_id'] ) ) {
				$results['shipping_method_id'] = WC()->session->speedy['shipping_method_id'];
			} else {
				$results['shipping_method_id'] = '';
			}

			if ( ! isset( $_POST['fixed_time_cb'] ) ) {
				$_POST['fixed_time_cb'] = null;
			}

			if ( WC()->session->speedy ) {
				WC()->session->set( 'speedy', array_merge(WC()->session->speedy, $_POST));
			} else {
				WC()->session->set( 'speedy', $_POST);
			}

			$results_speedy = $speedy_shipping_method->getQuotePublic();

			if (isset($results_speedy['speedy_error'])) {
				$results['error']['warning'] = $results_speedy['speedy_error'];
			}

			if (isset($results_speedy['quote'])) {
				$results['methods'] = $results_speedy['quote'];
			}

			if (isset($results['error'])) {
				$results['status'] = false;
			} else {
				$results['status'] = true;
			}
		} else {
			$results['status'] = false;
			$results['error'] = $errors;
		}

		wp_send_json( $results );
		exit();
	}

	static function speedy_save_data_form() {
		parse_str($_POST['data'], $_POST);

		if (isset($_POST['speedy_shipping_method_id'])) {
			$_POST['shipping_method_id'] = $_POST['speedy_shipping_method_id'];
		}

		WC()->session->set( 'speedy', array_merge(WC()->session->speedy, $_POST));
		exit();
	}

	/**
	 * Generate speedy methods
	 */
	static function set_speedy_method() {
		global $woocommerce;
		if ( ! defined('WOOCOMMERCE_CHECKOUT') ) {
		  define( 'WOOCOMMERCE_CHECKOUT', true );
		}
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$json = array();

		if (isset($_POST['method_id']) && isset($_POST['method_price'])) {
			$speedy_session = WC()->session->speedy;

			$speedy_session['shipping_method_cost'] = (float)$_POST['method_price'];
			$speedy_session['shipping_method_text'] = $_POST['method_price'];

			$speedy_session['shipping_method_id'] = (int)$_POST['method_id'];

			WC()->session->set( 'speedy', $speedy_session );

			WC_Cache_Helper::get_transient_version( 'shipping', true );

			$woocommerce->cart->calculate_totals();

			$json['new_shipping_value'] = $woocommerce->cart->shipping_total;
			$json['new_total'] = strip_tags( wc_price( $woocommerce->cart->total ) );
			$json['woocommerce_shipping_method_format'] = get_option( 'woocommerce_shipping_method_format' );

			$json['shipping_title'] = '';
			$json['price_text'] = '';

			if ( ! $speedy_shipping_method->invoice_courrier_sevice_as_text ) {
				$json['shipping_title'] = $speedy_shipping_method->title . ': ';
				$json['price_text'] = strip_tags( wc_price( $json['new_shipping_value'] ) );
			} else {
				$allowed_pricings = array(
					'calculator',
					'free',
					'calculator_fixed'
				);

				if ( isset( $speedy_session['cod'] ) && $speedy_session['cod'] && in_array( $speedy_shipping_method->pricing, $allowed_pricings )) {
					if ( $speedy_shipping_method->pricing == 'free' ) {
						$delta = 0.0001;
						if( abs( $speedy_session['shipping_method_cost'] - 0.0000 ) > $delta ) {
							$json['price_text'] = sprintf( __( '%s (%s дължими при доставка)', SPEEDY_TEXT_DOMAIN ), $speedy_shipping_method->title, strip_tags( wc_price( $speedy_session['shipping_method_cost'] ) ) );
						}
					} else {
						$json['price_text'] = sprintf( __( '%s (%s дължими при доставка)', SPEEDY_TEXT_DOMAIN ), $speedy_shipping_method->title, strip_tags( wc_price( $speedy_session['shipping_method_cost'] ) ) );
					}
				} else {
					$json['price_text'] = $speedy_shipping_method->title . ': ' . strip_tags( wc_price( $json['new_shipping_value'] ) );
				}
			}
		}

		echo json_encode($json);
		exit();

	}

	/**
	 * Comapre shipping address & speedy address
	 */
	static function speedy_compare_address() {
		$results = array();

		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			if (isset( $_POST['data'] )) {
				parse_str($_POST['data'], $post);
			}

			if ( $post['postcode'] != WC()->customer->get_shipping_postcode() ) {
				$results['error'] = true;
				$results['warning'] = __( 'Вашите данни за доставка са различни от данните за Спиди!', SPEEDY_TEXT_DOMAIN );
			} else {
				require_once(dirname(__FILE__) . '/speedy-eps-lib.php');
				$speedy = SpeedyEpsLib::getInstance();

				if ( $post['abroad'] || get_locale() != 'bg_BG' ) {
					$lang = 'en';
				} else {
					$lang = 'bg';
				}

				if ( !empty($post['country_id']) ) {
					$country_id = $post['country_id'];
				} else {
					$country_id = '100'; // country_id for Bulgaria
				}

				$cities = $speedy->getCities( WC()->customer->get_shipping_city(), WC()->customer->get_shipping_postcode(), $country_id, $lang );

				if ( ! $speedy->getError() ) {
					if ( empty( $cities ) ) {
						if ( $post['city'] != WC()->customer->get_shipping_city() ) {
							$results['error'] = true;
							$results['warning'] = __( 'Вашите данни за доставка са различни от данните за Спиди!', SPEEDY_TEXT_DOMAIN );
						}
					} elseif ( isset( $post['city_id'] ) ) {
						if ( $post['city_id'] != $cities[0]['id'] ) {
							$results['error'] = true;
							$results['warning'] = __( 'Вашите данни за доставка са различни от данните за Спиди!', SPEEDY_TEXT_DOMAIN );
						}
					}
				}
			}
		}

		wp_send_json( $results );
		exit();
	}

	/**
	 * Admin Options
	 *
	 * Setup the gateway settings screen.
	 * Override this in your gateway.
	 *
	 * @since 1.0.0
	 */
	public function admin_options() { ?>
		<h3><?php echo ( ! empty( $this->method_title ) ) ? $this->method_title : __( 'Settings', 'woocommerce' ) ; ?></h3>

		<?php echo ( ! empty( $this->method_description ) ) ? wpautop( $this->method_description ) : ''; ?>

		<table class="form-table" id="speedy-settings">
			<?php $this->generate_settings_html(); ?>
		</table><?php
	}

	/**
	* Generate system requirements html
	*/
	function generate_system_requirements_html( $key, $data ) {
		$php_version = preg_replace('/^([0-9\.]+).*/', '$1', phpversion());
		$mysql_version = get_mysql_version();

		$php_version_fulfilled = version_compare($php_version, MIN_PHP_VERSION_REQUIRED, '>=');
		$mysql_version_fulfilled = version_compare($mysql_version, MIN_MySQL_VERSION_REQUIRED, '>=');
		$soap_fulfilled = class_exists('SOAPClient');

		$requirements = array(
			array(
				'name' => __( 'Версия на PHP', SPEEDY_TEXT_DOMAIN ),
				'required' => MIN_PHP_VERSION_REQUIRED,
				'current' => $php_version,
				'is_success' => $php_version_fulfilled,
				'result' => $php_version_fulfilled ? __( 'Изпълнено', SPEEDY_TEXT_DOMAIN ) : __( 'Не е изпълнено', SPEEDY_TEXT_DOMAIN )
			),
			array(
				'name' => __( 'Версия на MySQL', SPEEDY_TEXT_DOMAIN ),
				'required' => MIN_MySQL_VERSION_REQUIRED,
				'current' => $mysql_version,
				'is_success' => $mysql_version_fulfilled,
				'result' => $mysql_version_fulfilled ? __( 'Изпълнено', SPEEDY_TEXT_DOMAIN ) : __( 'Не е изпълнено', SPEEDY_TEXT_DOMAIN )
			),
			array(
				'name' => __( 'SOAP разширение', SPEEDY_TEXT_DOMAIN ),
				'required' => '-',
				'current' => $soap_fulfilled ? __( 'Да', SPEEDY_TEXT_DOMAIN ) : __( 'Не', SPEEDY_TEXT_DOMAIN ),
				'is_success' => $soap_fulfilled,
				'result' => $soap_fulfilled ? __( 'Изпълнено', SPEEDY_TEXT_DOMAIN ) : __( 'Не е изпълнено', SPEEDY_TEXT_DOMAIN )
			),
		);

		ob_start();
		?>

		<tr valign="top">
			<th scope="row" class="titledesc"><?php echo $data['title']; ?></th>
			<td class="forminp">
				<table id="system_requirements">
					<thead>
						<tr valign="top">
							<th scope="row"><?php echo __( 'Изисквания', SPEEDY_TEXT_DOMAIN ); ?></th>
							<th scope="row"><?php echo __( 'Минимална изисквана версия', SPEEDY_TEXT_DOMAIN ); ?></th>
							<th scope="row"><?php echo __( 'Текуща версия / статус', SPEEDY_TEXT_DOMAIN ); ?></th>
							<th scope="row"><?php echo __( 'Резултат', SPEEDY_TEXT_DOMAIN ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($requirements as $requirement) { ?>
						<tr valign="top">
							<td class="forminp"><?php echo $requirement['name']; ?></td>
							<td class="forminp"><?php echo $requirement['required']; ?></td>
							<td class="forminp"><?php echo $requirement['current']; ?></td>
							<td class="forminp"><span style="color: <?php echo $requirement['is_success'] ? 'green' : 'red; text-transform: uppercase;'; ?>"><?php echo $requirement['result']; ?><span></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</td>
		</tr>


		<?php

		return ob_get_clean();
	}

	/**
	 * Generate Version HTML.
	 */
	function generate_version_html( $key, $data ) {
		$field = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'title'             => '',
			'css'               => '',
			'description'       => '',
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top" style="<?php echo esc_attr( $data['css'] ); ?>">
			<th scope="row" class="titledesc">
				<?php echo esc_attr( $data['title'] ); ?>
			</th>
			<td class="forminp">
				<?php echo $data['description']; ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	 /**
	 * Generate Buttons HTML.
	 */
	function generate_services_buttons_html( $key, $data ) {
		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'class'             => 'button-secondary',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'title_1'           => '',
			'title_2'           => '',
			'title_3'           => '',
			'button_generate'   => '',
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top" style="<?php echo esc_attr( $data['css'] ); ?>">
			<th scope="row" class="titledesc">
			</th>
			<td class="forminp">
				<fieldset>
					<div id="services_buttons" style="<?php echo ($data['button_generate']) ? 'display: none;' : ''; ?>">
						<a class="button speedy_button" onclick="jQuery('#woocommerce_speedy_shipping_method_allowed_methods option').attr('selected', 'selected');"><?php echo esc_attr( $data['title_1'] ); ?></a> / <a class="button speedy_button" onclick="jQuery('#woocommerce_speedy_shipping_method_allowed_methods option').attr('selected', false);"><?php echo esc_attr( $data['title_2'] ); ?></a>
					</div>
					<a id="generate_services_button" class="button speedy_button" onclick="getAllowedMethods();" style="<?php echo (!$data['button_generate']) ? 'display: none;' : ''; ?>"><?php echo esc_attr( $data['title_3'] ); ?></a>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate Currencies HTML.
	 */
	function generate_currency_rate_html( $key, $data ) {
		$field = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'title'             => '',
			'class'             => '',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'options'           => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<?php $row = 0; ?>
		<?php foreach ($data['options'] as $value) { ?>
		<tr valign="top" style="<?php echo esc_attr( $data['css'] ); ?>" id="currency_row_<?php echo $row; ?>">
			<th scope="row" class="titledesc">
				<?php echo esc_attr( $data['title'] ); ?>
			</th>
			<td class="forminp">
				<input type="text" class="<?php echo $field; ?>" name="<?php echo $field; ?>[<?php echo $row; ?>][iso_code]" placeholder="ISO Code" value="<?php echo $value['iso_code']; ?>" />
				<input type="text" class="<?php echo $field; ?>" name="<?php echo $field; ?>[<?php echo $row; ?>][rate]" placeholder="Rate" value="<?php echo $value['rate']; ?>" />
				<button type="button" class="button remove_currency" onclick="jQuery('#currency_row_<?php echo $row; ?>').remove();">Remove</button>
			</td>
		</tr>
		<?php $row++; ?>
		<?php } ?>
		<tr valign="top" id="add_rate">
			<th scope="row" class="titledesc">
			</th>
			<td class="forminp">
				<button type="button" class="button" onclick="addRate();"><?php echo __( 'Добавяне на валута / Валутен курс', SPEEDY_TEXT_DOMAIN ); ?></button>
			</td>
		</tr>
		<script type="text/javascript"><!--
			var row = <?php echo $row; ?>;
			function addRate() {
				html =  '<tr valign="top" style="<?php echo esc_attr( $data['css'] ); ?>" id="currency_row_' + row + '">';
				html += '  <th scope="row" class="titledesc">';
				html += '    <?php echo esc_attr( $data['title'] ); ?>';
				html += '  </th>';
				html += '  <td class="forminp">';
				html += '    <input type="text" class="<?php echo $field; ?>" name="<?php echo $field; ?>[' + row + '][iso_code]" placeholder="ISO Code" value="" />';
				html += '    <input type="text" class="<?php echo $field; ?>" name="<?php echo $field; ?>[' + row + '][rate]" placeholder="Rate" value="" />';
				html += '    <button type="button" class="button remove_currency" onclick="jQuery(\'#currency_row_' + row + '\').remove();">Remove</button>';
				html += '  </td>';
				html += '</tr>';

				jQuery('#add_rate').before(html);
				row++;
			}
		--></script>
		<?php
		return ob_get_clean();
	}

	function validate_currency_rate_field( $key ) {
		$field = $this->get_field_key( $key );

		if ( isset( $_POST[ $field ] ) ) {
			if (is_array($_POST[ $field ])) {
				foreach ($_POST[ $field ] as $p_field) {
					$value[] = array_map( 'wc_clean', array_map( 'stripslashes', (array) $p_field ) );
				}
			}
		} else {
			$value = '';
		}

		return $value;
	}

	public function validate_select_field( $key , $value = '') {
		$value = $this->get_option( $key );

		if ( isset( $_POST[ $this->plugin_id . $this->id . '_' . $key ] ) ) {
			$value = wc_clean( stripslashes( $_POST[ $this->plugin_id . $this->id . '_' . $key ] ) );
		}

		if ( !empty( $this->form_fields[$key]['required'] ) && !$value ) {
			$this->errors['field_empty_' . $key] = __( 'Моля попълнете задължителните полета!', SPEEDY_TEXT_DOMAIN );
		}

		return $value;
	}

	/**
	* Generate system speedy statuses html
	*/
	function generate_final_statuses_html( $key, $data ) {
		$field = $this->plugin_id . $this->id . '_' . $key;

		$defaults = array(
			'title'             => '',
			'class'             => '',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'options'           => array(),
		);

		foreach($this->speedy_statuses as $code => $value) {
			$defaults['options'][$code] = 'wc-pending';
		}

		$data['options'] = maybe_unserialize($data['options']);

		if (!$data['options']) {
			$data['options'] = maybe_unserialize($this->final_statuses);
		}

		$data['options'] = array_replace( $defaults['options'], $data['options']);;
		$wc_get_order_statuses = wc_get_order_statuses();

		ob_start();
		?>
		<style>
			#final_statuses th {
				padding-left: 10px
			}
			#final_statuses, #final_statuses td, #final_statuses th {
				border: 1px solid #ddd;
				border-spacing: 0;
				background: white;
			}
		</style>

		<tr valign="center" class="forminp speedy-group" >
			<th scope="row" style="vertical-align: middle;" class="titledesc"><?php echo $data['title']; ?></th>
			<td class="forminp">
				<table id="final_statuses">
					<thead>
						<tr valign="center">
							<th scope="row"><?php echo __( 'Спиди статус', SPEEDY_TEXT_DOMAIN ) ?></th>
							<th scope="row"><?php echo __( 'WooCommerce статус', SPEEDY_TEXT_DOMAIN ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($data['options'] as $id => $speedy_status) { ?>
						<?php if (!isset($this->speedy_statuses[$id])) { continue; } ?>
						<tr valign="top">
							<th class="forminp"><?php echo $this->speedy_statuses[$id]; ?></th>
							<td class="forminp">
								<select class="<?php echo $field; ?>" name="<?php echo $field; ?>[<?php echo $id; ?>]">
								<?php foreach ($wc_get_order_statuses as $key => $status) { ?>
								<?php $selected = $speedy_status == $key ? "selected" : ''; ?>
								<option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $status; ?></option>
								<?php } ?>
								</select>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	function validate_final_statuses_field( $key ) {
		$field = $this->get_field_key( $key );

		if ( isset( $_POST[ $field ] ) ) {
			if (is_array($_POST[ $field ])) {
				$value = maybe_serialize($_POST[ $field ]);
			}
		} else {
			$value = '';
		}

		return $value;
	}

	public function change_orders_statuses() {
		if (!$this->order_status_update) {
			return;
		}

		$bol_ids = $this->_getNotFinalizedOrders();
		$bol_ids['bol_ids'] = array_chunk($bol_ids['bol_ids'], $this->speedy::MAX_PARCEL_MULTIPLE_TRACK);

		foreach($bol_ids['bol_ids'] as $ids) {
			$deliveryInfo = $this->speedy->trackParcelMultiple($ids);
			foreach($deliveryInfo as $info) {
				$operationCode = $info->getOperationCode();

				if (!isset($this->speedy_statuses[$operationCode])) {
					continue;
				}

				$bolId = $info->getBarcode();
				$order_status = $this->final_statuses[$operationCode];
				$order_status = str_replace('wc-', '', $order_status);

				$speedy_order = $this->_getOrderByOrderIdByBolId($bolId);
				$order = wc_get_order($speedy_order['order_id']);
				$order->update_status($order_status);

				$this->_setFinalizedOrder($bolId);
			}
		}
	}

	/**
	 * Generate weight dimensions HTML.
	 */
	function generate_weight_dimensions_html( $key, $data ) {
		$field = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'title'             => '',
			'class'             => '',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'options'           => array(),
		);

		$weight_dimensions = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top" style="<?php echo esc_attr( $weight_dimensions['css'] ); ?>">
			<th scope="row" class="titledesc">
				<?php echo esc_attr( $weight_dimensions['title'] ); ?>
			</th>
			<td class="forminp">
				<table id="weight_dimensions">
					<thead>
						<tr>
							<td></td>
							<td colspan="5" align="center"><?php echo __( 'Размер на опаковката в см.', SPEEDY_TEXT_DOMAIN ); ?></td>
							<td></td>
						</tr>
						<tr>
							<td align="center"><?php echo __( 'Тегло на пратката', SPEEDY_TEXT_DOMAIN ); ?></td>
							<td align="center"><?php echo __( 'XS <br> (50 x 35 x 4,5)', SPEEDY_TEXT_DOMAIN ); ?></td>
							<td align="center"><?php echo __( 'S <br> (60 x 35 x 11)', SPEEDY_TEXT_DOMAIN ); ?></td>
							<td align="center"><?php echo __( 'M <br> (60 x 35 x 19)', SPEEDY_TEXT_DOMAIN ); ?></td>
							<td align="center"><?php echo __( 'L <br> (60 x 35 x 37)', SPEEDY_TEXT_DOMAIN ); ?></td>
							<td align="center"><?php echo __( 'XL <br> (60 x 60 x 60)', SPEEDY_TEXT_DOMAIN ); ?></td>
							<td></td>
						</tr>
					</thead>
					<tbody>
					<?php if(!empty($weight_dimensions['options'])) { ?>
					<?php foreach($weight_dimensions['options'] as $key => $dimention) { ?>
						<tr data-row="<?php echo $key; ?>">
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[<?php echo $key; ?>][WEIGHT]" value="<?php echo !empty($dimention['WEIGHT']) ? $dimention['WEIGHT'] : ''; ?>">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[<?php echo $key; ?>][XS]" value="<?php echo !empty($dimention['XS']) ? $dimention['XS'] : ''; ?>">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[<?php echo $key; ?>][S]" value="<?php echo !empty($dimention['S']) ? $dimention['S'] : ''; ?>">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[<?php echo $key; ?>][M]" value="<?php echo !empty($dimention['M']) ? $dimention['M'] : ''; ?>">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[<?php echo $key; ?>][L]" value="<?php echo !empty($dimention['L']) ? $dimention['L'] : ''; ?>">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[<?php echo $key; ?>][XL]" value="<?php echo !empty($dimention['XL']) ? $dimention['XL'] : ''; ?>">
							</td>
							<td>
								<button type="button" class="button" onclick="removeSpeedyWeightDimension(<?php echo $key; ?>);"><?php echo __( 'Изтрий', SPEEDY_TEXT_DOMAIN ); ?></button>
							</td>
						</tr>
					<?php } ?>
					<?php } else { ?>
						<tr data-row="0">
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[0][WEIGHT]">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[0][XS]">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[0][S]">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[0][M]">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[0][L]">
							</td>
							<td>
								<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[0][XL]">
							</td>
							<td>
								<button type="button" class="button" onclick="removeSpeedyWeightDimension(0);"><?php echo __( 'Изтрий', SPEEDY_TEXT_DOMAIN ); ?></button>
							</td>
						</tr>
					<?php } ?>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="6"></td>
						<td>
						<button type="button" class="button" onclick="addSpeedyWeightDimension();"><?php echo __( 'Добави', SPEEDY_TEXT_DOMAIN ); ?></button>
						</td>
					</tr>
					</tfoot>
				</table>
			</td>
		</tr>
		<script type="text/javascript"><!--
		function addSpeedyWeightDimension() {
		  var next_row = 0;

		  if(typeof jQuery('#weight_dimensions tbody tr:last').attr('data-row') != 'undefined') {
			next_row = parseInt(jQuery('#weight_dimensions tbody tr:last').attr('data-row')) + 1;
		  }

		  var html = '';
		  html  = '<tr data-row="' + next_row + '">';
		  html +=   '<td class="text-center">';
		  html +=     '<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[' + next_row + '][WEIGHT]" class="form-control">';
		  html +=   '</td>';
		  html +=   '<td class="text-center">';
		  html +=     '<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[' + next_row + '][XS]" class="form-control">';
		  html +=   '</td>';
		  html +=   '<td class="text-center">';
		  html +=     '<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[' + next_row + '][S]" class="form-control">';
		  html +=   '</td>';
		  html +=   '<td class="text-center">';
		  html +=     '<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[' + next_row + '][M]" class="form-control">';
		  html +=   '</td>';
		  html +=   '<td class="text-center">';
		  html +=     '<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[' + next_row + '][L]" class="form-control">';
		  html +=   '</td>';
		  html +=   '<td class="text-center">';
		  html +=     '<input type="text" name="<?php echo $this->id; ?>_weight_dimensions[' + next_row + '][XL]" class="form-control">';
		  html +=   '</td>';
		  html +=   '<td class="text-center">';
		  html +=     '<button type="button" class="button" onclick="removeSpeedyWeightDimension(' + next_row + ');" data-toggle="tooltip" title="" class="btn btn-danger"><?php echo __( 'Изтрий', SPEEDY_TEXT_DOMAIN ); ?></button>';
		  html +=   '</td>';
		  html += '</tr>';

		  jQuery('#weight_dimensions tbody').append(html);
		}
		function removeSpeedyWeightDimension(row) {
		  jQuery('#weight_dimensions tr[data-row=' + row + ']').remove();
		}
		//--></script>
		<style>
			#weight_dimensions, #weight_dimensions td {
				border: 1px solid #ddd;
				border-spacing: 0;
				background: white;
			}
		</style>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate table rate HTML.
	 */
	function generate_table_rate_file_html( $key, $data ) {
		$field = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'title'             => '',
			'class'             => '',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'options'           => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top" style="<?php echo esc_attr( $data['css'] ); ?>">
			<th scope="row" class="titledesc">
				<?php echo esc_attr( $data['title'] ); ?>
			</th>
			<td class="forminp">
				<input type="file" name="<?php echo $field; ?>" id="<?php echo $field; ?>" />
			</td>
		</tr>
		<script type="text/javascript"><!--

		--></script>
		<?php
		return ob_get_clean();
	}

	function validate_table_rate_file_field( $key ) {
		$field = $this->get_field_key( $key );
		$data = array();

		$csv_mimetypes = array(
			'text/csv',
			'text/plain',
			'application/csv',
			'text/comma-separated-values',
			'application/excel',
			'application/vnd.ms-excel',
			'application/vnd.msexcel',
			'text/anytext',
			'application/octet-stream',
			'application/txt',
		);

		if ( isset( $_FILES[ $field ] ) && !$_FILES[ $field ]['error'] ) {
			if (($handle_import = fopen($_FILES[ $field ]['tmp_name'], 'r')) !== false) {
				$handle_import_data = fgetcsv($handle_import, 100000); // remove title line
			}

			$file_columns = array(
				'ServiceID',
				'TakeFromOffice',
				'Weight',
				'OrderTotal',
				'PriceWithoutVAT',
				'FixedTimeDelivery',
			);

			$file_columns_indexes = array();
			foreach($handle_import_data as $index => $columnName) {
				$file_columns_indexes[$columnName] = array_search($columnName, $handle_import_data);
			}

			sort($handle_import_data);
			sort($file_columns);

			if ($handle_import_data == $file_columns) {
				while (($handle_import_data = fgetcsv($handle_import, 100000)) !== false) {
					$data[] = array(
						'service_id' => $handle_import_data[$file_columns_indexes['ServiceID']],
						'take_from_office' => $handle_import_data[$file_columns_indexes['TakeFromOffice']],
						'weight' => str_replace(',', '.', $handle_import_data[$file_columns_indexes['Weight']]),
						'order_total' => str_replace(',', '.', $handle_import_data[$file_columns_indexes['OrderTotal']]),
						'price_without_vat' => str_replace(',', '.', $handle_import_data[$file_columns_indexes['PriceWithoutVAT']]),
						'fixed_time_delivery' => str_replace(',', '.', $handle_import_data[$file_columns_indexes['FixedTimeDelivery']]),
					);
				}
			} else {
				$this->errors['file'] = sprintf(__( 'Неуспешно импортиране на файл %s!', SPEEDY_TEXT_DOMAIN ), $_FILES[ $field ]['name']);
			}

			if (empty($this->errors)) {
				$this->_importFilePrice( $data );
			}
		}

		return '';
	}

	/**
	 * Display errors by overriding the display_errors() method
	 * @see display_errors()
	 */
	public function display_errors( ) {
		// loop through each error and display it
		foreach ( $this->errors as $key => $value ) {
			?>
			<div class="error">
				<p><?php echo $value; ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		include(dirname(__FILE__) . '/includes/settings-speedy-shipping-method.php' );
	}

	/**
	 * is_available function.
	 *
	 * @param mixed $package
	 * @return bool
	 */
	public function is_available( $package ) {

		$is_available       = true;

		if ( 'no' == $this->enabled ) {
			$is_available = false;
		}

		if ( 'specific' == $this->availability ) {
			$ship_to_countries = $this->countries;
		} else {
			$ship_to_countries = array_keys( WC()->countries->get_shipping_countries() );
		}

		if ( is_array( $ship_to_countries ) && ! in_array( $package['destination']['country'], $ship_to_countries ) ) {
			$is_available = false;
		}

		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package );
	}

	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		$label = $this->title;

		if (WC()->session->get( 'speedy' ) && isset( WC()->session->speedy['shipping_method_cost'] ) ) {
			$cost = WC()->session->speedy['shipping_method_cost'];

			$allowed_pricings = array(
				'calculator',
				'free',
				'calculator_fixed'
			);

			if ( $this->invoice_courrier_sevice_as_text && isset( WC()->session->speedy['cod'] ) && WC()->session->speedy['cod'] ) {
				if( in_array( $this->pricing, $allowed_pricings ) ) {
					if ( $this->pricing == 'free' ) {
						$delta = 0.0001;
						if( abs( WC()->session->speedy['shipping_method_cost'] - 0.0000 ) > $delta ) {
							$label = sprintf( __( '%s (%s дължими при доставка)', SPEEDY_TEXT_DOMAIN ), $this->title, strip_tags( wc_price( WC()->session->speedy['shipping_method_cost'] ) ) );
							$cost = 0;
						}
					} else {
						$label = sprintf( __( '%s (%s дължими при доставка)', SPEEDY_TEXT_DOMAIN ), $this->title, strip_tags( wc_price( WC()->session->speedy['shipping_method_cost'] ) ) );
						$cost = 0;
					}
				}
			}

			if ( $this->pricing == 'fixed') {
				$cost = (float)$this->fixed_price;
			}
		} else {
			$cost = 0;
		}

		$rate = array(
			'id' => $this->id,
			'label' => $label,
			'cost' => $cost,
			'calc_tax' => 'per_order'
		);

		// Register the rate
		$this->add_rate( $rate );
		return $rate;
	}

	/**
	 * Validate speedy form function
	 * If is valid returns empty array
	 */
	public function validateSpeedyForm( $admin_validation = false ) {
		$errors = array();
		if (empty($_POST['data']) && WC()->session->speedy) {
			$post = WC()->session->speedy;
		} else {
			parse_str($_POST['data'], $post);
		}

		if ($admin_validation) {
			if ((mb_strlen($post['contents'], "UTF-8") < 1) || (mb_strlen($post['contents'], "UTF-8") > 100)) {
				$errors['error_contents'] = true;
			}

			if ($post['weight'] <= 0) {
				$errors['error_weight'] = true;
			}

			if ($post['count'] <= 0) {
				$errors['error_count'] = true;
			}

			if (!$post['packing']) {
				$errors['error_packing'] = true;
			}
		}

		if (!isset($post['abroad']) || !$post['abroad']) {
			if ($post['postcode'] && $post['city'] && $post['city_id'] &&
				(!$post['to_office'] && (($post['quarter'] && ($post['quarter_id'] && $post['city_nomenclature'] == 'FULL' || $post['city_nomenclature'] != 'FULL') && ($post['block_no'] || $post['street_no'])) ||
				($post['street'] && ($post['street_id'] && $post['city_nomenclature'] == 'FULL' || $post['city_nomenclature'] != 'FULL') && ($post['block_no'] || $post['street_no'])) || $post['note']) || ($post['to_office'] && $post['office_id']))) {
			} else {
				if ($post['to_office']) {
					$errors['error_office'] = __( 'Моля, въведете населено място и изберете офис!', SPEEDY_TEXT_DOMAIN );
				} else {
					$errors['error_address'] = __( 'Моля, въведете валиден адрес!', SPEEDY_TEXT_DOMAIN );
				}
			}
		} else {
			$this->speedy = SpeedyEpsLib::getInstance();
			$validAddress = $this->speedy->validateAddress( $post );
			if ($validAddress !== true) {
				$errors['error_address'] = $validAddress;
			}
		}

		if ( isset( $post['cod'] ) && $post['cod'] && isset( $post['active_currency_code'] ) ) {
			$currency_exists = false;
			foreach ( $this->currency_rate as $currency_rate ) {
				if ( $currency_rate['iso_code'] == $post['active_currency_code'] ) {
					$currency_exists = true;
					break;
				}
			}

			if ( ! $currency_exists ) {
				$errors['error_currency'] = sprintf( __( 'Не може да използвате Наложен платеж, валутата %s лиспва. Моля обърнете се към администраторите на магазина!', SPEEDY_TEXT_DOMAIN ), $post['active_currency_code'] );
			}
		}

		if ( ! $admin_validation ) {
			if (!isset($post['cod'])) {
				$errors['error_cod'] = __( 'Моля, изберете желаете ли плащане с наложен платеж през Спиди!', SPEEDY_TEXT_DOMAIN );
			}
		}

		return $errors;
	}

	/**
	 * Adding speedy address
	 */
	public function getQuotePublic() {
		global $woocommerce;
		$quote_data = array();

		$this->speedy = SpeedyEpsLib::getInstance();

		$method_data = array('quote' => array());

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		if (isset($chosen_methods['undefined'])) {
			unset($chosen_methods['undefined']);
			WC()->session->set( 'chosen_shipping_methods', $chosen_methods );
		}

		if (WC()->session->get( 'speedy' )) {

			$speedy_products = array();
			$data = array();
			$total = 0;
			$weight = 0;
			$weight_cart = 0;
			$totalNoShipping = 0;

			$speedy_session = WC()->session->get( 'speedy' );

			if ( ( isset( $speedy_session['abroad'] ) && $speedy_session['abroad'] ) || get_locale() != 'bg_BG' ) {
				$lang = 'en';
			} else {
				$lang = 'bg';
			}

			$total = $woocommerce->cart->subtotal - $woocommerce->cart->get_cart_discount_total( );

			foreach($woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product = $cart_item['data'];

				if (!$_product->is_downloadable()) {
					$product_weight = (float)$_product->get_weight();
					$product_weight = wc_get_weight($product_weight, 'kg');

					if (!empty($product_weight)) {
						$weight += $product_weight * $cart_item['quantity'];
					} else {
						$weight += $this->default_weight * $cart_item['quantity'];
					}

					$weight_cart += $product_weight * $cart_item['quantity'];

					$totalNoShipping += $_product->get_price_including_tax($cart_item['quantity']);
				}
			}

			if ($this->documents && (float)$weight > 0.25) {
				$weight = 0.25;
			}

			$speedy_session['total'] = $total;
			$speedy_session['totalNoShipping'] = $totalNoShipping;
			$speedy_session['weight'] = $weight;
			$speedy_session['weight_cart'] = $weight_cart;
			$speedy_session['count'] = 1;
			$speedy_session['taking_date'] = ($this->taking_date ? strtotime('+' . (int)$this->taking_date . ' day', mktime(9, 0, 0)) : time());
			$speedy_session['client_id'] = $this->client_id;
			$speedy_session['convertion_to_win1251'] = $this->convert_to_win_1251;
			$speedy_session['additional_copy_for_sender'] = $this->additional_copy_for_sender;

			if ($this->pricing == 'fixed' || $this->pricing == 'calculator_fixed') {
				$speedy_session['total'] += (float)$this->fixed_price;
			}

			$cod_payment = null;
			foreach (WC()->payment_gateways->payment_gateways as $payment_gateway) {
				if ($payment_gateway->id == 'cod' ) {
					$cod_payment = $payment_gateway;
					break;
				}
			}

			if ( class_exists('WC_Gateway_COD') && $cod_payment ) {
				if ( 'yes' == $cod_payment->enabled ) {
					if ( ! empty( $cod_payment->enable_for_methods ) ) {
						$disabled = true;
						foreach ( $cod_payment->enable_for_methods as $enabled_method ) {
							if ( $enabled_method == $this->id ) {
								$disabled = false;
								break;
							}
						}
						if ( $disabled ) {
							$speedy_session['cod'] = false;
						}
					}
				} else {
					$speedy_session['cod'] = false;
				}
			} else {
				$speedy_session['cod'] = false;
			}

			if (!isset($speedy_session['cod'])) {
				$speedy_session['cod'] = false;
			}

			$speedy_session['parcels_size'] = array(
				1 => array(
					'weight' => '',
					'width'  => '',
					'height' => '',
					'depth'  => '',
				)
			);

			$cart_products = $woocommerce->cart->get_cart();
			$countProducts = 0;
			$parcel_size = $this->parcel_sizes[1];
			$products = array();

			if (!$speedy_session['abroad']) {
				foreach ($cart_products as $cart_product) {
					$_product = new WC_Product( $cart_product['product_id'] );

					if (!$_product->is_virtual()) {

						$countProducts += $cart_product['quantity'];
						$sizes = $this->getSpeedyQuantityDimention($cart_product['product_id'], $cart_product['quantity']);

						if (!empty($sizes) || $this->speedyHasQuantityDimention($cart_product['product_id'])) {
							$sizes['quantity'] = $cart_product['quantity'];
							$products[] = $sizes;
							if (!empty($sizes['size'])) {
								$parcel_size = $this->compareSizes($parcel_size, $sizes['size']);
							}
						} else {
							$no_parcel_size = true;
						}
					}
				}

				$weight_size = $this->getSpeedyWeightDimention(wc_get_weight($woocommerce->cart->cart_contents_weight, 'kg'), $countProducts);

				if (!empty($products) && empty($no_parcel_size)) {
					for ($i = 1;$i <= count($this->parcel_sizes); $i++) {
						$parcel_full = 0;

						foreach ($products as $product) {
							if (empty($product['sizes'])) {
								$parcel_size = '';
								break 2;
							}
							$parcel_full += $product['quantity'] / $product['sizes'][$parcel_size];
						}

						if ($parcel_full > 1) {
							$next_size = array_search($parcel_size, $this->parcel_sizes) + 1;

							if (isset($this->parcel_sizes[$next_size])) {
								$parcel_size = $this->parcel_sizes[$next_size];
							} else {
								$parcel_size = '';
								break;
							}
						} else {
							break;
						}
					}
				} elseif($weight_size) {
					$size_compare = $this->calculateSize($products, $parcel_size);

					if($size_compare) {
						$parcel_size = $this->compareSizes($size_compare, $weight_size);
					} else {
						$parcel_size = $weight_size;
					}
				} elseif($this->min_package_dimention) {
					$size_compare = $this->calculateSize($products, $parcel_size);

					if($size_compare) {
						$parcel_size = $this->compareSizes($size_compare, $this->min_package_dimention);
					} else {
						$parcel_size = $this->min_package_dimention;
					}
				} else {
					$parcel_size = '';
				}
			} else {
				$parcel_size = '';
			}

			$speedy_session['parcel_size'] = $parcel_size;

			if (!empty($speedy_session['fixed_time_cb'])) {
				$speedy_session['fixed_time'] = $speedy_session['fixed_time_hour'] . $speedy_session['fixed_time_min'];
			} else {
				$speedy_session['fixed_time'] = null;
			}

			$services = $this->speedy->getServices( $lang );
			$methods_count = 0;

			$methods = $this->speedy->calculate($speedy_session);

			if (!$this->speedy->getError()) {
				foreach ($methods as $method) {
					$total_form = array();

					if (!$method->getErrorDescription()) {
						if ( ( $this->pricing == 'free' ) && ( $total >= (float)$this->free_shipping_total ) &&
							( $method->getServiceTypeId() == $this->free_method_city || $method->getServiceTypeId() == $this->free_method_intercity || in_array( $method->getServiceTypeId(), (array)$this->free_method_international ) ) ) {
							$method_total = 0;
						} elseif ($this->pricing == 'fixed') {
							$method_total = $this->fixed_price;
						} elseif ($this->pricing == 'table_rate') {
							$filter_data = array(
								'service_id' => $method->getServiceTypeId(),
								'take_from_office' => $speedy_session['to_office'],
								'weight' => $weight,
								'order_total' => $total,
								'fixed_time_delivery' => isset($speedy_session['fixed_time_cb']) ? 1 : 0,
							);

							$speedy_table_rate = $this->_getSpeedyTableRate($filter_data);

							if (empty($speedy_table_rate)) {
								continue;
							} else {
								$method_total = $speedy_table_rate['price_without_vat'];
							}
						} else {
							$method_total = $method->getResultInfo()->getAmounts()->getTotal();

							if ($this->pricing == 'calculator_fixed') {
								$method_total += $this->fixed_price;
							}

							$vat_fixedTimeDelivery = round(0.2 * $method->getResultInfo()->getAmounts()->getFixedTimeDelivery(), 2);
							$vat_codPremium = round(0.2 * $method->getResultInfo()->getAmounts()->getCodPremium(), 2);

							$total_form[] = array(
								'label' => __( 'Стойност', SPEEDY_TEXT_DOMAIN ),
								'value' => wc_price( (float)($method_total - ($method->getResultInfo()->getAmounts()->getCodPremium() + $vat_codPremium) - ($method->getResultInfo()->getAmounts()->getFixedTimeDelivery() + $vat_fixedTimeDelivery)) )
							);

							if (isset($speedy_session['fixed_time_cb'])) {
								$total_form[] = array(
									'label' => __( "Надбавка 'Фиксиран час'", SPEEDY_TEXT_DOMAIN ),
									'value' => wc_price( (float)$method->getResultInfo()->getAmounts()->getFixedTimeDelivery() + $vat_fixedTimeDelivery )
								);
							}

							if ($speedy_session['cod']) {
								$total_form[] = array(
									'label' => __( "Комисиона 'Нал. платеж'", SPEEDY_TEXT_DOMAIN ),
									'value' => wc_price( (float)$method->getResultInfo()->getAmounts()->getCodPremium() + $vat_codPremium )
								);
							}

							$total_form[] = array(
								'label' => __( 'Всичко', SPEEDY_TEXT_DOMAIN ),
								'value' => $this->convertSpeedyPrice($method_total, $this->currency, get_option('woocommerce_currency'), true)
							);
						}

						if($method->getServiceTypeId() == 500 && !empty($speedy_session['parcel_size'])) { // for SPEEDY POST
							$method_title = __( 'Спиди', SPEEDY_TEXT_DOMAIN ) . ' - ' . $services[$method->getServiceTypeId()] . ' (' . $speedy_session['parcel_size'] . ')';
						} else {
							$method_title = __( 'Спиди', SPEEDY_TEXT_DOMAIN ) . ' - ' . $services[$method->getServiceTypeId()];
						}

						$quote_data[] = array(
							'code'           => $method->getServiceTypeId(),
							'title'          => $method_title,
							'cost'           => $this->convertSpeedyPrice($method_total, $this->currency, get_option('woocommerce_currency')),
							'total_form'     => isset($total_form) ? $total_form : array(),
							'tax_class_id'   => 0,
							'text'           => $this->convertSpeedyPrice($method_total, $this->currency, get_option('woocommerce_currency'), true)
						);

						$methods_count++;
					}
				}

				$speedy_session['pricing'] = $this->pricing;

				WC()->session->set( 'speedy', $speedy_session);

				if ($methods_count) {
					$method_data['quote'] = $quote_data;
				} elseif (!$methods_count && $this->pricing == 'table_rate') {
					$method_data['speedy_error'] = __( 'За тази поръчка не може да бъде калкулирана цена. Моля обърнете се към администраторите на магазина!', SPEEDY_TEXT_DOMAIN );
				} else {
					$method_data['speedy_error'] = __( 'Моля, изберете друг офис или променете адреса за доставка!', SPEEDY_TEXT_DOMAIN );
				}
			} else {
				$method_data['speedy_error'] = $this->speedy->getError();
			}
		} else {
			$method_data['speedy_error'] = __( 'Моля, въведете коректно всички данни и кликнете Изчисли цена след това!', SPEEDY_TEXT_DOMAIN );
		}

		return $method_data;
	}

	 /**
	 * Function speedy_before_checkout_process()
	 */
	static function speedy_before_checkout_process( ) {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping = $chosen_methods[0];
		$errors = $speedy_shipping_method->validateSpeedyForm();

		$non_shipable = 0;
		foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = $cart_item['data'];

			if ($_product->is_virtual() || $_product->is_downloadable()) {
				$non_shipable++;
			}
		}

		$needs_shipping = $non_shipable != count(WC()->cart->get_cart());
		if (!$needs_shipping) {
			return;
		}

		if ($errors && $chosen_shipping == $speedy_shipping_method->id) {
			foreach ($errors as $error) {
				wc_add_notice( $error, 'error' );
			}
		}

		if (($chosen_shipping == $speedy_shipping_method->id)) {
			if ( ! isset( WC()->session->speedy['shipping_method_id'] ) ) {
				wc_add_notice( __( 'Моля изберете метод за доставка от Спиди!', SPEEDY_TEXT_DOMAIN ), 'error' );
			}
		}
	}

	 /**
	 * Function speedy_checkout_order_processed()
	 */
	static function speedy_checkout_order_processed( $order_id, $posted ) {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();

		if (($posted['shipping_method'][0] == $speedy_shipping_method->id) && WC()->session->speedy) {
			$data = WC()->session->speedy;

			$data['price_gen_method'] = $speedy_shipping_method->pricing;
			$speedy_shipping_method->_addOrder($order_id, $data);

			$speedy_shipping_method->_updateOrderInfo($order_id, $data);

			unset( WC()->session->speedy );
		}
	}

	 /**
	 * Function speedy_update_order_review()
	 * Update speedy address form
	 */
	static function speedy_update_order_review( $params ) {
		global $woocommerce;
		$wc_speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$wc_speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		if ( trim( $_POST['s_city'] ) != trim( $woocommerce->customer->get_shipping_city() ) || trim( $_POST['s_postcode'] ) != trim( $woocommerce->customer->get_shipping_postcode() ) ) {
			if ( WC()->session->get( 'speedy' ) ) {
				$speedy_data = WC()->session->get( 'speedy' );
			} else {
				$speedy_data = array();
			}

			if ( is_user_logged_in() && !isset( $speedy_address['city_id'] ) ){
				$speedy_address = $wc_speedy_shipping_method->_getSpeedyAddress(get_current_user_id( ));
				$speedy_data = $speedy_address;
			} else {
				$speedy_data['city_id'] = 0;
			}

			if (isset( $speedy_data['abroad'] ) && $speedy_data['abroad']) {
				$lang = 'en';
			} else {
				$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';
			}

			$cities = $wc_speedy_shipping_method->speedy->getCities( $_POST['s_city'], $_POST['s_postcode'], null, $lang );

			if (!$wc_speedy_shipping_method->speedy->getError()) {
				if (count($cities) == 1) {
					if ( $cities[0]['id'] != $speedy_data['city_id'] ) {
						$speedy_data['postcode'] = $cities[0]['postcode'];
						$speedy_data['city'] = $cities[0]['value'];
						$speedy_data['city_id'] = $cities[0]['id'];
						$speedy_data['city_nomenclature'] = $cities[0]['nomenclature'];
						$speedy_data['quarter'] = '';
						$speedy_data['quarter_id'] = 0;
						$speedy_data['street'] = '';
						$speedy_data['street_id'] = 0;
						$speedy_data['street_no'] = '';
						$speedy_data['block_no'] = '';
						$speedy_data['entrance_no'] = '';
						$speedy_data['floor_no'] = '';
						$speedy_data['apartment_no'] = '';
						$speedy_data['note'] = '';
						$speedy_data['office_id'] = 0;
					}
				} else {
					$speedy_data['postcode'] = '';
					$speedy_data['city'] = '';
					$speedy_data['city_id'] = 0;
					$speedy_data['city_nomenclature'] = '';
					$speedy_data['quarter'] = '';
					$speedy_data['quarter_id'] = 0;
					$speedy_data['street'] = '';
					$speedy_data['street_id'] = 0;
					$speedy_data['street_no'] = '';
					$speedy_data['block_no'] = '';
					$speedy_data['entrance_no'] = '';
					$speedy_data['floor_no'] = '';
					$speedy_data['apartment_no'] = '';
					$speedy_data['note'] = '';
					$speedy_data['office_id'] = 0;
				}
			} else {
				$speedy_data['postcode'] = '';
				$speedy_data['city'] = '';
				$speedy_data['city_id'] = 0;
				$speedy_data['city_nomenclature'] = '';
				$speedy_data['quarter'] = '';
				$speedy_data['quarter_id'] = 0;
				$speedy_data['street'] = '';
				$speedy_data['street_id'] = 0;
				$speedy_data['street_no'] = '';
				$speedy_data['block_no'] = '';
				$speedy_data['entrance_no'] = '';
				$speedy_data['floor_no'] = '';
				$speedy_data['apartment_no'] = '';
				$speedy_data['note'] = '';
				$speedy_data['office_id'] = 0;
			}
			WC()->session->speedy = $speedy_data;
		}

	}

	 /**
	 * Add speedy section in admin order page
	 */
	static function speedy_admin_order_meta( $post ) {
		global $thepostid, $theorder;
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();

		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		if ( ! is_object( $theorder ) ) {
			$theorder = wc_get_order( $thepostid );
		}

		$order = $theorder;

		$shipping_method = current( $order->get_items( 'shipping' ) );

		if ( $shipping_method['method_id'] == $speedy_shipping_method->id ) {
			$speedy_order_info = $speedy_shipping_method->_getOrderByOrderId( $order->get_id() );

			if ($speedy_order_info) {
				if ($speedy_order_info['bol_id']) {
					$speedy_shipping_method->getSpeedyLoadingInfo($speedy_order_info);
				} else {
					$speedy_shipping_method->getSpeedyGenerateForm($order->get_id(), $speedy_order_info);
				}
			} 
		}
	}

	 /**
	 * Get generate loading template
	 */
	private function getSpeedyGenerateForm($order_id, $speedy_order_info) {
		wp_enqueue_style( 'speedyStyle' );

		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();
		$speedy_order_data = maybe_unserialize( $speedy_order_info['data'] );

		$data['order_id'] = $order_id;

		if (isset($_POST['contents'])) {
			$data['contents'] = $_POST['contents'];
		} elseif (isset($speedy_order_data['contents'])) {
			$data['contents'] = $speedy_order_data['contents'];
		} else {
			$data['contents'] = __( 'Поръчка:', SPEEDY_TEXT_DOMAIN ) . ' ' . $order_id;
		}

		if (isset($_POST['weight'])) {
			$data['weight'] = $_POST['weight'];
		} elseif (isset($speedy_order_data['weight'])) {
			$data['weight'] = $speedy_order_data['weight'];
		} else {
			$data['weight'] = '';
		}

		if (isset($_POST['packing'])) {
			$data['packing'] = $_POST['packing'];
		} elseif (isset($speedy_order_data['packing'])) {
			$data['packing'] = $speedy_order_data['packing'];
		} else {
			$data['packing'] = $speedy_shipping_method->packing;
		}

		$data['clients'] = $this->speedy->getListContractClients();

		if (isset($_POST['client_id'])) {
			$data['client_id'] = $_POST['client_id'];
		} elseif (isset($speedy_order_data['packing'])) {
			$data['client_id'] = $speedy_order_data['client_id'];
		} else {
			$data['client_id'] = $speedy_shipping_method->client_id;
		}

		if (isset($_POST['option_before_payment'])) {
			$data['option_before_payment'] = $_POST['option_before_payment'];
		} elseif (isset($speedy_order_data['option_before_payment'])) {
			$data['option_before_payment'] = $speedy_order_data['option_before_payment'];
		} else {
			$data['option_before_payment'] = $speedy_shipping_method->option_before_payment;
		}

		if (isset($_POST['count'])) {
			$data['count'] = $_POST['count'];
		} elseif (isset($speedy_order_data['count'])) {
			$data['count'] = $speedy_order_data['count'];
		} else {
			$data['count'] = 1;
		}

		if (isset($_POST['parcels_size'])) {
			$data['parcels_sizes'] = $_POST['parcels_size'];
		} elseif (isset($speedy_order_data['parcels_size'])) {
			$data['parcels_sizes'] = $speedy_order_data['parcels_size'];
		} else {
			if (isset($speedy_order_data['width'])) {
				$data['parcels_sizes'][1]['width'] = $speedy_order_data['width'];
			} else {
				$data['parcels_sizes'][1]['width'] = '';
			}
			if (isset($speedy_order_data['height'])) {
				$data['parcels_sizes'][1]['height'] = $speedy_order_data['height'];
			} else {
				$data['parcels_sizes'][1]['height'] = '';
			}
			if (isset($speedy_order_data['depth'])) {
				$data['parcels_sizes'][1]['depth'] = $speedy_order_data['depth'];
			} else {
				$data['parcels_sizes'][1]['depth'] = '';
			}
			if (!isset($data['parcels_sizes'][1]['weight'])) {
				$data['parcels_sizes'][1]['weight'] = '';
			}
		}

		if (isset($_POST['shipping_method'])) {
			$shipping_method = explode('.', $_POST['shipping_method']);
			$data['shipping_method_id'] = $shipping_method[1];
		} elseif (isset($speedy_order_data['shipping_method_id'])) {
			$data['shipping_method_id'] = $speedy_order_data['shipping_method_id'];
		} else {
			$data['shipping_method_id'] = '';
		}

		if (isset($_POST['parcel_size'])) {
			$data['parcel_size'] = $_POST['parcel_size'];
		} elseif (isset($speedy_order_data['parcel_size'])) {
			$data['parcel_size'] = $speedy_order_data['parcel_size'];
		} else {
			$data['parcel_size'] = '';
		}

		if (isset($_POST['deffered_days'])) {
			$data['deffered_days'] = $_POST['deffered_days'];
		} elseif (isset($speedy_order_data['deffered_days'])) {
			$data['deffered_days'] = $speedy_order_data['deffered_days'];
		} else {
			$data['deffered_days'] = 0;
		}

		if (isset($_POST['client_note'])) {
			$data['client_note'] = $_POST['client_note'];
		} elseif (isset($speedy_order_data['client_note'])) {
			$data['client_note'] = $speedy_order_data['client_note'];
		} else {
			$data['client_note'] = '';
		}

		if (isset($_POST['cod'])) {
			$data['cod'] = $_POST['cod'];
		} elseif (isset($speedy_order_data['cod'])) {
			$data['cod'] = $speedy_order_data['cod'];
		} else {
			$data['cod'] = true;
		}

		if (isset($_POST['total'])) {
			$data['total'] = $_POST['total'];
		} elseif (isset($speedy_order_data['total'])) {
			$data['total'] = $speedy_order_data['total'];
		} else {
			$data['total'] = '';
		}

		if (isset($_POST['convertion_to_win1251'])) {
			$data['convertion_to_win1251'] = $_POST['convertion_to_win1251'];
		} elseif (isset($speedy_order_data['convertion_to_win1251'])) {
			$data['convertion_to_win1251'] = (bool)$this->get_option('convert_to_win_1251', 0);
		} else {
			$data['convertion_to_win1251'] = true;
		}

		if (isset($_POST['additional_copy_for_sender'])) {
			$data['additional_copy_for_sender'] = $_POST['additional_copy_for_sender'];
		} elseif (isset($speedy_order_data['additional_copy_for_sender'])) {
			$data['additional_copy_for_sender'] = (bool)$this->get_option('additional_copy_for_sender', 0);
		} else {
			$data['additional_copy_for_sender'] = true;
		}

		if (isset($_POST['insurance'])) {
			$data['insurance'] = $_POST['insurance'];
		} elseif (isset($speedy_order_data['insurance'])) {
			$data['insurance'] = $speedy_order_data['insurance'];
		} else {
			$data['insurance'] = $speedy_shipping_method->insurance;
		}

		if (isset($_POST['fragile'])) {
			$data['fragile'] = $_POST['fragile'];
		} elseif (isset($speedy_order_data['fragile'])) {
			$data['fragile'] = $speedy_order_data['fragile'];
		} else {
			$data['fragile'] = $speedy_shipping_method->fragile;
		}

		if (isset($_POST['totalNoShipping'])) {
			$data['totalNoShipping'] = $_POST['totalNoShipping'];
		} elseif (isset($speedy_order_data['totalNoShipping'])) {
			$data['totalNoShipping'] = $speedy_order_data['totalNoShipping'];
		} else {
			$data['totalNoShipping'] = '';
		}

		if (isset($_POST['abroad'])) {
			$data['abroad'] = $_POST['abroad'];
		} elseif (isset($speedy_order_data['abroad'])) {
			$data['abroad'] = $speedy_order_data['abroad'];
		} else {
			$data['abroad'] = 0;
		}

		if (isset($_POST['to_office'])) {
			$data['to_office'] = $_POST['to_office'];
		} elseif (isset($speedy_order_data['to_office'])) {
			$data['to_office'] = $speedy_order_data['to_office'];
		} else {
			$data['to_office'] = 0;
		}

		if (isset($this->request->post['is_apt'])) {
			$data['is_apt'] = $this->request->post['is_apt'];
		} elseif (isset($speedy_order_data['is_apt'])) {
			$data['is_apt'] = $speedy_order_data['is_apt'];
		} else {
			$data['is_apt'] = 0;
		}

		if (isset($_POST['postcode'])) {
			$data['postcode'] = $_POST['postcode'];
		} elseif (isset($speedy_order_data['postcode'])) {
			$data['postcode'] = $speedy_order_data['postcode'];
		} else {
			$data['postcode'] = '';
		}

		if (isset($_POST['city'])) {
			$data['city'] = $_POST['city'];
		} elseif (isset($speedy_order_data['city'])) {
			$data['city'] = $speedy_order_data['city'];
		} else {
			$data['city'] = '';
		}

		if (isset($_POST['city_id'])) {
			$data['city_id'] = $_POST['city_id'];
		} elseif (isset($speedy_order_data['city_id'])) {
			$data['city_id'] = $speedy_order_data['city_id'];
		} else {
			$data['city_id'] = 0;
		}

		if (isset($_POST['city_nomenclature'])) {
			$data['city_nomenclature'] = $_POST['city_nomenclature'];
		} elseif (isset($speedy_order_data['city_nomenclature'])) {
			$data['city_nomenclature'] = $speedy_order_data['city_nomenclature'];
		} else {
			$data['city_nomenclature'] = '';
		}

		if (isset($_POST['quarter'])) {
			$data['quarter'] = $_POST['quarter'];
		} elseif (isset($speedy_order_data['quarter'])) {
			$data['quarter'] = $speedy_order_data['quarter'];
		} else {
			$data['quarter'] = '';
		}

		if (isset($_POST['quarter_id'])) {
			$data['quarter_id'] = $_POST['quarter_id'];
		} elseif (isset($speedy_order_data['quarter_id'])) {
			$data['quarter_id'] = $speedy_order_data['quarter_id'];
		} else {
			$data['quarter_id'] = 0;
		}

		if (isset($_POST['street'])) {
			$data['street'] = $_POST['street'];
		} elseif (isset($speedy_order_data['street'])) {
			$data['street'] = $speedy_order_data['street'];
		} else {
			$data['street'] = '';
		}

		if (isset($_POST['street_id'])) {
			$data['street_id'] = $_POST['street_id'];
		} elseif (isset($speedy_order_data['street_id'])) {
			$data['street_id'] = $speedy_order_data['street_id'];
		} else {
			$data['street_id'] = 0;
		}

		if (isset($_POST['street_no'])) {
			$data['street_no'] = $_POST['street_no'];
		} elseif (isset($speedy_order_data['street_no'])) {
			$data['street_no'] = $speedy_order_data['street_no'];
		} else {
			$data['street_no'] = '';
		}

		if (isset($_POST['block_no'])) {
			$data['block_no'] = $_POST['block_no'];
		} elseif (isset($speedy_order_data['block_no'])) {
			$data['block_no'] = $speedy_order_data['block_no'];
		} else {
			$data['block_no'] = '';
		}

		if (isset($_POST['entrance_no'])) {
			$data['entrance_no'] = $_POST['entrance_no'];
		} elseif (isset($speedy_order_data['entrance_no'])) {
			$data['entrance_no'] = $speedy_order_data['entrance_no'];
		} else {
			$data['entrance_no'] = '';
		}

		if (isset($_POST['floor_no'])) {
			$data['floor_no'] = $_POST['floor_no'];
		} elseif (isset($speedy_order_data['floor_no'])) {
			$data['floor_no'] = $speedy_order_data['floor_no'];
		} else {
			$data['floor_no'] = '';
		}

		if (isset($_POST['apartment_no'])) {
			$data['apartment_no'] = $_POST['apartment_no'];
		} elseif (isset($speedy_order_data['apartment_no'])) {
			$data['apartment_no'] = $speedy_order_data['apartment_no'];
		} else {
			$data['apartment_no'] = '';
		}

		if (isset($_POST['office_id'])) {
			$data['office_id'] = $_POST['office_id'];
		} elseif (isset($speedy_order_data['office_id'])) {
			$data['office_id'] = $speedy_order_data['office_id'];
		} else {
			$data['office_id'] = 0;
		}

		if (isset($_POST['note'])) {
			$data['note'] = $_POST['note'];
		} elseif (isset($speedy_order_data['note'])) {
			$data['note'] = $speedy_order_data['note'];
		} else {
			$data['note'] = '';
		}

		if (isset($_POST['country'])) {
			$data['country'] = $_POST['country'];
		} elseif (isset($speedy_order_data['country'])) {
			$data['country'] = $speedy_order_data['country'];
		} else {
			$data['country'] = '';
		}

		if (isset($_POST['country_id'])) {
			$data['country_id'] = $_POST['country_id'];
		} elseif (isset($speedy_order_data['country_id'])) {
			$data['country_id'] = $speedy_order_data['country_id'];
		} else {
			$data['country_id'] = '';
		}

		if (isset($_POST['country_nomenclature'])) {
			$data['country_nomenclature'] = $_POST['country_nomenclature'];
		} elseif (isset($speedy_order_data['country_nomenclature'])) {
			$data['country_nomenclature'] = $speedy_order_data['country_nomenclature'];
		} else {
			$data['country_nomenclature'] = '';
		}

		if (isset($_POST['country_address_nomenclature'])) {
			$data['country_address_nomenclature'] = $_POST['country_address_nomenclature'];
		} elseif (isset($speedy_order_data['country_address_nomenclature'])) {
			$data['country_address_nomenclature'] = $speedy_order_data['country_address_nomenclature'];
		} else {
			$data['country_address_nomenclature'] = '';
		}

		if (isset($_POST['active_currency_code'])) {
			$data['active_currency_code'] = $_POST['active_currency_code'];
		} elseif (isset($speedy_order_data['active_currency_code'])) {
			$data['active_currency_code'] = $speedy_order_data['active_currency_code'];
		} else {
			$data['active_currency_code'] = '';
		}

		if (isset($_POST['required_state'])) {
			$data['required_state'] = $_POST['required_state'];
		} elseif (isset($speedy_order_data['required_state'])) {
			$data['required_state'] = $speedy_order_data['required_state'];
		} else {
			$data['required_state'] = '';
		}

		if (isset($_POST['required_postcode'])) {
			$data['required_postcode'] = $_POST['required_postcode'];
		} elseif (isset($speedy_order_data['required_postcode'])) {
			$data['required_postcode'] = $speedy_order_data['required_postcode'];
		} else {
			$data['required_postcode'] = '';
		}

		if (isset($_POST['state'])) {
			$data['state'] = $_POST['state'];
		} elseif (isset($speedy_order_data['state'])) {
			$data['state'] = $speedy_order_data['state'];
		} else {
			$data['state'] = '';
		}

		if (isset($_POST['state_id'])) {
			$data['state_id'] = $_POST['state_id'];
		} elseif (isset($speedy_order_data['state_id'])) {
			$data['state_id'] = $speedy_order_data['state_id'];
		} else {
			$data['state_id'] = '';
		}

		if (isset($_POST['address_1'])) {
			$data['address_1'] = $_POST['address_1'];
		} elseif (isset($speedy_order_data['address_1'])) {
			$data['address_1'] = $speedy_order_data['address_1'];
		} else {
			$data['address_1'] = '';
		}

		if (isset($_POST['address_2'])) {
			$data['address_2'] = $_POST['address_2'];
		} elseif (isset($speedy_order_data['address_2'])) {
			$data['address_2'] = $speedy_order_data['address_2'];
		} else {
			$data['address_2'] = '';
		}

		if (isset($_POST['fixed_time_cb'])) {
			$data['fixed_time_cb'] = $_POST['fixed_time_cb'];
		} elseif (isset($speedy_order_data['fixed_time_cb'])) {
			$data['fixed_time_cb'] = $speedy_order_data['fixed_time_cb'];
		} else {
			$data['fixed_time_cb'] = false;
		}

		if (isset($_POST['fixed_time_hour'])) {
			$data['fixed_time_hour'] = $_POST['fixed_time_hour'];
		} elseif (isset($speedy_order_data['fixed_time_hour'])) {
			$data['fixed_time_hour'] = $speedy_order_data['fixed_time_hour'];
		} else {
			$data['fixed_time_hour'] = '';
		}

		if (isset($_POST['fixed_time_min'])) {
			$data['fixed_time_min'] = $_POST['fixed_time_min'];
		} elseif (isset($speedy_order_data['fixed_time_min'])) {
			$data['fixed_time_min'] = $speedy_order_data['fixed_time_min'];
		} else {
			$data['fixed_time_min'] = '';
		}

		if ( $data['abroad'] || get_locale() != 'bg_BG' ) {
			$lang = 'en';
		} else {
			$lang = 'bg';
		}

		$data['fixed_time'] = $speedy_shipping_method->fixed_time;
		$data['ignore_obp'] = $speedy_shipping_method->ignore_obp;

		if (isset($_POST['payer_type'])) {
			$data['payer_type'] = $_POST['payer_type'];
		} elseif (isset($speedy_order_data['payer_type'])) {
			$data['payer_type'] = $speedy_order_data['payer_type'];
		} elseif (isset($speedy_order_data['shipping_method_cost'])) {
			$data['payer_type'] = $speedy_shipping_method->speedy->getPayerType($order_id, $speedy_order_data['shipping_method_cost']);
		} else {
			$data['payer_type'] = 0;
		}

		$data['offices'] = array();

		if ($data['city_id'] && $data['country_id']) {
			$data['offices'] = wp_cache_get('speedy.offices.' . md5($data['city_id'] . $lang . $data['country_id']));

			if ((empty($data) && empty($name)) || empty($name)) {
				$data['offices'] = $speedy_shipping_method->speedy->getOffices(null, $data['city_id'], $lang, $data['country_id']);
				wp_cache_set('speedy.offices.' . md5($data['city_id'] . $lang . $data['country_id']), $data);
			} else {
				$data['offices'] = $speedy_shipping_method->speedy->getOffices($name, $data['city_id'], $lang, $data['country_id']);
			}


			if ($speedy_shipping_method->speedy->getError()) {
				$data['error_office'] = $speedy_shipping_method->speedy->getError();
			}
		}

		$data['days'] = array(0, 1, 2);
		$data['taking_date'] = date_i18n('d-m-Y', ($speedy_shipping_method->taking_date ? strtotime('+' . (int) $speedy_shipping_method->taking_date . ' day', mktime(9, 0, 0)) : time()));

		$data['options_before_payment'] = array(
			'no_option' => __( 'Няма', SPEEDY_TEXT_DOMAIN ),
			'test'      => __( 'Тест', SPEEDY_TEXT_DOMAIN ),
			'open'      => __( 'Отвори', SPEEDY_TEXT_DOMAIN ),
		);

		$data['parcel_sizes'] = array(
			'XS' => 'XS',
			'S'  => 'S',
			'M'  => 'M',
			'L'  => 'L',
			'XL' => 'XL',
		);

		$cod_payment = null;
		foreach (WC()->payment_gateways->payment_gateways as $payment_gateway) {
			if ($payment_gateway->id == 'cod' ) {
				$cod_payment = $payment_gateway;
				break;
			}
		}

		if ( class_exists('WC_Gateway_COD') && $cod_payment ) {
			if ( 'yes' == $cod_payment->enabled && $speedy_order_data['cod_status'] ) {
				if ( ! empty( $cod_payment->enable_for_methods ) ) {
					foreach ( $cod_payment->enable_for_methods as $enabled_method ) {
						if (  $enabled_method == $speedy_shipping_method->id ) {
							$data['cod_status'] = true;
							break;
						}
					}
				} else {
					$data['cod_status'] = false;
				}
			} else {
				$data['cod_status'] = false;
			}
			
		} else {
			$data['cod_status'] = false;
		}

		$data['quote'] = array();

		wc_get_template( 'admin/html-speedy-generate.php',
			$data,
			'',
			plugin_dir_path(__FILE__) . '/templates/'
		);
	}

	 /**
	 * Get loading info template
	 */
	private function getSpeedyLoadingInfo($loading_info) {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		$data = array(
			'loading_num'      => $loading_info['bol_id'],
			'track_loading'    => 'http://www.speedy.bg/begin.php?shipmentNumber=' . $loading_info['bol_id'] . '&lang=' . (get_locale() == 'bg_BG' ? 'bg' : 'en'),
			'print_url'        => plugin_dir_url(__FILE__) . 'print_pdf.php?bol_id=' . $loading_info['bol_id'],
		);

		$data['print_return_voucher_requested_url'] = '';

		$return_voucher_requested = $speedy_shipping_method->speedy->checkReturnVoucherRequested($loading_info['bol_id']);
		if ($return_voucher_requested) {
			$data['print_return_voucher_requested_url'] = plugin_dir_url(__FILE__) . 'print_return_voucher.php?bol_id=' . $loading_info['bol_id'];
		}

		wc_get_template( 'admin/html-speedy-loading.php',
			$data,
			'',
			plugin_dir_path(__FILE__) . '/templates/'
		);
	}

	 /**
	 * Admin ajax check date for generate loading
	 */
	static function validate_bill_of_lading() {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		$json = array();

		// check APT office
		if (empty($_POST['abroad']) && !empty($_POST['speedy_shipping_to_office']) && !empty($_POST['speedy_option_before_payment']) && $_POST['speedy_option_before_payment'] != 'no_option' && !empty($_POST['speedy_office_id']) && !empty($_POST['speedy_city_id'])) {
			$office = $speedy_shipping_method->speedy->getOfficeById($_POST['speedy_office_id'], $_POST['speedy_city_id']);

			if(!empty($office) && $office->getOfficeType() == 3) { // 3 for APT office
				$json['error'] = true;
				$json['errors']['APT_office'] = __( 'Избраният офис е АПС и няма да се вземат под внимане опциите за ОПП, ТПП, Обратни Документи и Обратна Разписка', SPEEDY_TEXT_DOMAIN );
			}
		}

		// checkDate
		if (isset( $_POST['shipping_method_id'] )) {
			$shipping_method_id = $_POST['shipping_method_id'];
		} else {
			$shipping_method_id = '';
		}

		$taking_date = ($speedy_shipping_method->taking_date ? strtotime('+' . intval( $speedy_shipping_method->taking_date ) . ' day', mktime(9, 0, 0)) : time());
		$first_available_date = strtotime($speedy_shipping_method->speedy->getAllowedDaysForTaking(array('shipping_method_id' => $shipping_method_id, 'taking_date' => $taking_date)));

		if (!$speedy_shipping_method->speedy->getError() && $first_available_date) {
			if (date_i18n('d-m-Y', $first_available_date) != date_i18n('d-m-Y', $taking_date)) {
				$json['error'] = true;
				$json['errors']['warning'] = sprintf(__( 'Първата възможна дата за вземане на пратката е: %s. Желаете ли да създадете товарителницата?', SPEEDY_TEXT_DOMAIN ), date_i18n("d/m/Y", $first_available_date));
				$json['taking_date'] = date_i18n('d-m-Y', $first_available_date);
			}
		} else {
			$json['error'] = true;
			$json['errors']['warning'] = $speedy_shipping_method->speedy->getError();
		}

		// check BackDocumentsRequest and BackReceiptRequest
		if (!empty($shipping_method_id)) {
			$service = $speedy_shipping_method->speedy->getServiceById($shipping_method_id);

			if(!empty($service)) {
				if($service->getAllowanceBackDocumentsRequest()->getValue() == 'BANNED' && $service->getAllowanceBackReceiptRequest()->getValue() == 'BANNED') {
					$json['error'] = true;
					$json['errors']['document_receipt'] = __( 'Няма да се вземат под внимане опциите за Обратни Документи и Обратна Разписка', SPEEDY_TEXT_DOMAIN );
				} elseif($service->getAllowanceBackDocumentsRequest()->getValue() == 'BANNED') {
					$json['error'] = true;
					$json['errors']['document'] = __( 'Обратни Документи няма да се вземат под внимане', SPEEDY_TEXT_DOMAIN );
				} elseif($service->getAllowanceBackReceiptRequest()->getValue() == 'BANNED') {
					$json['error'] = true;
					$json['errors']['receipt'] = __( 'Обратна Разписка няма да се вземат под внимане', SPEEDY_TEXT_DOMAIN );
				} else {}
			}
		}

		wp_send_json( $json );
		exit;
	}

	 /**
	 * Admin ajax generate loading
	 */
	static function generate_loading() {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		$errors = $speedy_shipping_method->validateSpeedyForm(true); // If errors exits

		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty( $errors ) ) {
			if ( isset( $_POST['data'] ) ) {
				parse_str($_POST['data'], $_POST);
			}

			$_POST['taking_date'] = strtotime($_POST['taking_date']);

			$order = wc_get_order( intval( $_POST['order_id'] ) );

			$speedy = $speedy_shipping_method->_getOrderByOrderId( $order->id );
			$speedy_order_data = maybe_unserialize( $speedy['data'] );

			if ( isset( $_POST['shipping_method_id'] ) ) {
				$shipping_method_id = (int)$_POST['shipping_method_id'];
			} elseif ( isset( $speedy_order_data['shipping_method_id'] ) ) {
				$shipping_method_id = (int)$speedy_order_data['shipping_method_id'];
			} else {
				$shipping_method_id = 0;
			}

			if ($_POST['shipping_method_id'] != 500) {
				unset($_POST['parcel_size']);
			} else {
				foreach($_POST['parcels_size'] as $key => $parcel_size) {
					$_POST['parcels_size'][$key]['depth'] = '';
					$_POST['parcels_size'][$key]['height'] = '';
					$_POST['parcels_size'][$key]['width'] = '';
				}
			}

			if (!empty($_POST['fixed_time_cb'])) {
				$_POST['fixed_time'] = $_POST['fixed_time_hour'] . $_POST['fixed_time_min'];
			} else {
				$_POST['fixed_time'] = null;
			}

			if ( $shipping_method_id && isset( WC()->session->shipping_method_cost[$shipping_method_id] ) && isset( WC()->session->shipping_method_title[$shipping_method_id] )) {
				$_POST['shipping_method_id'] = $shipping_method_id;
				$_POST['shipping_method_cost'] = WC()->session->shipping_method_cost[$shipping_method_id];
				$_POST['shipping_method_title'] = WC()->session->shipping_method_title[$shipping_method_id];
			}

			if ( $order->customer_user ) {
				$user_data = get_userdata( $order->customer_user );
			}

			if ( $order->shipping_first_name ) {
				$firstname = $order->shipping_first_name;
			} else {
				$firstname = ( isset( $user_data ) && $user_data->first_name ) ? $user_data->first_name : '';
			}

			if ( $order->shipping_last_name ) {
				$lastname = $order->shipping_last_name;
			} else {
				$lastname = ( isset( $user_data ) && $user_data->last_name ) ? $user_data->last_name : '';
			}

			if ( $order->billing_email ) {
				$email = $order->billing_email;
			} else {
				$email = ( isset( $user_data ) && $user_data->user_email ) ? $user_data->user_email : '';
			}

			$order_info = array(
				'firstname'   => $firstname,
				'lastname'    => $lastname,
				'email'       => $email,
				'company'     => $order->billing_company,
				'telephone'   => $order->billing_phone,
				'order_id'    => $order->id
			);

			$data = array_merge($speedy_order_data, $_POST);

			$bol = $speedy_shipping_method->speedy->createBillOfLading($data, $order_info);

			if (!$speedy_shipping_method->speedy->getError() && $bol) {

				if (isset( $_POST['is_bol_recalculated'] ) && $_POST['is_bol_recalculated'] ) {
					$data['price_gen_method'] = $speedy_shipping_method->pricing;
				}

				$speedy_shipping_method->_editSpeedyOrder( $order->id, array( 'bol_id' => $bol['bol_id'], 'data' => $data ) );

				// Edit order shipping details 
				if ( isset ($_POST['is_bol_recalculated']) && isset ( $_POST['shipping_method_cost'] ) ) {
					$speedy_shipping_method->_updateOrderInfo( $order->id, $data, $_POST['shipping_method_cost']);
				}

				// Update order status
				$order->update_status($speedy_shipping_method->order_status_id);

				$response['status'] = true;
			} else {
				if (stripos($speedy_shipping_method->speedy->getError(), 'Not valid serviceTypeId') !== false) {
					$response['error']['error_warning'] = __( 'Моля, изчислете цената за доставка отново и при нужда изберете друг метод за доставка!', SPEEDY_TEXT_DOMAIN );
					$response['status'] = false;
				} else {
					$response['error']['error_warning'] = $speedy_shipping_method->speedy->getError();
					$response['status'] = false;
				}
			}
		} else {
			$response['error'] = $errors;
			$response['status'] = false;
		}

		wp_send_json( $response );
		exit;
	}

	/**
	 * Admin calculate price
	 */

	static function speedy_calculate_price() {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		$results = array();

		$errors = $speedy_shipping_method->validateSpeedyForm(true); // If errors exits

		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty( $errors ) ) {

			$results_speedy = $speedy_shipping_method->getQuoteAdmin();
			$results['methods'] = $results_speedy['quote'];

			if (isset($results_speedy['shipping_method_id'])) {
				$results['shipping_method_id'] = $results_speedy['shipping_method_id'];
			} else {
				$results['shipping_method_id'] = 0;
			}

			if ( isset( $results['error'] ) ) {
				$results['status'] = false;
				$results['methods'] = array();
				$results['shipping_method_id'] = 0;
				$results['error']['error_warning'] = $results['error'];

			} elseif ( isset( $results_speedy['speedy_error'] ) ) {
				$results['status'] = false;
				$results['methods'] = array();
				$results['shipping_method_id'] = 0;
				$results['error']['error_warning'] = $results_speedy['speedy_error'];
			} else {
				$results['status'] = true;

				foreach( $results['methods'] as $method ) {
					if ( $results['shipping_method_id'] == $method['code'] ) {
						$shipping_method_cost = $method['cost'];
					}
				}

				if ( isset( $shipping_method_cost ) && isset( $_POST['order_id'] ) ) {
					$results['payer_type'] = $speedy_shipping_method->speedy->getPayerType($_POST['order_id'], $shipping_method_cost, true);
				}else {
					$results['payer_type'] = false;
				}
			}
		} else {
			$results['status'] = false;
			$results['methods'] = array();
			$results['shipping_method_id'] = 0;
			$results['error'] = $errors;
		}

		wp_send_json( $results );
		exit();
	}

	 /**
	 * Admin ajax cancel loading
	 */
	static function cancel_loading() {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();

		if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset( $_POST['bol_id'] ) && $_POST['bol_id']) {
			$loading_info = $speedy_shipping_method->_getOrderByOrderIdByBolId( $_POST['bol_id'] );
			if ($loading_info) {
				$result = $speedy_shipping_method->cancelLoading($loading_info);
				if ( empty( $result ) ) {
					$response['status'] = true;
					$response['success'] = __( 'Готово, отказахте товарителница!', SPEEDY_TEXT_DOMAIN );
				} else {
					$response['status'] = false;
					$response['warning'] = $result['warning'];
				}
			}
		}

		wp_send_json( $response );
	}

	 /**
	 * Custom function - cancel loading
	 */
	public function cancelLoading($speedy_order_info) {
		$this->speedy = SpeedyEpsLib::getInstance();
		$error = array();

		if (!empty($speedy_order_info) && !empty($speedy_order_info['bol_id'])) {
			$cancelled = $this->speedy->cancelBol($speedy_order_info['bol_id']);

			if (!$this->speedy->getError() && $cancelled) {
				$this->_deleteOrder( $speedy_order_info['order_id'] );
			} else {
				$error['warning'] = $this->speedy->getError();
			}
		} else {
			$error['warning'] = __( 'Внимание: Товарителницата не съществува!', SPEEDY_TEXT_DOMAIN );
		}

		return $error;
	}

	 /**
	 * Custom function - request for courier
	 */
	public function RequestForCourier( $bol_ids = array() ) {
		$this->speedy = SpeedyEpsLib::getInstance();
		$error = array();

		if ($bol_ids) {
			$results = $this->speedy->requestCourier($bol_ids);

			if (!$this->speedy->getError()) {
				$error = array();

				foreach ($results as $result) {
					if (!$result->getErrorDescriptions()) {
						$this->_editOrderCourier($result->getBillOfLading(), true);
					} else {
						$error[] = $result->getBillOfLading() . ' - ' . implode(', ', $result->getErrorDescriptions());
					}
				}

				if ($error) {
					$error['warning'] = implode('<br />', $error);
				} else {
					$error['success'] = sprintf( __( 'Готово, заявихте куриер за товарителница/и: %s !', SPEEDY_TEXT_DOMAIN ), implode(', ', $bol_ids) );
				}
			} else {
				$error['warning'] = $this->speedy->getError();
			}
		} else {
			$error['warning'] = __( 'Няма избрани товарителници!', SPEEDY_TEXT_DOMAIN );
		}

		return $error;
	}

	 /**
	 * Admin ajax
	 * Update speedy order data after save order items
	 */
	public function after_save_order_items() {
		$response = array();
		if ( isset( $_POST['order_id'] ) ) {
			$speedy_shipping_method = new WC_Speedy_Shipping_Method();

			$order = wc_get_order( intval( $_POST['order_id'] ) );

			$speedy = $speedy_shipping_method->_getOrderByOrderId( $order->id );
			$speedy_order_data = maybe_unserialize( $speedy['data'] );

			$weight = 0;
			$totalNoShipping = 0;
			$total = $speedy_order_data['total'];
	
			$total = $order->get_total() - $order->get_total_shipping();

			foreach($order->get_items() as $cart_item ) {
				$_product = $cart_item['data'];

				if (!$_product->is_downloadable()) {
					$product_weight = (float)$_product->get_weight();
					$product_weight = wc_get_weight($product_weight, 'kg');

					if (!empty($product_weight)) {
						$weight += $product_weight * $cart_item['qty'];
					} else {
						$weight += $this->default_weight * $cart_item['qty'];
					}

					$totalNoShipping += $_product->get_price_including_tax($cart_item['qty']);
				}
			}

			if ($speedy_shipping_method->documents && (float)$weight > 0.25) {
				$weight = 0.25;
			}

			$speedy_order_data['total'] = $total;
			$speedy_order_data['weight'] = $weight;
			$speedy_order_data['totalNoShipping'] = $totalNoShipping;

			if ( ! $speedy['bol_id'] ) {
				$speedy_shipping_method->_editSpeedyOrder($order->id, array('bol_id' => '', 'data' => $speedy_order_data));
			}

			$response = array(
				'status'          => true,
				'total'           => $total,
				'weight'          => $weight,
				'totalNoShipping' => $totalNoShipping,
			);
		} else {
			$response['status'] = false;
		}

		wp_send_json( $response );
	}

	public function getQuoteAdmin() {
		if ( ! isset( $_POST['order_id'] ) || ! $_POST['order_id'] || 'shop_order' != get_post_type( $_POST['order_id'] ) ) {
			return false;
		}

		$this->speedy = SpeedyEpsLib::getInstance();

		$speedy_order_info = $this->_getOrderByOrderId( $_POST['order_id'] );
		$speedy_order_data = maybe_unserialize($speedy_order_info['data']);

		parse_str($_POST['data'], $_POST);

		if ( isset ( $_POST['shipping_method_id'] ) ) {
			$method_id = $_POST['shipping_method_id'];
		} elseif ( isset( $speedy_order_data['shipping_method_id'] ) ) {
			$method_id = $speedy_order_data['shipping_method_id'];
		} else {
			$method_id = 0;
		}

		$quote_data = array();

		$method_data = array();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!isset($_POST['postcode'])) {
				$_POST['postcode'] = '';
			}
			if (!isset($_POST['city'])) {
				$_POST['city'] = '';
			}
			if (!isset($_POST['city_id'])) {
				$_POST['city_id'] = 0;
			}
			if (!isset($_POST['city_nomenclature'])) {
				$_POST['city_nomenclature'] = '';
			}
			if (!isset($_POST['to_office'])) {
				$_POST['to_office'] = 0;
			}
			if (!isset($_POST['office_id'])) {
				$_POST['office_id'] = 0;
			}
			if (!isset($_POST['quarter'])) {
				$_POST['quarter'] = '';
			}
			if (!isset($_POST['quarter_id'])) {
				$_POST['quarter_id'] = 0;
			}
			if (!isset($_POST['street'])) {
				$_POST['street'] = '';
			}
			if (!isset($_POST['street_id'])) {
				$_POST['street_id'] = 0;
			}
			if (!isset($_POST['street_no'])) {
				$_POST['street_no'] = '';
			}
			if (!isset($_POST['block_no'])) {
				$_POST['block_no'] = '';
			}
			if (!isset($_POST['entrance_no'])) {
				$_POST['entrance_no'] = '';
			}
			if (!isset($_POST['floor_no'])) {
				$_POST['floor_no'] = '';
			}
			if (!isset($_POST['apartment_no'])) {
				$_POST['apartment_no'] = '';
			}
			if (!isset($_POST['note'])) {
				$_POST['note'] = '';
			}
			if (!isset($_POST['country'])) {
				$_POST['country'] = '';
			}
			if (!isset($_POST['country_id'])) {
				$_POST['country_id'] = 0;
			}
			if (!isset($_POST['country_nomenclature'])) {
				$_POST['country_nomenclature'] = '';
			}
			if (!isset($_POST['state'])) {
				$_POST['state'] = '';
			}
			if (!isset($_POST['state_id'])) {
				$_POST['state_id'] = '';
			}
			if (!isset($_POST['required_state'])) {
				$_POST['required_state'] = 0;
			}
			if (!isset($_POST['required_postcode'])) {
				$_POST['required_postcode'] = 0;
			}
			if (!isset($_POST['address_1'])) {
				$_POST['address_1'] = '';
			}
			if (!isset($_POST['address_2'])) {
				$_POST['address_2'] = '';
			}

			if (!isset($_POST['abroad'])) {
				$abroad = $_POST['abroad'];
			} elseif (isset($speedy_order_data['abroad'])) {
				$abroad = $speedy_order_data['abroad'];
			} else {
				$abroad = 0;
			}

			if ( $abroad || get_locale() != 'bg_BG' ) {
				$lang = 'en';
			} else {
				$lang = 'bg';
			}

			$total = $_POST['total'];
			$totalNoShipping = $_POST['totalNoShipping'];

			$weight = $_POST['weight'];
			$count = $_POST['count'];

			if ($this->documents && (float)$weight > 0.25) {
				$weight = 0.25;
			}

			$data['total'] = $total;
			$data['loading'] = true;

			if ( $_POST['insurance'] ) {
				$data['totalNoShipping'] = $totalNoShipping;
			} else {
				$data['totalNoShipping'] = 0;
			}

			$data['weight'] = $weight;
			$data['count'] = $count;
			$data['taking_date'] = ($this->taking_date ? strtotime('+' . (int)$this->taking_date . ' day', mktime(9, 0, 0)) : time());

			$cod_payment = null;
			foreach (WC()->payment_gateways->payment_gateways as $payment_gateway) {
				if ($payment_gateway->id == 'cod' ) {
					$cod_payment = $payment_gateway;
					break;
				}
			}

			if ( class_exists('WC_Gateway_COD') && $cod_payment ) {
				if ( 'yes' == $cod_payment->enabled ) {
					if ( ! empty( $cod_payment->enable_for_methods ) ) {
						foreach ( $cod_payment->enable_for_methods as $enabled_method ) {
							if (  $enabled_method == $this->id ) {
								$data['cod_status'] = true;
								break;
							}
						}
					} else {
						$data['cod_status'] = false;
					}
				} else {
					$data['cod_status'] = false;
				}
			} else {
				$data['cod_status'] = false;
			}

			if (isset($speedy_data['cod'])) {
				$data['cod'] = $speedy_data['cod'];
			} else {
				$data['cod'] = 0;
			}

			if (!empty($_POST['fixed_time_cb'])) {
				$data['fixed_time'] = $_POST['fixed_time_hour'] . $_POST['fixed_time_min'];
			} else {
				$data['fixed_time'] = null;
			}

			$_POST['taking_date'] = strtotime($_POST['taking_date']);

			if (!empty($method_id) && $method_id != 500) {
				unset($_POST['parcel_size']);
			} else {
				foreach ($_POST['parcels_size'] as $key => $parcel_size) {
					$_POST['parcels_size'][$key]['depth'] = '';
					$_POST['parcels_size'][$key]['height'] = '';
					$_POST['parcels_size'][$key]['width'] = '';
				}
			}

			$methods = $this->speedy->calculate(array_merge($data, $_POST));

			$services = $this->speedy->getServices( $lang );
			$methods_count = 0;

			if (!$this->speedy->getError()) {
				foreach ($methods as $method) {
					$total_form = array();

					if (!$method->getErrorDescription()) {
						if ( ( $this->pricing == 'free' ) && ( $data['total'] >= (float)$this->free_shipping_total ) &&
							( $method->getServiceTypeId() == $this->free_method_city || $method->getServiceTypeId() == $this->free_method_intercity || in_array( $method->getServiceTypeId(), (array)$this->free_method_international ) ) ) {
							$method_total = 0;
						} elseif ( $this->pricing == 'fixed' ) {
							$method_total = $this->fixed_price;
						} elseif ( $this->pricing == 'table_rate' ) {
							$filter_data = array(
								'service_id' => $method->getServiceTypeId(),
								'take_from_office' => $_POST['to_office'],
								'weight' => $data['weight'],
								'order_total' => $data['total'],
								'fixed_time_delivery' => isset($_POST['fixed_time_cb']) ? 1 : 0,
							);

							$speedy_table_rate = $this->_getSpeedyTableRate( $filter_data );

							if (empty($speedy_table_rate)) {
								continue;
							} else {
								$method_total = $speedy_table_rate['price_without_vat'];
							}
						} else {
							$method_total = $method->getResultInfo()->getAmounts()->getTotal();

							if ( $this->pricing == 'calculator_fixed' ) {
								$method_total += $this->fixed_price;
							}

							$vat_fixedTimeDelivery = round(0.2 * $method->getResultInfo()->getAmounts()->getFixedTimeDelivery(), 2);
							$vat_codPremium = round(0.2 * $method->getResultInfo()->getAmounts()->getCodPremium(), 2);

							$total_form[] = array(
								'label' => __( 'Стойност', SPEEDY_TEXT_DOMAIN ),
								'value' => wc_price( (float)($method_total - ($method->getResultInfo()->getAmounts()->getCodPremium() + $vat_codPremium) - ($method->getResultInfo()->getAmounts()->getFixedTimeDelivery() + $vat_fixedTimeDelivery)) )
							);

							if (isset($_POST['fixed_time_cb'])) {
								$total_form[] = array(
									'label' => __( "Надбавка 'Фиксиран час'", SPEEDY_TEXT_DOMAIN ),
									'value' => wc_price( (float)$method->getResultInfo()->getAmounts()->getFixedTimeDelivery() + $vat_fixedTimeDelivery )
								);
							}

							if ($_POST['cod']) {
								$total_form[] = array(
									'label' => __( "Комисиона 'Нал. платеж'", SPEEDY_TEXT_DOMAIN ),
									'value' => wc_price( (float)$method->getResultInfo()->getAmounts()->getCodPremium() + $vat_codPremium )
								);
							}

							$total_form[] = array(
								'label' =>__( 'Всичко', SPEEDY_TEXT_DOMAIN ),
								'value' => wc_price( (float)$method_total )
							);
						}

						$shipping_method_cost[$method->getServiceTypeId()] = $method_total;
						$shipping_method_title[$method->getServiceTypeId()] = $services[$method->getServiceTypeId()];

						$quote_data[] = array(
							'code'           => $method->getServiceTypeId(),
							'title'          => __( 'Спиди', SPEEDY_TEXT_DOMAIN ) . ' - ' . $services[$method->getServiceTypeId()],
							'cost'           => $method_total,
							'total_form'     => isset($total_form) ? $total_form : array(),
							'tax_class_id'   => 0,
							'text'           => wc_price( $method_total )
						);

						$methods_count++;
					}
				}

				WC()->session->shipping_method_cost = isset( $shipping_method_cost ) ? $shipping_method_cost : array();
				WC()->session->shipping_method_title = isset( $shipping_method_title ) ? $shipping_method_title : array();

				if ($methods_count) {
					unset($quote_data['speedy']);
					$method_data['quote'] = $quote_data;
					$method_data['shipping_method_id'] = $method_id;
				} elseif ( !$methods_count && $this->pricing == 'table_rate' ) {
					$method_data['speedy_error'] = __( 'За тази поръчка не може да бъде калкулирана цена. Моля обърнете се към администраторите на магазина!', SPEEDY_TEXT_DOMAIN );
				} else {
					$method_data['speedy_error'] = __( 'Моля, изберете друг офис или променете адреса за доставка!', SPEEDY_TEXT_DOMAIN );
				}
			} else {
				$method_data['speedy_error'] = $this->speedy->getError();
			}
		} else {
			$method_data['speedy_error'] = __( 'Моля, изчислете цената за доставка отново и при нужда изберете друг метод за доставка!', SPEEDY_TEXT_DOMAIN );
		}

		if (isset($method_data['speedy_error'])) {
			$method_data['quote']['speedy']['text'] = '';
		}

		return $method_data;
	}

	/**
	 * Database functions
	 */

	/**
	 * Getting speedy weight dimensions
	 */
	private function getWeightDimensions() {
		if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['woocommerce_speedy_shipping_method_enabled'])) {
			if (isset($_POST['speedy_shipping_method_weight_dimensions'])) {
				return $_POST['speedy_shipping_method_weight_dimensions'];
			} else {
				return array();
			}
		}

		global $wpdb;
		$weight_dimensions = array();

		$query = $wpdb->get_row( "SHOW TABLES LIKE '" . $wpdb->prefix . "speedy_weight_dimensions'" );

		if (!empty($query)) {
			return $wpdb->get_results( "SELECT * FROM `" . $wpdb->prefix . "speedy_weight_dimensions`", ARRAY_A);
		}

		return $weight_dimensions;
	}

	private function addWeightDimensions( $data ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'speedy_weight_dimensions';
		$wpdb->query( 'DELETE FROM ' . $table_name );

		foreach ($data as $value) {
			$wpdb->insert(
				$table_name,
				array(
					'WEIGHT' => $value['WEIGHT'],
					'XS' => $value['XS'],
					'S' => $value['S'],
					'M' => $value['M'],
					'L' => $value['L'],
					'XL' => $value['XL'],
				),
				array(
					'%f',
					'%f',
					'%f',
					'%f',
					'%f',
					'%f',
				)
			);
		}
	}

	/**
	 * Adding speedy address
	 */
	private function _addSpeedyAddress( $data ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'speedy_address';
		$wpdb->delete( $table_name, array( 'customer_id' => get_current_user_id() ) );

		$wpdb->insert(
			$table_name,
			array(
				'customer_id'          => get_current_user_id(),
				'postcode'             => $data['postcode'],
				'city'                 => $data['city'],
				'city_id'              => $data['city_id'],
				'city_nomenclature'    => $data['city_nomenclature'],
				'to_office'            => $data['to_office'],
				'office_id'            => $data['office_id'],
				'quarter'              => $data['quarter'],
				'quarter_id'           => $data['quarter_id'],
				'street'               => $data['street'],
				'street_id'            => $data['street_id'],
				'street_no'            => $data['street_no'],
				'block_no'             => $data['block_no'],
				'entrance_no'          => $data['entrance_no'],
				'floor_no'             => $data['floor_no'],
				'apartment_no'         => $data['apartment_no'],
				'note'                 => $data['note'],
				'country'              => $data['country'],
				'country_id'           => $data['country_id'],
				'country_nomenclature' => $data['country_nomenclature'],
				'state'                => $data['state'],
				'state_id'             => $data['state_id'],
				'required_state'       => $data['required_state'],
				'required_postcode'    => $data['required_postcode'],
				'address_1'            => $data['address_1'],
				'address_2'            => $data['address_2'],
				'abroad'               => $data['abroad'],
			),
			array(
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
			)
		);
	}

	/**
	 * Getting speedy address
	 */
	private function _getSpeedyAddress( $customer_id ) {
		global $wpdb;
		$mylink = array();

		$table_name = $wpdb->prefix . 'speedy_address';

		$query = $wpdb->prepare( "SELECT * FROM `" . $table_name . "` WHERE customer_id = '%d'", $customer_id );
		$mylink = $wpdb->get_row($query, ARRAY_A);

		return $mylink;
	}

	/**
	 * Add speedy order
	 */
	public function _addOrder( $order_id, $data ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'speedy_order';

		$wpdb->insert(
			$table_name,
			array(
				'order_id' => intval( $order_id ),
				'data'     => maybe_serialize( $data ),
			),
			array(
				'%d',
				'%s',
			)
		);
	}

	public function _getNotFinalizedOrders() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'speedy_order';
		$speedy_order_columns = $wpdb->get_results("SHOW COLUMNS FROM `" . $table_name . "` LIKE 'is_final'");

		if (empty($speedy_order_columns)) {
			$wpdb->query("ALTER TABLE `" . $table_name . "` ADD `is_final` TINYINT(1) NOT NULL DEFAULT '0';");
		}

		$rows = $wpdb->get_results( "SELECT bol_id FROM `" . $table_name . "` WHERE is_final = '0' AND bol_id IS NOT NULL AND bol_id != ''" );

		$results = array();
		$results['num_rows'] = $wpdb->num_rows;
		$results['bol_ids'] = array();

		foreach($rows as $row) {
			$results['bol_ids'][] = $row->bol_id;
		}

		return $results;
	}

	public function _setFinalizedOrder($bolId) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'speedy_order';

		$wpdb->query( "UPDATE `" . $table_name . "`SET is_final = '1' WHERE bol_id = " . (int)$bolId );
	}

	/**
	 * Get speedy order id
	 */
	public function _getOrderById( $speedy_order_id ) {
		global $wpdb;
		$result = array();

		$table_name = $wpdb->prefix . 'speedy_order';

		$query = $wpdb->prepare( "SELECT * FROM `" . $table_name . "` WHERE speedy_order_id = '%d'", $speedy_order_id );
		$result = $wpdb->get_row($query, ARRAY_A);

		return $result;
	}

	/**
	 * Get speedy order by order id
	 */
	public function _getOrderByOrderId( $order_id ) {
		global $wpdb;
		$result = array();

		$table_name = $wpdb->prefix . 'speedy_order';

		$query = $wpdb->prepare( "SELECT * FROM `" . $table_name . "` WHERE order_id = '%d'", $order_id );
		$result = $wpdb->get_row($query, ARRAY_A);

		return $result;
	}

	/**
	 * Get speedy order by bol id
	 */
	public function _getOrderByOrderIdByBolId( $bol_id ) {
		global $wpdb;
		$result = array();

		$table_name = $wpdb->prefix . 'speedy_order';

		$query = $wpdb->prepare( "SELECT * FROM `" . $table_name . "` WHERE bol_id = '%s'", $bol_id );
		$result = $wpdb->get_row($query, ARRAY_A);

		return $result;
	}

	 /**
	 * Edit speedy order
	 */
	public function _editSpeedyOrder( $order_id, $data ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'speedy_order';

		$wpdb->update(
			$table_name,
			array(
				'bol_id'       => $data['bol_id'],
				'data'         => maybe_serialize( $data['data'] ),
				'date_created' => date_i18n('Y-m-d H:i:s'),
			),
			array(
				'order_id'     => $order_id,
			),
			array(
				'%s',
				'%s',
				'%s',
			),
			array(
				'%d'
			)
		);
	}

	 /**
	 * Edit speedy order
	 */
	public function _deleteOrder( $order_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'speedy_order';

		$wpdb->update(
			$table_name,
			array(
				'bol_id'       => '',
				'date_created' => '0000-00-00 00:00:00',
				'courier'      => '0',
			),
			array(
				'order_id'     => $order_id,
			),
			null,
			array(
				'%d'
			)
		);
	}

	 /**
	 * Request Courier
	 */
	public function _editOrderCourier( $bol_id, $courier ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'speedy_order';

		$wpdb->update(
			$table_name,
			array(
				'courier'       => $courier,
			),
			array(
				'bol_id'     => $bol_id,
			),
			array(
				'%d'
			),
			array(
				'%s'
			)
		);
	}

	 /**
	 * Import Speedy table rate methods from file
	 */
	public function _importFilePrice( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'speedy_table_rate';

		$wpdb->query("TRUNCATE " . $table_name);

		foreach ($data as $row) {
			$wpdb->insert(
				$table_name,
				array(
					'service_id'          => $row['service_id'],
					'take_from_office'    => $row['take_from_office'],
					'weight'              => $row['weight'],
					'order_total'         => $row['order_total'],
					'price_without_vat'   => $row['price_without_vat'],
					'fixed_time_delivery' => $row['fixed_time_delivery'],
				),
				array(
					'%d',
					'%d',
					'%f',
					'%f',
					'%f',
					'%d',
				)
			);
		}
	}

	 /**
	 * Get Speedy table rate methods
	 */
	public function _getSpeedyTableRate( $data ) {
		global $wpdb;
		$result = array();

		$table_name = $wpdb->prefix . 'speedy_table_rate';

		$query = $wpdb->prepare( "SELECT price_without_vat FROM `" . $table_name . "` WHERE service_id = '%d' AND take_from_office = '%d' AND weight >= '%f' AND order_total >= '%f' AND fixed_time_delivery = '%d'",
			$data['service_id'],
			$data['take_from_office'],
			$data['weight'],
			$data['order_total'],
			$data['fixed_time_delivery']
		);
		$result = $wpdb->get_row($query, ARRAY_A);

		return $result;
	}

	 /**
	 * Update Order Meta shipping details
	 */
	public function _updateOrderInfo( $order_id, $data = array(), $shipping_cost = null ) {
		global $wpdb;

		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		$speedy_receiver_address = array();

		if (!isset($data['abroad']) || !$data['abroad']) {
			if ( ! $data['to_office'] ) {
				if ( $data['quarter'] ) {
					$speedy_receiver_address[] = $data['quarter'];
				}

				if ( $data['street'] ) {
					$speedy_receiver_address[] = $data['street'];
				}

				if ( $data['street_no'] ) {
					$speedy_receiver_address[] = __( '№:', SPEEDY_TEXT_DOMAIN ) . ' ' . $data['street_no'];
				}

				if ( $data['block_no'] ) {
					$speedy_receiver_address[] = __( 'Бл.:', SPEEDY_TEXT_DOMAIN ) . ' ' . $data['block_no'];
				}

				if ( $data['entrance_no'] ) {
					$speedy_receiver_address[] = __( 'Вх.:', SPEEDY_TEXT_DOMAIN ) . ' ' . $data['entrance_no'];
				}

				if ( $data['floor_no'] ) {
					$speedy_receiver_address[] = __( 'Ет.:', SPEEDY_TEXT_DOMAIN ) . ' ' . $data['floor_no'];
				}

				if ( $data['apartment_no'] ) {
					$speedy_receiver_address[] = __( 'Ап.:', SPEEDY_TEXT_DOMAIN ) . ' ' . $data['apartment_no'];
				}
			} else {
				$speedy_offices = $speedy_shipping_method->speedy->getOffices(null, $data['city_id']);
				foreach ( $speedy_offices as $speedy_office ) {
					if ( $speedy_office['id'] == (int)$data['office_id'] ) {
							$speedy_receiver_address[] = $speedy_office['label'];
						}
					}
				}

			if ( $data['note'] ) {
				$speedy_receiver_address[] = __( 'Забележка към адреса:', SPEEDY_TEXT_DOMAIN ) . ' ' . $data['note'];
			}

			update_post_meta( $order_id, '_shipping_address_1', implode(', ', $speedy_receiver_address) );

			if ( $data['city'] ) {
				update_post_meta( $order_id, '_shipping_city', $data['city'] );
			}

			if ( $data['postcode'] ) {
				update_post_meta( $order_id, '_shipping_postcode', $data['postcode'] );
			}

			update_post_meta( $order_id, '_shipping_country', 'BG' ); //Bulgaria
			update_post_meta( $order_id, '_shipping_state', '' ); //Sofia - town
		} else {
			if ( $data['country'] ) {
				update_post_meta( $order_id, '_shipping_country', $data['country'] );
			}
			if ( $data['state'] ) {
				update_post_meta( $order_id, '_shipping_state', $data['state'] );
			}
			if ( $data['city'] ) {
				update_post_meta( $order_id, '_shipping_city', $data['city'] );
			}
			if ( $data['postcode'] ) {
				update_post_meta( $order_id, '_shipping_postcode', $data['postcode'] );
			}
			if ( $data['address_1'] ) {
				update_post_meta( $order_id, '_shipping_address_1', $data['address_1'] );
			}
			if ( $data['address_2'] ) {
				update_post_meta( $order_id, '_shipping_address_2', $data['address_2'] );
			}
		}

		if ( ! is_null( $shipping_cost ) ) {
			$old_shipping_value = floatval( get_post_meta( $order_id, '_order_shipping', true ) );
			$order_total = floatval( get_post_meta( $order_id, '_order_total', true ) ) - $old_shipping_value + $shipping_cost;

			$order = wc_get_order( intval( $order_id ) );

			$order_shipping_meta_item_id = key( $order->get_items( 'shipping' ) );

			if ( ! $this->invoice_courrier_sevice_as_text ) {
				update_post_meta( $order_id, '_order_shipping', $shipping_cost );
				update_post_meta( $order_id, '_order_total', $order_total );

				wc_update_order_item_meta( $order_shipping_meta_item_id, 'cost', $shipping_cost );
			} else {
				$allowed_pricings = array(
					'calculator',
					'free',
					'calculator_fixed'
				);

				if ( isset( $data['cod'] ) && $data['cod'] && in_array( $this->pricing, $allowed_pricings )) {
					if ( $this->pricing == 'free' ) {
						$delta = 0.0001;
						if( abs( $data['cod'] - 0.0000 ) > $delta ) {
								wc_update_order_item( $order_shipping_meta_item_id, array('order_item_name' => sprintf( __( '%s (%s дължими при доставка)', SPEEDY_TEXT_DOMAIN ), $speedy_shipping_method->title, strip_tags( wc_price( $shipping_cost ) ) )) );
						}
					} else {
						wc_update_order_item( $order_shipping_meta_item_id, array('order_item_name' => sprintf( __( '%s (%s дължими при доставка)', SPEEDY_TEXT_DOMAIN ), $speedy_shipping_method->title, strip_tags( wc_price( $shipping_cost ) ) )) );
					}
				} else {
					update_post_meta( $order_id, '_order_shipping', $shipping_cost );
					update_post_meta( $order_id, '_order_total', $order_total );

					wc_update_order_item_meta( $order_shipping_meta_item_id, 'cost', $shipping_cost );
				}
			}
		}

		return;
	}

	public function convertSpeedyPrice($value, $from = 1, $to = 1, $format = false) {
		$f = false;
		$t = false;
		if (!empty($this->currency_rate)) {
			foreach($this->currency_rate as $currency) {
				if ($currency['iso_code'] === $from) {
					$from = $currency['rate'];
					$f = true;
				}
				if ($currency['iso_code'] === $to) {
					$to = $currency['rate'];
					$t = true;
				}
			}
		}

		if (!$f) {
			$from = 1;
		}

		if (!$t) {
			$to = 1;
		}

		if ($format) {
			return wc_price( $value * ($to / $from) );
		} else {
			return $value * ($to / $from);
		}
	}

	private function getSpeedyQuantityDimention($product_id, $product_quantity) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT * FROM ". $wpdb->prefix . "speedy_product_settings WHERE product_id = '%d'", $product_id );
		$data = $wpdb->get_row($query, ARRAY_A);

		if ($data) {
			$sizes = unserialize($data['quantity_dimentions']);

			uasort($sizes, array('WC_Speedy_Shipping_Method', 'cmp'));

			foreach ($sizes as $size => $quantity) {
				if ($quantity >= $product_quantity) {
					return array(
								'size'     => $size,
								'sizes'    => $sizes,
							);
				}
			}

			return false;
		} else {
			return false;
		}
	}

	private function speedyHasQuantityDimention($product_id) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT * FROM ". $wpdb->prefix . "speedy_product_settings WHERE product_id = '%d'", $product_id );
		$data = $wpdb->get_row($query, ARRAY_A);

		return !empty($data);
	}

	private function getSpeedyWeightDimention($weight, $product_quantity) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT " . implode(',', $this->parcel_sizes) . " FROM `" . $wpdb->prefix . "speedy_weight_dimensions` WHERE WEIGHT >= '%d' ORDER BY WEIGHT DESC LIMIT 1", $weight );
		$sizes = $wpdb->get_row($query, ARRAY_A);

		if ($sizes) {
			uasort($sizes, array('WC_Speedy_Shipping_Method', 'cmp'));

			foreach($sizes as $size => $quantity) {
				if($quantity >= $product_quantity) {
					return $size;
				}
			}
		} else {
			return false;
		}
	}

	// sorts the array by quantity without deleting the keys
	private function cmp($a, $b) {
		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}

	private function compareSizes($current_size, $compare_size) {
		if (!in_array($current_size, $this->parcel_sizes) || !in_array($compare_size, $this->parcel_sizes)) {
			return false;
		}

		if (array_search($current_size, $this->parcel_sizes) < array_search($compare_size, $this->parcel_sizes)) {
			return $compare_size;
		} else {
			return $current_size;
		}
	}

	private function calculateSize($products, $size_compare) {
		if(!empty($products)) {
			for ($i = 1;$i <= count($this->parcel_sizes); $i++) {
				$parcel_full = 0;

				foreach ($products as $product) {
					if (!empty($product['sizes'])) {
						$parcel_full += $product['quantity'] / $product['sizes'][$size_compare];
					}
				}

				if ($parcel_full > 1) {
					$next_size = array_search($size_compare, $this->parcel_sizes) + 1;

					if (isset($this->parcel_sizes[$next_size])) {
						$size_compare = $this->parcel_sizes[$next_size];
					} else {
						$size_compare = '';
						break;
					}
				} else {
					break;
				}
			}
		}

		return $size_compare;
	}
}
