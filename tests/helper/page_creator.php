<?php

trait Page_Creator {
	/**
	 * Creates a test page
	 *
	 * @param string $content
	 *
	 * @return int post_id
	 */
	public function create_page( $content ) {
		$page_guid = site_url() . "/test-page";
		$page_post = array(
			'post_title'     => 'test-page',
			'post_type'      => 'page',
			'post_name'      => 'test-page',
			'post_content'   => $content,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => 1,
			'menu_order'     => 0,
			'guid'           => $page_guid
		);

		return wp_insert_post( $page_post, false );
	}
}
