<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">

		<main id="main" class="site-main" role="main">

			<div class="error-404 not-found">

				<div class="page-content">

					<header class="page-header">
						<h1 class="page-title"><?php esc_html_e( '哎呀！那页是找不到的。Oops! That page can&rsquo;t be found.', 'storefront' ); ?></h1>
					</header><!-- .page-header -->

					<p><?php esc_html_e( '在这个地点什么也没有找到。尝试搜索或查看下面的链接。Nothing was found at this location. Try searching, or check out the links below.', 'storefront' ); ?></p>

					<?php
					echo '<section aria-label="' . esc_html__( '搜索Search', 'storefront' ) . '">';

					if ( storefront_is_woocommerce_activated() ) {
						the_widget( 'WC_Widget_Product_Search' );
					} else {
						get_search_form();
					}

					echo '</section>';

					if ( storefront_is_woocommerce_activated() ) {

						echo '<div class="fourohfour-columns-2">';

							echo '<section class="col-1" aria-label="' . esc_html__( '促销产品Promoted Products', 'storefront' ) . '">';

								storefront_promoted_products();

							echo '</section>';

							echo '<nav class="col-2" aria-label="' . esc_html__( '产品类别Product Categories', 'storefront' ) . '">';

								echo '<h2>' . esc_html__( '产品类别Product Categories', 'storefront' ) . '</h2>';

								the_widget( 'WC_Widget_Product_Categories', array(
									'count' => 1,
								) );

							echo '</nav>';

						echo '</div>';

						echo '<section aria-label="' . esc_html__( '流行产品Popular Products', 'storefront' ) . '">';

							echo '<h2>' . esc_html__( '流行产品Popular Products', 'storefront' ) . '</h2>';

							echo storefront_do_shortcode( 'best_selling_products', array(
								'per_page' => 4,
								'columns'  => 4,
							) );

						echo '</section>';
					}
					?>

				</div><!-- .page-content -->
			</div><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer();
