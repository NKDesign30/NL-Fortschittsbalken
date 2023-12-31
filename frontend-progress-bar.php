<?php
// Add the progress bar to the cart page
function display_progress_bar_on_cart_page()
{
  // Get the current cart total
  $cart_total = WC()->cart->get_cart_contents_total();

  // Get the thresholds
  $thresholds = get_option('wc_progress_bar_discount_thresholds_amount', []);



  // Display the progress bar
  echo '<div class="progress-bar">
        <div class="progress-bar-fill" style="width: ' . $progress . '%"></div>
    </div>';
}
add_action('woocommerce_before_cart', 'display_progress_bar_on_cart_page');

// Testfunktion für den Hook
function test_hook_function()
{
  echo 'Der Hook "woocommerce_before_cart" wurde aufgerufen!';
}

add_action('woocommerce_before_cart', 'test_hook_function');
