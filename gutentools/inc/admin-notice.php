<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gutentools_Notice' ) ) {

	class Gutentools_Notice {

		public static $instance;

		/**
		 * Get instance.
		 *
		 * @return Gutentools_Notice
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_notices', array( $this, 'gutentools_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_notice_script' ) );
			add_action( 'wp_ajax_gutentools_dismiss_notice', array( $this, 'dismiss_notice' ) );
		}

		/**
		 * Display the admin notice.
		 */
		public function gutentools_notice() {

			$last_dismissed = get_user_meta(
				get_current_user_id(),
				'gutentools_pro_notice_dismissed',
				true
			);

			// Don't show the notice if it was dismissed within the last 30 days.
			if ( $last_dismissed && ( time() - (int) $last_dismissed ) < ( 30 * DAY_IN_SECONDS ) ) {
				return;
			}

			?>
			<div class="notice notice-info is-dismissible gutentools-pro-notice">
				<h2><?php esc_html_e( 'Gutentools Pro', 'gutentools' ); ?></h2>

				<p>
					<?php
					esc_html_e(
						'Unlock your website’s potential with GutenTools – the flexible, user-friendly Gutenberg visual sitebuilder for WordPress.',
						'gutentools'
					);
					?>
				</p>

				<p>
					<a
						target="_blank"
						rel="noopener noreferrer"
						href="https://gutentools.com/price-plan/"
						class="button-primary"
					>
						<?php esc_html_e( 'Upgrade To Pro', 'gutentools' ); ?>
					</a>

					<a
						class="button-secondary"
						href="<?php echo esc_url( admin_url( 'admin.php?page=gutentools' ) ); ?>"
					>
						<?php esc_html_e( 'Plugin Info', 'gutentools' ); ?>
					</a>
				</p>
			</div>
			<?php
		}

		/**
		 * Enqueue the dismissal script.
		 *
		 * @param string $hook_suffix Current admin page.
		 */
		public function enqueue_notice_script( $hook_suffix ) {

			$last_dismissed = get_user_meta(
				get_current_user_id(),
				'gutentools_pro_notice_dismissed',
				true
			);

			// Don't load the script if the notice is currently suppressed.
			if ( $last_dismissed && ( time() - (int) $last_dismissed ) < ( 30 * DAY_IN_SECONDS ) ) {
				return;
			}

			wp_enqueue_script( 'jquery' );

			wp_add_inline_script(
				'jquery',
				"
				jQuery(document).on('click', '.gutentools-pro-notice .notice-dismiss', function() {
					jQuery.post(ajaxurl, {
						action: 'gutentools_dismiss_notice',
						nonce: '" . esc_js( wp_create_nonce( 'gutentools_dismiss_notice' ) ) . "'
					});
				});
				"
			);
		}

		/**
		 * Save the dismissal timestamp.
		 */
		public function dismiss_notice() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error();
			}

			check_ajax_referer( 'gutentools_dismiss_notice', 'nonce' );

			update_user_meta(
				get_current_user_id(),
				'gutentools_pro_notice_dismissed',
				time()
			);

			wp_send_json_success();
		}
	}

	Gutentools_Notice::get_instance();
}
