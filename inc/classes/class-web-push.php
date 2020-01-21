<?php

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;
use \Minishlink\WebPush\WebPush;
use \Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;

class Web_Push {
	use Singleton;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		global $wpdb;

		$vapid_public_key  = get_option( 'vapid_public_key' );
		$vapid_private_key = get_option( 'vapid_private_key' );

		$auth = array(
			'VAPID' => array(
				'subject'    => 'https://github.com/Minishlink/web-push-php-example/',
				'publicKey'  => $vapid_public_key,
				'privateKey' => $vapid_private_key,
			),
		);

		$this->web_push   = new WebPush( $auth );
		$this->table_name = $wpdb->prefix . 'push_susbscriptions';

		add_action( 'wp_ajax_update_subscription', array( $this, 'update_subscription_record' ) );
		add_action( 'wp_ajax_nopriv_update_subscription', array( $this, 'update_subscription_record' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 11 );
		add_action( 'add_meta_boxes', array( $this, 'push_notifications_meta_box' ) );
		add_action( 'save_post', array( $this, 'send_notification_on_publish' ), 10, 2 );
		add_action( 'wp_front_service_worker', array( $this, 'manage_push_event' ) );

	}

	/**
	 * Enqueue Assets and localize variables.
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		$fmtime = filemtime( RT_PWA_PUSH_PATH . '/assets/js/web-push.js' );

		wp_register_script(
			'web-push',
			RT_PWA_PUSH_URL . '/assets/js/web-push.js',
			array( 'jquery' ),
			$fmtime,
			true
		);

		wp_enqueue_script( 'web-push' );

		$vapid_public_key = get_option( 'vapid_public_key' );

		wp_localize_script( 'web-push', 'applicationServerPublicKey', $vapid_public_key );

		wp_localize_script( 'web-push', 'adminURL', admin_url( 'admin-ajax.php' ) );
	}

	public function send( $payload ) {

		$subscriptions = $this->get_active_susbcribers();

		foreach ( $subscriptions as $subscription ) {
			$subscription_object = Subscription::create(
				array(
					'endpoint'  => $subscription['user_endpoint'],
					'publicKey' => $subscription['p256dh'],
					'authToken' => $subscription['auth'],
				)
			);

			$this->web_push->sendNotification(
				$subscription_object,
				$payload
			);
		}

		$this->update_active_subscribers();
	}

	/**
	 * Get active push subscribers.
	 *
	 * @return void
	 */
	public function get_active_susbcribers() {

		global $wpdb;

		$results = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}push_susbscriptions WHERE device_status=1",
			ARRAY_A
		);

		return $results;

	}

	/**
	 * Update status of unsubscribed users.
	 *
	 * @return void
	 */
	public function update_active_subscribers() {

		global $wpdb;

		/**
		 * Check sent results
		 *
		 * @var MessageSentReport $report
		 */
		foreach ( $this->web_push->flush() as $report ) {
			$endpoint = $report->getRequest()->getUri()->__toString();

			if ( $report->isSubscriptionExpired() ) {
				$wpdb->update( $this->table_name, array( 'device_status' => 0 ), array( 'user_endpoint' => $endpoint ) );
			}
		}

	}


	/**
	 * Save subscription object.
	 *
	 * @return void
	 */
	public function update_subscription_record() {

		global $wpdb;

		$subscription = json_decode( filter_input( INPUT_POST, 'subscription', FILTER_DEFAULT ), true );

		$is_successfull = $wpdb->insert(
			$this->table_name,
			array(
				'user_endpoint'   => $subscription['endpoint'],
				'expiration_time' => $subscription['expirationTime'],
				'auth'            => $subscription['keys']['auth'],
				'p256dh'          => $subscription['keys']['p256dh'],
			)
		);

		if ( false === $is_successfull ) {
			return;
		}

		$subscription_object = Subscription::create(
			array(
				'endpoint'  => $subscription['endpoint'],
				'publicKey' => $subscription['keys']['p256dh'],
				'authToken' => $subscription['keys']['auth'],
			)
		);

		$payload = array(
			'data'  => '',
			'title' => 'Thankyou! for subscribing',
			'url'   => '/',
		);

		$this->web_push->sendNotification(
			$subscription_object,
			wp_json_encode( $payload )
		);

		foreach ( $this->web_push->flush() as $report ) {
			$endpoint = $report->getRequest()->getUri()->__toString();
		}

	}

	/**
	 * Add Push notifications meta box.
	 *
	 * @return void
	 */
	public function push_notifications_meta_box() {

		add_meta_box(
			'push_notify',
			'Push Notifications',
			array( $this, 'push_notify_html' ),
			'post',
			'side',
			'high'
		);

	}

	/**
	 * Push notification meta box html
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public function push_notify_html( $post ) {
		?>

		<input type="checkbox" id="notify_opt" name="notify_opt">
		<label for="notify_opt">Send Push Notification</label>

		<?php
	}

	/**
	 * Send push Notification to subscribers on post publish or update.
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return void
	 */
	public function send_notification_on_publish( $post_id, $post ) {

		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		if ( 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
			return;
		}

		$notify_opt = filter_input( INPUT_POST, 'notify_opt', FILTER_SANITIZE_STRING );

		if ( 'on' === $notify_opt ) {

			$push_data = array(
				'title' => get_the_title( $post ),
				'data'  => get_the_excerpt( $post ),
				'url'   => get_permalink( $post ),
			);

			$push_data = wp_json_encode( $push_data );

			$this->send( $push_data );
		}

	}

	/**
	 * Create subscription table on plugin activation.
	 *
	 * @return void
	 */
	public function susbcription_data_table() {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
				subscription_id bigint(20) NOT NULL AUTO_INCREMENT,
				user_endpoint varchar(200) NOT NULL UNIQUE,
				expiration_time varchar(30),
				auth varchar(100) NOT NULL,
				p256dh varchar(100) NOT NULL,
				device_status varchar(1) DEFAULT '1',
				update_time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (subscription_id)
			) $charset_collate;";

		\dbDelta( $sql );
	}

	/**
	 * Manages Push event.
	 *
	 * @param \WP_Service_Worker_Scripts $scripts Instance to register service worker behavior with.
	 *
	 * @return void
	 */
	public function manage_push_event( \WP_Service_Worker_Scripts $scripts ) {

		$scripts->register(
			'manage',
			array(
				'src' => sprintf( '%s/assets/js/push-event.js', untrailingslashit( RT_PWA_PUSH_URL ) ),
			)
		);
	}

}
