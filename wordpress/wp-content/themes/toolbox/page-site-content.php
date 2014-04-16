<?php
/**
 * Template Name: Site Content
 * Description: The template for creating site content sections (pages) 
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'page' ); ?>

				<?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>