<?php 

class CurrencySettings {
    private static $_instance;

    public static function get_instance() {
        if ( static::$_instance == null ) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct()
    {
        $this->add_filters();
        $this->add_actions();
        
    }

    public function add_actions() {
        add_action('admin_enqueue_scripts', [ $this, 'run_scripts']);
    }

    public function run_scripts() {
        wp_enqueue_script( 'wcc-admin-settings' );
    }

    public function add_filters() {
        add_filter( 'woocommerce_general_settings', array($this, 'currency_settings_page'));
    }

    public function currency_settings_page($settings = null) {

        $options = [
            'bnm' => 'BNM', 
            'oer' => 'Open Exchange Rate API', 
            'fixedrate' => 'Fixed Rate'
        ];

        $addon_settings = [
            array(
                'title' => __( 'CHIP Convert Currency API Options', 'woocommerce' ),
                'type'  => 'title',
                'desc'  => __( 'The following options convert the base currency to MYR for CHIP purpose', 'woocommerce' ),
                'id'    => 'wcc_api_options',
            ),
            array(
                'title'    => __( 'API Options', 'woocommerce' ),
                'desc'     => __( 'Configure your preferred providers, Default : BNM', 'woocommerce' ),
                'id'       => 'chip_wcc_options',
                'default'  => 'bnm',
                'type'     => 'select',
                'class'    => 'wc-enhanced-select',
                'desc_tip' => true,
                'options'  => $options,
            ),
            array(
                'title'   => __( 'Open Exchange Rate API Key', 'woocommerce' ),
                'desc'    => __( 'If you are using Open Exchange Rate, you need to set the key for the exchange to work', 'woocommerce' ),
                'id'      => 'wcc_oer_key',
                'css'     => 'min-width: 50px;',
                'default' => '',
                'desc_tip' => true,
                'type'    => 'text',
            ),
            array(
                'title'   => __( 'Fixed Exchange Rate', 'woocommerce' ),
                'desc'    => __( 'You may use your own exchange rate instead of automated from API providers. By setting this option, the plugin will not fetch exchange rate from API providers.', 'woocommerce' ),
                'id'      => 'wcc_fixed_rate',
                'css'     => 'min-width: 50px;',
                'default' => '',
                'desc_tip' => true,
                'type'    => 'text',
            ),
            array(
                'title'   => __( 'Percentage Charge', 'woocommerce' ),
                'desc'    => __( 'Add percentage charge, the charge calculation are added after conversion is done.', 'woocommerce' ),
                'id'      => 'wcc_percentage_rate',
                'css'     => 'min-width: 50px;',
                'default' => '',
                'desc_tip' => true,
                'type'    => 'text',
            ),

            array(
                'title'   => __( 'Fixed Charge (cent in MYR)', 'woocommerce' ),
                'desc'    => __( 'Add fixed charge, the charge calculation are added after conversion is done.', 'woocommerce' ),
                'id'      => 'wcc_fixed_charge',
                'css'     => 'min-width: 50px;',
                'default' => '',
                'desc_tip' => true,
                'type'    => 'text',
            ),
            
            array(
                'type' => 'sectionend',
                'id'   => 'api_options',
            ),
        ];
          
        if (! is_null($settings)) {
            return array_merge($settings, $addon_settings);
        } else {
            return $addon_settings;
        }
    }
}

CurrencySettings::get_instance();


