<?php
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Speedy_Orders_Table extends WP_List_Table {
	private $per_page;
	private $total_items;

	function get_data() {
		global $wpdb;

		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		$speedy_orders = array();
		$order_exclude = array('customer', 'delivery_date');

		$table_name = $wpdb->prefix . 'speedy_order';

		// Get total orders
		$total_query = 'SELECT COUNT(*) FROM `' . $table_name . '` WHERE bol_id > 0';
		$this->total_items = intval ($wpdb->get_var($total_query) );

		$query = 'SELECT * FROM `' . $table_name . '` WHERE bol_id > 0';

		// Search
		if ( isset( $_GET['s'] ) && $_GET['s'] ) {
			$query .= ' AND bol_id LIKE \'%' . trim( $_GET['s'] ) . '%\' OR order_id LIKE \'%' . trim( $_GET['s'] ) . '%\'';
		}

		// Order By
		if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) && !in_array($_GET['orderby'], $order_exclude)) {
			$orderby = $_GET['orderby'];
			$order = $_GET['order'];
		} else {
			$orderby = 'date_created';
			$order = 'desc';
		}

		$query .= ' ORDER BY ' . $orderby . ' ' . $order;

		// Limit
		$this->per_page = apply_filters( 'edit_speedy_orders_per_page', 20 );

		$offset = ($this->get_pagenum() - 1) * $this->per_page;
		$query .= " LIMIT " . $offset . "," . $this->per_page;

		$results = $wpdb->get_results($query, ARRAY_A);

		foreach ($results as $speedy_order) {
			$user_id = get_post_meta( $speedy_order['order_id'], '_customer_user', true);

			$customer = '';
			if ($user_id) {
				$user_data = get_userdata( $user_id );
				$customer = $user_data->first_name . ' ' .  $user_data->last_name;

				if ( ' ' == $customer ) {
					$customer_firstname = get_post_meta( $speedy_order['order_id'], '_shipping_first_name', true);
					$customer_lastname = get_post_meta( $speedy_order['order_id'], '_shipping_last_name', true);
					$customer = $customer_firstname . ' ' . $customer_lastname;
				}
			} else {
				$customer_firstname = get_post_meta( $speedy_order['order_id'], '_shipping_first_name', true);
				$customer_lastname = get_post_meta( $speedy_order['order_id'], '_shipping_last_name', true);
				$customer = $customer_firstname . ' ' . $customer_lastname;
			}

			$order = wc_get_order($speedy_order['order_id']);
			$address = str_replace( $order->get_formatted_shipping_full_name() . '<br/>', '', $order->get_formatted_shipping_address() );
			$address = str_replace( __( 'Доставка до адрес', SPEEDY_TEXT_DOMAIN ) . ':', __( 'Доставка до адрес', SPEEDY_TEXT_DOMAIN ) . '<br/>', $address );
			$address = str_replace( __( 'Доставка до офис', SPEEDY_TEXT_DOMAIN ) . ':', __( 'Доставка до офис', SPEEDY_TEXT_DOMAIN ) . '<br/>', $address );

			$deliveryInfo = $speedy_shipping_method->speedy->getDeliveryInfo($speedy_order['bol_id']);
			$deliveryDate = '';

			$order_status = wc_get_order_status_name($order->get_status());

			if (isset($deliveryInfo)) {
				if (!empty($deliveryInfo->getDeliveryDate())) {
					$deliveryDate = date('Y-m-d H:i:s', strtotime($deliveryInfo->getDeliveryDate()));
				}

				if (!empty($deliveryInfo->getConsignee())) {
					$deliveryDate .= $deliveryInfo->getConsignee();
				}

				if (!empty($deliveryInfo->getDeliveryNote())) {
					$deliveryDate .= '<br>' . $deliveryInfo->getDeliveryNote();
				}
			}

			$speedy_orders[] = array(
				'id' => $speedy_order['speedy_order_id'],
				'bol_id' => $speedy_order['bol_id'],
				'order_id' => $speedy_order['order_id'],
				'customer' => $customer,
				'address' => $address,
				'date_created' => $speedy_order['date_created'],
				'courier' => $speedy_order['courier'],
				'status' => $order_status,
				'delivery_date' => $deliveryDate,
			);
		}

		if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) && $_GET['orderby'] == 'customer' ) {
			usort( $speedy_orders, array( &$this, 'usort_reorder' ) );
		} elseif ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) && $_GET['orderby'] == 'delivery_date' ) {
			usort( $speedy_orders, array( &$this, 'usort_reorder_date' ) );
		}

		$speedy_shipping_method->change_orders_statuses();

		return $speedy_orders;
	}

	function usort_reorder_date( $a, $b ) {
		if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) && $_GET['orderby'] == 'delivery_date' ) {
			$orderby = $_GET['orderby'];
			$order = $_GET['order'];

			$result = strtotime($a[$orderby]) > strtotime($b[$orderby]) ? 1 : -1;

			return ( $order === 'asc' ) ? $result : -$result;
		}
	}

	function usort_reorder( $a, $b ) {
		if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) && $_GET['orderby'] == 'customer' ) {
			$orderby = $_GET['orderby'];
			$order = $_GET['order'];

			$result = strcmp( $a[$orderby], $b[$orderby] );

			return ( $order === 'asc' ) ? $result : -$result;
		}
	}

	function get_columns(){
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'bol_id'        => __( 'Товарителница', SPEEDY_TEXT_DOMAIN ),
			'order_id'      => __( 'Поръчка №', SPEEDY_TEXT_DOMAIN ),
			'customer'      => __( 'Клиент', SPEEDY_TEXT_DOMAIN ),
			'address'       => __( 'Адрес за доставка (Спиди)', SPEEDY_TEXT_DOMAIN ),
			'date_created'  => __( 'Дата на създаване', SPEEDY_TEXT_DOMAIN ),
			'status'        => __( 'Статус', SPEEDY_TEXT_DOMAIN ),
			'delivery_date' => __( 'Получена на дата', SPEEDY_TEXT_DOMAIN ),
		);
		return $columns;
	}

	function prepare_items() {
		$this->process_bulk_action();

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->get_data();

		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => $this->per_page
		));

	}

	function column_cb($item) {
		return sprintf('<input type="checkbox" name="speedy_order_id[]" value="%s" />', $item['id']);
	}

	function column_bol_id( $item ) {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$speedy_shipping_method->speedy = SpeedyEpsLib::getInstance();

		$actions = array(
			'cancel'  => sprintf( '<a href="?page=%s&action=%s&speedy_order_id=%s">' . __( 'Откажи', SPEEDY_TEXT_DOMAIN ) . '</a>', $_GET['page'], 'cancel', $item['id'] ),
			'track'   => sprintf( '<a href="?page=%s&action=%s&speedy_order_id=%s">' . __( 'Проследи', SPEEDY_TEXT_DOMAIN ) . '</a>', $_GET['page'], 'track', $item['id'] ),
		);

		$speedy_settings = get_option('woocommerce_speedy_shipping_method_settings');

		if ( ! $speedy_settings['from_office'] ) {
			$actions['request'] = ( ! $item['courier'] ) ? sprintf( '<a href="?page=%s&action=%s&speedy_order_id=%s">' . __( 'Заяви куриер', SPEEDY_TEXT_DOMAIN ) . '</a>', $_GET['page'], 'request', $item['id'] ) : '<span style="color:#000;">' . __( 'Куриерът е заявен', SPEEDY_TEXT_DOMAIN ) . '</span>';
		}

		$return_voucher_requested = $speedy_shipping_method->speedy->checkReturnVoucherRequested($item['bol_id']);
		if ($return_voucher_requested) {
			$actions['print_return_voucher'] = sprintf( '<a href="' . plugin_dir_url(__FILE__) . 'print_return_voucher.php?bol_id=' . $item['bol_id'] . '" target="_blank">' . __( 'Ваучер за връщане', SPEEDY_TEXT_DOMAIN ) . '</a>', $_GET['page'], 'print_return_voucher', $item['id'] );
		}

		$column_bol_id = '<a href="' . plugin_dir_url(__FILE__) . 'print_pdf.php?bol_id=' . $item['bol_id'] . '" target="_blank" >' . $item['bol_id'] . '</a>';

		return sprintf('%1$s %2$s', $column_bol_id, $this->row_actions($actions) );
	}

	function column_order_id( $item ) {
		return '<a href="' . admin_url() . 'post.php?post=' . $item['order_id'] . '&action=edit' . '" >' . $item['order_id'] . '</a>';
	}

	function column_date_created( $item ) {
		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime ($item['date_created'] ) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'bol_id':
			case 'order_id':
			case 'customer':
			case 'address':
			case 'date_created':
			case 'status':
			case 'delivery_date':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'bol_id'        => array( 'bol_id', true ),
			'order_id'      => array( 'order_id', true ),
			'customer'      => array( 'customer', true ),
			'date_created'  => array( 'date_created', true ),
			'delivery_date' => array( 'delivery_date', true ),
		);

		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'cancel'  => __( 'Откажи', SPEEDY_TEXT_DOMAIN ),
			'track'   => __( 'Проследи', SPEEDY_TEXT_DOMAIN ),
		);

		$speedy_settings = get_option('woocommerce_speedy_shipping_method_settings');

		if ( ! $speedy_settings['from_office'] ) {
		$actions['request'] = __( 'Заяви куриер', SPEEDY_TEXT_DOMAIN );
		}
		return $actions;
	}

	function process_bulk_action() {
		// security check!
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) )
				wp_die( 'Nope! Security check failed!' );
		}

		$action = $this->current_action();

		switch ( $action ) {

			case 'track':
				$this->track_speedy_orders();
				break;

			case 'cancel':
				$this->cancel_speedy_orders();
				break;

			case 'request':
				$this->request_courier_speedy_orders();
				break;

			default:
				// do nothing or something else
				break;
		}

		return;
	}

	function track_speedy_orders() {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$url_data = array();
		$errors = array();
		$bol_ids = array();

		if ( isset( $_GET['speedy_order_id'] ) ) {
			if ( is_array( $_GET['speedy_order_id'] ) ) {
				foreach($_GET['speedy_order_id'] as $speedy_order_id) {
					$speedy_order_data = $speedy_shipping_method->_getOrderById( $speedy_order_id );
					$url_data[] = $speedy_order_data['bol_id'];
					$bol_ids[] = $speedy_order_data['bol_id'];
				}
				$url_data = implode(',', $url_data);
			} else {
				$speedy_order_data = $speedy_shipping_method->_getOrderById( $_GET['speedy_order_id'] );
				$url_data = $speedy_order_data['bol_id'];
				$bol_ids[] = $speedy_order_data['bol_id'];
			}

			if ( ! empty( $url_data ) ) {
				$locale = get_locale() == 'bg_BG' ? 'bg' : 'en';
				echo "<script type=\"text/javascript\">
					window.open('http://www.speedy.bg/begin.php?shipmentNumber=$url_data&lang=$locale', '_blank')
				</script>";
			}
		} else {
			$errors[] = __( 'Няма избрани товарителници!', SPEEDY_TEXT_DOMAIN );
		}

		if ( ! empty ($errors) ) {
			foreach ( $errors as $error ) {
				echo '<div class="error"><p>' . $error . '</p></div>';
			}
		} else {
			echo '<div class="updated fade"><p>' . sprintf (__( 'Готово, проследихте товарителница/и: %s !', SPEEDY_TEXT_DOMAIN ), implode(', ', $bol_ids) ) . '</p></div>';
		}

		return;
	}

	function cancel_speedy_orders() {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$errors = array();
		$bol_ids = array();
		if ( isset( $_GET['speedy_order_id'] ) ) {
			if ( is_array( $_GET['speedy_order_id'] ) ) {
				foreach($_GET['speedy_order_id'] as $speedy_order_id) {
					$speedy_order_data = $speedy_shipping_method->_getOrderById( $speedy_order_id );
					$errors = $speedy_shipping_method->cancelLoading( $speedy_order_data );
					$bol_ids[] = $speedy_order_data['bol_id'];
				}
			} else {
				$speedy_order_data = $speedy_shipping_method->_getOrderById( $_GET['speedy_order_id'] );
				$errors = $speedy_shipping_method->cancelLoading( $speedy_order_data );
				$bol_ids[] = $speedy_order_data['bol_id'];
			}
		} else {
			$errors[] = __( 'Няма избрани товарителници!', SPEEDY_TEXT_DOMAIN );
		}

		if ( ! empty ($errors) ) {
			foreach ( $errors as $error ) {
				echo '<div class="error"><p>' . $error . '</p></div>';
			}
		} else {
			echo '<div class="updated fade"><p>' . sprintf (__( 'Готово, отказахте товарителница/и: %s !', SPEEDY_TEXT_DOMAIN ), implode(', ', $bol_ids) ) . '</p></div>';
		}

		return;
	}

	function request_courier_speedy_orders() {
		$speedy_shipping_method = new WC_Speedy_Shipping_Method();
		$bol_ids = array();
		$result = array();

		if ( isset( $_GET['speedy_order_id'] ) ) {
			if ( is_array( $_GET['speedy_order_id'] ) ) {
				foreach($_GET['speedy_order_id'] as $speedy_order_id) {
					$speedy_order_data = $speedy_shipping_method->_getOrderById( $speedy_order_id );
					$bol_ids[] = $speedy_order_data['bol_id'];
				}
			} else {
				$speedy_order_data = $speedy_shipping_method->_getOrderById( $_GET['speedy_order_id'] );
				$bol_ids[] = $speedy_order_data['bol_id'];
			}

			$result = $speedy_shipping_method->RequestForCourier( $bol_ids );

			if (isset ( $result['success'] ) ) {
				echo '<div class="updated fade"><p>' . $result['success'] . '</p></div>';
			}

			if (isset ( $result['warning'] ) ) {
				echo '<div class="error"><p>' . $result['warning'] . '</p></div>';
			}
		} else {
			$errors[] = __( 'Няма избрани товарителници!', SPEEDY_TEXT_DOMAIN );
		}

		if ( ! empty ($errors) ) {
			foreach ( $errors as $error ) {
				echo '<div class="error"><p>' . $error . '</p></div>';
			}
		}

		return;
	}

	function no_items() {
		_e( 'Няма намерени резултати!', SPEEDY_TEXT_DOMAIN );
	}
}

$speedy_orders_table = new Speedy_Orders_Table();