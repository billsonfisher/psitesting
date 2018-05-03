<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package premier
 * @since premier 1.0
 */

get_header(); ?>

		<h1 class="search-title"><?php printf( __( 'Search Results for: %s', 'premier' ), '<span>' . get_search_query() . '</span>' ); ?></h1>

		<div id="primary_wrap">
		<div id="primary_fullwidth" class="content-area">
			<div id="content" class="fullwidth" role="main">

			<?php if ( have_posts() ) : ?>

				<?php premier_content_nav( 'nav-above' ); ?>

				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'search' ); ?>

				<?php endwhile; ?>

				<?php premier_content_nav( 'nav-below' ); ?>

			<?php else : ?>

				<?php get_template_part( 'no-results', 'search' ); ?>

			<?php endif; ?>

			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->


</div>
<?php get_footer(); ?>