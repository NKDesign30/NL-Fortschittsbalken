<?php
/*
Plugin Name: WooCommerce Fortschrittsbalken Rabatt
Description: Ein Plugin, das einen Rabatt basierend auf dem Warenkorbwert hinzufügt
Version: 1.5.4
Author: Niko
*/

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  function calculate_discount($cart)
  {
    // Den Endwarenkorbwert holen
    $total = floatval(wc_clean(wc_format_decimal($cart->get_cart_total(), wc_get_price_decimals())));

    // Die Schwellenwerte abrufen
    $thresholds = get_option('wc_progress_bar_discount_thresholds', array());


    // Die Schwellenwerte in aufsteigender Reihenfolge sortieren
    usort($thresholds, function ($a, $b) {
      return $a['amount'] <=> $b['amount'];
    });

    // Rabatt und kostenlosen Versand initialisieren
    $discount_rate = 0;
    $free_shipping = false;

    // Durchlaufe die Schwellenwerte
    foreach ($thresholds as $threshold) {
      if ($total >= $threshold['amount']) {
        // Rabatt berechnen
        $discount_rate = $threshold['discount'];
        $free_shipping = $threshold['free_shipping'];
      } else {
        break; // Wenn der Warenkorbwert unter einem Schwellenwert liegt, brechen wir die Schleife ab
      }
    }

    // Rabatt auf den Warenkorb anwenden
    $discount = $total * ($discount_rate / 100);
    if ($discount > $total) {
      $discount = $total; // Stellen Sie sicher, dass der Rabatt nicht größer als der Gesamtbetrag ist
    }

    // Den berechneten Rabatt ausgeben
    error_log('Berechneter Rabatt: ' . $discount);
    $cart->add_fee(__('Rabatt', 'woocommerce'), -$discount);

    if ($free_shipping) {
      $cart->add_fee(__('Kostenloser Versand', 'woocommerce'), -$cart->get_shipping_total());
    }
  }
  add_action('woocommerce_cart_calculate_fees', 'calculate_discount');
}


add_action('admin_menu', 'wc_progress_bar_discount_menu');

function wc_progress_bar_discount_menu()
{
  add_options_page(
    'WooCommerce Fortschrittsbalken Rabatt',
    'WooCommerce Fortschrittsbalken Rabatt',
    'manage_options',
    'wc-progress-bar-discount',
    'wc_progress_bar_discount_options_page'
  );
}

function wc_progress_bar_discount_options_page()
{
  $thresholds = get_option('wc_progress_bar_discount_thresholds', array());

  if (isset($_POST['submit'])) {
    $thresholds = array();
    if (isset($_POST['wc_progress_bar_discount_thresholds_amount']) && is_array($_POST['wc_progress_bar_discount_thresholds_amount'])) {
      $amounts = $_POST['wc_progress_bar_discount_thresholds_amount'];

      for ($i = 0; $i < count($amounts); $i++) {
        $amount = isset($amounts[$i]) ? floatval($amounts[$i]) : 0;
        $discount = isset($_POST['wc_progress_bar_discount_thresholds_discount'][$i]) ? floatval($_POST['wc_progress_bar_discount_thresholds_discount'][$i]) : 0;
        $free_shipping = isset($_POST['wc_progress_bar_discount_thresholds_free_shipping'][$i]) ? true : false;

        $thresholds[] = array(
          'amount' => $amount,
          'discount' => $discount,
          'free_shipping' => $free_shipping,
        );
      }
    }

    update_option('wc_progress_bar_discount_thresholds', $thresholds);
  }

?>
  <div class="wrap">
    <h1>WooCommerce Fortschrittsbalken Rabatt</h1>
    <form method="post" action="">
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Schwellenwerte</th>
          <td>
            <table id="wc-progress-bar-discount-thresholds-table">
              <tr>
                <th>Betrag (€)</th>
                <th>Rabatt (%)</th>
                <th>Kostenloser Versand</th>
              </tr>
              <?php for ($i = 0; $i < 3; $i++) : ?>
                <tr>
                  <td><input type="number" id="amount-<?php echo $i + 1; ?>" name="wc_progress_bar_discount_thresholds_amount[]" value="<?php echo isset($thresholds[$i]['amount']) ? esc_attr($thresholds[$i]['amount']) : ''; ?>"></td>
                  <td><input type="number" id="discount-<?php echo $i + 1; ?>" name="wc_progress_bar_discount_thresholds_discount[]" value="<?php echo isset($thresholds[$i]['discount']) ? esc_attr($thresholds[$i]['discount']) : ''; ?>"></td>
                  <td><input type="checkbox" name="wc_progress_bar_discount_thresholds_free_shipping[]" <?php checked(isset($thresholds[$i]['free_shipping']) && $thresholds[$i]['free_shipping'], true); ?>></td>
                </tr>

              <?php endfor; ?>
            </table>
          </td>
        </tr>
      </table>
      <?php submit_button('Änderungen speichern', 'primary', 'submit', false); ?>
    </form>
  </div>
<?php
}

add_action('admin_init', 'wc_progress_bar_discount_settings');

function wc_progress_bar_discount_settings()
{
  register_setting('wc_progress_bar_discount', 'wc_progress_bar_discount_thresholds', 'wc_progress_bar_discount_validate_thresholds');
}

function insert_progress_bar_below_subtotal()
{
  $cart_total = WC()->cart->get_cart_contents_total();
  $thresholds = get_option('wc_progress_bar_discount_thresholds', []);
  $progress = 0;

  for ($i = 0; $i < count($thresholds); $i++) {
    if ($cart_total < $thresholds[$i]['amount']) {
      $progress = $cart_total / $thresholds[$i]['amount'] * 100;
      break;
    }
  }

  if ($i == count($thresholds)) {
    $progress = 100;
  }

  /* echo '<div class="progress-bar">
        <div class="progress-bar-fill" style="width: ' . $progress . '%"></div>
        <div class="progress-bar-circle" data-content="\f48b"></div>
        <div class="progress-bar-circle" data-content="\f091"></div>
        <div class="progress-bar-circle" data-content="\f055"></div>
        </div>'*/
}

add_action('woocommerce_cart_totals_after_order_total', 'insert_progress_bar_below_subtotal');
