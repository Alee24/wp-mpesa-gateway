<?php get_header(); ?>

<div id="primary" class="content-area mpesa-shop-page">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title">Shop</h1>
            <?php
            // Optional: Cart Icon with count
            $cart_count = isset($_SESSION['mpesa_cart']) ? count($_SESSION['mpesa_cart']) : 0;
            ?>
            <a href="<?php echo get_permalink( get_option('mpesa_cart_page_id') ); ?>" class="mpesa-cart-link">
                Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)
            </a>
        </header>

        <?php
        // Logic to handle static page vs archive
        global $wp_query;
        $products = $wp_query; // Default to main query
        $is_static_shop = is_page( get_option('mpesa_shop_page_id') );

        if ( $is_static_shop ) {
            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
            if ( $paged < 2 ) {
                $paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
            }

            $args = array(
                'post_type'      => 'mpesa_product',
                'post_status'    => 'publish',
                'posts_per_page' => 12, // Default WP limit or custom
                'paged'          => $paged,
            );
            $products = new WP_Query( $args );
        }
        ?>

        <?php if ( $products->have_posts() ) : ?>
            <div class="mpesa-products-grid">
                <?php while ( $products->have_posts() ) : $products->the_post(); 
                    $price = get_post_meta( get_the_ID(), '_price', true );
                    $img = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                ?>
                    <div class="mpesa-product-card">
                        <div class="product-image" style="background-image: url('<?php echo esc_url($img ?: WP_MPESA_GATEWAY_URL . 'assets/images/placeholder.png'); ?>');">
                            <a href="<?php the_permalink(); ?>"></a>
                            <?php 
                            $type = get_post_meta( get_the_ID(), '_product_type', true );
                            if($type): ?>
                                <span class="mpesa-badge"><?php echo ucfirst($type); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h2 class="product-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <div class="product-meta-row">
                                <span class="product-price">KES <?php echo number_format((float)$price, 2); ?></span>
                                <button class="mpesa-btn add-to-cart" data-id="<?php the_ID(); ?>">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php 
            if ( $is_static_shop ) {
                $big = 999999999;
                echo '<div class="pagination">';
                echo paginate_links( array(
                    'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                    'format'  => '?paged=%#%',
                    'current' => max( 1, $paged ),
                    'total'   => $products->max_num_pages
                ) );
                echo '</div>';
            } else {
                the_posts_pagination();
            }
            ?>
            
            <?php wp_reset_postdata(); ?>

        <?php else : ?>
            <p>No products found. Please add products from the dashboard.</p>
        <?php endif; ?>

    </main>
</div>

<?php get_footer(); ?>
