<?php if ($this->products) : ?>
<div class="admin-featured-products">
    <h3 class="sep">
        <span><?php echo $this->translate('Store Listings') ?></span>
    </h3>

    <div id="admin-featured-products">
        <ul>
            <?php foreach ($this->products as $product) : ?>
                <li>
                    <a href="<?php echo Engine_Settings::get('warehouse.website_url'); ?>/store/product/<?php echo $product['slug']; ?>" target="_blank">
                        <img src="<?php echo $product['logo']['url']; ?>" />
                        <span>
                <?php echo $product['name']; ?>
                            <span>
                    by <?php echo $product['expert']['name']; ?>
                </span>
                            <span>
                    <?php echo $product['price'] === '0.00' ? 'Free' : '$' . $product['price']; ?>
                </span>
            </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="admin-featured-view-more" style="margin-bottom: 30px;">
            <a href="https://www.socialengine.com/store/" target="_blank">View More Store Listings &#187; </a>
        </div>
    </div>
</div>
<?php endif; ?>
