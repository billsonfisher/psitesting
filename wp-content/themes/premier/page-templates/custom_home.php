<?php
/*
 * Template Name: Custom Home Page
 * Description: A home page with featured slider and widgets.
 *
 * @package premier
 * @since premier 1.0
 */

get_header(); ?>

  <div id="primary_home" class="content-area">
  	<div id="content" class="fullwidth_home" role="main">

    
      <?php if(! get_theme_mod('homepage_service_bool')): ?>

        <div class="section_services group">
          <div class="featuretext_middle animated" data-fx="fadeInUp">
            
            <h3><?php if(get_theme_mod('homepage_service_title')){ echo esc_html(get_theme_mod('homepage_service_title')); } else { _e('Services', 'premier'); }?></h3>
            
            <div class="unity-separator"></div>

            <div class="col home_span_1_of_3 box-1">        
              <div class="featuretext">
                <h4><a href="<?php echo esc_url( get_theme_mod( 'header_one_url' ) ); ?>"><?php echo esc_html(get_theme_mod( 'featured_textbox_header_one' ) ); ?></a></h4>
                <p><?php echo esc_html(get_theme_mod( 'featured_textbox_text_one' ) ); ?></p>
                <?php if ( get_theme_mod( 'header-one-file-upload' ) ) : ?>
                  <a href="<?php echo esc_url( get_theme_mod( 'header_one_url' ) ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'header-one-file-upload' ) ); ?>"  alt="feature one"></a>
                <?php else : ?>
                  <?php echo '<h4>' . __('Insert Image', 'premier') . '</h4>'; ?>
                <?php endif; ?>
              </div>
            </div>

            <div class="col home_span_1_of_3 box-2">         
              <div class="featuretext">
              <h4><a href="<?php echo esc_url( get_theme_mod( 'header_two_url' ) ); ?>"><?php echo esc_html(get_theme_mod( 'featured_textbox_header_two' ) ); ?></a></h4>
              <p><?php echo esc_html(get_theme_mod( 'featured_textbox_text_two' ) ); ?></p>
              <?php if ( get_theme_mod( 'header-two-file-upload' ) ) : ?>
                <a href="<?php echo esc_url( get_theme_mod( 'header_two_url' ) ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'header-two-file-upload' ) ); ?>"  alt="feature two"></a>
              <?php else : ?>
                <?php echo '<h4>' . __('Insert Image', 'premier') . '</h4>'; ?>
              <?php endif; ?>
              </div>
            </div>

            <div class="col home_span_1_of_3 box-3">         
              <div class="featuretext">
              <h4><a href="<?php echo esc_url( get_theme_mod( 'header_three_url' ) ); ?>"><?php echo esc_html(get_theme_mod( 'featured_textbox_header_three' ) ); ?></a></h4>
              <p><?php echo esc_html(get_theme_mod( 'featured_textbox_text_three' ) ); ?></p>
              <?php if ( get_theme_mod( 'header-three-file-upload' ) ) : ?>
                <a href="<?php echo esc_url( get_theme_mod( 'header_three_url' ) ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'header-three-file-upload' ) ); ?>"  alt="feature three"></a>
              <?php else : ?>
                <?php echo '<h4>' . __('Insert Image', 'premier') . '</h4>'; ?>
              <?php endif; ?>
              </div>
            </div>

        </div> 
      </div> 

    <?php endif; ?>

     <?php if(! get_theme_mod('homepage_promotional_bool')): ?>

        <div id="featuretext_container">
          <div class="featuretext_left animated" data-fx="slideInLeft">
            <h2><?php if( get_theme_mod( 'featured_textbox' ) ){ echo esc_html(get_theme_mod( 'featured_textbox' ) ); } else { _e( 'Promotional Bar', 'premier' ); } ?></h2>
          </div>  
          <div class="featuretext_right animated" data-fx="slideInRight">
            <?php if ( get_theme_mod( 'featured_button_url' ) ) : ?>
              <a href="<?php echo esc_url( get_theme_mod( 'featured_button_url' ) ); ?>" ><?php echo esc_attr(get_theme_mod( 'featured_button_txt' )); ?></a>
            <?php endif; ?>
          </div>  
        </div>

      <?php endif; ?> 
      
      
    <?php if(! get_theme_mod('homepage_recent_bool')): ?>

      <div class="section_thumbnails group animated" data-fx="fadeInUp">

        <h3><?php if(get_theme_mod('homepage_recent_title')){ echo esc_html(get_theme_mod('homepage_recent_title')); } else { _e('Recent Posts', 'premier'); }?></h3>

        <div class="unity-separator"></div>

        <?php $the_query = new WP_Query(
          array(
            'showposts' => 6,
            'post__not_in' => get_option("sticky_posts"),
          ));
        ?>

        <?php while ($the_query -> have_posts()) : $the_query -> the_post(); ?>

          <div class="col home_span_1_of_3">
            <article class="recent">
              
                <?php
                  if ( has_post_thumbnail() ) {
                    $image_src = wp_get_attachment_image_src( get_post_thumbnail_id(),'recent' );
                    echo '<img alt="post" class="imagerct" src="' . $image_src[0] . '">';
                  }
                ?>
                
              <div class="recent_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
            </article>
          </div>	

        <?php endwhile; ?>

      </div>

    <?php endif; ?>


    <?php if(! get_theme_mod('homepage_partners_bool')): ?> 

      <div class="section_clients group">
        <div class="client animated" data-fx="fadeInUp">

          <h3><?php if(get_theme_mod('homepage_partners_title')){ echo esc_html( get_theme_mod( 'homepage_partners_title' ) ); } else { _e('Partners', 'premier'); } ?></h3>

          <div class="unity-separator"></div>
          
          <div class="col span_1_of_4">
            <div class="client_recent">
              <?php if ( get_theme_mod( 'logo-one-file-upload' ) ) : ?>
                <a href="<?php echo esc_url( get_theme_mod( 'logo_one_url' ) ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'logo-one-file-upload' ) ); ?>"  alt="logo one"></a>
              <?php else : ?>
                <?php echo '<h4>' . __('Insert Image', 'premier') . '</h4>'; ?>
              <?php endif; ?>
            </div>
          </div>

          <div class="col span_1_of_4">
            <div class="client_recent">
              <?php if ( get_theme_mod( 'logo-two-file-upload' ) ) : ?>
                <a href="<?php echo esc_url( get_theme_mod( 'logo_two_url' ) ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'logo-two-file-upload' ) ); ?>"  alt="logo two"></a>
              <?php else : ?>
                <?php echo '<h4>' . __('Insert Image', 'premier') . '</h4>'; ?>
              <?php endif; ?>
            </div>
          </div>

          <div class="col span_1_of_4">
            <div class="client_recent">
              <?php if ( get_theme_mod( 'logo-three-file-upload' ) ) : ?>
                <a href="<?php echo esc_url( get_theme_mod( 'logo_three_url' ) ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'logo-three-file-upload' ) ); ?>"  alt="logo three"></a>
              <?php else : ?>
                <?php echo '<h4>' . __('Insert Image', 'premier') . '</h4>'; ?>
              <?php endif; ?>
            </div>
          </div>

          <div class="col span_1_of_4">
            <div class="client_recent">
              <?php if ( get_theme_mod( 'logo-four-file-upload' ) ) : ?>
                <a href="<?php echo esc_url( get_theme_mod( 'logo_four_url' ) ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'logo-four-file-upload' ) ); ?>"  alt="logo four"></a>
              <?php else : ?>
                <?php echo '<h4>' . __('Insert Image', 'premier') . '</h4>'; ?>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>

    <?php endif; ?>


		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->

<?php get_footer(); ?>