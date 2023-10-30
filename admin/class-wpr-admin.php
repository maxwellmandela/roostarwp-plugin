<?php

/**
 * The admin-specific functionality of the plugin.
 *
 *
 * @package    WRP
 * @subpackage WRP/admin
 */

/**
 *
 * @package    WRP
 * @subpackage WRP/admin
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */
class WRP_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $wpr    The ID of this plugin.
	 */
	private $wpr;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wpr       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($wpr, $version)
	{

		$this->wpr = $wpr;
		$this->version = $version;

		add_action('admin_menu', array($this, 'create_menu'), 10);

		$this->load_dependencies();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook_suffix)
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WRP_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WRP_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// if ($hook_suffix == 'toplevel_page_wrp-home') {
		wp_enqueue_style('{$this->wpr}-styles', plugin_dir_url(__FILE__) . 'css/style.css', [], false);
		wp_enqueue_style('{$this->wpr}-emoji', "https://emoji-css.afeld.me/emoji.css", [], false);
		wp_enqueue_style('{$this->wpr}-fa', "https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css", [], false);
		// }
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook_suffix)
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WRP_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WRP_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// if ($hook_suffix == 'toplevel_page_wrp-home') {
		wp_enqueue_script('{$this->wpr}' . '-pusher', plugin_dir_url(__FILE__) . 'js/pusher.min.js', $this->version, null);

		$title_nonce = wp_create_nonce('title_example');
		wp_localize_script('wp', 'wrp_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce'    => $title_nonce,
		));
		// }
	}

	public function create_menu()
	{
		$capability = 'administrator';

		add_menu_page('Roostar settings', 'Roostar Livechat', $capability, 'wpr-home', array($this, 'render_admin_page'), 'dashicons-format-chat', 10);
		add_submenu_page('wpr-home', 'Roostar Conversations', 'Conversations', 'manage_options', 'wpr_conversations', array($this, 'conversations_page'), 9);
		add_submenu_page('wpr-home', 'Roostar Training', 'Custom Training', 'manage_options', 'wpr_chatbots', array($this, 'chatbots_page'), 8);
	}

	public function render_admin_page()
	{
		include_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-wrp-admin-display.php';
	}

	public function settings_page()
	{
		include_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-wrp-admin-display-settings.php';
	}

	public function chatbots_page()
	{
		include_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-wrp-admin-display-chatbots.php';
	}

	public function conversations_page()
	{
		include_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-wrp-admin-display-conversations.php';
	}

	public function load_dependencies()
	{
		/**
		 * Http Requests handler
		 */
		include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wrp-admin-ajax.php';
		include_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/settings.php';
	}
}
