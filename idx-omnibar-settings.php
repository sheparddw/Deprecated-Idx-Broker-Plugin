<?php
class idx_omnibar_settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'IDX Omnibar',
            'manage_options',
            'idx-omnibar-settings',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'my_option_name' );
        ?>
        <div class="wrap">
            <h2>IDX Omnibar Settings</h2>
            <p>After the <a href="https://wordpress.org/plugins/idx-broker-platinum/" target="_blank">IDX Broker Plugin</a> is installed, hit the Refresh button below.</p>
            <form method="post" action="">
            <?php
                // This prints out all hidden setting fields
                //settings_fields( 'my_option_group' );
                //do_settings_sections( 'idx-omnibar-settings' );
                submit_button('Refresh Locations');
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init(){}



    /**
     * Print the Section text
     */
    public function print_section_info()
    {

    }


}

if( is_admin() )
    $idx_omnibar_settings_page = new idx_omnibar_settings();

if(isset($_POST['submit'])){
  include 'idx-omnibar-get-locations.php';
}
