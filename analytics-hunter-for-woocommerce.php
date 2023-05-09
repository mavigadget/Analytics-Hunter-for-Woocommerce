<?php
/**
 * Plugin Name:         Analytics Hunter for WooCommerce
 * Plugin URI:          https://www.analyticshunter.com
 * Description:         Grow your business with Analytics Hunter! Use this official plugin to help analyze your business.
 * Version:             1.0.0
 * Requires at least:   6.2
 * Requires PHP:        7.4
 * Author:              Analyticshunter
 * Author URI:          https://www.analyticshunter.com
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         analytics-hunter-for-woocommerce
 * Domain Path:         /languages
 * WC requires at least: 7.6.1
 * WC tested up to: 7.6.1
 */

defined( 'ABSPATH' ) || exit;

class WC_Analytics_Hunter_Loader {

    /**
     * @var string the plugin version.
     */
    const PLUGIN_VERSION = '1.0.0';

    // Minimum PHP version required by this plugin.
    const MINIMUM_PHP_VERSION = '7.4.0';

    // Minimum WooCommerce version required by this plugin.
    const MINIMUM_WC_VERSION = '7.6.1';

    // Minimum WordPress version required by this plugin.
    const MINIMUM_WP_VERSION = '6.2';

    // Plugin name, for displaying notices
    const PLUGIN_NAME = 'Analytics Hunter for WooCommerce';

    /**
     * Admin notices to add.
     *
     * @var array Array of admin notices.
     */
    private array $notices;


    /**
     * Constructs the class.
     *
     * @since 1.0.0
     */
    public function __construct() {

        register_activation_hook( __FILE__, array( $this, 'activation_check' ) );

        add_action( 'admin_init', array( $this, 'check_environment' ) );

        add_action( 'admin_notices', array( $this, 'add_plugin_notices' ) ); // admin_init is too early for the get_current_screen() function.
        add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );

        // If the environment check fails, initialize the plugin.
        if ( $this->is_environment_compatible() ) {
            add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        }
    }

    /**
     * Initializes the plugin.
     *
     * @since 1.0.0
     */
    public function init_plugin(): void
    {

        if ( ! $this->plugins_compatible() ) {
            return;
        }

        require_once plugin_dir_path( __FILE__ ) . 'class-wc-analyticshunter.php';

        // fire it up!
        if ( function_exists( 'analytics_hunter_for_woocommerce' ) ) {
            analytics_hunter_for_woocommerce();
        }
    }


    /**
     * Determines if the required plugins are compatible.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    private function plugins_compatible(): bool
    {
        return $this->is_wp_compatible() && $this->is_wc_compatible();
    }


    /**
     * Checks the server environment and other factors and deactivates plugins as necessary.
     *
     * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
     *
     * @internal
     *
     * @since 1.0.0
     */
    public function activation_check(): void
    {

        if ( ! $this->is_environment_compatible() ) {

            $this->deactivate_plugin();

            wp_die( self::PLUGIN_NAME . ' could not be activated. ' . $this->get_environment_message() );
        }
    }


    /**
     * Determines if the server environment is compatible with this plugin.
     *
     * Override this method to add checks for more than just the PHP version.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    private function is_environment_compatible(): bool
    {
        return version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' );
    }


    /**
     * Deactivates the plugin.
     *
     * @internal
     *
     * @since 1.0.0
     */
    protected function deactivate_plugin(): void
    {

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }


    /**
     * Gets the message for display when the environment is incompatible with this plugin.
     *
     * @since 1.0.0
     *
     * @return string
     */
    private function get_environment_message(): string
    {

        return sprintf( 'The minimum PHP version required for this plugin is %1$s. You are running %2$s.', self::MINIMUM_PHP_VERSION, PHP_VERSION );
    }


    /**
     * Checks the environment on loading WordPress, just in case the environment changes after activation.
     *
     * @internal
     *
     * @since 1.0.0
     */
    public function check_environment(): void
    {

        if ( ! $this->is_environment_compatible() && is_plugin_active( plugin_basename( __FILE__ ) ) ) {

            $this->deactivate_plugin();

            $this->add_admin_notice( 'bad_environment', 'error', self::PLUGIN_NAME . ' has been deactivated. ' . $this->get_environment_message() );
        }
    }

    /**
     * Adds an admin notice to be displayed.
     *
     * @param string $slug    The slug for the notice.
     * @param string $class   The css class for the notice.
     * @param string $message The notice message.
     *@since 1.0.0
     *
     */
    private function add_admin_notice(string $slug, string $class, string $message ): void
    {

        $this->notices[ $slug ] = array(
            'class'   => $class,
            'message' => $message,
        );
    }


    /**
     * Determines if the WordPress compatible.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    private function is_wp_compatible(): bool
    {

        if ( ! self::MINIMUM_WP_VERSION ) {
            return true;
        }

        return version_compare( get_bloginfo( 'version' ), self::MINIMUM_WP_VERSION, '>=' );
    }


    /**
     * Adds notices for out-of-date WordPress and/or WooCommerce versions.
     *
     * @internal
     *
     * @since 1.0.0
     */
    public function add_plugin_notices(): void
    {

        if ( ! $this->is_wp_compatible() ) {
            if ( current_user_can( 'update_core' ) ) {
                $this->add_admin_notice(
                    'update_wordpress',
                    'error',
                    sprintf(
                    /* translators: %1$s - plugin name, %2$s - minimum WordPress version required, %3$s - update WordPress link open, %4$s - update WordPress link close */
                        esc_html__( '%1$s requires WordPress version %2$s or higher. Please %3$supdate WordPress &raquo;%4$s', 'facebook-for-woocommerce' ),
                        '<strong>' . self::PLUGIN_NAME . '</strong>',
                        self::MINIMUM_WP_VERSION,
                        '<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">',
                        '</a>'
                    )
                );
            }
        }

        // Notices to install and activate or update WooCommerce.
        $screen = get_current_screen();
        if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
            return; // Do not display the install/update/activate notice in the update plugin screen.
        }

        $plugin = 'woocommerce/woocommerce.php';
        // Check if WooCommerce is activated.
        if ( ! $this->is_wc_activated() ) {

            if ( $this->is_wc_installed() ) {
                // WooCommerce is installed but not activated. Ask the user to activate WooCommerce.
                if ( current_user_can( 'activate_plugins' ) ) {
                    $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
                    $message        = sprintf(
                    /* translators: %1$s - Plugin Name, %2$s - activate WooCommerce link open, %3$s - activate WooCommerce link close. */
                        esc_html__( '%1$s requires WooCommerce to be activated. Please %2$sactivate WooCommerce%3$s.', 'facebook-for-woocommerce' ),
                        '<strong>' . self::PLUGIN_NAME . '</strong>',
                        '<a href="' . esc_url( $activation_url ) . '">',
                        '</a>'
                    );
                    $this->add_admin_notice(
                        'activate_woocommerce',
                        'error',
                        $message
                    );
                }
            } else {
                // WooCommerce is not installed. Ask the user to install WooCommerce.
                if ( current_user_can( 'install_plugins' ) ) {
                    $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
                    $message     = sprintf(
                    /* translators: %1$s - Plugin Name, %2$s - install WooCommerce link open, %3$s - install WooCommerce link close. */
                        esc_html__( '%1$s requires WooCommerce to be installed and activated. Please %2$sinstall WooCommerce%3$s.', 'facebook-for-woocommerce' ),
                        '<strong>' . self::PLUGIN_NAME . '</strong>',
                        '<a href="' . esc_url( $install_url ) . '">',
                        '</a>'
                    );
                    $this->add_admin_notice(
                        'install_woocommerce',
                        'error',
                        $message
                    );
                }
            }
        } elseif ( ! $this->is_wc_compatible() ) { // If WooCommerce is activated, check for the version.
            if ( current_user_can( 'update_plugins' ) ) {
                $update_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $plugin, 'upgrade-plugin_' . $plugin );
                $this->add_admin_notice(
                    'update_woocommerce',
                    'error',
                    sprintf(
                    /* translators: %1$s - Plugin Name, %2$s - minimum WooCommerce version, %3$s - update WooCommerce link open, %4$s - update WooCommerce link close, %5$s - download minimum WooCommerce link open, %6$s - download minimum WooCommerce link close. */
                        esc_html__( '%1$s requires WooCommerce version %2$s or higher. Please %3$supdate WooCommerce%4$s to the latest version, or %5$sdownload the minimum required version &raquo;%6$s', 'facebook-for-woocommerce' ),
                        '<strong>' . self::PLUGIN_NAME . '</strong>',
                        self::MINIMUM_WC_VERSION,
                        '<a href="' . esc_url( $update_url ) . '">',
                        '</a>',
                        '<a href="' . esc_url( 'https://downloads.wordpress.org/plugin/woocommerce.' . self::MINIMUM_WC_VERSION . '.zip' ) . '">',
                        '</a>'
                    )
                );
            }
        }
    }


    /**
     * Query WooCommerce activation.
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_wc_activated(): bool
    {
        return class_exists( 'WooCommerce' );
    }


    /**
     * Determine if WooCommerce is installed.
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_wc_installed(): bool
    {
        $plugin            = 'woocommerce/woocommerce.php';
        $installed_plugins = get_plugins();

        return isset( $installed_plugins[ $plugin ] );
    }


    /**
     * Determines if the WooCommerce compatible.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    private function is_wc_compatible(): bool
    {

        if ( ! self::MINIMUM_WC_VERSION ) {
            return true;
        }

        return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, self::MINIMUM_WC_VERSION, '>=' );
    }


    /**
     * Displays any admin notices added with \WC_Analytics_Hunter_Loader::add_admin_notice()
     *
     * @internal
     *
     * @since 1.0.0
     */
    public function admin_notices(): void
    {

        foreach ($this->notices as $notice ) {

            ?>
            <div class="<?php echo esc_attr( $notice['class'] ); ?>">
                <p>
                    <?php
                    echo wp_kses(
                        $notice['message'],
                        array(
                            'a'      => array(
                                'href' => array(),
                            ),
                            'strong' => array(),
                        )
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }
}

new WC_Analytics_Hunter_Loader();