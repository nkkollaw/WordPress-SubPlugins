<?php # -*- coding: utf-8 -*-
defined( 'ABSPATH' ) or die();

if ( ! function_exists( 'add_subplugin_support' ) ) {
	/**
	 * @param       $plugin_folder
	 * @param       $prefix
	 * @param array $args
	 *
	 * @return Biont_SubPlugins
	 */
	function add_subplugin_support( $plugin_folder, $prefix, $args = array() ) {

		foreach (
			array(
				'Biont_SubPlugins_PluginListTable',
				'Biont_SubPlugins_DefaultUI',
				'Biont_SubPlugins',
			) as $file
		) {
			require_once dirname( __FILE__ ) . '/inc/' . $file . '.php';
		}

		$plugins = new Biont_SubPlugins( $plugin_folder, $prefix, $args );
		if ( did_action( 'plugins_loaded' ) ) {
			$plugins->register();
		} else {
			add_action( 'plugins_loaded', array( $plugins, 'register' ) );
		}

		return $plugins;
	}
}

if ( ! function_exists( 'biont_get_subplugin_handler' ) ) {

	/**
	 * @param $prefix
	 *
	 * @return Biont_SubPlugins
	 */
	function biont_get_subplugin_handler( $prefix ) {

		return Biont_SubPlugins::get_instance( $prefix );

	}
}

if ( ! function_exists( 'biont_get_plugin_data' ) ) {

	/**
	 * Pretty much the same as WP-core get_plugin_data(),
	 * but with a few adaptions that sadly weren't possible with the original function
	 *
	 * @param      $prefix
	 * @param      $plugin_file
	 * @param bool $markup
	 * @param bool $translate
	 *
	 * @return array|bool|mixed
	 */
	function biont_get_plugin_data( $prefix, $plugin_file, $markup = TRUE, $translate = TRUE ) {

		if ( ! function_exists( '_get_plugin_data_markup_translate' ) ) {
			include( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = wp_cache_get( $prefix . $plugin_file, $prefix . '_subplugins' );
		if ( $plugin_data === FALSE ) {
			$default_headers = array(
				'Name'        => strtoupper( $prefix ) . '-Plugin Name',
				'PluginURI'   => 'Plugin URI',
				'Version'     => 'Version',
				'Description' => 'Description',
				'Author'      => 'Author',
				'AuthorURI'   => 'Author URI',
				'TextDomain'  => 'Text Domain',
				'DomainPath'  => 'Domain Path',
				'Network'     => 'Network',
				// Site Wide Only is deprecated in favor of Network.
				'_sitewide'   => 'Site Wide Only',
			);

			$default_headers = apply_filters( $prefix . '_plugin_data_headers', $default_headers );

			$plugin_data = get_file_data( $plugin_file, $default_headers, 'plugin' );

			$plugin_data[ 'Network' ] = ( 'true' == strtolower( $plugin_data[ 'Network' ] ) );

			if ( $markup || $translate ) {
				$plugin_data = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup, $translate );
			} else {
				$plugin_data[ 'Title' ]      = $plugin_data[ 'Name' ];
				$plugin_data[ 'AuthorName' ] = $plugin_data[ 'Author' ];
			}

			wp_cache_set( $prefix . $plugin_file, $plugin_data, $prefix . '_subplugins' );
		}

		return $plugin_data;

	}
}
