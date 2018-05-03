<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package premier
 * @since premier 1.0
 */
?>

	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'premier' ), 'after' => '</div>' ) ); ?>
        <footer class="entry-meta">
		<?php edit_post_link( __( 'Edit', 'premier' ), '<span class="edit-link">', '</span>' ); ?>
        </footer><!-- .entry-meta -->
	</div><!-- .entry-content -->
