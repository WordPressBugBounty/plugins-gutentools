<?php 
if( !class_exists( 'Gutentools_Post_Ticker' ) ){

	class Gutentools_Post_Ticker extends Gutentools_Block{

		public $slug = 'post-ticker';

		/**
		* Title of this block.
		*
		* @access public
		* @since 1.0.0
		* @var string
		*/
		public $title = '';
		public $count = 0 ;

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

	    public function process_script(){
	    	
	        foreach ( $this->blocks as $block ) {
	        	
		    	$attrs = $this->get_attrs_with_default( $block[ 'attrs' ] );

		    	if ( ! empty( $attrs ) ) {

			    	$dimension_properties = [
					    'padding'            => 'padding', 
					    'titlePadding'       => 'padding', 
					    'labelPadding'       => 'padding', 
					    'arrowSize'          => 'size',
					    'arrowIconSize'      => 'font-size'
					];

					$typography_properties = [
						'titleTypo', 
						'labelTypo', 
					];

					$dimensions = self::extract_properties( 'dimension', $attrs, $dimension_properties );
					$typographies = self::extract_properties( 'typography', $attrs, $typography_properties );
					extract($dimensions);											
					extract($typographies);											
		    			    			    	    		    			    			    	    		    	
			    	foreach( self::$devices as $device ){


						$devices_style = [
							'' => array_merge(
								$padding[ $device ]
							),
							'.gutentools-post-ticker-label' => array_merge(
								$labelPadding[ $device ],
								$labelTypo[ $device ],
							),
							'.gutentools-post-title' => array_merge(
								$titlePadding[ $device ],
								$titleTypo[ $device ],
							),
							'.gutentools-post-ticker-arrow, .gutentools-post-ticker-pause, .gutentools-post-ticker-play' => array_merge(
								$arrowSize[ $device ],
								$arrowIconSize[ $device ],
							)

						];

						self::add_styles([
							'attrs' => $attrs,
							'css'   => $devices_style,
						], $device );
					}

					$desktop_css = [
					    '' => [
					        'background-color' => $attrs['bgColor'],
					    ],
					    '.gutentools-post-ticker-label' => [
					        'color' => $attrs['labelColor'],
					        'background-color' => $attrs['labelBgColor'],
					    ],
					    '.gutentools-post-title a' => [
					        'color' => $attrs['titleColor'],
					    ],
					    '.gutentools-post-ticker-arrow, .gutentools-post-ticker-pause, .gutentools-post-ticker-play' => [
					        'color' => $attrs['arrowColor'],
					        'background-color' => $attrs['arrowBGColor'],
					        'border' => 'none',
					    ],
					    '.gutentools-post-ticker-arrow:hover, .gutentools-post-ticker-pause:hover, .gutentools-post-ticker-play:hover' => [
					        'color' => $attrs['arrowHoverColor'],
					        'background-color' => $attrs['arrowBGHoverColor'],
					    ]
					    ];
			
					self::add_styles( array(
						'attrs' => $attrs,
						'css' => $desktop_css,
					));

					$animation = $this->get_slider_direction( $attrs[ 'animation' ]);
					$block_id = $attrs[ 'block_id' ];
					ob_start();
					?>


					jQuery('#<?php echo esc_js($block_id); ?> .gutentools-ticker-content').AcmeTicker({
			            type:'<?php echo esc_js( $animation[0] ) ?>',
			            speed: <?php echo esc_js( $animation[2] ) ?>,
			            direction: '<?php echo esc_js( $animation[1] ) ?>',
			            controls: {
			                prev: jQuery('#<?php echo esc_js($block_id); ?> .gutentools-post-ticker-prev'),
			                toggle: jQuery('#<?php echo esc_js($block_id); ?> .gutentools-post-ticker-toggle'),
			                next: jQuery('#<?php echo esc_js($block_id); ?> .gutentools-post-ticker-next')            
			            }

			        });

			        jQuery('#<?php echo esc_js($block_id); ?> .gutentools-post-ticker-pause').on('click', function() {
			            jQuery(this).hide();
			            jQuery('#<?php echo esc_js($block_id); ?> .gutentools-post-ticker-play').show();
			        });

			        jQuery('#<?php echo esc_js($block_id); ?> .gutentools-post-ticker-play').on('click', function() {
			            jQuery(this).hide();
			            jQuery('#<?php echo esc_js($block_id); ?> .gutentools-post-ticker-pause').show();
			        });
			        
					<?php
					$js = ob_get_clean();
					self::add_scripts( $js );


					
		    	}
			}
	    	
	    }

	    public function get_slider_direction($value) {
		    $mappings = [
		        'slideup' => ['vertical', 'down', 600],
		        'slidedown' => ['vertical', 'up', 600],
		        'slideleft' => ['horizontal', 'left', 600],
		        'slideright' => ['horizontal', 'right', 600],
		        'typewriter' => ['typewriter', '', 50],
		        'continuous' => ['marquee', 'left', 0.05]
		    ];

		    $normalizedValue = strtolower(trim($value));
		    
		    return $mappings[$normalizedValue] ?? ['horizontal', 'right'];
		}

	    public function get_query(){
	    	
			$args = array(
					'post_type'   => 'post',
					'post_status' => 'publish',
					'ignore_sticky_posts' => true,
					'posts_per_page' => $this->attrs[ 'postsToShow' ],
					'order' => $this->attrs[ 'order' ],
					'orderby' => $this->attrs[ 'orderBy' ],
					'tax_query' => array(),
				);

			if( isset( $this->attrs[ 'categories' ] ) && ! empty( $this->attrs[ 'categories' ] ) ){
					$args['tax_query'][] = array(
						'taxonomy' => 'category',
						'field'    => 'id',
						'terms'    => $this->attrs[ 'categories' ],
					);
				}
			
			$query = new WP_Query( $args );

			return $query;
		}

		public function render( $attrs, $content, $block ) {
			$this->attrs = $attrs;
						
			$query 	= $this->get_query( );
			
			$this->count = $query->post_count;
			
			ob_start();
		    
		    ?>
		    <div id=<?php echo esc_attr( $attrs[ 'block_id' ] ) ?> class="gutentools-post-ticker">
		    	<?php if( $this->attrs[ 'enableLabel' ] ): ?>
			    <div class="gutentools-post-ticker-label"><?php echo $this->attrs['label'] ?></div>
				<?php endif; ?>
			    <div class="gutentools-post-ticker-box">
			        <ul class="gutentools-ticker-content">
			           	<?php $this->ticker_template( $query ) ?>
			        </ul>

			    </div>
			    <div class="gutentools-post-ticker-controls gutentools-post-ticker-horizontal-controls">
		    	<?php if( $this->attrs[ 'enableArrow' ] ): ?>	
			        <button class="gutentools-post-ticker-arrow gutentools-post-ticker-prev"></button>
				<?php endif;
					if( $this->attrs[ 'enablePausePlay' ] ): ?>
			        <button class="gutentools-post-ticker-pause gutentools-post-ticker-toggle"></button>
			        <button class="gutentools-post-ticker-play gutentools-post-ticker-toggle" style="display: none;"></button>
			    <?php endif; 
					if( $this->attrs[ 'enableArrow' ] ): ?>
			        <button class="gutentools-post-ticker-arrow gutentools-post-ticker-next"></button>
   				<?php endif; ?>
			    </div>
			</div>   
	  <?php
		    return ob_get_clean();
		}

		public function ticker_template( $query ){

			if ( $query->have_posts() ) :
			    foreach( $query->posts as $post ):
			        $id  = $post->ID;
					
			        $link = get_the_permalink( $id );            
			        ?>

			        <li class="ticker-item gutentools-post-title"  data-id=<?php echo esc_attr( $id ) ?>>
						<a href="<?php echo esc_url( $link ) ?>"><?php echo esc_html( $post->post_title ); ?> </a>
					</li>
			    <?php 
				endforeach; 
			endif; 
			
		}

	}

	Gutentools_Post_Ticker::get_instance();
}