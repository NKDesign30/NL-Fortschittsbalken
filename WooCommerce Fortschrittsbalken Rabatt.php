<?php
/*
Plugin Name: WooCommerce Fortschrittsbalken Rabatt
Description: Ein Plugin, das einen Rabatt basierend auf dem Warenkorbwert hinzufügt
Version: 1.5.3


Author: Niko
*/
// Zeige Fehlermeldungen an, um mögliche Fehler zu identifizieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Prüfen, ob WooCommerce aktiv ist
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  function calculate_discount($cart)
  {
    // Den Warenkorbwert holen
    $total = $cart->cart_contents_total;

    // Die Schwellenwerte und entsprechenden Rabatte definieren
    $thresholds = get_option('wc_progress_bar_discount_thresholds', array(
      array('amount' => 40, 'discount' => 0, 'free_shipping' => false),
      array('amount' => 70, 'discount' => 10, 'free_shipping' => true),
      array('amount' => 100, 'discount' => 15, 'free_shipping' => true)
    ));

    // Die Schwellenwerte sortieren
    usort($thresholds, function ($a, $b) {
      return $a['amount'] <=> $b['amount'];
    });

    // Rabatt und kostenlosen Versand initialisieren
    $discount = 0;
    $free_shipping = false;

    // Den Rabatt und kostenlosen Versand berechnen
    foreach ($thresholds as $threshold) {
      if ($total >= $threshold['amount']) {
        $discount = $threshold['discount'];
        $free_shipping = $threshold['free_shipping'];
      } else {
        break;
      }
    }

    // Den Rabatt anwenden
    $cart->add_fee(__('Rabatt', 'woocommerce'), -$discount / 100 * $total);

    // Kostenloser Versand anwenden
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
  // Die gespeicherten Schwellenwerte abrufen
  $thresholds = get_option('wc_progress_bar_discount_thresholds', array());

  // Wenn ein Formular gesendet wurde, die eingegebenen Werte speichern
  if (isset($_POST['submit'])) {
    $thresholds = array();

    // Die eingegebenen Werte überprüfen und speichern
    if (isset($_POST['wc_progress_bar_discount_thresholds_amount']) && is_array($_POST['wc_progress_bar_discount_thresholds_amount'])) {
      $amounts = $_POST['wc_progress_bar_discount_thresholds_amount'];

      // Die eingegebenen Beträge und Rabatte überprüfen und speichern
      for ($i = 0; $i < count($amounts); $i++) {
        $amount = isset($amounts[$i]) ? floatval($amounts[$i]) : 0;
        $discount = isset($_POST['wc_progress_bar_discount_thresholds_discount'][$i]) ? floatval($_POST['wc_progress_bar_discount_thresholds_discount'][$i]) : 0;
        $free_shipping = isset($_POST['wc_progress_bar_discount_thresholds_free_shipping'][$i]) ? true : false;

        // Den Schwellenwert speichern
        $thresholds[] = array(
          'amount' => $amount,
          'discount' => $discount,
          'free_shipping' => $free_shipping
        );
      }
    }

    // Die Schwellenwerte speichern
    update_option('wc_progress_bar_discount_thresholds', $thresholds);
  }

?>
  <div class="wrap">
    <h1>WooCommerce Fortschrittsbalken Rabatt</h1>
    <form method="post" action="">
      <?php
      settings_fields('wc_progress_bar_discount');
      do_settings_sections('wc-progress-bar-discount');
      ?>
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
              <?php foreach ($thresholds as $i => $threshold) : ?>
                <tr>
                  <td><input type="number" name="wc_progress_bar_discount_thresholds_amount[]" value="<?php echo esc_attr($threshold['amount']); ?>"></td>
                  <td><input type="number" name="wc_progress_bar_discount_thresholds_discount[]" value="<?php echo esc_attr($threshold['discount']); ?>"></td>
                  <td><input type="checkbox" name="wc_progress_bar_discount_thresholds_free_shipping[]" <?php checked($threshold['free_shipping'], true); ?>></td>
                </tr>
              <?php endforeach; ?>
            </table>
            <button type="button" class="button wc-progress-bar-discount-add-threshold">Schwellenwert hinzufügen</button>
          </td>
        </tr>
      </table>
      <?php
      submit_button('Änderungen speichern', 'primary', 'submit', false);
      ?>
    </form>
  </div>
<?php
}

add_action('admin_init', 'wc_progress_bar_discount_settings');

function wc_progress_bar_discount_settings()
{
  register_setting('wc_progress_bar_discount', 'wc_progress_bar_discount_thresholds', 'wc_progress_bar_discount_validate_thresholds');
}
// Hier fügen Sie den Fortschrittsbalken-Code ein
function insert_progress_bar_below_subtotal()
{
  // Get the current cart total
  $cart_total = WC()->cart->get_cart_contents_total();

  // Get the thresholds
  $thresholds = get_option('wc_progress_bar_discount_thresholds_amount', []);

  // Calculate the progress
  $progress = 0;
  for ($i = 0; $i < count($thresholds); $i++) {
    if ($cart_total < $thresholds[$i]) {
      $progress = $cart_total / $thresholds[$i] * 100;
      break;
    }
  }
  if ($i == count($thresholds)) {
    $progress = 100;
  }

  // Display the progress bar
  echo '<div class="progress-bar">
        <div class="progress-bar-fill" style="width: ' . $progress . '%"></div>
    </div>';
}
add_action('woocommerce_widget_shopping_cart_before_buttons', 'insert_progress_bar_below_subtotal');


function wc_progress_bar_discount_validate_thresholds($input)
{
  // Die Eingabe ist ein Array von Schwellenwerten
  $output = array();

  // Jeden Schwellenwert validieren und bereinigen
  foreach ($input as $i => $threshold) {
    // Den Betrag validieren und bereinigen
    $output[$i]['amount'] = isset($threshold['amount']) ? floatval($threshold['amount']) : 0;

    // Den Rabatt validieren und bereinigen
    $output[$i]['discount'] = isset($threshold['discount']) ? floatval($threshold['discount']) : 0;

    // Den kostenlosen Versand validieren und bereinigen
    $output[$i]['free_shipping'] = isset($threshold['free_shipping']) ? boolval($threshold['free_shipping']) : false;
  }

  return $output;
}

// Skript für das Admin-Bereich laden
function wc_progress_bar_discount_admin_scripts($hook)
{
  if ($hook !== 'settings_page_wc-progress-bar-discount') {
    return;
  }
  wp_enqueue_script('wc_progress_bar_discount_admin', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery'), '1.0.0', true);
  wp_enqueue_script('wc_progress_bar_discount_progress_bar', plugin_dir_url(__FILE__) . 'assets/js/progress-bar.js', array('jquery'), '1.0.0', true);
}

add_action('admin_enqueue_scripts', 'wc_progress_bar_discount_admin_scripts');

?>