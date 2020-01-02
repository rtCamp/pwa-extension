<?php

namespace RT\PWA\Inc;

use \RT\PWA\Inc\Traits\Singleton;
use \Minishlink\WebPush\WebPush;
use \Minishlink\WebPush\Subscription;

class Web_Push {
	use Singleton;

	public function __construct() {

		$auth = array(
			'VAPID' => array(
				'subject'    => 'https://github.com/Minishlink/web-push-php-example/',
				'publicKey'  => 'BGOxOFFkJX1hZd4CInZel4PnjCMkAFRKGD3xORlwItM4Sl1QS5L81rf2axzmSuj7Zt-T7blYt4GnBWxlfNpGZU0',
				'privateKey' => 'yccB2PFvcBhSjY38H1Dxl2wUXM7gCwDPl3cD7_J022s',
			),
		);

		$this->web_push = new WebPush( $auth );

		add_action( 'wp_ajax_update_subscription', array( $this, 'update_subscription_record' ) );
		add_action( 'wp_ajax_nopriv_update_subscription', array( $this, 'update_subscription_record' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 11 );
		add_action( 'parse_request', array( $this, 'send_web_push' ) );
		add_filter( 'query_vars', array( $this, 'send_push_query' ), 10, 1 );

	}

	public function enqueue_assets() {

		$fmtime = filemtime( RT_PWA_EXTENSIONS_PATH . '/assets/js/web-push.js' );
		wp_register_script(
			'web-push',
			RT_PWA_EXTENSIONS_URL . '/assets/js/web-push.js',
			[ 'jquery' ],
			$fmtime,
			true
		);
		wp_enqueue_script( 'web-push' );

		wp_localize_script( 'web-push', 'adminURL', admin_url( 'admin-ajax.php' ) );
	}

	public function send( $payload ) {

		$subscriptions = get_option( 'push_subscribers' );

		foreach( $subscriptions as $subscription ) {
			$subscription_object = Subscription::create(
				[
					'endpoint'  => $subscription['endpoint'],
					'publicKey' => $subscription['keys']['p256dh'],
					'authToken' => $subscription['keys']['auth'],
				]
			);

			$this->web_push->sendNotification(
				$subscription_object,
				$payload
			);
		}

		/**
		 * Check sent results
		 *
		 * @var MessageSentReport $report
		 */
		foreach ( $this->web_push->flush() as $report ) {
			$endpoint = $report->getRequest()->getUri()->__toString();

			if ( $report->isSuccess() ) {
				error_log( var_export( "[v] Message sent successfully for subscription {$endpoint}.", true ) );
			} else {
				error_log( var_export( "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}", true ) );
			}
		}
	}


	public function update_subscription_record() {
		$subscription        = json_decode( filter_input( INPUT_POST, 'subscription' ), true );
		$subscription_list   = get_option( 'push_subscribers' ) ?? [];
		$subscription_list[] = $subscription;

		update_option( 'push_subscribers', $subscription_list );

		$subscription_object = Subscription::create(
			[
				'endpoint'  => $subscription['endpoint'],
				'publicKey' => $subscription['keys']['p256dh'],
				'authToken' => $subscription['keys']['auth'],
			]
		);

		$res = $this->web_push->sendNotification(
			$subscription_object,
			'Thankyou! for subscribing'
		);

		foreach ( $this->web_push->flush() as $report ) {
			$endpoint = $report->getRequest()->getUri()->__toString();

			if ( $report->isSuccess() ) {
				error_log( var_export( "[v] Message sent successfully for subscription {$endpoint}.", true ) );;
			} else {
				error_log( var_export( "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}", true ) );;
			}
		}

	}

	public function send_web_push( $query ) {
		$message = 'Default data';
		if ( isset( $query->query_vars['send_push'] ) ) {
			$message = $query->query_vars['send_push'];
			$this->send( $message );
		}

	}

	function send_push_query( $query ) {
		$query[] = 'send_push';
		return $query;
	}
}
