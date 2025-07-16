<?php 
if( !class_exists( 'Gutentools_Image_Slider' ) ){

	class Gutentools_Image_Slider extends Gutentools_Block{

		public $slug = 'image-slider';

		/**
		* Title of this block.
		*
		* @access public
		* @since 1.0.0
		* @var string
		*/
		public $title = '';
		public $count = 0;

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
	    
	    public static function get_instance() {
	        if ( null === self::$instance ) {
	            self::$instance = new self();
	        }
	        return self::$instance;
	    }

	    public function process_script() {
            foreach ( $this->blocks as $block ) {
                
                $attrs = $this->get_attrs_with_default( $block['attrs'] );

                if ( ! empty( $attrs ) ) {

                    $dimension_properties = [
                        'imgHeight' => 'height',
                        'imgWidth'  => 'width',
                        'imgRadius' => 'border-radius',
                        'margin'    => 'margin',
                        'padding'   => 'padding',
                        'arrowSize' => 'size',
                        'arrowIconSize' => 'font-size',
                        'arrowRadius' => 'border-radius',
                        'arrowPositionRight' => 'right',
                        'arrowPositionLeft' => 'left',
                        'dotsSize'  => 'size',
                        'dotsRadius' => 'border-radius',
                    ];

                    $dimensions = self::extract_properties( 'dimension', $attrs, $dimension_properties );
                    extract($dimensions);
                    
                    foreach( self::$devices as $device ) {
                        $devices_style = [
                            '.gutentools-image-slider-wrapper img' => array_merge(
                                $imgHeight[$device],
                                $imgWidth[$device],
                                $imgRadius[$device],
                                $margin[$device],
                                $padding[$device]
                            ),
                            '.gutentools-slider-arrow' => array_merge(
                                $arrowSize[$device],
                                $arrowIconSize[$device],
                                $arrowRadius[$device]
                            ),
                            '.gutentools-next-arrow' => array_merge(
                                $arrowPositionRight[$device]
                            ),
                            '.gutentools-prev-arrow' => array_merge(
                                $arrowPositionLeft[$device]
                            ),
                            '.slick-dots li button' => array_merge(
                                $dotsSize[$device],
                                $dotsRadius[$device]
                            )
                        ];

                        self::add_styles([
                            'attrs' => $attrs,
                            'css'   => $devices_style,
                        ], $device );
                    }

                    // Desktop-specific styles
                    $desktop_css = [
                        '.gutentools-image-slider-wrapper img' => [
                            'object-fit' => $attrs['objectFit'],
                            'border' => $attrs['borders'] ?? 'none',
                        ],
                        '.gutentools-image-slider' => [
                            'padding' => '0 ' .$attrs['gap'].'px',
                        ],

                        '.gutentools-image-slider-wrapper .gutentools-slider-arrow' => [
                            'background-color' => $attrs['arrowBGColor'],
                            'color' => $attrs['arrowColor'],
                        ],
                        '.gutentools-image-slider-wrapper .slick-dots button' => [
                            'background-color' => $attrs['dotsColor'],
                        ],
                        '.gutentools-image-slider-wrapper .slick-dots .slick-active button' => [
                            'opacity' => 1,
                        ],
                    ];

                    // Add hover styles
                    $hover_css = [
                        '.gutentools-image-slider-wrapper .gutentools-slider-arrow:hover' => [
                            'background-color' => $attrs['arrowBGHoverColor'],
                            'color' => $attrs['arrowHoverColor'],
                        ],
                    ];

                    self::add_styles([
                        'attrs' => $attrs,
                        'css' => $desktop_css,
                    ]);

                    self::add_styles([
                        'attrs' => $attrs,
                        'css' => $hover_css,
                    ], 'desktop', true);

                    if( !is_array( $attrs[ 'slideToShow' ] ) ){
						$slideToShow = [
							'values' => [
								'desktop' => $attrs[ 'slideToShow' ],
								'tablet'  => $attrs[ 'slideToShow' ],
								'mobile'  => $attrs[ 'slideToShow' ],
							]
						];
					}else{
						$slideToShow = $attrs[ 'slideToShow' ];	
					}

                    $slide = $this->count > esc_attr( $slideToShow[ 'values' ][ 'desktop' ] ) ? esc_attr( $slideToShow[ 'values' ][ 'desktop' ] ) : $this->count;
                    $fade = ( $attrs['fade'] && $slide == 1 ) ? 'true' : 'false';

                    // Initialize the slider
                    ob_start();
                    $dots = ( $attrs[ 'enableDots' ]  && $this->count > $slide )? 'true' : 'false';	
                    ?>
                    var slider = {
                            dots: <?php echo esc_attr( $dots ) ?>,
                            arrows: <?php echo $attrs['enableArrows'] ? 'true' : 'false'; ?>,
                            infinite: true,
                            speed: <?php echo esc_attr($attrs['speed']); ?>,
                            autoplay: <?php echo $attrs['autoplay'] ? 'true' : 'false'; ?>,
                            fade: <?php echo $fade ?>,
                            slidesToShow: <?php echo esc_attr( $slide ) ?>,
                            slidesToScroll: 1,
                            cssEase: 'ease-in' ,
                            prevArrow: '<button type="button" class="gutentools-prev-arrow gutentools-slider-arrow"><i class="fa fa-angle-left"></i></button>',
                            nextArrow: '<button type="button" class="gutentools-next-arrow gutentools-slider-arrow"><i class="fa fa-angle-right"></i></button>',
                            responsive: [
                                {
                                    breakpoint: 767,
                                    settings: {
                                        slidesToShow: <?php echo esc_attr( $slideToShow[ 'values' ][ 'mobile' ] ); ?>
                                    }
                                },
                                {
                                    breakpoint: 1024,
                                    settings: {
                                        slidesToShow: <?php echo esc_attr( $slideToShow[ 'values' ][ 'tablet' ] ); ?>
                                    }
                                }
                            ]
                        };
                        jQuery('#<?php echo esc_attr( $this->block_id ); ?> .gutentools-image-slider-init').slick( slider );

                    <?php
                    $js = ob_get_clean();
                    self::add_scripts($js);
                }
            }
        }

        public function render( $attrs, $content, $block ) {
            $this->attrs = $attrs;
            $this->count = count($attrs['images']);
            ob_start();
            
            ?>
            <div id="<?php echo esc_attr($attrs['block_id']); ?>" class="gutentools-image-slider-block">
                <div class="gutentools-image-slider-wrapper">
                    <?php if (!empty($attrs['images'])) : ?>
                        <div class="slick-slider gutentools-image-slider-init">
                            <?php foreach ($attrs['images'] as $image) : ?>
                                <div class="gutentools-image-slider">
                                    <img src="<?php echo esc_url($image['url']); ?>" 
                                         alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" 
                                         class="gutentools-slider-image" />
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="gutentools-slider-placeholder">
                            <?php esc_html_e('Please add images to the slider', 'gutentools'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

	}

	Gutentools_Image_Slider::get_instance();
}