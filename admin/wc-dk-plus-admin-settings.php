<?php

class WC_DK_PLUS_Settings {
	private $options;

	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	public function init() {
		
        add_settings_section( 'wc_dk_plus_section_developers', __( 'Save DK Plus API username and password', 'wc_dk_plus_td' ), array( $this, 'developers_section_callback' ), 'wc_dk_plus_page' );
        
        register_setting(
            'wc_dk_plus_setting',
            'wc_dk_plus_username', 
            array( $this, 'sanitize' ) // Sanitize
        );
        
		add_settings_field(
			'wc_dk_plus_username',
			__( 'Username', 'wc_dk_plus_td' ),
			array( $this, 'settings_username_callback' ),
			'wc_dk_plus_page',
			'wc_dk_plus_section_developers',
			array(
				'label_for'             => 'wc_dkplus_username',
				'class'                 => 'wc_dkplus_row',
				'wc_dkplus_custom_data' => 'custom',
			)
		);
        
        register_setting(
            'wc_dk_plus_setting',
            'wc_dk_plus_password', 
            array( $this, 'sanitize' ) // Sanitize
        );
        
        
        add_settings_field(
			'wc_dk_plus_password',
			__( 'Password', 'wc_dk_plus_td' ),
			array( $this, 'settings_password_callback' ),
			'wc_dk_plus_page',
			'wc_dk_plus_section_developers',
			array(
				'label_for'             => 'wc_dkplus_password',
				'class'                 => 'wc_dkplus_row',
				'wc_dkplus_custom_data' => 'custom',
			)
		);
	}
    
    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['username'] ) )
            $new_input['username'] = absint( $input['username'] );

        if( isset( $input['password'] ) )
            $new_input['password'] = sanitize_text_field( $input['password'] );

        return $new_input;
    }


	public function add_menu_page() {
		add_menu_page(
			'Woocommerce - DK Plus Settings',
			'DK Plus Options',
			'manage_options',
			'wc_dk_plus_page',
			array( $this, 'options_page_html' )
		);
	}

	public function developers_section_callback( $args ) {
		echo '<p id="' . esc_attr( $args['id'] ) . '">' . esc_html__( '', 'wc_dk_plus_td' ) . '</p>';
	}

	public function settings_username_callback( $args ) {
		$this->options = get_option( 'wporg_options' );
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="wporg_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo isset( $this->options[ $args['label_for'] ] ) ? esc_attr( $this->options[ $args['label_for'] ] ) : ''; ?>" /> 
        <?php
	}
    
    public function settings_password_callback ($args) {
        $this->options = get_option( 'wporg_options' );
       ?> 	
    <input type="password" id="<?php echo esc_attr( $args['label_for'] ); ?>_password" name="wporg_options[<?php echo esc_attr( $args['label_for'] ); ?>_password]" value="<?php echo isset( $this->options[ $args['label_for'] . '_password' ] ) ? esc_attr( $this->options[ $args['label_for'] . '_password' ] ) : ''; ?>" />
    <?php
    }

	public function options_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'wc_dkplus_messages', 'wc_dkplus_message', __( 'Settings Saved', 'wc_dk_plus_td' ), 'updated' );
		}

		settings_errors( 'wc_dkplus_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wc_dk_plus_setting' );
				do_settings_sections( 'wc_dk_plus_page' );
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}
}

// Instantiate the class
$wc_dkplus_settings = new WC_DK_PLUS_Settings();
