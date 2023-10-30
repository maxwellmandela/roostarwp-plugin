<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/maxwellmandela
 * @since      1.0.0
 *
 * @package    WRP
 * @subpackage WRP/admin/partials
 */
?>


<?php 

if ( rw_fs()->is_not_paying() ) {
    echo  '<section><h1>' . __( 'Enable auto-reply, chatgpt-like conversations for your customers', 'roostar-wp' ) . '</h1>' ;
    echo  '<a href="' . rw_fs()->get_upgrade_url() . '">' . __( 'Upgrade Now!', 'roostar-wp' ) . '</a>' ;
    echo  '
    </section>' ;
}

?>


