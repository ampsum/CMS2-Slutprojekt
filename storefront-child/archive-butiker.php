<?php
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title">Våra butiker</h1>
			</header>
			<?php

			while(have_posts()){
				the_post();
				?>
				<header class="entry-header">

				<?php
			the_title( sprintf( '<h2 class="alpha entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
				?>
			</header>
			<?php
			}
		else :

			get_template_part( 'content', 'none' );

		endif;
		?>

		</main>
	</div>

<?php
do_action( 'storefront_sidebar' );
get_footer();
