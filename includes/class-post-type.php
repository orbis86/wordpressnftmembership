<?php

/**
 * This class loads ACF
 *
 * @link       https://wpemailmanager.com
 * @since      1.0.0
 *
 * @package    NFT_Memberships
 * @subpackage NFT_Memberships/includes
 */

namespace NFT_Memberships;

use NFT_Memberships\Traits\Singleton;

class Post_Type {

	use Singleton;

	/**
	 * Load the class
	 */
	public function init() {
		//Register Custom Post Type
		$this->nft_memberships_post_type();

	}

	/**
	 * Register Custom Post Type
	 */
	public function nft_memberships_post_type() {

		$labels = array(
			'name'                  => _x( 'NFT Memberships', 'Post Type General Name', 'nft-memberships' ),
			'singular_name'         => _x( 'NFT Membership', 'Post Type Singular Name', 'nft-memberships' ),
			'menu_name'             => __( 'NFT Memberships', 'nft-memberships' ),
			'name_admin_bar'        => __( 'NFT Membership', 'nft-memberships' ),
			'archives'              => __( 'NFT Membership Archives', 'nft-memberships' ),
			'attributes'            => __( 'NFT Membership Attributes', 'nft-memberships' ),
			'parent_item_colon'     => __( 'Parent NFT Membership:', 'nft-memberships' ),
			'all_items'             => __( 'All Memberships', 'nft-memberships' ),
			'add_new_item'          => __( 'Add New NFT Membership', 'nft-memberships' ),
			'add_new'               => __( 'Add New', 'nft-memberships' ),
			'new_item'              => __( 'New NFT Membership', 'nft-memberships' ),
			'edit_item'             => __( 'Edit NFT Membership', 'nft-memberships' ),
			'update_item'           => __( 'Update NFT Membership', 'nft-memberships' ),
			'view_item'             => __( 'View NFT Membership', 'nft-memberships' ),
			'view_items'            => __( 'View NFT Memberships', 'nft-memberships' ),
			'search_items'          => __( 'Search NFT Membership', 'nft-memberships' ),
			'not_found'             => __( 'Not found', 'nft-memberships' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'nft-memberships' ),
			'featured_image'        => __( 'Featured Image', 'nft-memberships' ),
			'set_featured_image'    => __( 'Set featured image', 'nft-memberships' ),
			'remove_featured_image' => __( 'Remove featured image', 'nft-memberships' ),
			'use_featured_image'    => __( 'Use as featured image', 'nft-memberships' ),
			'insert_into_item'      => __( 'Insert into item', 'nft-memberships' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'nft-memberships' ),
			'items_list'            => __( 'Items list', 'nft-memberships' ),
			'items_list_navigation' => __( 'Items list navigation', 'nft-memberships' ),
			'filter_items_list'     => __( 'Filter items list', 'nft-memberships' ),
		);
		$args   = array(
			'label'               => __( 'NFT Membership', 'nft-memberships' ),
			'description'         => __( 'NFT Memberships', 'nft-memberships' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'custom-fields' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-groups',
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
		);
		register_post_type( 'nft-memberships', $args );

	}

}