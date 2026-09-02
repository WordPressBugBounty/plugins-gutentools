<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Icon List Block
 *
 * @package Gutentools
 * @since 1.1.8
 */

if ( ! class_exists( 'Gutentools_Icon_List' ) ) {

	class Gutentools_Icon_List extends Gutentools_Block {

		/**
		 * Block slug.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var string
		 */
		public $slug = 'icon-list';

		/**
		 * Title of this block.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var string
		 */
		public $title = '';

		/**
		 * Description of this block.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var string
		 */
		public $description = '';

		/**
		 * SVG Icon for this block.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var string
		 */
		public $icon = '';

		/**
		 * Block attributes.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var array
		 */
		public $attrs = '';

		/**
		 * Instance.
		 *
		 * @var Gutentools_Icon_List
		 */
		protected static $instance;

		/**
		 * Get singleton instance.
		 *
		 * @since 1.0.0
		 * @return Gutentools_Icon_List
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Generate responsive styles for the block.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function process_script() {

			foreach ( $this->blocks as $block ) {

				$attrs = $this->get_attrs_with_default( $block['attrs'] );

				if ( ! empty( $attrs ) ) {

					$dimension_properties = array(
						'listPadding'  => 'padding',
						'listMargin'   => 'margin',
						'textPadding'  => 'padding',
						'textMargin'   => 'margin',
						'iconRadius'   => 'border-radius',
						'iconSize'     => 'size',
						'iconFontSize' => 'font-size',
						'iconGap'      => array( 'margin-right' ),
						'itemGap'      => array( 'gap' ),
					);

					$typography_properties = array(
						'textTypo',
					);

					$dimensions   = self::extract_properties( 'dimension', $attrs, $dimension_properties );
					$typographies = self::extract_properties( 'typography', $attrs, $typography_properties );
					extract( $dimensions ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
					extract( $typographies ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

					$is_grid = isset( $attrs['layout'] ) && 'grid' === $attrs['layout'];

					foreach ( self::$devices as $device ) {

						$wrapper_styles = array_merge(
							$listPadding[ $device ],
							$listMargin[ $device ],
							$itemGap[ $device ]
						);

						if ( $is_grid ) {
							$column_count = 1;
							if ( isset( $attrs['columns']['values'][ $device ] ) ) {
								$column_count = max( 1, absint( $attrs['columns']['values'][ $device ] ) );
							}
							$wrapper_styles['display']               = 'grid';
							$wrapper_styles['grid-template-columns'] = 'repeat(' . $column_count . ', 1fr)';
						}

						$devices_style = array(
							'.gutentools-icon-list-wrapper' => $wrapper_styles,
							'.gutentools-icon-list-icon'    => array_merge(
								$iconRadius[ $device ],
								$iconSize[ $device ],
								$iconFontSize[ $device ],
								$iconGap[ $device ]
							),
							'.gutentools-icon-list-text'    => array_merge(
								$textPadding[ $device ],
								$textMargin[ $device ],
								$textTypo[ $device ]
							),
						);

						self::add_styles(
							array(
								'attrs' => $attrs,
								'css'   => $devices_style,
							),
							$device
						);
					}

					$desktop_css = array(
						'.gutentools-icon-list-icon' => array(
							'color'      => $attrs['iconColor'],
							'background' => $attrs['iconBgColor'],
						),
						'.gutentools-icon-list-text' => array(
							'color' => $attrs['textColor'],
						),
					);

					$icon_border = $attrs['iconBorder'] ?? null;
					$desktop_css['.gutentools-icon-list-icon'] = array_merge(
						$desktop_css['.gutentools-icon-list-icon'] ?? array(),
						array(
							'border' => $icon_border,
						)
					);

					self::add_styles(
						array(
							'attrs' => $attrs,
							'css'   => $desktop_css,
						)
					);
				}
			}
		}

		/**
		 * Parse list items from attributes.
		 *
		 * @since 1.0.0
		 * @param mixed $items Items attribute value.
		 * @return array
		 */
		protected function get_list_items( $items ) {
			if ( empty( $items ) ) {
				return array();
			}

			if ( is_array( $items ) ) {
				return $items;
			}

			$decoded = json_decode( $items, true );
			return is_array( $decoded ) ? $decoded : array();
		}

		/**
		 * Render the block on the frontend.
		 *
		 * @since 1.0.0
		 * @param array  $attrs   Block attributes.
		 * @param string $content Block content.
		 * @param object $block   Block instance.
		 * @return string
		 */
		public function render( $attrs, $content, $block ) {
			$this->attrs = $attrs;
			$list_items  = $this->get_list_items( $attrs['items'] ?? '' );
			$alignment   = isset( $attrs['alignment'] ) ? $attrs['alignment'] : 'left';
			$layout      = isset( $attrs['layout'] ) && 'grid' === $attrs['layout'] ? 'grid' : 'list';
			$block_id    = isset( $attrs['block_id'] ) ? $attrs['block_id'] : '';

			ob_start();
			?>
			<div id="<?php echo esc_attr( $block_id ); ?>">
				<ul class="gutentools-icon-list-wrapper gutentools-icon-list-layout-<?php echo esc_attr( $layout ); ?> gutentools-align-<?php echo esc_attr( $alignment ); ?>">
					<?php foreach ( $list_items as $item ) : ?>
						<?php
						$icon_class = 'fa-solid fa-check';
						if ( ! empty( $item['icon']['icon'] ) ) {
							$icon_class = $item['icon']['icon'];
						}
						$item_text = isset( $item['text'] ) ? $item['text'] : '';
						?>
						<li class="gutentools-icon-list-item">
							<span class="gutentools-icon-list-icon">
								<i class="fa <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
							</span>
							<span class="gutentools-icon-list-text"><?php echo esc_html( $item_text ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
			return ob_get_clean();
		}
	}

	Gutentools_Icon_List::get_instance();
}
