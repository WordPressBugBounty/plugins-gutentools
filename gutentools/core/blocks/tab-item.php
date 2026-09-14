<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gutentools_Tab_Item' ) ) {

	class Gutentools_Tab_Item extends Gutentools_Block {

		public $slug = 'tab-item';
		public $title = '';
		public $description = '';
		public $icon = '';
		public $attrs = '';

		protected static $instance;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function render( $attrs, $content, $block ) {
			$this->attrs = $attrs;

			return $content;
		}
	}

	Gutentools_Tab_Item::get_instance();
}