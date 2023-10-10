<?php
// Sicherstellen, dass der direkte Zugriff auf die Datei verhindert wird
if (!defined('ABSPATH')) {
  exit;
}

class Elementor_Progress_Bar_Widget extends \Elementor\Widget_Base
{

  // Widget-ID, eindeutiger Bezeichner für das Widget
  public function get_name()
  {
    return 'progress_bar_widget';
  }

  // Widget-Titel, der im Elementor-Editor angezeigt wird
  public function get_title()
  {
    return __('Progress Bar', 'woocommerce-fortschrittsbalken-rabatt');
  }

  // Widget-Symbol, das im Elementor-Editor angezeigt wird
  public function get_icon()
  {
    return 'eicon-skill-bar';
  }

  // Widget-Kategorien im Elementor-Editor
  public function get_categories()
  {
    return ['general'];
  }

  // Inhalt des Widgets auf der Webseite anzeigen
  protected function render()
  {
    // Den Fortschrittsbalken anzeigen
    $this->display_progress_bar();
  }

  // Funktion zur Anzeige des Fortschrittsbalkens
  private function display_progress_bar()
  {
    // Holen Sie die Werte für den Fortschrittsbalken aus den Plugin-Einstellungen
    $thresholds = get_option('wc_progress_bar_discount_thresholds', array());

    // Holen Sie den aktuellen Warenkorbwert
    $cart_total = WC()->cart->get_subtotal();

    // Berechnen Sie den Fortschritt
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

    // Den Fortschrittsbalken anzeigen
    echo '<div class="progress-bar">
                <div class="progress-bar-fill" style="width: ' . $progress . '%"></div>
              </div>';
  }
}

// Das Elementor-Widget registrieren
function register_elementor_progress_bar_widget()
{
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Elementor_Progress_Bar_Widget());
}
add_action('elementor/widgets/widgets_registered', 'register_elementor_progress_bar_widget');
