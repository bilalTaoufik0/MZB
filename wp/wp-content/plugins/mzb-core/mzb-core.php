<?php
/**
 * Plugin Name: MZB Core
 * Description: Fonctionnalités custom du site de formations IA (CPT, shortcodes, hooks WooCommerce).
 * Author: MZB IA Academy Team
 * Version: 1.0.0
 *
 * @package MZB_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Sécurité : blocage de l'accès direct.
}

/*
========================================================================
 * 1. CUSTOM POST TYPE : TÉMOIGNAGES
 * ===================================================================== */

/**
 * Enregistre le Custom Post Type "Témoignages".
 *
 * @return void
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

/*
========================================================================
 * 2. TAXONOMIE : NIVEAU DE FORMATION (POUR LES PRODUITS)
 * ===================================================================== */

/**
 * Taxonomie hiérarchique "mzb_level" rattachée aux produits WooCommerce.
 * Ex : Intermédiaire, Avancé, Expert.
 *
 * @return void
 */
add_action( 'init', 'mzb_register_level_taxonomy' );
function mzb_register_level_taxonomy() {

	$labels = array(
		'name'          => 'Niveaux de formation',
		'singular_name' => 'Niveau de formation',
	);

	$args = array(
		'hierarchical'      => true, // Comportement type "catégorie".
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'niveau-formation' ),
	);

	// Attaché au post type 'product' (WooCommerce).
	register_taxonomy( 'mzb_level', 'product', $args );
}

/*
========================================================================
 * 3. SHORTCODES FORMATIONS POPULAIRES
 * ===================================================================== */

/**
 * Shortcodes :
 *   [mzb_formations_populaires]
 *
 * -> Affiche X formations WooCommerce les plus vendues
 *    sous forme de cartes, pour la section "FORMATIONS POPULAIRES".
 *
 * @param array $atts Attributs du shortcode.
 *
 * @return string HTML rendu.
 */
add_shortcode( 'mzb_formations_populaires', 'mzb_popular_formations_shortcode' );
function mzb_popular_formations_shortcode( $atts ) {

	// Attributs avec valeurs par défaut.
	$atts = shortcode_atts(
		array(
			'limit' => 3,
		),
		$atts
	);

	// Requête : produits triés par total des ventes.
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

		// Wrapper utilisé par ton CSS : .mzb-popular-grid.
		echo '<div class="mzb-popular-grid">';

		while ( $query->have_posts() ) {
			$query->the_post();
			global $product;

			$product_id = get_the_ID();

			// Niveau (taxonomie mzb_level).
			$levels     = wc_get_product_terms(
				$product_id,
				'mzb_level',
				array( 'fields' => 'names' )
			);
			$level_name = ! empty( $levels ) ? $levels[0] : '';

			// Catégorie WooCommerce.
			$cats     = wc_get_product_terms(
				$product_id,
				'product_cat',
				array( 'fields' => 'names' )
			);
			$cat_name = ! empty( $cats ) ? $cats[0] : '';

			echo '<article class="mzb-training-card">';
				echo '<a href="' . esc_url( get_permalink() ) . '">';

					// Bandeau jaune : titre.
					echo '<div class="mzb-card-header">';
						echo '<span class="mzb-card-title">' . esc_html( get_the_title() ) . '</span>';
					echo '</div>';

					// Image du produit.
					echo '<div class="mzb-card-image">';
			if ( has_post_thumbnail() ) {
				echo get_the_post_thumbnail( $product_id, 'medium_large' );
			} else {
				echo '<div class="mzb-card-image-placeholder"></div>';
			}
					echo '</div>';

					// Bas de carte : niveau | catégorie.
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

						echo '</div>'; // .mzb-card-meta
					echo '</div>'; // .mzb-card-footer

				echo '</a>';
			echo '</article>'; // .mzb-training-card
		}

		echo '</div>'; // .mzb-popular-grid

		wp_reset_postdata();
	}

	return ob_get_clean();
}

/*
========================================================================
 * 4. MESSAGE PRODUIT NUMÉRIQUE SUR LES FICHES PRODUIT
 * ===================================================================== */

/**
 * Ajoute un petit message sous le bouton "Ajouter au panier"
 * pour préciser que la formation est un produit numérique.
 *
 * @return void
 */
add_action( 'woocommerce_after_add_to_cart_button', 'mzb_product_digital_notice' );
function mzb_product_digital_notice() {
	echo '<p class="mzb-digital-notice">ATTENTION : accès aux fichiers à télécharger après le paiement pendant 24h. Aucun envoi physique.</p>';
}

/*
========================================================================
 * 5. SHORTCODE TÉMOIGNAGES + AFFICHAGE NOTE
 * ===================================================================== */

/**
 * Shortcode : [mzb_temoignages limit="3"]
 *
 * Affiche les témoignages du CPT mzb_testimonial
 * + une note en étoiles (métadonnée mzb_rating).
 *
 * @param array $atts Attributs du shortcode.
 *
 * @return string HTML rendu.
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

			// Note (1 à 5) stockée en métadonnée.
			$rating = (int) get_post_meta( get_the_ID(), 'mzb_rating', true );

			echo '<div class="mzb-testimonial-card">';

				echo '<div class="mzb-testimonial-main">';

					// Étoiles si une note est définie.
			if ( $rating > 0 ) {
				echo '<div class="mzb-testimonial-rating">';
				for ( $i = 1; $i <= 5; $i++ ) {
					$class = $i <= $rating ? 'is-filled' : '';
					echo '<span class="mzb-star ' . esc_attr( $class ) . '">★</span>';
				}
				echo '</div>';
			}

					echo '<div class="mzb-testimonial-content">' . wp_kses_post( wpautop( get_the_content() ) ) . '</div>';
					echo '<div class="mzb-testimonial-author">— ' . esc_html( get_the_title() ) . '</div>';

				echo '</div>'; // .mzb-testimonial-main

				// Gros "M" à droite de la carte.
				echo '<div class="mzb-testimonial-mark">M</div>';

			echo '</div>'; // .mzb-testimonial-card
		}

		echo '</div>'; // .mzb-testimonials

		wp_reset_postdata();
	}

	return ob_get_clean();
}

/*
========================================================================
 * 6. METABOX : NOTE (1 À 5) POUR LES TÉMOIGNAGES
 * ===================================================================== */

/**
 * Ajout de la metabox "Note du témoignage (1 à 5)" dans l'admin.
 *
 * @return void
 */
add_action( 'add_meta_boxes', 'mzb_add_testimonial_rating_metabox' );
function mzb_add_testimonial_rating_metabox() {

	add_meta_box(
		'mzb_testimonial_rating',
		'Note du témoignage (1 à 5)',
		'mzb_testimonial_rating_metabox_callback',
		'mzb_testimonial',
		'side',
		'default'
	);
}

/**
 * Callback d’affichage de la metabox rating.
 *
 * @param WP_Post $post Post en cours d’édition.
 *
 * @return void
 */
function mzb_testimonial_rating_metabox_callback( $post ) {

	$value = get_post_meta( $post->ID, 'mzb_rating', true );

	// Nonce pour sécuriser l'enregistrement de la note.
	wp_nonce_field( 'mzb_save_testimonial_rating', 'mzb_rating_nonce' );
	?>
	<label for="mzb_rating">Note :</label>
	<select name="mzb_rating" id="mzb_rating" class="widefat">
		<option value="">Aucune</option>
		<?php
		for ( $i = 1; $i <= 5; $i++ ) {
			printf(
				'<option value="%1$s"%2$s>%1$s étoile(s)</option>',
				esc_attr( $i ),
				selected( (int) $value, $i, false )
			);
		}
		?>
	</select>
	<?php
}

/**
 * Sauvegarde de la note lors de l’enregistrement du témoignage.
 *
 * @param int $post_id ID du post en cours de sauvegarde.
 *
 * @return void
 */
add_action( 'save_post_mzb_testimonial', 'mzb_save_testimonial_rating' );
function mzb_save_testimonial_rating( $post_id ) {

	// Vérifie la présence du nonce.
	if ( ! isset( $_POST['mzb_rating_nonce'] ) ) {
		return;
	}

	// Vérifie la validité du nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mzb_rating_nonce'] ) ), 'mzb_save_testimonial_rating' ) ) {
		return;
	}

	// Évite les autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Vérifie les capacités.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Si le champ n'est pas envoyé, on ne touche pas à la meta.
	if ( ! isset( $_POST['mzb_rating'] ) ) {
		return;
	}

	$rating = intval( $_POST['mzb_rating'] );

	// Si la valeur est hors plage, on supprime la méta.
	if ( $rating < 1 || $rating > 5 ) {
		delete_post_meta( $post_id, 'mzb_rating' );
	} else {
		update_post_meta( $post_id, 'mzb_rating', $rating );
	}
}
