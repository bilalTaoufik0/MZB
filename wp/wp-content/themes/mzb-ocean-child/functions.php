<?php
/**
 * Fonctions du thème enfant MZB IA Academy (OceanWP Child)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'mzb_child_enqueue_styles', 20 );
function mzb_child_enqueue_styles() {

    // Style parent OceanWP
    wp_enqueue_style(
        'oceanwp-style',
        get_template_directory_uri() . '/style.css'
    );

    // Google Fonts : Montserrat
    wp_enqueue_style(
        'mzb-google-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap',
        array(),
        null
    );

    // Style du thème enfant
    wp_enqueue_style(
        'mzb-ocean-child-style',
        get_stylesheet_uri(),
        array( 'oceanwp-style', 'mzb-google-fonts' ),
        wp_get_theme()->get( 'Version' )
    );
}
