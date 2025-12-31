<?php get_header(); ?>

<div id="primary" class="content-area mpesa-single-product">
    <main id="main" class="site-main">

        <?php while ( have_posts() ) : the_post(); 
            $price = get_post_meta( get_the_ID(), '_price', true );
            $stock = get_post_meta( get_the_ID(), '_stock', true );
            $type = get_post_meta( get_the_ID(), '_product_type', true );
        ?>

        <div class="mpesa-product-container">
            <div class="product-gallery">
                <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'large' ); ?>
                <?php endif; ?>
            </div>
            
            <div class="product-summary">
                <h1 class="product-title"><?php the_title(); ?></h1>
                <p class="product-price">KES <?php echo number_format($price, 2); ?></p>
                <div class="product-description">
                    <?php the_content(); ?>
                </div>

                <div class="cart-actions">
                    <input type="number" id="qty" value="1" min="1" max="<?php echo esc_attr($stock); ?>">
                    <button class="mpesa-btn add-to-cart-single" data-id="<?php the_ID(); ?>">
                        Add to Cart
                    </button>
                    <div id="add-to-cart-message"></div>
                </div>

                <div class="product-meta">
                    <span>Type: <?php echo ucfirst($type); ?></span>
                </div>
            </div>
        </div>

        <?php endwhile; ?>

    </main>
</div>

<?php get_footer(); ?>
