<?php


/**
 * Register all helpers for the plugin
 *
 * @link       https://github.com/maxwellmandela
 * @since      1.0.0
 *
 * @package    WRP
 * @subpackage WRP/includes
 */



if (!function_exists('is_woocommerce_activated')) {
    /**
     * Check if WooCommerce is activated
     * @return bool
     */
    function wpr_is_woocommerce_activated()
    {
        return class_exists('WooCommerce') ? true : false;
    }
}
