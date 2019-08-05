<?php
/**
 * Provide the admin area view for the plugin
 */

declare( strict_types = 1 );

// If this file is called directly, abort.
defined( 'WPINC' ) || die();

$options = [
	'theme' => '',
];

// phpcs:disable WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET ) && isset( $_GET['theme'] ) ) {
	$options['theme'] = sanitize_text_field( wp_unslash( $_GET['theme'] ) );
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended

?>

<div class="wrap" id="template-usage">

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="POST" action="<?php echo esc_url( self::get_network_form_action_url() ); ?>">

		<?php wp_nonce_field( 'template-usage-validate' ); ?>

		<p><?php esc_html_e( 'Discover which templates are currently being used.', 'template-usage' ); ?></p>

		<div class="manage-menus" style="margin: 1em 0;">

			<?php $this->display_theme_select( $options['theme'] ); ?>

			<span class="submit-btn"><input type="submit" class="button" value="Select"></span>

		</div>

		<?php $this->display_template_report( $options['theme'] ); ?>

	</form>

</div>
