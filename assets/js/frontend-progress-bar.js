// Funktion zum Hinzufügen von benutzerdefinierten CSS-Dateien im Frontend
function add_custom_styles_frontend()
{
    // Pfad zu Ihrer CSS-Datei im Plugin
    $css_url_frontend = plugins_url('assets/css/frontend-style.css', __FILE__);
    wp_enqueue_style('custom_styles_frontend', $css_url_frontend);
}
add_action('wp_enqueue_scripts', 'add_custom_styles_frontend');

// Funktion zum Hinzufügen von benutzerdefinierten JS-Dateien im Frontend
function add_custom_scripts_frontend()
{
    // Pfad zu Ihrer JS-Datei im Plugin
    $js_url_frontend = plugins_url('assets/js/frontend-progress-bar.js', __FILE__);
    wp_enqueue_script('custom_scripts_frontend', $js_url_frontend, array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'add_custom_scripts_frontend');


jQuery(document).ready(function($) {
    // Event-Listener für Änderungen im Mengeneingabefeld
    $('.qty').on('change', function() {
        // Verzögerung hinzufügen, um sicherzustellen, dass die Menge aktualisiert wurde
        setTimeout(function() {
            updateProgressBar();
        }, 500); // 0,5 Sekunden Verzögerung
    });

    function updateProgressBar() {
        // AJAX-Anfrage, um den aktuellen Warenkorb-Gesamtbetrag und die Schwellenwerte zu erhalten
        $.post(ajaxurl, {
            action: 'get_cart_total_and_thresholds'
        }, function(response) {
            if(response.success) {
                // Aktualisieren Sie den Fortschrittsbalken basierend auf dem Antwortwert
                var progress = calculateProgress(response.data.cart_total, response.data.thresholds);
                $('.progress-bar-fill').css('width', progress + '%');
            }
        });
    }

    function calculateProgress(cartTotal, thresholds) {
        var progress = 0;
        for (var i = 0; i < thresholds.length; i++) {
            if (cartTotal < thresholds[i]) {
                progress = cartTotal / thresholds[i] * 100;
                break;
            }
        }
        if (i == thresholds.length) {
            progress = 100;
        }
        return progress;
    }
});

