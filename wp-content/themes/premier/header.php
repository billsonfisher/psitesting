<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package premier
 * @since premier 1.0
 */
?><!DOCTYPE html>
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />

<?php if(get_theme_mod('premier_global_favicon')) : ?>
	<link rel="shortcut icon" href="<?php echo esc_url(get_theme_mod('premier_global_favicon')); ?>" />
<?php endif; ?>

<?php if(get_theme_mod('premier_global_apple_icon')) : ?>
	<link rel="apple-touch-icon" href="<?php echo esc_url(get_theme_mod('premier_global_apple_icon')); ?>">
<?php endif; ?>

<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div id="wrap">
		<div id="page" class="hfeed site">

			<?php do_action( 'before' ); ?>

		    <div id="masthead-wrap">

			    <div id="topbar_container">
				    <div class="topbar">
					    <?php

							$list_contact_options = array(
								array('telnumber', __( 'Telephone Number', 'premier'), 'phone'),
								array('mobile', __( 'Mobile Number', 'premier'), 'mobile'),
								array('email', __( 'Email Address', 'premier'), 'envelope'),
								array('address', __( 'Address', 'premier'), 'map-marker'),
							);

							echo "<div class='topbar_content_left'>";

							$arraycount = count($list_contact_options);
							for ($row = 0; $row <  $arraycount; $row++) {
								if( get_theme_mod( $list_contact_options[$row][0] . '_textbox_header_one' ) ){
									echo '<div class="contact ' . $list_contact_options[$row][0] . '"><i class="fa fa-' . $list_contact_options[$row][2] . '"></i> ' . get_theme_mod( $list_contact_options[$row][0] . '_textbox_header_one' ) . '</div>';
								}
							}

							echo "</div>";

					    ?>
				    	<div class="topbar_content_right <?php if ( is_woocommerce_activated()) : ?>withCart<?php endif; ?>"><?php get_template_part( 'inc/socmed' ); ?>
				    	<?php
					    	if ( is_woocommerce_activated()) {
					    		echo '<div class="top-bar-mini-cart"><ul>';
								do_action( 'premier_header_cart' );
								echo '</ul></div>';
							} 
						?>
						</div><!-- .topbar_content_right -->
				    </div>
			    </div>

				<header id="masthead" class="site-header header_container" role="banner">

					<?php if ( get_theme_mod( 'premier_logo' ) ) : ?>
				<div class="logo_wrapper">
						<div class="site-logo">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo esc_url( get_theme_mod( 'premier_logo' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"></a>
						</div>
				</div>
					<?php else : ?>
				<div class="site-introduction_wrapper">
						<div class="site-introduction">

							<h1 class="site-title"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
							<p class="site-description"><?php bloginfo( 'description' ); ?></p>
                            </div>
						</div>
					<?php endif; ?>


				<?php if(is_front_page()):?>

					<?php
						$header_image = get_header_image();
                    ?>

                    <?php
						if(get_theme_mod('homepage_slider_show')):
							echo('<div class="entry-header">');
						else:
							if($header_image):
								echo ('<div class="entry-header-wrap" style="background-image:url(' . $header_image . ');">');
							else:
								echo ('<div class="entry-header-wrap">');
							endif;
						endif;
					?>

				</div><!-- .entry-header -->

      <?php endif; //end is_front_page?>

<?php if(is_front_page()):?>
	<?php if(get_theme_mod('homepage_slider_show')): ?>

        <?php

          $featured_cat   =   get_theme_mod( 'homepage_slider_cat' );
          $number         =   get_theme_mod( 'homepage_slider_slide_no' );

          $the_query     =   new WP_Query( array(
            'cat'             => $featured_cat,
            'posts_per_page'  => $number,
            'meta_query'      => array(
                array(
                  'key'           => '_thumbnail_id',
                  'compare'       => 'EXISTS',
                ),
            ),
          ));

        ?>

        <div class="flex-container">
          <div class="flexslider">
            <ul class="slides">
              <?php  while ($the_query -> have_posts()) : $the_query -> the_post(); ?>
                <li>
                  <?php the_post_thumbnail(); ?>
                  <div class="caption_wrap">
                    <div class="flex-caption">
                      <div class="flex-caption-title">

                        <a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
                        <div class="unity-separator"></div>
                      </div>
                      <p><?php echo esc_html(premier_get_slider_excerpt()); ?> <a href="<?php the_permalink() ?>">...</a></p>
                      <div class="flex-more-link"><a href="<?php the_permalink() ?>"><?php echo __('Read More', 'premier'); ?></a></div>
                    </div>
                  </div>
                </li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>

  <?php endif; ?>
<?php endif; ?>

      <?php if(!is_front_page()):?>
			<?php
			if ( has_post_thumbnail() ) {
				$image_src = wp_get_attachment_image_src( get_post_thumbnail_id(),'featured' );
			}
  			?>


        <div class="entry-header-wrap" <?php if ( has_post_thumbnail() ): ?>style="background-image:url(<?php echo $image_src[0]; ?>);"<?php endif; ?>>
                        <h1 class="page-title">
                        <?php if( is_home() && get_option('page_for_posts') ) { ?>
                        <?php echo apply_filters('the_title',get_page( get_option('page_for_posts') )->post_title); ?>
                        <?php } elseif( is_singular() ) { ?>
                        <?php the_title(); ?>
                        <?php } ?>


                        <?php
							if ( is_category() ) {
								printf( __( 'Category Archives: %s', 'premier' ), '<span>' . single_cat_title( '', false ) . '</span>' );

							}  elseif ( is_woocommerce_activated() && is_product_category() ){
								single_term_title();
							} elseif( is_woocommerce_activated() && is_shop() ){
								woocommerce_page_title();
							}
							elseif ( is_tag() ) {
								printf( __( 'Tag Archives: %s', 'premier' ), '<span>' . single_tag_title( '', false ) . '</span>' );

							} elseif ( is_author() ) {
								/* Queue the first post, that way we know
								 * what author we're dealing with (if that is the case).
								*/
								the_post();
								printf( __( 'Author Archives: %s', 'premier' ), '<span class="vcard"><a class="url fn n" href="' . get_author_posts_url( get_the_author_meta( "ID" ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>' );
								/* Since we called the_post() above, we need to
								 * rewind the loop back to the beginning that way
								 * we can run the loop properly, in full.
								 */
								rewind_posts();

							} elseif ( is_day() ) {
								printf( __( 'Daily Archives: %s', 'premier' ), '<span>' . get_the_date() . '</span>' );

							} elseif ( is_month() ) {
								printf( __( 'Monthly Archives: %s', 'premier' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );

							} elseif ( is_year() ) {
								printf( __( 'Yearly Archives: %s', 'premier' ), '<span>' . get_the_date( 'Y' ) . '</span>' );

							} 
						?>

                        </h1>

        </div><!-- .entry-header -->
	<?php endif; ?>


					<nav role="navigation" class="site-navigation main-navigation">

						<h1 class="assistive-text"><a href="#" title="<?php _e('Navigation Toggle', 'premier'); ?>"><?php _e( 'Menu', 'premier' ); ?></a></h1>

						<div class="assistive-text skip-link">
							<a href="#content" title="<?php esc_attr_e( 'Skip to content', 'premier' ); ?>"><?php _e( 'Skip to content', 'premier' ); ?></a>
						</div>

						<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>

						<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">

							<label>
								<span class="screen-reader-text">Search for:</span>
                                <input type="search" class="search-field" placeholder="<?php echo __('SEARCH...', 'premier'); ?>" value="" name="s" title="<?php echo __('search', 'premier'); ?>" />
							</label>

							<input type="submit" class="search-submit" value="Search" />

						</form>

					</nav><!-- .site-navigation .main-navigation -->

				</header><!-- #masthead .site-header -->

			</div><!-- #masthead-wrap -->

			<div id="main" class="site-main">
