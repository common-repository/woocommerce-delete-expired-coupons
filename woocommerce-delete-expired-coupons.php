<?php
/**
 * Plugin Name: WooCommerce Delete Expired Coupons
 * Plugin URI: https://github.com/PinchOfCode/woocommerce-delete-expired-coupons/
 * Description: Automatically delete WooCommerce expired coupons.
 * Version: 1.1.1
 * Author: Pinch Of Code
 * Author URI: http://pinchofcode.com
 * Requires at least: 3.8
 * Tested up to: 3.9.2
 *
 * License:  GPL-2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/PinchOfCode/woocommerce-delete-expired-coupons/
 *
 * Text Domain: woocommerce-delete-expired-coupons
 * Domain Path: /i18n/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Delete_Expired_Coupons' ) ) :

/**
 * Automatically delete WooCommerce expired coupons.
 *
 * @author Pinch Of Code <info@pinchofcode.com>
 * @copyright (c) 2014 - Pinch Of Code
 */
class WC_Delete_Expired_Coupons {
    /**
     * __construct method
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
    * Initialize the plugin.
    */
    public function init() {
 
        // Checks if WooCommerce is installed.
        if ( class_exists( 'WC_Integration' ) ) {
            include_once 'wc-delete-expired-coupons-integration.php';

            // Register the integration.
            add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
        } else {
            add_action( 'admin_notices', array( $this, 'wc_inactive' ) );
        }
    }

    /**
     * Checks if WooCommerce is active. If not, print an admin notice and exit.
     *
     * @return void
     */
    public function wc_inactive(){
        echo '<div class="error"><p>WooCommerce Delete Expired Coupons ' . __('is enabled but not effective. It requires <a href="http://wordpress.org/plugins/woocommerce/" target="_blank" title="WooCommerce - excelling eCommerce">WooCommerce</a> in order to work.', 'woocommerce-delete-expired-coupons' ) . '</p></div>';
    }

    /**
     * Add a new integration to WooCommerce.
     */
    public function add_integration( $integrations ) {
        $integrations[] = 'WC_Delete_Expired_Coupons_Integration';
        return $integrations;
    }
}

$WC_Delete_Expired_Coupons = new WC_Delete_Expired_Coupons();

endif; // if ! class_exists
