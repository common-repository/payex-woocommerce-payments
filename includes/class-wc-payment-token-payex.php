<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_Payment_Token_Payex extends WC_Payment_Token_CC {
	/**
	 * Token Type String.
	 *
	 * @var string
	 */
	protected $type = 'Payex';

	/**
	 * Stores Credit Card payment token data.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'last4'        => '',
		'expiry_year'  => '',
		'expiry_month' => '',
		'card_type'    => '',
		'masked_pan'   => '',
	);

	/**
	 * Get type to display to user.
	 *
	 * @param string $deprecated Deprecated since WooCommerce 3.0.
	 *
	 * @return string
	 */
	public function get_display_name( $deprecated = '' ) {
		ob_start();
		?>
        <img src="<?php echo WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/' . $this->get_card_type() . '.png' ) ?>"
             alt="<?php echo wc_get_credit_card_type_label( $this->get_card_type() ); ?>"/>
		<?php echo esc_html( $this->get_meta( 'masked_pan' ) ); ?>
		<?php echo esc_html( $this->get_expiry_month() . '/' . substr( $this->get_expiry_year(), 2 ) ); ?>

		<?php
		$display = ob_get_contents();
		ob_end_clean();

		return $display;
	}

	/**
	 * Validate credit card payment tokens.
	 *
	 * @return boolean True if the passed data is valid
	 */
	public function validate() {
		if ( false === parent::validate() ) {
			return false;
		}

		if ( ! $this->get_masked_pan( 'edit' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Hook prefix
	 * @return string
	 */
	protected function get_hook_prefix() {
		return 'woocommerce_payment_token_payex_get_';
	}

	/**
	 * Returns Masked Pan
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string Masked Pan
	 */
	public function get_masked_pan( $context = 'view' ) {
		return $this->get_prop( 'masked_pan', $context );
	}

	/**
	 * Set the last four digits.
	 *
	 * @param string $masked_pan Masked Pan
	 */
	public function set_masked_pan( $masked_pan ) {
		$this->set_prop( 'masked_pan', $masked_pan );
	}

	/**
	 * Returns if the token is marked as default.
	 *
	 * @return boolean True if the token is default
	 */
	public function is_default() {
		// Mark Method as Checked on "Payment Change" page
		if ( WC_Gateway_Payex_Cc::wcs_is_payment_change() &&
		     isset( $_GET['change_payment_method'] ) &&
		     abs( $_GET['change_payment_method'] ) > 0 ) {
			$subscription = wcs_get_subscription( $_GET['change_payment_method'] );
			$tokens       = $subscription->get_payment_tokens();
			foreach ( $tokens as $token_id ) {
				if ( $this->get_id() == $token_id ) {
					return true;
				}
			}

			return false;
		}

		return parent::is_default();
	}

	/**
	 * Controls the output for credit cards on the my account page.
	 *
	 * @param array $item Individual list item from woocommerce_saved_payment_methods_list.
	 * @param WC_Payment_Token $payment_token The payment token associated with this method entry.
	 *
	 * @return array                           Filtered item.
	 */
	public static function wc_get_account_saved_payment_methods_list_item( $item, $payment_token ) {
		if ( 'payex' !== strtolower( $payment_token->get_type() ) ) {
			return $item;
		}

		$card_type               = $payment_token->get_card_type();
		$item['method']['id']    = $payment_token->get_id();
		$item['method']['last4'] = $payment_token->get_last4();
		$item['method']['brand'] = ( ! empty( $card_type ) ? ucfirst( $card_type ) : esc_html__( 'Credit card', 'woocommerce' ) );
		$item['expires']         = $payment_token->get_expiry_month() . '/' . substr( $payment_token->get_expiry_year(), - 2 );

		return $item;
	}

	/**
	 * Controls the output for credit cards on the my account page.
	 *
	 * @param $method
	 *
	 * @return void
	 */
	public static function wc_account_payment_methods_column_method( $method ) {
		if ( $method['method']['gateway'] === 'payex_psp_cc' ) {
			$token = new WC_Payment_Token_Payex( $method['method']['id'] );
			echo $token->get_display_name();

			return;
		}

		// Default output
		// @see woocommerce/myaccount/payment-methods.php
		if ( ! empty( $method['method']['last4'] ) ) {
			/* translators: 1: credit card type 2: last 4 digits */
			echo sprintf( __( '%1$s ending in %2$s', 'woocommerce' ), esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) ), esc_html( $method['method']['last4'] ) );
		} else {
			echo esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) );
		}
	}

	/**
	 * Fix html on Payment methods list
	 *
	 * @param string $html
	 * @param WC_Payment_Token $token
	 * @param WC_Payment_Gateway $gateway
	 *
	 * @return string
	 */
	public static function wc_get_saved_payment_method_option_html( $html, $token, $gateway ) {
		if ( $token->get_gateway_id() === 'payex_psp_cc' ) {
			// Revert esc_html()
			$html = html_entity_decode( $html, ENT_COMPAT | ENT_XHTML, 'UTF-8' );
		}

		return $html;
	}
}

// Improve Payment Method output
add_filter( 'woocommerce_payment_methods_list_item', 'WC_Payment_Token_Payex::wc_get_account_saved_payment_methods_list_item', 10, 2 );
add_action( 'woocommerce_account_payment_methods_column_method', 'WC_Payment_Token_Payex::wc_account_payment_methods_column_method', 10, 1 );
add_filter( 'woocommerce_payment_gateway_get_saved_payment_method_option_html', 'WC_Payment_Token_Payex::wc_get_saved_payment_method_option_html', 10, 3 );
