<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Payex_Psp_Update {

	/** @var array DB updates that need to be run */
	private static $db_updates = array(
		'1.1.0' => 'updates/update-1.1.0.php',
		'1.2.0' => 'updates/update-1.2.0.php',
	);

	/**
	 * Handle updates
	 */
	public static function update() {
		$current_version = get_option( 'woocommerce_payex_psp_version' );
		foreach ( self::$db_updates as $version => $updater ) {
			if ( version_compare( $current_version, $version, '<' ) ) {
				include dirname( __FILE__ ) . '/../' . $updater;
				self::update_db_version( $version );
			}
		}
	}

	/**
	 * Update DB version.
	 *
	 * @param string $version
	 */
	private static function update_db_version( $version ) {
		delete_option( 'woocommerce_payex_psp_version' );
		add_option( 'woocommerce_payex_psp_version', $version );
	}
}