<?php get_header(); ?>

	<div id="content">
		<div id="inner-content" class="wrap cf">
			<header class="article-header">
			<div id="inner-content" class="wrap cf">
						<h1 class="archive-title h2">
							<?php the_archive_title(); ?>
						</h1>
				</div>
		</header>
			<div id='masonry' class="blog-list container">
					<?php while ( have_posts() ) : the_post(); ?>
	  						<?php get_template_part( 'home-content/home', get_post_format() ); ?>
	  				<?php endwhile;  ?>
     				<div class="gutter-sizer"></div>
			</div>
			<div class="pagination">
				<?php $older_text = __('Older Entries','bohaute'); next_posts_link($older_text); ?>
			</div>
		</div> <!-- inner-content -->
	</div> <!-- content -->

<?php get_footer(); ?>