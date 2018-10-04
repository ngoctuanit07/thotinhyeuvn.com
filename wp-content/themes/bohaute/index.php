<?php get_header(); ?>
		<div class="front-wrapper">
			<div id="content">
				<div id="blog" class="wrap cf">
					<div id='masonry' class="blog-list container">
							<?php while ( have_posts() ) : the_post(); ?>
			  						<?php get_template_part( 'home-content/home', get_post_format() ); ?>
			  				<?php endwhile;  ?>
							<div class="clear"></div>
		     				<div class="gutter-sizer"></div>
					</div>
					<div class="pagination">
						<?php $older_text = __('Older Entries','bohaute'); next_posts_link($older_text); ?>
					</div>
				</div> <!-- inner-content -->
			</div> <!-- content -->
		</div><!-- front-wrapper -->

<?php get_footer(); ?>