<?php
/**
 * Plugin Name: Timify
 * Plugin URI:  https://www.themeim.com/
 * Description: Generate blogs following the latest trend with Timify. Apply post date, post time, and reading time on your blogs along with modifying them. And it is so easy to use
 * Version:     1.1.2
 * Author:      ThemeIM
 * Author URI:  https://themeim.com/
 * License:     GPLv2+ 
 * Text Domain: timify
 * Domain Path: /languages/
 */

 
/**
 * Copyright (c) 2021 themeim (email : support@themeim.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// don't call the file directly
defined( 'ABSPATH' ) || exit();

/**
 * Main Timify Class.
 *
 * @class Timify
 */
final class Timify {
	/**
	 * Timify version.
	 *
	 * @var string
	 */
	protected $version = '1.1.1';

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.6.0';


	/**
	 * The single instance of the class.
	 *
	 * @var Timify
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Timify Instance.
	 *
	 * Ensures only one instance of Timify is loaded or can be loaded.
	 *
	 * @return Timify - Main instance.
	 * @since 1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'timify' ), '1.0.0' );
	}

	/**
	 * Universalizing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Universalizing instances of this class is forbidden.', 'timify' ), '1.0.0' );
	}


	/**
	 * Timify constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define Timify Constants.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function define_constants() {
		define( 'TIMIFY_VERSION', $this->version );
		define( 'TIMIFY_FILE', __FILE__ );
		define( 'TIMIFY_PATH', dirname( TIMIFY_FILE ) );
		define( 'TIMIFY_INCLUDES', TIMIFY_PATH . '/includes' );
		define( 'TIMIFY_URL', plugins_url( '', TIMIFY_FILE ) );
		define( 'TIMIFY_ASSETS_URL', TIMIFY_URL . '/assets' );
		define( 'TIMIFY_BASENAME', plugin_basename( __FILE__ ) );
	}

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
	 * 
     * @since 1.0.0
	 * 
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON') && !defined('REST_REQUEST');
        }
    }

	/**
	 * Include all required files
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function includes() {
		//boot
		//require_once( TIMIFY_PATH . '/vendor/autoload.php' );
		require_once( TIMIFY_INCLUDES . '/script-functions.php' );
		require_once( TIMIFY_INCLUDES . '/db.php' );
		require_once( TIMIFY_INCLUDES . '/helpers/class.helper-functions.php' );

		require_once( TIMIFY_INCLUDES . '/frontend/class.frontend.php' );
		require_once( TIMIFY_INCLUDES . '/frontend/class.shortcode.php' );

		if ( $this->is_request( 'admin' ) ) {
            require_once( TIMIFY_INCLUDES . '/admin/admin-init.php' );
		}
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */

	private function init_hooks() {
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		add_action( 'admin_init', array( $this, 'check_environment' ) );
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );
		add_action( 'init', array( $this, 'localization_setup' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'plugin_action_links_'.TIMIFY_BASENAME, array( &$this, 'plugin_action_links' ) );
		add_action( 'admin_notices', array(&$this,'admin_notice_info') );

		add_action( 'wp_ajax_timify_remove_notification', array($this,'remove_notification') );
		add_action( 'wp_ajax_nopriv_timify_remove_notification', array($this,'remove_notification') );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );

	}

	public function remove_notification(){
		add_option('timify_admin_notice_info', 1);
		if ( !get_transient( 'timify_admin_notice_time_'. get_current_user_id() ) ) {
			update_option('track_transient', 0);
		}
	}

	/**
	 * Ensure theme and server variable compatibility
	 */
    public function check_environment()
    {
        if (version_compare(PHP_VERSION, $this->min_php, '<=') &&  version_compare( WP_VERSION, '3.6', '<=' ) ) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die("Unsupported PHP version Min required PHP Version:{$this->min_php}");
        }
    }

	/**
	 * increase themes sells with notice info
	 * 
	 * @since 1.0.0
	 * @return void
	 */

	public function admin_notice_info() {
		$themes  = array( 'blurb', 'dialer', 'vigo');
		$active_theme = get_option( 'template' );
		if ( ! in_array( $active_theme, $themes ) ) {
			if ( get_option('has_transient') == 0 ) {
				set_transient( 'timify_admin_notice_time_'. get_current_user_id() , true, WEEK_IN_SECONDS );
				update_option('has_transient', 1);
				update_option('track_transient', 1);
			}
			if (  !get_option('timify_admin_notice_info') || ( get_option('track_transient') && !get_transient( 'timify_admin_notice_time_'. get_current_user_id() ) ) ) {
				$all_themes = wp_get_themes();
				?>
				<div class="timify-notice notice notice-info is-dismissible">
					<p>
						<?php echo sprintf( __( 'You are currently using %1$s theme. Did you know that ThemeIM theme and plugins give you more features and flexibility.Check out our <a href="%2$s">Themes and Plugins </a> now!', 'timify' ), $all_themes[ $active_theme ], 'https://themeim.com' ); ?>
					</p>
				</div>
				<?php
			}
		} else {
			delete_option('timify_admin_notice_info');
			delete_option('has_transient');
			delete_option('track_transient');
		}
	}

    /**
	 * Activate plugin.
	 * @return void
	 * @since 1.0.0
	 */

	public function activate_plugin() {
		timify_create_table();
	}

	/**
	 * Deactivate plugin.
	 * @return void
	 * @since 1.0.0
	 */

	public function deactivate_plugin() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		delete_option( 'timify_settings' );
		delete_option( 'timify_reading_settings' );
		delete_option( 'timify_word_settings' );
		delete_option( 'timify_view_settings' );
	}

	/**
	 * loaded all plugins, trigger the timify_loaded hook.
	 * @since 1.0.0
	 */
	public function on_plugins_loaded() {
		do_action( 'timify_loaded' );
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'timify', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Plugin action links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$admin_url = admin_url();
		$link = array( '<a href="'.$admin_url.'options-general.php?page=timify_settings">Settings</a>' );

		return array_merge( $links, $link );
	}

	/**
	 * Add plugin docs links in plugin row links
	 *
	 * @param mixed $links Links
	 * @param mixed $file File
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$row_meta = array(
				'docs' => '<a href="' . esc_url( apply_filters( 'timify_docs_url', 'https://themeim.com/demo/timify/docs' ) ) . '" aria-label="' . esc_attr__( 'View documentation', 'timify' ) . '">' . esc_html__( 'Docs', 'timify' ) . '</a>',
			);
			return array_merge( $links, $row_meta );
		}
		return $links;
	}

	/**
	 * @return string
	 * @since 1.0.0
	 */
	public function get_version() {
		return $this->version;
	}


}

/**
 * @return Timify
 */
function timify_init() {
	return Timify::instance();
}

//fire off the plugin
timify_init();