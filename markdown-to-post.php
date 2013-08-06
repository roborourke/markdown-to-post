<?php
/**
Plugin Name: Markdown to Post
Plugin URI:
Description: Takes markdown file URL and converts it into a post using the GitHub API
Author: Robert O'Rourke @ interconnect/it
Version: 0.1
Author URI: http://interconnectit.com
License: http://www.gnu.org/licenses/gpl-3.0.txt
*/

class markdown_to_post {

	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_rewrite_endpoint( 'markdown', EP_ROOT );

		add_filter( 'pre_get_posts', array( $this, 'query' ) );

	}

	public function query( $query ) {

		if ( $query->is_main_query() && $query->get( 'markdown' ) ) {

			$html = $this->fetch( $query->get( 'markdown' ) );

			if ( $html ) {

				echo $html;
				die;

			}

		}

		return $query;
	}

	public function fetch( $url ) {

		$args = array(
			'text' => '',
			'mode' => 'markdown'
		);

		if ( preg_match( '/([a-z0-9_-]+\/[a-z0-9_-]+)/', $url, $github ) ) {

			$url = "https://raw.github.com/$github[1]/master/README.md";
			$args[ 'mode' ] = 'gfm';
			$args[ 'context' ] = $github[ 1 ];

		} else {

			$url = urldecode( $url );

		}

		$markdown = wp_remote_get( $url, array(
			'sslverify' => false
		) );

		if ( ! is_wp_error( $markdown ) ) {

			$args[ 'text' ] = $markdown[ 'body' ];

			$html = wp_remote_post( 'https://api.github.com/markdown', array(
				'body' => json_encode( $args ),
				'sslverify' => false
			) );

			if ( ! is_wp_error( $html ) )
				return $html[ 'body' ];

		}

		return false;
	}

}

$markdown_to_post = new markdown_to_post();
