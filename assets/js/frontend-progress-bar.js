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
