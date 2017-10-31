<?php 
/**
 * Template Name: Three columns, Sidebars Left and Right
 *
 * @package Fluida
 */
$fluida_template_layout = 'three-columns-sided'; // layout name override for page template
get_header(); ?>

	<div id="container" class="<?php echo $fluida_template_layout; ?>">
		<main id="main" role="main" <?php cryout_schema_microdata( 'main' ); ?> class="main">
			<?php get_template_part( 'content/content', 'page' ); ?>
		</main><!-- #main -->
		<?php get_sidebar( 'left' ); get_sidebar('right' ); ?>
	</div><!-- #container -->

<?php get_footer(); ?>