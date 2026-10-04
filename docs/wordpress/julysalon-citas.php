<?php
/**
 * Plugin Name: JulySalon - Enviar citas al sistema
 * Description: Cuando un cliente envía el formulario de citas (Contact Form 7), la cita se registra en el sistema JulySalon.
 * Version: 1.0
 *
 * INSTALACIÓN
 * 1. Cambia los 3 valores de CONFIGURACIÓN de abajo.
 * 2. En WordPress: Plugins → Añadir nuevo → Subir plugin → elige este archivo
 *    comprimido en .zip (o súbelo por FTP a wp-content/plugins/) → Activar.
 * 3. El formulario de Contact Form 7 debe tener campos con estos nombres:
 *    nombre, telefono, email (opcional), servicio, fecha, hora, notas (opcional).
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ================= CONFIGURACIÓN ================= */

// Dirección del sistema JulySalon en internet + /api/v1/citas/recibir
define('JULYSALON_API_URL', 'https://apptest.julysalon.online/api/v1/citas/recibir');


// La misma clave que WORDPRESS_API_TOKEN en el archivo .env del sistema
define('JULYSALON_API_TOKEN', 'PEGA-AQUI-LA-CLAVE');

// ID del formulario de citas en Contact Form 7 (aparece en el shortcode,
// ej. [contact-form-7 id="123"]). Déjalo en 0 para enviar TODOS los formularios.
define('JULYSALON_FORM_ID', 0);

/* ================================================= */

add_action('wpcf7_mail_sent', function ($contact_form) {
    if (JULYSALON_FORM_ID && (int) $contact_form->id() !== (int) JULYSALON_FORM_ID) {
        return;
    }

    $submission = WPCF7_Submission::get_instance();
    if (!$submission) {
        return;
    }

    $datos = $submission->get_posted_data();

    // Los campos "select" de Contact Form 7 llegan como lista; se toma el valor.
    $valor = function ($campo) use ($datos) {
        $v = $datos[$campo] ?? '';
        return is_array($v) ? trim((string) reset($v)) : trim((string) $v);
    };

    $respuesta = wp_remote_post(JULYSALON_API_URL, [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-Api-Key'    => JULYSALON_API_TOKEN,
        ],
        'body' => wp_json_encode([
            'nombre'   => $valor('nombre'),
            'telefono' => $valor('telefono'),
            'email'    => $valor('email'),
            'servicio' => $valor('servicio'),
            'fecha'    => $valor('fecha'),
            'hora'     => $valor('hora'),
            'notas'    => $valor('notas'),
        ]),
    ]);

    // Si el sistema no respondió bien, queda anotado en el registro de errores
    // de WordPress (wp-content/debug.log si WP_DEBUG_LOG está activo).
    if (is_wp_error($respuesta)) {
        error_log('JulySalon citas: no se pudo conectar - ' . $respuesta->get_error_message());
    } elseif (wp_remote_retrieve_response_code($respuesta) !== 201) {
        error_log('JulySalon citas: respuesta ' . wp_remote_retrieve_response_code($respuesta) . ' - ' . wp_remote_retrieve_body($respuesta));
    }
});
