<?php

require_once __DIR__ . '/vendor/autoload.php';

class WC_Analyticshunter{
    /** @var string the plugin version */
    const VERSION = WC_Analytics_Hunter_Loader::PLUGIN_VERSION;

    /** @var string for backwards compatibility TODO: remove this in v2.0.0 {CW 2020-02-06} */
    const PLUGIN_VERSION = self::VERSION;

    /** @var string the plugin ID */
    const PLUGIN_ID = 'analytics_hunter_for_woocommerce';

    /**
     * Constructs the plugin.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initializes the plugin.
     *
     * @internal
     */
    public function init() {

    }
}

/**
 * Gets the Analytics Hunter for WooCommerce plugin instance.
 *
 * @since 1.0.0
 *
 * @return WC_Analyticshunter instance of the plugin
 */
function analytics_hunter_for_woocommerce(): WC_Analyticshunter
{
    return new WC_Analyticshunter();
}
