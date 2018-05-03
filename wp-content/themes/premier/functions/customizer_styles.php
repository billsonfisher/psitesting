<?php
//////////////////////////////////////////////////////////////////
// Customizer - Add CSS
//////////////////////////////////////////////////////////////////
function premier_customizer_css() {

?>

	<link rel="stylesheet" href="<?php echo get_template_directory_uri();?>/css/<?php echo esc_attr(strtolower( get_theme_mod( 'premier_color_scheme', 'red' ) ) ); ?>.css" type="text/css" media="screen">

<?php

	if( get_theme_mod('premier_custom_main_color_bool') ){

	?>

		<style>
			a.more-link,
			.main-navigation li.current_page_item,
			#main-navigation li.current-menu-parent,
			.main-navigation li.current_page_item:hover a,
			#main-navigation li.current_page_item:hover,
			.main-navigation li.current-menu-parent:hover > a,
			.main-navigation li:hover a,
			.main-navigation > li > a,
			.main-navigation li.current_page_ancestor a,
			.main-navigation ul ul li a:hover,
			#main-navigation ul ul li a:hover i,
			.main-navigation li.current-menu-parent ul.sub-menu li.current_page_item a,
			.page-header,
			.flex-more-link a,
			.entry-title,
			.featuretext,
			#featuretext_container,
			.unity-separator,
			#smoothup:hover,
			.thumbs-more-link a:hover,
			.grid-more-link,
			.recent_title,
			.tagcloud a{
				background-color: <?php echo esc_attr(get_theme_mod( 'premier_custom_main_color' )); ?>;
			}

			.topbar_content_left .contact i,
			.testimonial-client-name a,
			.testimonial-client-name,
			.testimonial_home .testimonial .quote,
			.genericon:hover,
			.social-media li:hover,
			.main-small-navigation .menu li a,
			.main-small-navigation li.current_page_item a,
			.main-small-navigation li.current-menu-item a,
			.main-small-navigation ul ul a:hover,
			.topbar_content_left:before,
			.socialIcons a,
			.socialIcons a:visited,
			.entry-content a,
			.entry-content a:visited,
			.entry-summary a,
			.entry-summary a:visited,
			.entry-content a:hover,
			.entry-summary a:hover,
			.grid-more-link a hover,
			.nav-social-btn:hover i,
			.nav-social-btn:focus i,
			.nav-social-btn i{
				color: <?php echo esc_attr(get_theme_mod( 'premier_custom_main_color' )); ?>;
			}

			.tagcloud a,
			.social-media li:hover,
			.social-media li:focus {
				border-color: <?php echo esc_attr(get_theme_mod( 'premier_custom_main_color' )); ?>;
			}

		</style>
	<?php
		}// end if color override.
	?>

		<style>

			/* Font Settings */
			<?php if(get_theme_mod('font_body_display')): ?>
				body {font-family: <?php echo(get_theme_mod('font_body'));?>;}
			<?php endif; ?>

			<?php if(get_theme_mod('font_headers_display')): ?>
				h1,h2,h3,h4,h5,h6,.page-title,.flex-caption-title, .footer-widgets h4 {font-family: <?php echo(get_theme_mod('font_headers'));?>;}
			<?php endif; ?>

			<?php if( get_theme_mod('font_site_title_display') ): ?>
				#masthead-wrap .site-title { font-family: <?php echo( get_theme_mod('font_site_title') );?>; }
			<?php endif; ?>

		</style>

	<?php
}
add_action( 'wp_head', 'premier_customizer_css' );
