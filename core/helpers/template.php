<?php  /**
 * Template helper funtions
 *
 * This file is used to display general template elements, such as breadcrumbs, site-wide pagination,  etc.
 *
 * @package Hatch
 * @since Hatch 1.0
 */

/**
* Print breadcrumbs
*
* @param    varchar         $wrapper        Type of html wrapper
* @param    varchar         $wrapper_class  Class of HTML wrapper
* @echo     string                          Post Meta HTML
*/

if( !function_exists( 'hatch_bread_crumbs' ) ) {
	function hatch_bread_crumbs( $wrapper = 'nav', $wrapper_class = 'bread-crumbs', $seperator = '/' ) {
		global $post; ?>
		<<?php echo $wrapper; ?> class="<?php echo $wrapper_class; ?>">
			<ul>
				<?php /* Home */ ?>
				<li><a href="<?php echo home_url(); ?>"><?php _e('Home',HATCH_THEME_SLUG); ?></a></li>

				<?php

				/* Base Page
					- Shop
					- Search
					- Post type parent page
				*/
				if( is_search() ) { ?>
					<li><?php echo $seperator; ?></li>
					<li><?php _e('Search',HATCH_THEME_SLUG); ?></li>
				<?php } elseif( function_exists('is_shop') && ( is_post_type_archive( 'product' ) || ( get_post_type() == "product") ) ) { ?>
					<li><?php echo $seperator; ?></li>
					<?php if( function_exists( 'woocommerce_get_page_id' )  && '' != woocommerce_get_page_id('shop') ) { ?>
						<?php $shop_page = get_post( woocommerce_get_page_id('shop') ); ?>
						<li><a href="<?php echo get_permalink( $shop_page->ID ); ?>"><?php echo $shop_page->post_title; ?></a></li>
					<?php } else { ?>
						<li><a href=""><?php _e( 'Shop' , HATCH_THEME_SLUG ); ?></li>
					<?php }
				} elseif( is_post_type_archive() || is_singular() || is_tax() ) {

					// Get the post type object
					$post_type = get_post_type_object( get_post_type() );

					// Check if we have the relevant information we need to query the page
					if( !empty( $post_type ) && isset( $post_type->labels->slug ) ) {

						// Query template
						$parentpage = get_template_link( $post_type->labels->slug .".php");

						// Display page if it has been found
						if( !empty( $parentpage ) ) { ?>
							<li><?php echo $seperator; ?></li>
							<li><a href="<?php echo get_permalink($parentpage->ID); ?>"><?php echo $parentpage->post_title; ?></a></li>
						<?php }
					};

				}

				/* Categories, Taxonomies & Parent Pages

					- Page parents
					- Category & Taxonomy parents
					- Category for current post
					- Taxonomy for current post
				*/

				if( is_page() ) {

					// Start with this page's parent ID
					$parent_id = $post->post_parent;

					// Loop through parent pages and grab their IDs
					while( $parent_id ) {
						$page = get_page($parent_id);
						$parent_pages[] = $page->ID;
						$parent_id = $page->post_parent;
					}

					// If there are parent pages, output them
					if( isset( $parent_pages ) && is_array($parent_pages) ) {
						$parent_pages = array_reverse($parent_pages);
						foreach ( $parent_pages as $page_id ) { ?>
							<!-- Parent page title -->
							<li><?php echo $seperator; ?></li>
							<li><a href="<?php echo get_permalink( $page_id ); ?>"><?php echo get_the_title( $page_id ); ?></a></li>
						<?php }
					}

				} elseif( is_category() || is_tax() ) {

					// Get the taxonomy object
					if( is_category() ) {
						$category_title = single_cat_title( "", false );
						$category_id = get_cat_ID( $category_title );
						$category_object = get_category( $category_id );
						$term = $category_object->slug;
						$taxonomy = 'category';
					} else {
						$term = get_query_var('term' );
						$taxonomy = get_query_var( 'taxonomy' );
					}

					$term = get_term_by( 'slug', $term , $taxonomy );

					// Start with this terms's parent ID
					$parent_id = $term->parent;

					// Loop through parent terms and grab their IDs
					while( $parent_id ) {
						$cat = get_term_by( 'id' , $parent_id , $taxonomy );
						$parent_terms[] = $cat->term_id;
						$parent_id = $cat->parent;
					}

					// If there are parent terms, output them
					if( isset( $parent_terms ) && is_array($parent_terms) ) {
						$parent_terms = array_reverse($parent_terms);

						foreach ( $parent_terms as $term_id ) {
							$term = get_term_by( 'id' , $term_id , $taxonomy ); ?>

							<li><?php echo $seperator; ?></li>
							<li><a href="<?php echo get_term_link( $term_id , $taxonomy ); ?>"><?php echo $term->name; ?></a></li>

						<?php }
					}

				} elseif ( is_single() && get_post_type() == 'post' ) {

					// Get all post categories but use the first one in the array
					$category_array = get_the_category();

					foreach ( $category_array as $category ) { ?>

						<li><?php echo $seperator; ?></li>
						<li><a href="<?php echo get_category_link( $category->term_id ); ?>"><?php echo get_cat_name( $category->term_id ); ?></a></li>

					<?php }

				} elseif( is_singular() ) {

					// Get the post type object
					$post_type = get_post_type_object( get_post_type() );

					// If this is a product, make sure we're using the right term slug
					if( is_post_type_archive( 'product' ) || ( get_post_type() == "product" ) ) {
						$taxonomy = 'product_cat';
					} elseif( !empty( $post_type ) && isset( $post_type->taxonomies[0] ) ) {
						$taxonomy = $post_type->taxonomies[0];
					};

					if( isset( $taxonomy ) && !is_wp_error( $taxonomy ) ) {
						// Get the terms
						$terms = get_the_terms( $post->ID, $taxonomy );

						// If this term is legal, proceed
						if( is_array( $terms ) ) {

							// Loop over the terms for this post
							foreach ( $terms as $term ) { ?>

								<li><?php echo $seperator; ?></li>
								<li><a href="<?php echo get_term_link( $term->slug, $taxonomy ); ?>"><?php echo $term->name; ?></a></li>

							<?php }
						}
					}
				} ?>

				<?php /* Current Page / Post / Post Type

					- Page / Page / Post type title
					- Search term
					- Curreny Taxonomy
					- Current Tag
					- Current Category
				*/

				if( is_singular() ) { ?>

					<li><?php echo $seperator; ?></li>
					<li><span class="current"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></span></li>

				<?php } elseif ( is_search() ) { ?>

					<li><?php echo $seperator; ?></li>
					<li><span class="current">"<?php the_search_query(); ?>"</span></li>

				<?php } elseif( is_tax() ) {

					// Get this term's details
					$term = get_term_by( 'slug', get_query_var('term' ), get_query_var( 'taxonomy' ) ); ?>

					<li><?php echo $seperator; ?></li>
					<li><span class="current"><?php echo $term->name; ?></span></li>

				<?php  } elseif( is_tag() ) { ?>

					<li><?php echo $seperator; ?></li>
					<li><span class="current"><?php echo single_tag_title(); ?></span></li>

				<?php } elseif( is_category() ) { ?>

					<li><?php echo $seperator; ?></li>
					<li><span class="current"><?php echo single_cat_title(); ?></span></li>

				<?php } elseif ( is_archive() && is_month() ) { ?>

					<li><?php echo $seperator; ?></li>
					<li><span class="current"><?php echo get_the_date( 'F Y' ); ?></span></li>

				<?php } elseif ( is_archive() && is_year() ) { ?>

					<li><?php echo $seperator; ?></li>
					<li><span class="current"><?php echo get_the_date( 'Y' ); ?></span></li>

				<?php } elseif ( is_archive() && is_author() ) { ?>

					<li><?php echo $seperator; ?></li>
					<li><span class="current"><?php echo get_the_author(); ?></span></li>

				<?php } ?>
			</ul>
		</<?php echo $wrapper; ?>>
	<?php }
} // hatch_post_meta

/**
* Print pagination
*
* @param    array           $args           Arguments for this function, including 'query', 'range'
* @param    varchar         $wrapper        Type of html wrapper
* @param    varchar         $wrapper_class  Class of HTML wrapper
* @echo     string                          Post Meta HTML
*/
if( !function_exists( 'hatch_pagination' ) ) {
	function hatch_pagination( $args = NULL , $wrapper = 'div', $wrapper_class = 'pagination' ) {

		// Set up some globals
		global $wp_query, $paged;

		// Get the current page
		if( empty($paged ) ) $paged = ( get_query_var('page') ? get_query_var('page') : 1 );

		// Set a large number for the 'base' argument
		$big = 99999;

		// Get the correct post query
		if( !isset( $args[ 'query' ] ) ){
			$use_query = $wp_query;
		} else {
			$use_query = $args[ 'query' ];
		} ?>

		<<?php echo $wrapper; ?> class="<?php echo $wrapper_class; ?>">
			<?php echo paginate_links( array(
				'base' => str_replace( $big, '%#%', get_pagenum_link($big) ),
				'prev_next' => true,
				'mid_size' => ( isset( $args[ 'range' ] ) ? $args[ 'range' ] : 3 ) ,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'type' => 'list',
				'current' => $paged,
				'total' => $use_query->max_num_pages
			) ); ?>
		</<?php echo $wrapper; ?>>
	<?php }
} // hatch_pagination

/**
* Get Page Title
*
* Returns an array including the title and excerpt used across the site
*
* @param    array           $args           Arguments for this function, including 'query', 'range'
* @echo     array           $title_array    Section Title & Excerpt
*/
if( !function_exists( 'hatch_get_page_title' ) ) {
	function hatch_get_page_title() {
		global $post;

		// Setup return
		$title_array = array();

		if(!empty($parentpage) && !is_search()) {
			$parentpage = get_template_link(get_post_type().".php");
			$title_array['title'] = $parentpage->post_title;
			if($parentpage->post_excerpt != ''){ $title_array['excerpt'] = $parentpage->post_excerpt; }

		} elseif( is_page() ) {
			while ( have_posts() ) { the_post();
				$title_array['title'] = get_the_title();
				if( $post->post_excerpt != "") $title_array['excerpt'] = strip_tags( get_the_excerpt() );
			};
		} elseif( is_search() ) {
			$title_array['title'] = __( 'Search' , HATCH_THEME_SLUG );
			$title_array['excerpt'] = the_search_query();
		} elseif( is_tag() ) {
			$title_array['title'] = single_tag_title();
		} elseif(!is_page() && is_category() ) {
			$category = get_the_category();
			$title_array['title'] = $category[0]->name;
			$title_array['excerpt'] = $category[0]->description;
		} elseif (!is_page() && get_query_var('term' ) != '' ) {
			$term = get_term_by( 'slug', get_query_var('term' ), get_query_var( 'taxonomy' ) );
			$title_array['title'] = $term->name;
			$title_array['excerpt'] = $term->description;
		} elseif ( is_day() ) {
			$title_array['title' ] = sprintf( __( 'Daily Archives: %s', HATCH_THEME_SLUG ), get_the_date() );
		} elseif ( is_month() ) {
			$title_array['title' ] = sprintf( __( 'Monthly Archives: %s', HATCH_THEME_SLUG ), get_the_date( _x( 'F Y', 'monthly archives date format', HATCH_THEME_SLUG ) ) );
		} elseif ( is_year() ) {
			$title_array['title' ] = sprintf( __( 'Yearly Archives: %s', HATCH_THEME_SLUG ), get_the_date( _x( 'Y', 'yearly archives date format', HATCH_THEME_SLUG ) ) );
		} elseif( function_exists('is_shop') && ( is_post_type_archive( 'product' ) || ( get_post_type() == "product") ) ) {
			if( function_exists( 'woocommerce_get_page_id' )  && '' != woocommerce_get_page_id('shop') ) {
				$shop_page = get_post( woocommerce_get_page_id('shop') );
				$title_array['title' ] = $shop_page->post_title;
			} else {
				$title_array['title' ] = __( 'Shop' , HATCH_THEME_SLUG );
			}
		} elseif( is_single() ) {
			$title_array['title' ] = get_the_title();
		} else {
			$title_array['title' ] = __( 'Archives', HATCH_THEME_SLUG );
		}

		return apply_filters( 'hatch_get_page_title' , $title_array );
	}
} // hatch_get_page_title

/**
 * Retrieve the classes for the header element as an array.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @return array Array of classes.
 */
if( !function_exists( 'hatch_get_header_class' ) ) {
	function hatch_get_header_class( $class = '' ){

		$header_align_option = hatch_get_theme_mod( 'header-layout-layout' );
		$header_fixed_option = hatch_get_theme_mod( 'header-layout-fixed' );

		$classes = array();

		// Add the general site header class
		$classes[] = 'header-site';

		// Handle fixed / not fixed
		if( TRUE == $header_fixed_option ){
			$classes[] = 'header-fixed';
			$classes[] = 'invert';
		}

		// Add alignment classes
		if( 'header-logo-left' == $header_align_option ){
			$classes[] = 'header-left';
		} else if( 'header-logo-right' == $header_align_option ){
			$classes[] = 'header-right';
		} else if( 'header-logo-top' == $header_align_option ){
			$classes[] = 'nav-clear';
		} else if( 'header-logo-center-top' == $header_align_option ){
			$classes[] = 'header-center';
		} else if( 'header-logo-center' == $header_align_option ){
			$classes[] = 'header-inline';
		}

		if ( ! empty( $class ) ) {
			if ( !is_array( $class ) )
				$class = preg_split( '#\s+#', $class );
			$classes = array_merge( $classes, $class );
		} else {
			// Ensure that we always coerce class to being an array.
			$class = array();
		}

		// Default to Header Left if there are no matches above
		if( empty( $classes ) ) $classes[] = 'header-left';

		$classes = array_map( 'esc_attr', $classes );

		$classes = apply_filters( 'hatch_header_class', $classes, $class );

		return array_unique( $classes );

	}
} // hatch_get_header_class

/**
 * Display the classes for the header element.
 *
 * @param string|array $class One or more classes to add to the class list.
 */

if( !function_exists( 'hatch_header_class' ) ) {
	function hatch_header_class( $class = '' ) {
		// Separates classes with a single space, collates classes for body element
		echo 'class="' . join( ' ', hatch_get_header_class( $class ) ) . '"';
	}
} // hatch_header_class

/**
 * Retrieve the classes for the center column on archive and single pages
 *
 * @param varchar $postid Post ID to check the page template on
 * @return array Array of classes.
 */
if( !function_exists( 'hatch_get_center_column_class' ) ) {
	function hatch_get_center_column_class( $class = '' ){

		$classes = array();

		// This div will always have the .column class
		$classes[] = 'column';

		$left_sidebar_active = ( hatch_can_show_sidebar( 'left-sidebar' ) ? is_active_sidebar( HATCH_THEME_SLUG . '-left-sidebar' ) : FALSE );
		$right_sidebar_active = ( hatch_can_show_sidebar( 'right-sidebar' ) ? is_active_sidebar( HATCH_THEME_SLUG . '-right-sidebar' ) : FALSE );

		// Set classes according to the sidebars
		if( $left_sidebar_active && $right_sidebar_active ){
			$classes[] = 'span-6';
		} else if( $left_sidebar_active ){
			$classes[] = 'span-9';
		} else if( $right_sidebar_active ){
			$classes[] = 'span-9';
		} else {
			$classes[] = 'span-12';
		}

		// If there is a left sidebar and no right sidebar, add the no-gutter class
		if( $left_sidebar_active && !$right_sidebar_active ){
			$classes[] = 'no-gutter';
		}

		// Default to Header Left if there are no matches above
		if( empty( $classes ) ) {
			$classes[] = 'span-8';
		}

		$classes = array_map( 'esc_attr', $classes );

		$classes = apply_filters( 'hatch_center_column_class', $classes, $class );

		return array_unique( $classes );

	}
} // hatch_center_column_class

/**
 * Display the classes for the header element.
 *
 * @param string|array $class One or more classes to add to the class list.
 */

if( !function_exists( 'hatch_center_column_class' ) ) {
	function hatch_center_column_class( $class = '' ) {
		// Separates classes with a single space, collates classes for body element
		echo 'class="' . join( ' ', hatch_get_center_column_class( $class ) ) . '"';
	}
} // hatch_header_class

/**
 * Retrieve theme modification value for the current theme.
 *
 * @param string $name Theme modification name.
 * @return string
 */
if( !function_exists( 'hatch_get_theme_mod' ) ) {
	function hatch_get_theme_mod( $name = '' ) {
		// Add the theme prefix to our hatch option
		$name = HATCH_THEME_SLUG . '-' . $name;

		return get_theme_mod( $name );
	}
} // hatch_get_header_class

/**
 * Translates an image ratio select-icon input into a nice clean image ratio we can use
 *
 * @param string $value Value of the input
 * @return string Image size
 */
if( !function_exists( 'hatch_translate_image_ratios' ) ) {
	function hatch_translate_image_ratios( $value = '' ) {

		if( 'image-no-crop' == $value ) {
			$image_ratio = '';
		} else {
			$image_ratio = str_replace( 'image-' , '', $value );
		}

		return $image_ratio;
	}
} // hatch_get_header_class

/**
 * Check customizer and page template settings before allowing a sidebar to display
 *
 * @param   int     $sidebar                Sidebar slug to check
 */
if( !function_exists( 'hatch_can_show_sidebar' ) ) {

	function hatch_can_show_sidebar( $sidebar = 'left-sidebar' ){

		 if( is_page() ) {

			// Check the pages use page templates to decide which sidebars are allowed
			$can_show_sidebar =
				(
					is_page_template( 'template-' . $sidebar . '.php' ) ||
					is_page_template( 'template-both-sidebar.php' )
				);

		} elseif ( is_single() ) {

			// Check the single page option
		   $can_show_sidebar = hatch_get_theme_mod( 'content-layout-single-' . $sidebar );

		} else {

			// Check the arhive page option
		   $can_show_sidebar = hatch_get_theme_mod( 'content-layout-archive-' . $sidebar );

		}

		return $classes = apply_filters( 'hatch_can_show_sidebar', $can_show_sidebar, $sidebar );
	}

}

/**
 * Check customizer and page template settings before displaying a sidebar
 *
 * @param   int     $sidebar                Sidebar slug to check
 * @param   varchar $container_class       Sidebar container class
 * @return  html    $sidebar                Sidebar template
 */
if( !function_exists( 'hatch_maybe_get_sidebar' ) ) {
	function hatch_maybe_get_sidebar( $sidebar = 'left', $container_class = 'column', $return = FALSE ) {

		global $post;

		$show_sidebar = hatch_can_show_sidebar( $sidebar );

		if( TRUE == $show_sidebar ) { ?>
			<?php if( is_active_sidebar( HATCH_THEME_SLUG . '-' . $sidebar ) ) { ?>
				<div class="<?php echo $container_class; ?>">
			<?php } ?>
				<?php dynamic_sidebar( HATCH_THEME_SLUG . '-' . $sidebar ); ?>
			<?php if( is_active_sidebar( HATCH_THEME_SLUG . '-' . $sidebar ) ) { ?>
				</div>
			<?php } ?>
		<?php }
	}
} // hatch_get_header_class


/**
 * Include additional scripts in the side header
 *
 * @return  html    $additional_header_scripts                Scripts to be included in the header
 */
if( !function_exists( 'hatch_add_additional_header_scripts' ) ) {
	function hatch_add_additional_header_scripts() {

		$additional_header_scripts = hatch_get_theme_mod( 'header-scripts-scripts' );

		if( '' != $additional_header_scripts ) {
			echo stripslashes( $additional_header_scripts );
		}
	}
	add_action ( 'wp_head', 'hatch_add_additional_header_scripts' );
} // hatch_add_additional_header_scripts

/**
 * Include additional scripts in the side footer
 *
 * @return  html    $additional_header_scripts Scripts to be included in the header
 */
if( !function_exists( 'hatch_add_additional_footer_scripts' ) ) {
	function hatch_add_additional_footer_scripts() {

		$additional_footer_scripts = hatch_get_theme_mod( 'footer-scripts-scripts' );

		if( '' != $additional_footer_scripts ) {
			echo stripslashes( $additional_footer_scripts );
		}
	}
	add_action ( 'wp_footer', 'hatch_add_additional_footer_scripts' );
} // hatch_add_additional_header_scripts


/**
 * Include Google Analytics
 *
 * @return  html    $scripts Prints Google Analytics
 */
if( !function_exists( 'hatch_add_google_analytics' ) ) {
	function hatch_add_google_analytics() {

		$analytics_id = hatch_get_theme_mod( 'header-scripts-google-id' );

		if( '' != $analytics_id ) { ?>
			<script>
			  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			  ga('create', '<?php echo $analytics_id; ?>', 'auto');
			  ga('send', 'pageview');

			</script>
		<?php }
	}
	add_action ( 'wp_head', 'hatch_add_google_analytics' );
} // hatch_add_google_analytics

/**
* Background Style Generator
*
* @param    varchar     $type   Type of style to generate, background, color, text-shadow, border
* @param    array       $args
*
* @return   varchar     $inline_css CSS to append to the inline widget styles that have been generated
*/
if( !function_exists( 'hatch_inline_styles' ) ) {
	function hatch_inline_styles( $container_id = NULL, $type = 'background' , $args = array() ){

		// Get the generated CSS
		global $inline_css;

		$css = '';


		if( empty( $args ) || ( !is_array( $args ) && '' == $args ) ) return;

		switch ( $type ) {

			case 'background' :

				// Set the background array
				$bg_args = $args['background'];

				if( isset( $bg_args['color'] ) && '' != $bg_args['color'] ){
					$css .= 'background-color: ' . $bg_args['color'] . '; ';
				}

				if( isset( $bg_args['repeat'] ) && '' != $bg_args['repeat'] ){
					$css .= 'background-repeat: ' . $bg_args['repeat'] . ';';
				}

				if( isset( $bg_args['position'] ) && '' != $bg_args['position'] ){
					$css .= 'background-position: ' . $bg_args['position'] . ';';
				}

				if( isset( $bg_args['stretch'] ) && '' != $bg_args['stretch'] ){
					$css .= 'background-size: cover;';
				}

				if( isset( $bg_args['fixed'] ) && '' != $bg_args['fixed'] ){
					$css .= 'background-attachment: fixed;';
				}

				if( isset( $bg_args['image'] ) && '' != $bg_args['image'] ){
					$image = wp_get_attachment_image_src( $bg_args['image'] , 'full' );
					$css.= 'background-image: url(\'' . $image[0] .'\');';
				}
			break;

			case 'color' :

				if( '' == $args[ 'color' ] ) return ;
				$css .= 'color: ' . $args[ 'color' ] . ';';

			break;

			case 'text-shadow' :

				if( '' == $args[ 'text-shadow' ] ) return ;
				$css .= 'text-shadow: 0px 0px 10px rgba(' . implode( ', ' , hex2rgb( $args[ 'text-shadow' ] ) ) . ', 0.75);';

			break;

		}

		$inline_css = '';
		if( isset( $args['selectors'] ) ) {
			foreach ( $args['selectors'] as $selector ) {
				$inline_css .= $selector . '{' . $css . '} ';
			}
		} else if ( isset( $args['selectors'] ) && is_string( $args['selectors'] ) && '' != $args['selectors'] ) {
			$inline_css .= $args['selectors'] . ' {' . $css . '} ';
		} else {
			$inline_css .= '{' . $css . '} ';
		}

		// If there is a container ID specified, append it to the beginning of the declaration
		if( NULL != $container_id ) {
			$inline_css .= '  #' . $container_id . ' ' . $inline_css;

		}

		wp_enqueue_style( HATCH_THEME_SLUG . '-custom-widget-styles', get_template_directory_uri() . '/css/inline.css' );
		wp_add_inline_style( HATCH_THEME_SLUG . '-custom-widget-styles', $inline_css );

		return $inline_css;
	}
}