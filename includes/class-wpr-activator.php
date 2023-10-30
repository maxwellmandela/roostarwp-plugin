<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WRP
 * @subpackage WRP/includes
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */
class WRP_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{

		if (defined('WPR_VERSION')) {
			$version = WPR_VERSION;
		} else {
			$version = '1.0.0';
		}


		$saved_version = (int) get_site_option('wpr_db_version');
		if ($saved_version != $version || !$saved_version) {
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}wpr_ft` (
				id INT AUTO_INCREMENT PRIMARY KEY,
				ft_name varchar(255) NOT NULL,
				ft LONGTEXT NOT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
			) $charset_collate;";

			$sql2 = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}wpr_chats` (
				id INT AUTO_INCREMENT PRIMARY KEY,
				ft_user varchar(255) NOT NULL,
				ft_user_display_name varchar(255),
				ft_user_email varchar(255),
				ft_enabled tinyint(1) DEFAULT 1,
				is_hidden tinyint(1) DEFAULT 0,
				chat LONGTEXT,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			try {
				$wpdb->query($sql);
				$wpdb->query($sql2);
				update_site_option('wpr_db_version', $version);
			} catch (\Throwable $th) {
				throw $th;
			}
		}

		// set pusher admin channel key
		update_option("wpr_pusher_channel_key", md5('replacewithastrongpassword'));
	}
}
