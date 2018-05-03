<?php

/**

 * This template generates links to social media icons once set in the theme options.  

 *

 * @package premier

 */
?>
	<ul class="social-media">
		<?php if ( get_theme_mod( 'twitter' ) ) : ?>
			<li><a class="nav-social-btn twitter-icon" title="Twitter" href="<?php echo esc_url( get_theme_mod( 'twitter' ) ); ?>" target="_blank"><i class="fa fa-twitter"></i></a></li>
		<?php endif; ?>
		
		<?php if ( get_theme_mod( 'facebook' ) ) : ?>
			<li><a class="nav-social-btn facebook-icon" title="Facebook" href="<?php echo esc_url( get_theme_mod( 'facebook' ) ); ?>" target="_blank"><i class="fa fa-facebook-square"></i></a></li>
		<?php endif; ?>
		
		<?php if ( get_theme_mod( 'pinterest' ) ) : ?>
			<li><a class="nav-social-btn pinterest-icon" title="Pinterest" href="<?php echo esc_url( get_theme_mod( 'pinterest' ) ); ?>" target="_blank"><i class="fa fa-pinterest"></i></a></li>
		<?php endif; ?>
		
		<?php if ( get_theme_mod( 'linkedin' ) ) : ?>
			<li><a class="nav-social-btn linkedin-icon" title="LinkedIn" href="<?php echo esc_url( get_theme_mod( 'linkedin' ) ); ?>" target="_blank"><i class="fa fa-linkedin"></i></a></li>
		<?php endif; ?>
		
		<?php if ( get_theme_mod( 'googleplus' ) ) : ?>
			<li><a class="nav-social-btn google-icon" title="Google+" href="<?php echo esc_url( get_theme_mod( 'googleplus' ) ); ?>" target="_blank"><i class="fa fa-google-plus-square"></i></a></li>
		<?php endif; ?>
		
		<?php if ( get_theme_mod( 'flickr' ) ) : ?>
			<li><a class="nav-social-btn flickr-icon" title="Flickr" href="<?php echo esc_url( get_theme_mod( 'flickr' ) ); ?>" target="_blank"><i class="fa fa-flickr"></i></a></li>
		<?php endif; ?>
		
		<?php if ( get_theme_mod( 'youtube' ) ) : ?>
			<li><a class="nav-social-btn youtube-icon" title="Youtube" href="<?php echo esc_url( get_theme_mod( 'youtube' ) ); ?>" target="_blank"><i class="fa fa-youtube-play"></i></a></li>
		<?php endif; ?>
		
		<?php if ( get_theme_mod( 'vimeo' ) ) : ?>
			<li><a class="nav-social-btn vimeo-icon" title="Vimeo" href="<?php echo esc_url( get_theme_mod( 'vimeo' ) ); ?>" target="_blank"><i class="fa fa-vimeo-square"></i></a></li>
		<?php endif; ?>
		
		<?php if ( get_theme_mod( 'dribble' ) ) : ?>
			<li><a class="nav-social-btn dribbble-icon" title="Dribbble" href="<?php echo esc_url( get_theme_mod( 'dribble' ) ); ?>" target="_blank"><i class="fa fa-dribbble"></i></a></li>
		<?php endif; ?>	
		
		<?php if ( get_theme_mod( 'tumblr' ) ) : ?>
			<li><a class="nav-social-btn tumblr-icon" title="Tumblr" href="<?php echo esc_url( get_theme_mod( 'tumblr' ) ); ?>" target="_blank"><i class="fa fa-tumblr-square"></i></a></li>
		<?php endif; ?>

		<?php if ( get_theme_mod( 'github' ) ) : ?>
			<li><a class="nav-social-btn github-icon" title="Github" href="<?php echo esc_url( get_theme_mod( 'github' ) ); ?>" target="_blank"><i class="fa fa-github"></i></a></li>
		<?php endif; ?>	
	    
	    <?php if ( get_theme_mod( 'instagram' ) ) : ?>
			<li><a class="nav-social-btn instagram-icon" title="Instagram" href="<?php echo esc_url( get_theme_mod( 'instagram' ) ); ?>" target="_blank"><i class="fa fa-instagram"></i></a></li>
		<?php endif; ?>		

		<?php if ( get_theme_mod( 'xing' ) ) : ?>
			<li><a class="nav-social-btn xing-icon" title="Xing" href="<?php echo esc_url( get_theme_mod( 'xing' ) ); ?>" target="_blank"><i class="fa fa-xing"></i></a></li>
		<?php endif; ?>		

	</ul><!-- #social-icons-->