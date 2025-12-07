<?php
/**
 * Plugin Name: MZB Core
 * Description: Fonctionnalités custom du site de formations IA (CPT, shortcodes, hooks WooCommerce).
 * Author: MZB IA Academy Team
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Empêche l'accès direct
}


/**
 * Custom Post Type : Témoignages
 */
add_action( 'init', 'mzb_register_testimonial_cpt' );
function mzb_register_testimonial_cpt() {
    $labels = array(
        'name'          => 'Témoignages',
        'singular_name' => 'Témoignage',
        'add_new_item'  => 'Ajouter un témoignage',
        'edit_item'     => 'Modifier le témoignage',
        'menu_name'     => 'Témoignages',
    );

    $args = array(
        'label'         => 'Témoignages',
        'labels'        => $labels,
        'public'        => true,
        'show_in_menu'  => true,
        'menu_position' => 20,
        'menu_icon'     => 'dashicons-testimonial',
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
    );

    register_post_type( 'mzb_testimonial', $args );
}


/**
 * Taxonomie : Niveau de formation (pour les produits)
 */
add_action( 'init', 'mzb_register_level_taxonomy' );
function mzb_register_level_taxonomy() {
    $labels = array(
        'name'          => 'Niveaux de formation',
        'singular_name' => 'Niveau de formation',
    );

    $args = array(
        'hierarchical'      => true, // comme une catégorie
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'niveau-formation' ),
    );

    // On l'attache au post type 'product' (WooCommerce)
    register_taxonomy( 'mzb_level', 'product', $args );
}

/**
 * Shortcode : [mzb_cartes_populaires]
 * Affiche 3 cartes de formations populaires (maquette Figma)
 */
add_shortcode( 'mzb_cartes_populaires', 'mzb_cartes_populaires_fn' );
function mzb_cartes_populaires_fn( $atts ) {

    $atts = shortcode_atts(
        array(
            'limit' => 3,
        ),
        $atts
    );

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => intval( $atts['limit'] ),
        'meta_key'       => 'total_sales',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );

    ob_start();

    if ( $query->have_posts() ) {

        echo '<div class="mzb-popular-trainings">';

        while ( $query->have_posts() ) {
            $query->the_post();
            global $product;

            $product_id = get_the_ID();

            // Niveau (taxonomie mzb_level)
            $levels = wc_get_product_terms( $product_id, 'mzb_level', array(
                'fields' => 'names',
            ) );
            $level_name = ! empty( $levels ) ? $levels[0] : '';

            // Catégorie produit
            $cats = wc_get_product_terms( $product_id, 'product_cat', array(
                'fields' => 'names',
            ) );
            $cat_name = ! empty( $cats ) ? $cats[0] : '';

            echo '<div class="mzb-training-card">';
            echo '<a href="' . esc_url( get_permalink() ) . '">';

            // Bandeau jaune titre
            echo '<div class="mzb-card-header">';
            echo '<span class="mzb-card-title">' . esc_html( get_the_title() ) . '</span>';
            echo '</div>';

            // Zone image centrale
            echo '<div class="mzb-card-image">';
            if ( has_post_thumbnail() ) {
                echo get_the_post_thumbnail( $product_id, 'medium_large' );
            } else {
                echo '<div class="mzb-card-image-placeholder"></div>';
            }
            echo '</div>';

            // Bas de carte : niveau | catégorie
            echo '<div class="mzb-card-footer">';
            echo '<div class="mzb-card-meta">';
            if ( $level_name ) {
                echo '<span class="mzb-card-level">' . esc_html( $level_name ) . '</span>';
            }
            if ( $level_name && $cat_name ) {
                echo '<span class="mzb-card-sep">|</span>';
            }
            if ( $cat_name ) {
                echo '<span class="mzb-card-cat">' . esc_html( $cat_name ) . '</span>';
            }
            echo '</div>';
            echo '</div>'; // footer

            echo '</a>';
            echo '</div>'; // card
        }

        echo '</div>'; // .mzb-popular-trainings

        wp_reset_postdata();
    }

    return ob_get_clean();
}



/**
 * Message sur la fiche produit : préciser que c'est un produit numérique
 */
add_action( 'woocommerce_after_add_to_cart_button', 'mzb_product_digital_notice' );
function mzb_product_digital_notice() {
    echo '<p class="mzb-digital-notice">📌 Produit numérique : accès aux fichiers à télécharger après le paiement. Aucun envoi physique.</p>';
}


/**
 * Shortcode : [mzb_temoignages]
 * Affiche les témoignages (CPT mzb_testimonial)
 */
add_shortcode( 'mzb_temoignages', 'mzb_testimonials_shortcode' );
function mzb_testimonials_shortcode( $atts ) {

    $atts = shortcode_atts(
        array(
            'limit' => 3,
        ),
        $atts
    );

    $args = array(
        'post_type'      => 'mzb_testimonial',
        'posts_per_page' => intval( $atts['limit'] ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );

    ob_start();

    if ( $query->have_posts() ) {
        echo '<div class="mzb-testimonials">';

        while ( $query->have_posts() ) {
            $query->the_post();

            echo '<div class="mzb-testimonial-card">';
            echo '<div class="mzb-testimonial-content">' . wp_kses_post( wpautop( get_the_content() ) ) . '</div>';
            echo '<div class="mzb-testimonial-author">— ' . esc_html( get_the_title() ) . '</div>';
            echo '</div>';
        }

        echo '</div>';

        wp_reset_postdata();
    }

    return ob_get_clean();
}
