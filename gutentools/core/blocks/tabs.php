<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Gutentools_Tabs')) {

	class Gutentools_Tabs extends Gutentools_Block
	{

		public $slug = 'tabs';

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

		public $attrs = '';

		protected static $instance;

		/**
		 * Get instance.
		 *
		 * @return object
		 */
		public static function get_instance()
		{

			if (null === self::$instance) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Process block styles.
		 *
		 * @return void
		 */
		public function process_script()
		{

			foreach ($this->blocks as $block) {

				$attrs = $this->get_attrs_with_default($block['attrs']);

				if (empty($attrs)) {
					continue;
				}

				/**
				 * Dimension properties.
				 */
				$dimension_properties = [
					'tabPadding' => 'padding',
					'contentPadding' => 'padding',
					'wrapperPadding' => 'padding',
					'wrapperMargin' => 'margin',
				];

				/**
				 * Typography properties.
				 */
				$typography_properties = [
					'tabTypo',
				];

				$dimensions = self::extract_properties(
					'dimension',
					$attrs,
					$dimension_properties
				);

				$typographies = self::extract_properties(
					'typography',
					$attrs,
					$typography_properties
				);

				extract($dimensions);
				extract($typographies);

				/**
				 * Responsive styles.
				 */
				foreach (self::$devices as $device) {

					$devices_style = [

						'.gutentools-tabs__nav-btn' => array_merge(
							$tabPadding[$device],
							$tabTypo[$device]
						),

						'.gutentools-tabs__panels' => array_merge(
							$contentPadding[$device]
						),

						'.gutentools-tabs' => array_merge(
							$wrapperPadding[$device],
							$wrapperMargin[$device]
						),

					];

					self::add_styles(
						[
							'attrs' => $attrs,
							'css' => $devices_style,
						],
						$device
					);
				}

				/**
				 * Desktop / global styles.
				 */
				$desktop_css = [

					'.gutentools-tabs' => [
						'background' => $attrs['wrapperBgColor'],
					],

					'.gutentools-tabs__nav' => [
						'gap' => $attrs['tabGap'] . 'px',
						'justify-content' => $this->get_alignment_value(
							$attrs['tabAlignment']
						),
					],

					'.gutentools-tabs__nav-btn' => [
						'background' => $attrs['tabBg'],
						'color' => $attrs['tabColor'],
						'border-radius' => $attrs['tabBorderRadius'] . 'px',
					],

					'.gutentools-tabs__nav-btn--active' => [
						'background' => $attrs['tabActiveBg'],
						'color' => $attrs['tabActiveColor'],
					],

					'.gutentools-tabs__panels' => [
						'margin-top' => $attrs['tabContentGap'] . 'px',
						'background' => $attrs['contentBg'],
						'border-radius' => $attrs['contentBorderRadius'] . 'px',
					],

				];

				/**
				 * Content border.
				 */
				if (
					!empty($attrs['contentBorderStyle']) &&
					'none' !== $attrs['contentBorderStyle']
				) {
					$desktop_css['.gutentools-tabs__panels']['border'] =
						$attrs['contentBorderWidth'] . 'px ' .
						$attrs['contentBorderStyle'] . ' ' .
						$attrs['contentBorderColor'];
				} else {
					$desktop_css['.gutentools-tabs__panels']['border'] = 'none';
				}

				/**
				 * Hover styles.
				 */
				if (!empty($attrs['tabHoverBg'])) {
					$desktop_css['.gutentools-tabs__nav-btn:hover']['background'] =
						$attrs['tabHoverBg'];
				}

				if (!empty($attrs['tabHoverColor'])) {
					$desktop_css['.gutentools-tabs__nav-btn:hover']['color'] =
						$attrs['tabHoverColor'];
				}

				self::add_styles(
					[
						'attrs' => $attrs,
						'css' => $desktop_css,
					]
				);
			}
		}

		/**
		 * Convert tab alignment to CSS flex alignment.
		 *
		 * @param string $alignment Alignment value.
		 * @return string
		 */
		private function get_alignment_value($alignment)
		{

			switch ($alignment) {

				case 'center':
					return 'center';

				case 'right':
					return 'flex-end';

				case 'left':
				default:
					return 'flex-start';
			}
		}

		/**
		 * Render Tabs block.
		 *
		 * The parent block creates the navigation and controls
		 * the tab panels. Child tab-item blocks provide the
		 * actual tab content.
		 *
		 * @param array  $attrs Block attributes.
		 * @param string $content Block content.
		 * @param object $block Block instance.
		 *
		 * @return string
		 */
		public function render($attrs, $content, $block)
		{

			$this->attrs = $attrs;

			$block_id = !empty($attrs['block_id'])
				? $attrs['block_id']
				: 'gutentools-tabs-' . wp_unique_id();

			$inner_blocks = [];

			if (
				isset($block->parsed_block['innerBlocks']) &&
				is_array($block->parsed_block['innerBlocks'])
			) {
				$inner_blocks = $block->parsed_block['innerBlocks'];
			}

			if (empty($inner_blocks)) {
				return '';
			}

			ob_start();
			?>

			<div id="<?php echo esc_attr($block_id); ?>" class="gutentools-tabs wp-block-gutentools-tabs">

				<nav class="gutentools-tabs__nav" role="tablist" aria-label="<?php esc_attr_e('Tabs', 'gutentools'); ?>">

					<?php foreach ($inner_blocks as $index => $inner_block): ?>

						<?php
						
						if (
							isset($inner_block['attrs']['tabTitle']) &&
							!empty($inner_block['attrs']['tabTitle'])
						) {
							$title = wp_strip_all_tags(
								$inner_block['attrs']['tabTitle']
							);
						}

						$tab_id = $block_id . '-tab-' . $index;
						$panel_id = $block_id . '-panel-' . $index;

						$is_active = 0 === $index;
						?>

						<button class="gutentools-tabs__nav-btn<?php echo $is_active ? ' gutentools-tabs__nav-btn--active' : ''; ?>"
							data-tab-index="<?php echo esc_attr($index); ?>" id="<?php echo esc_attr($tab_id); ?>" role="tab"
							type="button" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr($panel_id); ?>">
							<?php echo esc_html($title); ?>
						</button>

					<?php endforeach; ?>

				</nav>

				<div class="gutentools-tabs__panels">

					<?php
					/*
					 * The child tab-item blocks are already rendered
					 * inside $content.
					 *
					 * We only output the content here.
					 */
					echo $content;
					?>

				</div>

			</div>

			<?php

			return ob_get_clean();
		}
	}

	Gutentools_Tabs::get_instance();
}