<?php

if ( !defined( 'ABSPATH' ) )
   exit;

class WC_DK_PLUS_Settings {
	private $username;
    private $password;

	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	public function init() {

		add_settings_section( 'wc_dk_plus_section', __( 'Save DK Plus API username and password', 'wc_dk_plus_td' ), array( $this, 'developers_section_callback' ), 'wc_dk_plus_page' );

		add_settings_field(
			'wc_dk_plus_username',
			__( 'Username', 'wc_dk_plus_td' ),
			array( $this, 'settings_username_callback' ),
			'wc_dk_plus_page',
			'wc_dk_plus_section',
			array(
				'label_for'             => 'wc_dk_plus_username'
			)
		);

		add_settings_field(
			'wc_dk_plus_password',
			__( 'Password', 'wc_dk_plus_td' ),
			array( $this, 'settings_password_callback' ),
			'wc_dk_plus_page',
			'wc_dk_plus_section',
			array(
				'label_for'             => 'wc_dk_plus_password',
			)
		);
        
		register_setting(
			'wc_dk_plus_page',
			'wc_dk_plus_password',
		); 
		register_setting(
			'wc_dk_plus_page',
			'wc_dk_plus_username',
		);

	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['wc_dk_plus_username'] ) ) {
			$new_input['wc_dk_plus_username'] = absint( $input['wc_dk_plus_username'] );
		}

		if ( isset( $input['wc_dk_plus_password'] ) ) {
			$new_input['wc_dk_plus_password'] = sanitize_text_field( $input['wc_dk_plus_password'] );
		}

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
		$this->username = get_option( 'wc_dk_plus_username' );
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo isset( $this->username ) ? esc_attr( $this->username ) : 'username-text'; ?>" /> 
		<?php
	}

	public function settings_password_callback( $args ) {
		$this->password = get_option( 'wc_dk_plus_password' );
		?>
		<input type="password" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for']); ?>" value="<?php echo isset( $this->password ) ? esc_attr( $this->password ) : 'password-text'; ?>" />
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
				settings_fields( 'wc_dk_plus_page' );
				do_settings_sections( 'wc_dk_plus_page' );
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}
}
