<?php
/**
 * Template Name: One column, no sidebar
 *
 * A custom page template without sidebar.
 *
 * The "Template Name:" bit above allows this to be selectable
 * from a dropdown menu on the edit page screen.
 *
 * @package Fluida
 */
$fluida_template_layout = 'one-column'; // layout name override for page template
get_header(); ?>

	<div id="container" class="one-column">
		<main id="main" role="main" <?php cryout_schema_microdata( 'main' ); ?> class="main">
			<?php get_template_part( 'content/content', 'page' ); ?>
		</main><!-- #main -->	
	</div><!-- #container -->

<?php get_footer(); ?>
