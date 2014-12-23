<?php  /**
 * Contact Details &amp; Maps Widget
 *
 * This file is used to register and display the Hatch - Portfolios widget.
 *
 * @package Hatch
 * @since Hatch 1.0
 */
if( !class_exists( 'Hatch_Contact_Widget' ) ) {
	class Hatch_Contact_Widget extends Hatch_Widget {

		/**
		* Widget variables
		*
	 	* @param  	varchar    		$widget_title    	Widget title
	 	* @param  	varchar    		$widget_id    		Widget slug for use as an ID/classname
	 	* @param  	varchar    		$post_type    		(optional) Post type for use in widget options
	 	* @param  	varchar    		$taxonomy    		(optional) Taxonomy slug for use as an ID/classname
	 	* @param  	array 			$checkboxes    	(optional) Array of checkbox names to be saved in this widget. Don't forget these please!
	 	*/
		private $widget_title = 'Contact Details &amp; Maps';
		private $widget_id = 'map';
		private $post_type = '';
		private $taxonomy = '';
		public $checkboxes = array(
				'show_google_map',
				'show_address',
				'show_contact_form'
			);

		/**
		*  Widget construction
		*/
	 	function Hatch_Contact_Widget(){
	 		/* Widget settings. */
			$widget_ops = array( 'classname' => 'obox-hatch-' . $this->widget_id .'-widget', 'description' => 'This widget is used to display your ' . $this->widget_title . '.' );

			/* Widget control settings. */
			$control_ops = array( 'width' => HATCH_WIDGET_WIDTH_SMALL, 'height' => NULL, 'id_base' => HATCH_THEME_SLUG . '-widget-' . $this->widget_id );

			/* Create the widget. */
			$this->WP_Widget( HATCH_THEME_SLUG . '-widget-' . $this->widget_id , '(' . HATCH_THEME_TITLE . ') ' . $this->widget_title . ' Widget', $widget_ops, $control_ops );

			/* Setup Widget Defaults */
			$this->defaults = array (
				'title' => 'Find Us',
				'excerpt' => 'We are based in one of the most beautiful places on earth. Come visit us for a coffee!',
				'contact_form' => NULL,
				'address_shown' => NULL,
				'show_google_map' => 'on',
				'show_contact_form' => 'on',
				'google_maps_location' => NULL,
				'google_maps_long_lat' => NULL,
				'map_height' => 400,
				'design' => array(
					'layout' => 'layout-boxed',
					'background' => array(
						'position' => 'center',
						'repeat' => 'no-repeat'
					),
					'fonts' => array(
						'align' => 'text-center',
						'size' => 'medium',
						'color' => NULL,
						'shadow' => NULL
					)
				)
			);
		}

		/**
		*  Widget front end display
		*/
	 	function widget( $args, $instance ) {

			// Turn $args array into variables.
			extract( $args );

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			$widget = wp_parse_args( $instance , $instance_defaults );

			// Check if we have a map present
			if( isset( $widget['show_google_map'] ) && ( '' != $widget['google_maps_location'] || '' != $widget['google_maps_long_lat'] ) ) {
				$hasmap = true;
			}
			// Set the background styling
			if( !empty( $widget['design'][ 'background' ] ) ) hatch_inline_styles( $widget_id, 'background', array( 'background' => $widget['design'][ 'background' ] ) );
			if( !empty( $widget['design']['fonts'][ 'color' ] ) ) hatch_inline_styles( $widget_id, 'color', array( 'selectors' => array( '.section-title h3.heading' , '.section-title p.excerpt' , '.section-title small' ) , 'color' => $widget['design']['fonts'][ 'color' ] ) );

			// Set the map width
			$mapwidth = 'span-12'; ?>

			<section class="widget content-vertical-massive row" id="<?php echo $widget_id; ?>">

				<?php if( $this->check_and_return( $widget , 'title' ) || $this->check_and_return( $widget , 'excerpt' ) ) { ?>
					<div class="container clearfix">
						<div class="section-title <?php echo $this->check_and_return( $widget , 'design', 'fonts', 'size' ); ?> <?php echo $this->check_and_return( $widget , 'design', 'fonts', 'align' ); ?> clearfix">
							<?php if( '' != $widget['title'] ) { ?>
								<h3 class="heading"><?php echo $widget['title']; ?></h3>
							<?php } ?>
							<?php if( '' != $widget['excerpt'] ) { ?>
								<p class="excerpt"><?php echo $widget['excerpt']; ?></p>
							<?php } ?>
						</div>
					</div>
				<?php } // if title || excerpt ?>


				<div class="row <?php if('layout-boxed' == $this->check_and_return( $widget , 'design' , 'layout' ) ) echo 'container'; ?>">
					<?php if( ( '' != $widget['address_shown'] && isset( $widget['show_address'] ) ) || ( isset( $widget['show_contact_form'] ) && '' != $widget['contact_form'] ) ) {?>
						<div class="column span-6 form">
							<?php if( isset( $widget['show_address'] ) &&  '' != $widget['address_shown'] ) { ?>
								<address class="copy">
									<p><?php echo $widget['address_shown']; ?></p>
								</address>
							<?php } ?>
							<?php if( isset( $widget['show_contact_form'] ) && '' != $widget['contact_form'] ) { ?>
								<?php echo do_shortcode( $widget['contact_form'] ); ?>
							<?php } ?>
						</div>
						<?php $mapwidth = 'span-6'; ?>
					<?php } // if show_contact_form || address_shown ?>
					<div class="column <?php echo $mapwidth; ?>">
						<?php if( isset( $hasmap ) ) { ?>
							<div class="hatch-map" style="height: <?php echo $widget['map_height']; ?>px;" <?php if( '' != $widget['google_maps_location'] ) { ?>data-location="<?php echo $widget['google_maps_location']; ?>"<?php } ?> <?php if( '' != $widget['google_maps_long_lat'] ) { ?>data-longlat="<?php echo $widget['google_maps_long_lat']; ?>"<?php } ?>></div>
						<?php } ?>
					</div>
				</div>
			</section>

	 		<?php // Enqueue the map js
	 			wp_enqueue_script( HATCH_THEME_SLUG . " -map-api","http://maps.googleapis.com/maps/api/js?sensor=false");
	 			wp_enqueue_script( HATCH_THEME_SLUG . "-map-trigger", get_template_directory_uri()."/core/widgets/js/maps.js", array( "jquery" ) );
	 		?>
	 	<?php }

		/**
		*  Widget update
		*/
		function update($new_instance, $old_instance) {
			if ( isset( $this->checkboxes ) ) {
				foreach( $this->checkboxes as $cb ) {
					if( isset( $old_instance[ $cb ] ) ) {
						$old_instance[ $cb ] = strip_tags( $new_instance[ $cb ] );
					}
				} // foreach checkboxes
			} // if checkboxes
			return $new_instance;
		}

		/**
		*  Widget form
		*
		* We use regulage HTML here, it makes reading the widget much easier than if we used just php to echo all the HTML out.
		*
		*/
		function form( $instance ){

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			// Parse $instance
			$instance = wp_parse_args( $instance, $instance_defaults );
			extract( $instance, EXTR_SKIP ); ?>
			<!-- Form HTML Here -->
			<?php $this->design_bar()->bar(
				'side', // CSS Class Name
				array(
					'name' => $this->get_field_name( 'design' ),
					'id' => $this->get_field_id( 'design' ),
				), // Widget Object
				$instance, // Widget Values
				array(
					'layout',
					'fonts',
					'custom',
					'background'
				), // Standard Components
				array(
					'display' => array(
						'icon-css' => 'icon-display',
						'label' => 'Display',
						'elements' => array(
								'map_height' => array(
									'type' => 'number',
									'name' => $this->get_field_name( 'map_height' ) ,
									'id' => $this->get_field_id( 'map_height' ) ,
									'min' => 150,
									'max' => 1600,
									'value' => ( isset( $map_height ) ) ? $map_height : NULL,
									'label' => __( 'Map Height', HATCH_THEME_SLUG )
								),
								'show_google_map' => array(
										'type' => 'checkbox',
										'name' => $this->get_field_name( 'show_google_map' ) ,
										'id' => $this->get_field_id( 'show_google_map' ) ,
										'value' => ( isset( $show_google_map ) ) ? $show_google_map : NULL,
										'label' => __( 'Show Google Map', HATCH_THEME_SLUG )
									),
								'show_address' => array(
										'type' => 'checkbox',
										'name' => $this->get_field_name( 'show_address' ) ,
										'id' => $this->get_field_id( 'show_address' ) ,
										'value' => ( isset( $show_address ) ) ? $show_address : NULL,
										'label' => __( 'Show Address', HATCH_THEME_SLUG )
									),
								'show_contact_form' => array(
										'type' => 'checkbox',
										'name' => $this->get_field_name( 'show_contact_form' ) ,
										'id' => $this->get_field_id( 'show_contact_form' ) ,
										'value' => ( isset( $show_contact_form ) ) ? $show_contact_form : NULL,
										'label' => __( 'Show Contact Form', HATCH_THEME_SLUG )
									)
							)
						)
					)
				);?>
			<div class="hatch-container-large">

				<?php $this->form_elements()->header( array(
					'title' => __( 'Contact', HATCH_THEME_SLUG ),
					'icon_class' =>'location'
				) ); ?>

				<section class="hatch-accordion-section hatch-content">
					<div class="hatch-row hatch-push-bottom clearfix">
						<p class="hatch-form-item">
							<?php echo $this->form_elements()->input(
								array(
									'type' => 'text',
									'name' => $this->get_field_name( 'title' ) ,
									'id' => $this->get_field_id( 'title' ) ,
									'placeholder' => __( 'Enter title here', HATCH_THEME_SLUG ),
									'value' => ( isset( $title ) ) ? $title : NULL ,
									'class' => 'hatch-text hatch-large'
								)
							); ?>
						</p>
						<p class="hatch-form-item">
							<?php echo $this->form_elements()->input(
								array(
									'type' => 'textarea',
									'name' => $this->get_field_name( 'excerpt' ) ,
									'id' => $this->get_field_id( 'excerpt' ) ,
									'placeholder' =>  __( 'Short Excerpt', HATCH_THEME_SLUG ),
									'value' => ( isset( $excerpt ) ) ? $excerpt : NULL ,
									'class' => 'hatch-textarea hatch-large'
								)
							); ?>
						</p>
						<p class="hatch-form-item">
							<?php echo $this->form_elements()->input(
								array(
									'type' => 'textarea',
									'name' => $this->get_field_name( 'contact_form' ) ,
									'id' => $this->get_field_id( 'contact_form' ) ,
									'placeholder' =>  __( 'Contact Form Embed Code', HATCH_THEME_SLUG ),
									'value' => ( isset( $contact_form ) ) ? $contact_form : NULL ,
									'class' => 'hatch-textarea hatch-large'
								)
							); ?>
						</p>
					</div>

					<div class="hatch-row clearfix">
						<div class="hatch-column hatch-span-12">
							<div class="hatch-panel">
								<?php $this->form_elements()->section_panel_title(
									array(
										'type' => 'panel',
										'title' => __( 'Address' , HATCH_THEME_SLUG ),
										'tooltip' => __(  'Place your help text here please.', HATCH_THEME_SLUG )
									)
								); ?>
								<div class="hatch-content">
									<p class="hatch-form-item">
										<label for="<?php echo $this->get_field_id( 'google_maps_location' ); ?>"><?php _e( 'Google Maps Location' , HATCH_THEME_SLUG ); ?></label>
										<?php echo $this->form_elements()->input(
											array(
												'type' => 'text',
												'name' => $this->get_field_name( 'google_maps_location' ) ,
												'id' => $this->get_field_id( 'google_maps_location' ) ,
												'placeholder' => __( 'e.g. 300 Prestwich Str, Cape Town, South Africa', HATCH_THEME_SLUG ),
												'value' => ( isset( $google_maps_location ) ) ? $google_maps_location : NULL
											)
										); ?>
									</p>
									<p class="hatch-form-item">
										<label for="<?php echo $this->get_field_id( 'google_maps_long_lat' ); ?>"><?php _e( 'Google Maps Latitude & Longitude (Optional)' , HATCH_THEME_SLUG ); ?></label>
										<?php echo $this->form_elements()->input(
											array(
												'type' => 'text',
												'name' => $this->get_field_name( 'google_maps_long_lat' ) ,
												'id' => $this->get_field_id( 'google_maps_long_lat' ) ,
												'placeholder' => __( 'e.g. 33.9253 S, 18.4239 E', HATCH_THEME_SLUG ),
												'value' => ( isset( $google_maps_long_lat ) ) ? $google_maps_long_lat : NULL
											)
										); ?>
									</p>
									<p class="hatch-form-item">
										<label for="<?php echo $this->get_field_id( 'address_shown' ); ?>"><?php _e( 'Address Shown' , HATCH_THEME_SLUG ); ?></label>
										<?php echo $this->form_elements()->input(
											array(
												'type' => 'textarea',
												'name' => $this->get_field_name( 'address_shown' ) ,
												'id' => $this->get_field_id( 'address_shown' ) ,
												'placeholder' => __( 'e.g. Prestwich Str, Cape Town', HATCH_THEME_SLUG ),
												'value' => ( isset( $address_shown ) ) ? $address_shown : NULL,
												'class' => 'hatch-textarea'
											)
										); ?>
									</p>
								</div>
							</div>
						</div>
					</div>

				</section>
			</div>



		<?php } // Form
	} // Class

	// Add our function to the widgets_init hook.
	 register_widget("Hatch_Contact_Widget");
}