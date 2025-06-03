<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Aviv Order Management', 'aviv-order-admin'); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="#orders" class="nav-tab nav-tab-active"><?php echo esc_html__('Orders', 'aviv-order-admin'); ?></a>
        <a href="#clients" class="nav-tab"><?php echo esc_html__('Clients', 'aviv-order-admin'); ?></a>
        <a href="#products" class="nav-tab"><?php echo esc_html__('Products', 'aviv-order-admin'); ?></a>
    </h2>

    <div id="loading-message"><?php echo esc_html__('Loading...', 'aviv-order-admin'); ?></div>
    <div id="error-message"></div>

    <!-- Orders Tab -->
    <div id="orders" class="tab-content">
        <form id="orders-filter" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="product_filter"><?php echo esc_html__('Product', 'aviv-order-admin'); ?></label>
                    <select id="product_filter" name="product_filter">
                        <option value=""><?php echo esc_html__('All Products', 'aviv-order-admin'); ?></option>
                        <?php
                        $products = get_posts([
                            'post_type' => 'product',
                            'posts_per_page' => -1,
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ]);
                        foreach ($products as $product) {
                            printf(
                                '<option value="%s">%s</option>',
                                esc_attr($product->ID),
                                esc_html($product->post_title)
                            );
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status_filter"><?php echo esc_html__('Status', 'aviv-order-admin'); ?></label>
                    <select id="status_filter" name="status_filter">
                        <option value=""><?php echo esc_html__('All Statuses', 'aviv-order-admin'); ?></option>
                        <?php
                        foreach (wc_get_order_statuses() as $status => $label) {
                            printf(
                                '<option value="%s">%s</option>',
                                esc_attr($status),
                                esc_html($label)
                            );
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_from"><?php echo esc_html__('From Date', 'aviv-order-admin'); ?></label>
                    <input type="text" id="date_from" name="date_from" class="datepicker" />
                </div>
                <div class="form-group">
                    <label for="date_to"><?php echo esc_html__('To Date', 'aviv-order-admin'); ?></label>
                    <input type="text" id="date_to" name="date_to" class="datepicker" />
                </div>
            </div>
            <div class="button-row">
                <button type="submit" class="button button-primary"><?php echo esc_html__('Apply Filters', 'aviv-order-admin'); ?></button>
                <button type="button" id="reset-filter" class="button"><?php echo esc_html__('Reset', 'aviv-order-admin'); ?></button>
            </div>
        </form>

        <table id="orders-table" class="aviv-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Order', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Date', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Client', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Contact', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Product', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Rental Dates', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Total', 'aviv-order-admin'); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Clients Tab -->
    <div id="clients" class="tab-content">
        <table id="clients-table" class="aviv-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Client Name', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Email', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Phone', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Total Orders', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Total Spent', 'aviv-order-admin'); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Products Tab -->
    <div id="products" class="tab-content">
        <table id="products-table" class="aviv-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Product Name', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Total Rentals', 'aviv-order-admin'); ?></th>
                    <th><?php echo esc_html__('Reserved Dates', 'aviv-order-admin'); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
