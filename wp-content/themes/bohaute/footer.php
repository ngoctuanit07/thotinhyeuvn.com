			<footer class="footer" role="contentinfo">

				<div id="inner-footer" class="wrap cf">
					<div class="source-org copyright">
						 &#169; <?php echo date(__('Y','bohaute')); ?> <?php bloginfo( 'name' ); ?> 
						<span><?php if(is_home()): ?>
							- <a href="http://wordpress.org/" target="_blank"><?php _e('Powered by WordPress','bohaute'); ?></a> <span><?php _e('and','bohaute'); ?></span> <a href="http://fashionblogger.rocks" target="_blank"><?php _e('Fashion Blogger','bohaute'); ?></a>
						<?php endif; ?>
						</span>
					</div>

					<div class="social-icons footer-social">
		           		<?php echo bohaute_social_icons(); ?>
                	</div> <!-- social-icons-->

				</div>

			</footer>
			<a href="#" class="scrollToTop"><span class="fa fa-chevron-circle-up"></span></a>
		</div>

		<?php wp_footer(); ?>
	</body>

</html> <!-- end of site. what a ride! -->