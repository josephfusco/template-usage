<?php

declare( strict_types = 1 );

namespace Template_Usage;

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the Template Usage, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 */
class Admin extends Plugin {

	/**
	 * The multisite sites.
	 *
	 * @var array
	 */
	private $sites;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		// Exit if not in wp-admin.
		if ( ! is_admin() ) {
			return;
		}

		$this->sites = get_sites();
	}

	/**
	 * Register the options page for the plugin.
	 */
	public function register_network_admin_page() : void {
		add_menu_page(
			__( 'Template Usage', 'template-usage' ),
			__( 'Template Usage', 'template-usage' ),
			'manage_options',
			'template-usage',
			[ $this, 'network_admin_page_cb' ],
			'dashicons-admin-page'
		);
	}

	/**
	 * Callback for the network admin page.
	 */
	public function network_admin_page_cb() : void {
		include_once 'Admin/template-usage.php';
	}

	/**
	 * Get the form action url needed for a network admin page.
	 */
	public static function get_network_form_action_url() : string {
		return add_query_arg(
			'action',
			'template_usage',
			network_admin_url( 'edit.php' )
		);
	}

	/**
	 * Do stuff with our saved form options.
	 */
	public function update_options() : void {
		check_admin_referer( 'template-usage-validate' );

		$options = [
			'theme' => '',
		];

		if ( isset( $_POST ) && isset( $_POST['theme'] ) ) {
			$option['theme'] = sanitize_text_field( wp_unslash( $_POST['theme'] ) );
		}

		$redirect_url = add_query_arg(
			[
				'page'  => 'template-usage',
				'theme' => $option['theme'],
			],
			network_admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );

		exit;
	}

	/**
	 * Enqueue our plugin's styles.
	 *
	 * @param string $hook The admin page hook.
	 */
	public function enqueue_styles( $hook ) : void {
		if ( 'toplevel_page_template-usage' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			self::get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'Admin/template-usage.css',
			[],
			self::get_plugin_version(),
			'all'
		);
	}

	/**
	 * Outputs the theme select control.
	 *
	 * @param string $theme_template The selected theme template.
	 */
	public function display_theme_select( $theme_template = '' ) {
		$html   = '';
		$themes = wp_get_themes();

		$html .= '<label for="theme-select" class="selected-menu">' . __( 'Display all available page templates for all sites that use the following theme:', 'template-usage' ) . '</label>';
		$html .= '<select name="theme" id="theme-select">';

		foreach ( $themes as $theme ) {
			$template = $theme->get_template();

			if ( $theme_template === $template ) {
				$html .= '<option value="' . $template . '" selected>' . $theme . '</option>';
			} else {
				$html .= '<option value="' . $template . '">' . $theme . '</option>';
			}
		}

		$html .= '</select>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Outputs the template usage report.
	 *
	 * @param string $theme_template The selected theme template.
	 */
	public function display_template_report( $theme_template = '' ) {
		// Don't display anything if no theme has been selected.
		if ( empty( $theme_template ) ) {
			return;
		}

		$theme     = wp_get_theme( $theme_template );
		$html      = '';
		$templates = [];

		if ( $this->sites ) {
			foreach ( $this->sites as $blog ) {
				switch_to_blog( $blog->blog_id );

				$current_theme_template = get_template();

				if ( $current_theme_template === $theme_template ) {
					$blog_templates = get_page_templates();
					$templates      = array_merge( $blog_templates, $templates );
				}

				restore_current_blog();
			}
		}

		if ( ! empty( $templates ) ) {
			// Add the default template.
			$templates['Default Template'] = 'default';

			$html .= '<p>' . sprintf(
				/* translators: %1$s: The total Number of templates found, %2$s: Theme selected theme. */
				__( 'Found %1$s page template(s) for sites using %2$s.', 'template-usage' ),
				'<strong>' . count( $templates ) . '</strong>',
				'<strong>' . $theme_template . '</strong>',
			) . '</p>';

			$html .= '<ul>';

			// Loop through our available page templates.
			foreach ( $templates as $template_name => $template_file ) {
				$active_template = false;

				$html .= '<li data-template-file="' . sanitize_title( $template_file ) . '"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><h2 class="template-name">' . $template_name . '</h2>';
				$html .= '<ul class="page-list">';

				if ( $this->sites ) {

					foreach ( $this->sites as $blog ) {
						switch_to_blog( $blog->blog_id );

						$current_theme_template = get_template();

						if ( $current_theme_template === $theme_template ) {

							$query = new \WP_Query(
								[
									'post_type'  => 'page',
									'meta_key'   => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
									'meta_value' => $template_file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
								]
							);

							if ( $query->have_posts() ) {
								while ( $query->have_posts() ) {
									$query->the_post();

									$blog_details = get_blog_details( $blog->blog_id );

									$html .= '<li class="template-page"><a target="_blank" rel="noopener noreferrer" href="' . get_the_permalink() . '">' . get_the_title() . '</a><span class="additional-info"> - ' . $blog_details->blogname . ' (' . $blog->blog_id . ')</span></li>';

									$active_template = true;
								}
							}

							wp_reset_postdata();
						}

						restore_current_blog();
					}

					if ( ! $active_template ) {
						$html .= '<span class="additional-info">' . __( 'No pages are using this template.', 'template-usage' ) . '</span>';
					}
				}

				$html .= '</ul></li>';
			}

			$html .= '</ul>';
		} else {
			$html .= __( 'No templates found. This theme is most likely not active within this multisite.', 'template-usage' );
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
