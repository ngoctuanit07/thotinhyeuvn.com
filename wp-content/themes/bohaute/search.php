<?php get_header(); ?>

		<div class="front-wrapper">
			<div id="content">
				<div id="inner-content" class="wrap cf">
					<header class="article-header">
					<div id="inner-content" class="wrap cf">
							<h1 class="archive-title"><span><?php _e( 'Search Results for:', 'bohaute' ); ?></span> <?php echo esc_attr(get_search_query()); ?></h1>
						</div>
				</header>
					<div id='masonry' class="blog-list container">
							
							<?php while ( have_posts() ) : the_post(); ?>
			  						<?php get_template_part( 'home-content/home', get_post_format() ); ?>
			  				<?php endwhile;  ?>
			  				<div class="widget-area-wrap">
							</div>
		     				<div class="gutter-sizer"></div>
					</div>
					<div class="pagination">
						<?php $older_text = __('Older Entries','bohaute'); next_posts_link($older_text); ?>
					</div>
				</div> <!-- inner-content -->
			</div> <!-- content -->
		</div><!-- front-wrapper -->

<?php get_footer(); ?>