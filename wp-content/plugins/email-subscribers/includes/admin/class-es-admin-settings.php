<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Admin Settings
 *
 * @package    Email_Subscribers
 * @subpackage Email_Subscribers/admin
 * @author     Your Name <email@example.com>
 */
class ES_Admin_Settings {

	static $instance;

	public $subscribers_obj;

	public function __construct() {
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function es_settings_callback() {

		$submitted     = ! empty( $_POST['submitted'] ) ? $_POST['submitted'] : '';
		$submit_action = ! empty( $_POST['submit_action'] ) ? $_POST['submit_action'] : '';

		$nonce = esc_attr( Email_Subscribers::get_request( '_wpnonce' ) );

		if ( 'submitted' === $submitted && 'ig-es-save-admin-settings' === $submit_action ) {
			$options = $_POST;
			$options = apply_filters( 'ig_es_before_save_settings', $options );

			$options['ig_es_disable_wp_cron']   = isset( $options['ig_es_disable_wp_cron'] ) ? $options['ig_es_disable_wp_cron'] : 'no';
			$options['ig_es_track_email_opens'] = isset( $options['ig_es_track_email_opens'] ) ? $options['ig_es_track_email_opens'] : 'no';

			$text_fields_to_sanitize = array(
				'ig_es_from_name',
				'ig_es_admin_emails',
				'ig_es_email_type',
				'ig_es_optin_type',
				'ig_es_post_image_size',
				'ig_es_track_email_opens',
				'ig_es_enable_welcome_email',
				'ig_es_welcome_email_subject',
				'ig_es_confirmation_mail_subject',
				'ig_es_notify_admin',
				'ig_es_admin_new_contact_email_subject',
				'ig_es_enable_cron_admin_email',
				'ig_es_cron_admin_email_subject',
				'ig_es_cronurl',
				'ig_es_hourly_email_send_limit',
				'ig_es_disable_wp_cron'
			);

			$texarea_fields_to_sanitize = array(
				'ig_es_unsubscribe_link_content',
				'ig_es_subscription_success_message',
				'ig_es_subscription_error_messsage',
				'ig_es_unsubscribe_success_message',
				'ig_es_unsubscribe_error_message',
				'ig_es_welcome_email_content',
				'ig_es_confirmation_mail_content',
				'ig_es_admin_new_contact_email_content',
				'ig_es_cron_admin_email',
				'ig_es_blocked_domains'
			);

			$email_fields_to_sanitize = array(
				'ig_es_from_email'
			);

			foreach ( $options as $key => $value ) {
				if ( substr( $key, 0, 6 ) === 'ig_es_' ) {

				    $value = stripslashes_deep($value);

					if ( in_array( $key, $text_fields_to_sanitize ) ) {
						$value = sanitize_text_field( $value );
					} elseif ( in_array( $key, $texarea_fields_to_sanitize ) ) {
						$value = wp_kses_post($value);
					} elseif ( in_array( $key, $email_fields_to_sanitize ) ) {
						$value = sanitize_email( $value );
					}

					update_option( $key, wp_unslash( $value ) );
				}
			}

			do_action( 'ig_es_after_settings_save', $options );

			$message = __( 'Settings have been saved successfully!' );
			$status  = 'success';
			ES_Common::show_message( $message, $status );
		}


		?>

        <div class="wrap essettings">
            <h1 class="wp-heading-inline">Settings</h1>
            <form action="" method="post" id="email_tabs_form" class="ig-settings-form rcorners">

				<?php settings_fields( 'email_subscribers_settings' ); 
				$es_settings_tabs = array(
					'general' => array('icon' => 'admin-generic', 'name' => __( 'General', 'email-subscribers' ) ),
					'signup_confirmation'=> array('icon' => 'groups', 'name' => __( 'Notifications', 'email-subscribers' ) ),
					'email_sending'=> array('icon' => 'schedule', 'name' => __( 'Email Sending', 'email-subscribers' ) ),
					'security_settings'=> array('icon' => 'lock', 'name' => __( 'Security', 'email-subscribers' ) ),
				);
				$es_settings_tabs = apply_filters('ig_es_settings_tabs', $es_settings_tabs);
				?>

                <div id="es-settings-tabs">
                    <div id="menu-tab-listing" class="">
                        <ul class="main-tab-nav">
                        	<?php 
                        	foreach ($es_settings_tabs as $key => $value) {
							?>
                            	<li class="ig-menu-tab"><a href="#tabs-<?php echo $key?>"><i class="dashicons dashicons-<?php echo $value['icon']?>"></i>&nbsp;<?php echo $value['name'] ?></a></li>
							<?php
							}
                        	?>
                        </ul>
                    </div>
                    <div id="menu-tab-content">
						<?php $settings = self::get_registered_settings(); 
						foreach ($settings as $key => $value) {
							?>
							<div id="tabs-<?php echo $key?>"><?php $this->render_settings_fields( $value ); ?></div>
							<?php
						}
						?>
                    </div>

                </div>

                <!--
                <div class="content save">
                    <input type="hidden" name="submitted" value="submitted"/>
                    <input type="hidden" name="submit_action" value="ig-es-save-admin-settings"/>
					<?php $nonce = wp_create_nonce( 'es-update-settings' ); ?>

                    <input type="hidden" name="update-settings" id="ig-update-settings" value="<?php echo $nonce; ?>"/>
					<?php submit_button(); ?>
                </div>
                -->
            </form>
        </div>
		<?php

	}

	public function es_roles_sanitize_options( $input ) {
		$input['option_display_mode'] = wp_filter_nohtml_kses( $input['option_display_mode'] );
		$input['option_font_size']    = sanitize_text_field( absint( $input['option_font_size'] ) );
		$input['option_font_color']   = sanitize_text_field( $input['option_font_color'] );
		$input['option_custom_css']   = esc_textarea( $input['option_custom_css'] );

		return $input;
	}

	public static function get_registered_settings() {

		$general_settings = array(

			'sender_information' => array(
				'id'         => 'sender_information',
				'name'       => __( 'Sender', 'email-subscribers' ),
				'sub_fields' => array(
					'from_name' => array(
						'id'          => 'ig_es_from_name',
						'name'        => __( 'Name', 'email-subscribers' ),
						'desc'        => __( 'Choose a FROM name for all the emails to be sent from this plugin.', 'email-subscribers' ),
						'type'        => 'text',
						'placeholder' => __( 'Name', 'email-subscribers' ),
						'default'     => ''
					),

					'from_email' => array(
						'id'          => 'ig_es_from_email',
						'name'        => __( 'Email', 'email-subscribers' ),
						'desc'        => __( 'Choose a FROM email address for all the emails to be sent from this plugin', 'email-subscribers' ),
						'type'        => 'text',
						'placeholder' => __( 'Email Address', 'email-subscribers' ),
						'default'     => ''
					),
				)
			),

			'admin_email' => array(
				'id'      => 'ig_es_admin_emails',
				'name'    => __( 'Email Addresses', 'email-subscribers' ),
				'type'    => 'text',
				'desc'    => __( 'Enter the admin email addresses that should receive notifications (separated by comma).', 'email-subscribers' ),
				'default' => ''
			),

			'email_type' => array(
				'id'      => 'ig_es_email_type',
				'name'    => __( 'Email Type', 'email-subscribers' ),
				'desc'    => __( 'Select whether to send HTML or Plain Text email using WordPress or PHP mail(). We recommend to send email using WordPres', 'email-subscribers' ),
				'type'    => 'select',
				'options' => ES_Common::get_email_sending_type(),
				'default' => 'wp_html_mail'
			),

			'ig_es_optin_type' => array(
				'id'      => 'ig_es_optin_type',
				'name'    => __( 'Opt-in Type', 'email-subscribers' ),
				'desc'    => '',
				'type'    => 'select',
				'options' => ES_Common::get_optin_types(),
				'default' => ''
			),

			'ig_es_post_image_size' => array(
				'id'      => 'ig_es_post_image_size',
				'name'    => __( 'Image Size', 'email-subscribers' ),
				'type'    => 'select',
				'options' => ES_Common::get_image_sizes(),
				'desc'    => __( '<p>Select image size for {{POSTIMAGE}} to be shown in the Post Notification Emails.</p>', 'email-subscribers' ),
				'default' => 'full'
			),

			'ig_es_track_email_opens'        => array(
				'id'      => 'ig_es_track_email_opens',
				'name'    => __( 'Track Opens', 'email-subscribers' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Check this if you want to track email opening.', 'email-subscribers' ),
				'default' => 'yes'
			),

			//'ig_es_unsubscribe_link'             => array( 'type' => 'text', 'options' => false, 'placeholder' => '', 'readonly' => 'readonly', 'supplemental' => '', 'default' => '', 'id' => 'ig_es_unsubscribe_link', 'name' => __( 'Unsubscribe Link', 'email-subscribers' ), 'desc' => '', ),
			'ig_es_unsubscribe_link_content' => array(
				'type'         => 'textarea',
				'options'      => false,
				'placeholder'  => '',
				'supplemental' => '',
				'default'      => '',
				'id'           => 'ig_es_unsubscribe_link_content',
				'name'         => __( 'Show Unsubscribe Message In Email Footer', 'email-subscribers' ),
				'desc'         => __( 'Add text which you want your contact to see in footer to unsubscribe. Use {{UNSUBSCRIBE-LINK}} keyword to add unsubscribe link.', 'email-subscribers' ),
			),

			//'ig_es_optin_link'                   => array( 'type' => 'text', 'options' => false, 'readonly' => 'readonly', 'placeholder' => '', 'supplemental' => '', 'default' => '', 'id' => 'ig_es_optin_link', 'name' => 'Double Opt-In Confirmation Link', 'desc' => '', ),

			'subscription_messages' => array(
				'id'         => 'subscription_messages',
				'name'       => __( 'Subscription Success/ Error Messages', 'email-subscribers' ),
				'sub_fields' => array(
					'ig_es_subscription_success_message' => array(
						'type'         => 'textarea',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => __( 'You have been subscribed successfully!', 'email-subscribers' ),
						'id'           => 'ig_es_subscription_success_message',
						'name'         => __( 'Success Message', 'email-subscribers' ),
						'desc'         => __( 'Show this message if contact is successfully subscribed from Double Opt-In (Confirmation) Email', 'email-subscribers' )
					),

					'ig_es_subscription_error_messsage' => array(
						'type'         => 'textarea',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => __( 'Oops.. Your request couldn\'t be completed. This email address seems to be already subscribed / blocked.', 'email-subscribers' ),
						'id'           => 'ig_es_subscription_error_messsage',
						'name'         => __( 'Error Message', 'email-subscribers' ),
						'desc'         => __( 'Show this message if any error occured after clicking confirmation link from Double Opt-In (Confirmation) Email.', 'email-subscribers' )
					),

				)
			),

			'unsubscription_messages' => array(
				'id'         => 'unsubscription_messages',
				'name'       => __( 'Unsubscribe Success/ Error Messages', 'email-subscribers' ),
				'sub_fields' => array(

					'ig_es_unsubscribe_success_message' => array(
						'type'         => 'textarea',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => __( 'Thank You, You have been successfully unsubscribed. You will no longer hear from us.', 'email-subscribers' ),
						'id'           => 'ig_es_unsubscribe_success_message',
						'name'         => __( 'Success Message', 'email-subscribers' ),
						'desc'         => __( 'Once contact clicks on unsubscribe link, he/she will be redirected to a page where this message will be shown.', 'email-subscribers' )
					),


					'ig_es_unsubscribe_error_message' => array(
						'type'         => 'textarea',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => 'Oops.. There was some technical error. Please try again later or contact us.',
						'id'           => 'ig_es_unsubscribe_error_message',
						'name'         => __( 'Error Message', 'email-subscribers' ),
						'desc'         => __( 'Show this message if any error occured after clicking on unsubscribe link.', 'email-subscribers' )
					)
				)
			),


			/*
			'sent_report_subject' => array(
				'id'      => 'ig_es_sent_report_subject',
				'name'    => __( 'Sent Report Subject', 'email-subscribers' ),
				'type'    => 'text',
				'desc'    => __( 'Subject for the email report which will be sent to admin.', 'email-subscribers' ),
				'default' => 'Your email has been sent'
			),

			'sent_report_content' => array(
				'id'   => 'ig_es_sent_report_content',
				'name' => __( 'Sent Report Content', 'email-subscribers' ),
				'type' => 'textarea',
				'desc' => __( 'Content for the email report which will be sent to admin.</p><p>Available Keywords: {{COUNT}}, {{UNIQUE}}, {{STARTTIME}}, {{ENDTIME}}', 'email-subscribers' ),
			),
			*/
		);

		$general_settings = apply_filters( 'ig_es_registered_general_settings', $general_settings );

		$signup_confirmation_settings = array(

			'welcome_emails' => array(
				'id'         => 'welcome_emails',
				'name'       => __( 'Welcome Email', 'email-subscribers' ),
				'sub_fields' => array(

					'ig_es_enable_welcome_email' => array(
						'type'         => 'select',
						'options'      => array( 'yes' => __( 'Yes', 'email-subscribers' ), 'no' => __( 'No', 'email-subscribers' ) ),
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => 'yes',
						'id'           => 'ig_es_enable_welcome_email',
						'name'         => __( 'Enable?', 'email-subscribers' ),
						'desc'         => __( 'Send welcome email to new contact after signup.', 'email-subscribers' ),
					),

					'ig_es_welcome_email_subject' => array(
						'type'         => 'text',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => '',
						'id'           => 'ig_es_welcome_email_subject',
						'name'         => __( 'Subject', 'email-subscribers' ),
						'desc'         => '',
					),
					'ig_es_welcome_email_content' => array(
						'type'         => 'textarea',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => '',
						'id'           => 'ig_es_welcome_email_content',
						'name'         => __( 'Content', 'email-subscribers' ),
						'desc'         => __( 'Available keywords. {{FIRSTNAME}}, {{LASTNAME}}, {{NAME}}, {{EMAIL}}, {{LIST}}, {{UNSUBSCRIBE-LINK}}', 'email-subscribers' ),
					),
				)
			),

			'confirmation_notifications' => array(
				'id'         => 'confirmation_notifications',
				'name'       => __( 'Confirmation Email', 'email-subscribers' ),
				'sub_fields' => array(

					'ig_es_confirmation_mail_subject' => array(
						'type'         => 'text',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => '',
						'id'           => 'ig_es_confirmation_mail_subject',
						'name'         => __( 'Subject', 'email-subscribers' ),
						'desc'         => '',
					),

					'ig_es_confirmation_mail_content' => array(
						'type'         => 'textarea',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => '',
						'id'           => 'ig_es_confirmation_mail_content',
						'name'         => __( 'Content', 'email-subscribers' ),
						'desc'         => __( 'If Double Optin is set, contact will receive confirmation email with above content. You can use {{FIRSTNAME}}, {{LASTNAME}}, {{NAME}}, {{EMAIL}}, {{SUBSCRIBE-LINK}} keywords', 'email-subscribers' ),
					)
				)
			),

			'admin_notifications' => array(

				'id'         => 'admin_notifications',
				'name'       => __( 'Admin Notification On New Subscription', 'email-subscribers' ),
				'sub_fields' => array(

					'notify_admin' => array(
						'id'      => 'ig_es_notify_admin',
						'name'    => __( 'Notify?', 'email-subscribers' ),
						'type'    => 'select',
						'options' => array(
							'yes' => __( 'Yes', 'email-subscribers' ),
							'no'  => __( 'No', 'email-subscribers' )
						),
						'desc'    => __( 'Set this option to "Yes" to notify admin(s) for new contact signup.', 'email-subscribers' ),
						'default' => 'yes'
					),


					'new_contact_email_subject' => array(
						'id'      => 'ig_es_admin_new_contact_email_subject',
						'name'    => __( 'Subject', 'email-subscribers' ),
						'type'    => 'text',
						'desc'    => __( 'Subject for the admin email whenever a new contact signs up and is confirmed', 'email-subscribers' ),
						'default' => __( 'New email subscription', 'email-subscribers' )
					),

					'new_contact_email_content' => array(
						'id'      => 'ig_es_admin_new_contact_email_content',
						'name'    => __( 'Content', 'email-subscribers' ),
						'type'    => 'textarea',
						'desc'    => __( 'Content for the admin email whenever a new subscriber signs up and is confirmed. Available Keywords: {{NAME}}, {{EMAIL}}, {{LIST}}', 'email-subscribers' ),
						'default' => '',
					),
				)
			),

			'ig_es_cron_report' => array(
				'id'         => 'ig_es_cron_report',
				'name'       => __( 'Admin Notification On Every Campaign Sent', 'email-subscribers' ),
				'sub_fields' => array(

					'ig_es_enable_cron_admin_email'  => array(
						'id'      => 'ig_es_enable_cron_admin_email',
						'name'    => __( 'Notify?', 'email-subscribers' ),
						'type'    => 'select',
						'options' => array(
							'yes' => __( 'Yes', 'email-subscribers' ),
							'no'  => __( 'No', 'email-subscribers' )
						),
						'desc'    => __( 'Set this option to "Yes" to notify admin(s) on every campaign sent.', 'email-subscribers' ),
						'default' => 'yes'
					),
					'ig_es_cron_admin_email_subject' => array(
						'type'         => 'text',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => __( 'Campaign Sent!', 'email-subscribers' ),
						'id'           => 'ig_es_cron_admin_email_subject',
						'name'         => __( 'Subject', 'email-subscribers' ),
						'desc'         => '',
					),

					'ig_es_cron_admin_email' => array(
						'type'         => 'textarea',
						'options'      => false,
						'placeholder'  => '',
						'supplemental' => '',
						'default'      => '',
						'id'           => 'ig_es_cron_admin_email',
						'name'         => __( 'Content', 'email-subscribers' ),
						'desc'         => __( 'Send report to admin(s) whenever campaign is successfully sent to all contacts. Available Keywords: {{DATE}}, {{SUBJECT}}, {{COUNT}}', 'email-subscribers' ),
					)

				)
			)
		);

		$signup_confirmation_settings = apply_filters( 'ig_es_registered_signup_confirmation_settings', $signup_confirmation_settings );

		$email_sending_settings = array(
			'ig_es_cronurl'         => array(
				'type'         => 'text',
				'placeholder'  => '',
				'supplemental' => '',
				'default'      => '',
				'readonly'     => 'readonly',
				'id'           => 'ig_es_cronurl',
				'name'         => __( 'Cron URL', 'email-subscribers' ),
				'desc'         => __( sprintf( __( "You need to visit this URL to send email notifications. Know <a href='%s' target='_blank'>how to run this in background</a>", 'email-subscribers' ),
					"https://www.icegram.com/documentation/es-how-to-schedule-cron-emails-in-cpanel/?utm_source=es&utm_medium=in_app&utm_campaign=view_docs_help_page" ) )
			),
			'ig_es_disable_wp_cron' => array(
				'type'         => 'checkbox',
				'placeholder'  => '',
				'supplemental' => '',
				'default'      => 'no',
				'id'           => 'ig_es_disable_wp_cron',
				'name'         => __( 'Disable WordPress Cron', 'email-subscribers' ),
				'desc'         => __( 'Check this if you do not want Email Subscribers to use WP cron for sending emails', 'email-subscribers' )
			),

			'ig_es_hourly_email_send_limit' => array(
				'type'         => 'text',
				'placeholder'  => '',
				'supplemental' => '',
				'default'      => 50,
				'id'           => 'ig_es_hourly_email_send_limit',
				'name'         => __( 'Maximum Emails To Send In An Hour', 'email-subscribers' ),
				'desc'         => __( 'Total emails your host can send in an hour.', 'email-subscribers' )
			),

			'ig_es_test_send_email' => array(
				'type'         => 'html',
				'html'         => '<input id="es-test-email" type="email"/><input type="submit" name="submit" id="es-send-test" class="button button-primary" value="Send Email"><span class="es_spinner_image_admin" id="spinner-image" style="display:none"><img src="' . EMAIL_SUBSCRIBERS_URL . '/public/images/spinner.gif' . '"/></span>',
				'placeholder'  => '',
				'supplemental' => '',
				'default'      => '',
				'id'           => 'ig_es_test_send_email',
				'name'         => __( 'Send Test Email', 'email-subscribers' ),
				'desc'         => __( 'Enter email address to send test email.', 'email-subscribers' )
			)
		);

		$email_sending_settings = apply_filters( 'ig_es_registered_email_sending_settings', $email_sending_settings );

		$security_settings = array(
			'blocked_domains' => array(
				'id'      => 'ig_es_blocked_domains',
				'name'    => __( 'Blocked Domain(s)', 'email-subscribers' ),
				'type'    => 'textarea',
				'info'    => __( 'Seeing spam signups from particular domains? Enter domains names (one per line) that you want to block here.', 'email-subscribers' ),
				'default' => ''
			),

		);

		$security_settings = apply_filters( 'ig_es_registered_security_settings', $security_settings );

		$es_settings = array(
			'general'             => $general_settings,
			'signup_confirmation' => $signup_confirmation_settings,
			'email_sending'       => $email_sending_settings,
			'security_settings'   => $security_settings
		);

		return apply_filters( 'ig_es_registered_settings', $es_settings );
	}

	public function field_callback( $arguments ) {
		$field_html = '';
		if ( 'ig_es_cronurl' === $arguments['id'] ) {
			$value = ES_Common::get_cron_url();
		} else {
			$value = get_option( $arguments['id'] ); // Get the current value, if there is one
		}

		if ( ! $value ) { // If no value exists
			$value = ! empty( $arguments['default'] ) ? $arguments['default'] : ''; // Set to our default
		}

		$uid         = ! empty( $arguments['id'] ) ? $arguments['id'] : '';
		$type        = ! empty( $arguments['type'] ) ? $arguments['type'] : '';
		$placeholder = ! empty( $arguments['placeholder'] ) ? $arguments['placeholder'] : '';
		$readonly    = ! empty( $arguments['readonly'] ) ? $arguments['readonly'] : '';
		$html        = ! empty( $arguments['html'] ) ? $arguments['html'] : '';

		// Check which type of field we want
		switch ( $arguments['type'] ) {
			case 'text': // If it is a text field
				$field_html = sprintf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" %5$s />', $uid, $type, $placeholder, $value, $readonly );
				break;
			case 'password': // If it is a text field
				$field_html = sprintf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" %5$s />', $uid, $type, $placeholder, $value, $readonly );
				break;
			case 'email':
				$field_html = sprintf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $uid, $type, $placeholder, $value );
				break;
			case 'textarea':
				$field_html = sprintf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" size="100" rows="12" cols="58">%3$s</textarea>',
					$uid, $placeholder, $value );
				break;
			case 'file':
				$field_html = '<input type="text" id="logo_url" name="' . $uid . '" value="' . $value . '" /> <input id="upload_logo_button" type="button" class="button" value="Upload Logo" />';
				break;
			case 'checkbox' :
				$field_html = '<input type="checkbox" name="' . $uid . '"  value="yes" ' . checked( $value, 'yes', false ) . '/>' . $placeholder . '</input>';
				break;

			case 'select':
				if ( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$options_markup = "";
					foreach ( $arguments['options'] as $key => $label ) {
						$options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key,
							selected( $value, $key, false ), $label );
					}
					$field_html = sprintf( '<select name="%1$s" id="%1$s">%2$s</select>', $uid, $options_markup );
				}
				break;
			case 'html' :
				$field_html = $html;
			default;
		}

		$field_html .= '<br />';

		//If there is help text
		if ( ! empty( $arguments['desc'] ) ) {
			$helper     = $arguments['desc'];
			$field_html .= sprintf( '<span class="helper"> %s</span>', $helper ); // Show it
		}

		return $field_html;
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function es_get_all_settings() {

		global $wpdb;

		$condition                        = 'ig_es';
		$get_all_es_settings_from_options = $wpdb->prepare( "SELECT option_name, option_value
	 														FROM {$wpdb->prefix}options
	 														WHERE option_name LIKE %s", $wpdb->esc_like( $condition ) . '%' );
		$result                           = $wpdb->get_results( $get_all_es_settings_from_options, ARRAY_A );

		$settings = array();

		if ( ! empty( $result ) ) {
			foreach ( $result as $index => $data ) {
				$settings[ $data['option_name'] ] = $data['option_value'];
			}
		}

		return $settings;
	}

	function render_settings_fields( $fields ) {

		$html = "<table class='form-table'>";
		$html .= "<tbody>";
		$button_html = '';
		foreach ( $fields as $field ) {
			if(!empty($field['name'])){
				$html .= "<tr><th scope='row'>";
				$html .= $field['name'];

				//If there is help text
				if ( ! empty( $field['info'] ) ) {
					$helper = $field['info'];
					$html   .= "<br />" . sprintf( '<span class="helper">%s</span>', $helper ); // Show it
				}
				$button_html =  "<tr><td></td>" ;

				$html .= "</th>";
			}

			$html .= "<td>";
			if ( ! empty( $field['sub_fields'] ) ) {
				foreach ( $field['sub_fields'] as $sub_field ) {
					$html .= ( $sub_field !== reset( $field['sub_fields'] ) ) ? '<br/>' : '';
					$html .= '<div class="es_sub_headline"><strong>' . $sub_field['name'] . '</strong></div>';
					$html .= $this->field_callback( $sub_field ) . '<br/>';
				}
			} else {
				$html .= $this->field_callback( $field );
			}

			$html .= "</td></tr>";
		}
		$button_html = (!empty($button_html)) ? "<tr>" : $button_html;
		$html  .= $button_html."<td class='es-settings-submit-btn'>";
		$html  .= '<input type="hidden" name="submitted" value="submitted"/>';
		$html  .= '<input type="hidden" name="submit_action" value="ig-es-save-admin-settings"/>';
		$nonce = wp_create_nonce( 'es-update-settings' );
		$html  .= '<input type="hidden" name="update-settings" id="ig-update-settings" value="' . $nonce . '"/>';
		$html  .= '<input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Save Settings', 'email-subscribers' ) . '">';
		$html  .= "</td></tr>";
		$html  .= "</tbody>";
		$html  .= "</table>";
		echo $html;

	}


}