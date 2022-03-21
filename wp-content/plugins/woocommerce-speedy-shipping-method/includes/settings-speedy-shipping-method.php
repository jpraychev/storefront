<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Settings for Your Shipping method
 */

$this->speedy = SpeedyEpsLib::getInstance();

$hours = array();
for ($i = 1; $i <= 24; $i++) {
	$hours[str_pad($i, 2, '0', STR_PAD_LEFT)] = str_pad($i, 2, '0', STR_PAD_LEFT);
}
$minutes = array();
for ($i = 0; $i <= 59; $i++) {
	$minutes[str_pad($i, 2, '0', STR_PAD_LEFT)] = str_pad($i, 2, '0', STR_PAD_LEFT);
}

$lang = (get_locale() == 'bg_BG') ? 'bg' : 'en';

$services_select = array();
$services = $this->speedy->getServices( $lang );

foreach ($services as $service_id => $service) {
	$services_select[$service_id] = $service . ' (' . sprintf( __( 'ID: %s', SPEEDY_TEXT_DOMAIN ), $service_id) . ')';
}

$available_money_transfer = $this->speedy->isAvailableMoneyTransfer();

$offices = array();
foreach ($this->speedy->getOffices( null, null, $lang ) as $office) {
	$offices[$office['id']] = $office['label'];
}

$currency_code_options = array();
$allowed_currencies = array('BGN', 'RON', 'EUR', 'USD');

foreach ( get_woocommerce_currencies() as $code => $name ) {
	if (in_array($code, $allowed_currencies)) {
		$currency_code_options[ $code ] = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
	}
}

$speedy_options = get_option('woocommerce_speedy_shipping_method_settings');
$currency_rate = array();

if (!empty($speedy_options['currency_rate'])) {
	foreach ($speedy_options['currency_rate'] as $key => $value) {
		$currency_rate[$key] = array(
			'iso_code' => $value['iso_code'],
			'rate'     => $value['rate'],
		);
	}
}

$final_statuses = $this->final_statuses;
if (!empty($speedy_options['final_statuses'])) {
	$final_statuses = $speedy_options['final_statuses'];
}

$weight_dimensions = $this->getWeightDimensions();

$clients = $this->speedy->getListContractClients();
$contact_clients = array();

if (count($clients) > 1) {
	$contact_clients[0] = __( 'Без опция', SPEEDY_TEXT_DOMAIN );
}

foreach ($clients as $client) {
	$contact_clients[(string)$client['clientId']] = sprintf(__( 'ID: %s, Име: %s, Адрес: %s', SPEEDY_TEXT_DOMAIN ), $client['clientId'], $client['name'], $client['address']);
}

$this->form_fields = array(
	'system_requirements' => array(
		'title'        => __( 'Минимални системни изисквания за работа на модула за доставка:', SPEEDY_TEXT_DOMAIN ),
		'type'         => 'system_requirements'
	),
	'version' => array(
		'title'        => __( 'Версия:', SPEEDY_TEXT_DOMAIN ),
		'type'         => 'version',
		'description'  => $this->version
	),
	'enabled' => array(
		'title'   => __( 'Статус: <span style="color: #f00;">*</span>', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'checkbox',
		'label'   => __( 'Включи Спиди', SPEEDY_TEXT_DOMAIN ),
		'default' => 'no'
	),
	'title' => array(
		'title'       => __( 'Име:', SPEEDY_TEXT_DOMAIN ),
		'desc_tip' => __( 'Името което потребителя ще вижда при плащане.', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
		'default'     => __( 'Спиди', SPEEDY_TEXT_DOMAIN ),
	),
	'server_address' => array(
		'title'       => __( 'Адрес на сървъра:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
		'default'     => 'https://www.speedy.bg/eps/main01.wsdl'
	),
	'username' => array(
		'title'       => __( 'Потребителско име: <span style="color: #f00;">*</span>', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
	),
	'password' => array(
		'title'       => __( 'Парола: <span style="color: #f00;">*</span>', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'password',
	),
	'name' => array(
		'title'       => __( 'Лице за контакти: <span style="color: #f00;">*</span>', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
	),
	'telephone' => array(
		'title'       => __( 'Телефон: <span style="color: #f00;">*</span>', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
	),
	'workingtime_end_hour' => array(
		'title'       => __( 'Край на работното време:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => $hours,
	),
	'workingtime_end_min' => array(
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => $minutes,
	),
	'allowed_methods' => array(
		'title'       => __( 'Позволени методи: <span style="color: #f00;">*</span>', SPEEDY_TEXT_DOMAIN ),
		'desc_tip' => __( 'Използвай CTRL за избор на повече от един метод.', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'multiselect',
		// 'class'       => 'wc-enhanced-select',
		'options'     => $services_select,
		'css'         => 'width: 25em; height: 10em;',
	),
	'services_buttons' => array(
		'title_1'         => __( 'Маркирайте всички', SPEEDY_TEXT_DOMAIN ),
		'title_2'         => __( 'Размаркирайте всички', SPEEDY_TEXT_DOMAIN ),
		'title_3'         => __( 'Генерирай методите', SPEEDY_TEXT_DOMAIN ),
		'type'            => 'services_buttons',
		'button_generate' => ( !$services_select ) ? true : false,
	),
	'client_id' => array(
		'title'       => __( 'Обект, от който тръгват пратките: <span style="color: #f00;">*</span>', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'select',
		'options'     => $contact_clients,
		'class'       => 'wc-enhanced-select',
		'required'    => true,
	),
	'pricing' => array(
		'title'       => __( 'Образуване на цена за доставка:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => array(
			'calculator'       => __( 'Спиди калкулатор', SPEEDY_TEXT_DOMAIN ),
			'calculator_fixed' => __( 'Спиди калкулатор + Надбавка за обработка', SPEEDY_TEXT_DOMAIN ),
			'fixed'            => __( 'Фиксирана цена за доставка', SPEEDY_TEXT_DOMAIN ),
			'free'             => __( 'Безплатна доставка', SPEEDY_TEXT_DOMAIN ),
			'table_rate'       => __( 'Цена от файл', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'table_rate_file' => array(
		'title'       => __( 'Цена от файл:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'table_rate_file',
	),
	'fixed_price' => array(
		'title'       => __( 'Фиксирана цена за доставка / Надбавка за обработка:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
	),
	'free_shipping_total' => array(
		'title'       => __( 'Праг на стойност на поръчката за безплатна доставка:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
	),
	'free_method_city' => array(
		'title'       => __( 'Безплатна градска услуга:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => $services_select,
	),
	'free_method_intercity' => array(
		'title'       => __( 'Безплатна междуградска услуга:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => $services_select,
	),
	'free_method_international' => array(
		'title'       => __( 'Позволени международни услуги:', SPEEDY_TEXT_DOMAIN ),
		'desc_tip' => __( 'Използвай CTRL за избор на повече от един метод.', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'multiselect',
		// 'class'       => 'wc-enhanced-select',
		'options'     => $services_select,
		'css'         => 'width: 25em; height: 10em;',
	),
	'back_documents' => array(
		'title'   => __( 'Заявка за обратни документи:', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'options' => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'back_receipt' => array(
		'title'   => __( 'Заявка за обратна разписка:', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'options' => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'default_weight' => array(
		'title'       => __( 'Тегло по подразбиране за един брой: <span style="color: #f00;">*</span>', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
	),
	'packing' => array(
		'title'       => __( 'Опаковка:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
	),
	'money_transfer' => array(
		'title'    => __( 'Паричен превод (вместо наложен платеж):', SPEEDY_TEXT_DOMAIN ),
		'desc_tip' => __( 'Използвай Пощенски Паричен Превод вместо Наложен платеж при създаване на товарителница.', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => (!$available_money_transfer) ? 'hidden-row' : 'wc-enhanced-select',
		'options'  => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		)
	),
	'option_before_payment' => array(
		'title'         => __( 'Опции преди плащане:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'         => 'wc-enhanced-select',
		'options'       => array(
			'no_option'  =>  __( 'Няма', SPEEDY_TEXT_DOMAIN ),
			'test'       =>  __( 'Тествай', SPEEDY_TEXT_DOMAIN ),
			'open'       =>  __( 'Отвори', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'return_payer_type' => array(
		'title'         => __( 'Платец на куриерска услуга на товарителница за връщане:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'         => ($this->get_option('option_before_payment') == 'no_option') ? 'hidden-row' : 'wc-enhanced-select',
		'options'       => array(
			'0'         => __( 'Подател', SPEEDY_TEXT_DOMAIN ),
			'1'         => __( 'Получател', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'return_package_city_service_id' => array(
		'title'         => __( 'Градска услуга на товарителница за връщане:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'         => ($this->get_option('option_before_payment') == 'no_option') ? 'hidden-row' : 'wc-enhanced-select',
		'options'       => $services_select,
	),
	'return_package_intercity_service_id' => array(
		'title'         => __( 'Междуградска услуга на товарителница за връщане:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'         => ($this->get_option('option_before_payment') == 'no_option') ? 'hidden-row' : 'wc-enhanced-select',
		'options'       => $services_select,
	),
	'ignore_obp' => array(
		'title'         => __( 'Автоматично изключване на опциите преди плащане при доставка до автомат:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'         => ($this->get_option('option_before_payment') == 'no_option') ? 'hidden-row' : 'wc-enhanced-select',
		'options'       => array(
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'return_voucher' => array(
		'title'         => __( 'Ваучер за връщане:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'         => 'wc-enhanced-select',
		'options'       => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'return_voucher_payer_type' => array(
		'title'         => __( 'Платец на куриерска услуга по товарителница за връщане:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'   => (!$this->get_option('return_voucher')) ? 'hidden-row' : 'wc-enhanced-select',
		'options'       => array(
			ParamCalculation::PAYER_TYPE_SENDER     => __( 'Подател', SPEEDY_TEXT_DOMAIN ),
			ParamCalculation::PAYER_TYPE_RECEIVER   => __( 'Получател', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'return_voucher_city_service_id' => array(
		'title'         => __( 'Градска услуга на товарителница за връщане:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'   => (!$this->get_option('return_voucher')) ? 'hidden-row' : 'wc-enhanced-select',
		'options'     => $services_select,
	),
	'return_voucher_intercity_service_id' => array(
		'title'         => __( 'Междуградска услуга на товарителница за връщане:', SPEEDY_TEXT_DOMAIN ),
		'type'          => 'select',
		'class'   => (!$this->get_option('return_voucher')) ? 'hidden-row' : 'wc-enhanced-select',
		'options'     => $services_select,
	),
	'label_printer' => array(
		'title'   => __( 'Принтер за етикети:', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'options' => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'additional_copy_for_sender' => array(
		'title'   => __( 'Допълнително хартиено копие на товарителниците:', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'options' => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'insurance' => array(
		'title'   => __( 'Добавете oбявена стойност:', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'options' => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'fragile' => array(
		'title'   => __( 'Чупливи стоки:', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'select',
		'class'   => (!$this->get_option('insurance')) ? 'hidden-row' : 'wc-enhanced-select',
		'options' => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		)
	),
	'from_office' => array(
		'title'   => __( 'Изпратете от офис:', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'options' => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'office_id' => array(
		'title'   => __( 'Изберете офис:', SPEEDY_TEXT_DOMAIN ),
		'type'    => 'select',
		'class'   => (!$this->get_option('from_office')) ? 'hidden-row' : 'wc-enhanced-select',
		'options' => $offices,
	),
	'documents' => array(
		'title'    => __( 'Съдържа документи:', SPEEDY_TEXT_DOMAIN ),
		'desc_tip' => __( 'Документална пратка.', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'fixed_time' => array(
		'title'    => __( 'Фиксиран час на доставка:', SPEEDY_TEXT_DOMAIN ),
		'desc_tip' => __( 'Показване на опция за избор на фиксиран час на доставка за услугите, които го позволяват.', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'check_office_work_day' => array(
		'title'    => __( 'Позволи калкулация на цена за временно неработещи офиси:', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'default'  => '1',
		'options'  => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'invoice_courrier_sevice_as_text' => array(
		'title'    => __( 'Цената за доставка участва във фактурата:', SPEEDY_TEXT_DOMAIN ),
		'desc_tip' => __( 'Настройка за WooCommerce, която определя дали куриерска услуга за сметка на получателя се добавя като стойност в стоковата разписка (фактура) или се визуализира със стойност в името на артикула и нулева цена.', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'taking_date' => array(
		'title'       => __( 'Брой работни дни за отлагане на доставката:', SPEEDY_TEXT_DOMAIN ),
		'type'        => 'text',
	),
	'currency' => array(
		'title'    => __( 'Валута:', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => $currency_code_options,
		'css'      => 'min-width:350px;',
		'default'  => 'BGN',
	),
	'order_status_id' => array(
		'title'    => __( 'Статус на поръчката след генериране на товарителница:', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => wc_get_order_statuses(),
	),
	'order_status_update' => array(
		'title'    => __( 'Синхронизация на статусите:', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'final_statuses' => array(
		'title'    => '',
		'type'     => 'final_statuses',
		'options'  => $final_statuses
	),
	'availability' => array(
		'title' 		=> __( 'Наличност на метода', SPEEDY_TEXT_DOMAIN ),
		'type' 			=> 'select',
		'default' 		=> 'all',
		'class'			=> 'availability wc-enhanced-select',
		'options'		=> array(
			'all' 		=> __( 'Всички налични страни', SPEEDY_TEXT_DOMAIN ),
			'specific' 	=> __( 'Специфични страни', SPEEDY_TEXT_DOMAIN )
		)
	),
	'countries' => array(
		'title' 		=> __( 'Специфични страни', SPEEDY_TEXT_DOMAIN ),
		'type' 			=> 'multiselect',
		'class'			=> 'wc-enhanced-select',
		'css'			=> 'width: 450px;',
		'default' 		=> '',
		'options'		=> WC()->countries->get_shipping_countries(),
		'custom_attributes' => array(
			'data-placeholder' => __( 'Изберете страна', SPEEDY_TEXT_DOMAIN )
		)
	),
	'weight_dimensions' => array(
		'title' 		=> __( 'СПИДИ ПОЩА - Конвертиране на тегло към размер', SPEEDY_TEXT_DOMAIN ),
		'type'			=> 'weight_dimensions',
		'options'		=> $weight_dimensions,
	),
	'min_package_dimention' => array(
		'title' 		=> __( 'СПИДИ ПОЩА - Минимален транспортен размер за цяла пратка', SPEEDY_TEXT_DOMAIN ),
		'type'			=> 'select',
		'options'		=> array(
			''   =>  __( 'Няма', SPEEDY_TEXT_DOMAIN ),
			'XS' =>  'XS',
			'S'  =>  'S',
			'M'  =>  'M',
			'L'  =>  'L',
			'XL' =>  'XL',
		),
	),
	'convert_to_win_1251' => array(
		'title'    => __( 'Автоматично транслитериране:', SPEEDY_TEXT_DOMAIN ),
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => array(
			'0' =>  __( 'Не', SPEEDY_TEXT_DOMAIN ),
			'1' =>  __( 'Да', SPEEDY_TEXT_DOMAIN ),
		),
	),
	'currency_rate' => array(
		'title' 		=> __( 'Валутен курс', SPEEDY_TEXT_DOMAIN ),
		'type'			=> 'currency_rate',
		'options'		=> $currency_rate,
	),
);
