<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WRP
 * @subpackage WRP/includes
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */
class WRP_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		global $wpdb;

		$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}wpr_ft`; 
		DROP TABLE IF EXISTS `{$wpdb->prefix}wpr_chats`";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		try {
			$wpdb->query($sql);

			// remove other settings
		} catch (\Throwable $th) {
			throw $th;
		}
	}
}
