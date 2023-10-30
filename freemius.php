<?php
if (!function_exists('rw_fs')) {
    // Create a helper function for easy SDK access.
    function rw_fs()
    {
        global $rw_fs;

        if (!isset($rw_fs)) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $rw_fs = fs_dynamic_init(array(
                'id'                  => '12714',
                'slug'                => 'wpr-home',
                'premium_slug'        => 'wpr-home',
                'type'                => 'plugin',
                'public_key'          => 'pk_f6748ccc3b7de782d44cff16f8789',
                'is_premium'          => true,
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'wpr-home',
                    'support'        => false,
                ),
            ));
        }

        return $rw_fs;
    }

    // Init Freemius.
    rw_fs();
    // Signal that SDK was initiated.
    do_action('rw_fs_loaded');
}
