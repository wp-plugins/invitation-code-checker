<?php
/*
Plugin Name: Invitation Code Checker
Plugin URI: http://wordpress.org/extend/plugins/invitation-code-checker/
Description: With this plugin registrations are only allowed if the user has an invitation code. This plugin is only for WordPress MU and is BuddyPress compatible.
Author: Dennis Morhardt
Version: 1.0.1
Author URI: http://www.dennismorhardt.de/
Site Wide Only: true

Copyright 2009 Dennis Morhardt <info@dennismorhardt.de>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Invitation_Code_Checker {
	var $errors;

	function Invitation_Code_Checker() {
		add_action( 'signup_extra_fields', array( &$this, 'add_code_field_to_signup' ) );
		add_action( 'bp_after_signup_profile_fields', array( &$this, 'add_code_field_to_signup_buddypress' ) );
		add_filter( 'wpmu_validate_user_signup', array( &$this, 'checkup' ) );
		add_filter( 'bp_signup_validate', array( &$this, 'checkup_buddypress' ) );
		add_action( 'update_wpmu_options', array( &$this, 'save_admin_options' ) );
		add_action( 'wpmu_options', array( &$this, 'add_admin_options' ) );
		load_plugin_textdomain( 'invitation-code-checker', WP_PLUGIN_URL . basename( dirname( __FILE__ ) ), basename( dirname( __FILE__ ) ) );
	}
	
	function add_code_field_to_signup( $errors ) {
		$code = get_site_option( 'invitation_code' );
		if ( empty( $code ) ) return; ?>
		<label for="invitation_code"><?php _e( 'Invitation Code:', 'invitation-code-checker' ) ?></label>
		<?php if ( $errmsg = $errors->get_error_message( 'invitation_code' ) ) { ?>
			<p class="error"><?php echo $errmsg ?></p>
		<?php } ?>
		<input name="invitation_code" style="font-size:24px; margin:5px 0px; width:100%;" type="text" id="invitation_code" value="<?php echo wp_specialchars( $_POST['invitation_code'], 1 ) ?>" maxlength="200" /><br /><?php _e( 'The registration is only permitted with a valid invitation code.', 'invitation-code-checker' ) ?>
	<?php }
	
	function add_code_field_to_signup_buddypress() {
		$code = get_site_option( 'invitation_code' );
		if ( empty( $code ) ) return; ?>
		<div class="register-section" id="invitation-code-section" style="margin:0 0 40px; clear:left; width:48%;">
			<label for="invitation_code"><?php _e( 'Invitation Code', 'invitation-code-checker' ) ?> <?php _e( '(required)', 'invitation-code-checker' ) ?></label>
			<?php if ( is_object( $this->errors ) && $errmsg = $this->errors->get_error_message( 'invitation_code' ) ) { ?>
				<div class="error"><?php echo $errmsg ?></div>
			<?php } ?>
			<input type="text" name="invitation_code" id="invitation_code" style="width:50%;" value="<?php echo wp_specialchars( $_POST['invitation_code'], 1 ) ?>" />
		</div>
	<?php }
	
	function checkup( $content ) {
		$code = esc_sql( $_POST['invitation_code'] );
		$code_db = get_site_option( 'invitation_code' );

		if ( empty( $code_db ) )
			return $content;
		
		if ( empty( $code ) )
			$content['errors']->add( 'invitation_code', __( 'Please enter an invitation code!', 'invitation-code-checker' ) );	
	
		if ( $code != $code_db )
			$content['errors']->add( 'invitation_code', __( 'The entered invitation code is wrong. Please try again!', 'invitation-code-checker' ) );
	
		$this->errors = $content['errors'];

		return $content;
	}
	
	function checkup_buddypress() {
		global $bp;
		
		if( $this->errors->errors['invitation_code'][0] )
			$bp->signup->errors['invitation_code'] = $this->errors->errors['invitation_code'][0];
	}
	
	function add_admin_options() { ?>
		<h3><?php _e( 'Invitation Code', 'invitation-code-checker' ) ?></h3> 
		<table class="form-table">
			<tr valign="top"> 
				<th scope="row"><?php _e('Invitation Code:') ?></th> 
				<td>
					<input name="invitation_code" type="text" id="invitation_code" style="width: 95%" value="<?php echo get_site_option( 'invitation_code' ) ?>" size="45" />
					<br />
					<?php _e( 'Enter an invitation code, which is required to register. Leave blank to disable.', 'invitation-code-checker' ) ?>
				</td> 
			</tr> 
		</table>
	<?php }
	
	function save_admin_options() {
		$code = esc_sql( $_POST['invitation_code'] );
		update_site_option( "invitation_code", $code );
	}
}

$Invitation_Code_Checker = new Invitation_Code_Checker();

?>