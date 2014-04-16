<?php
// ---------------------------------------------------------------------------------------------------------
// AJAX Calls
// ---------------------------------------------------------------------------------------------------------

add_action('wp_ajax_nopriv_get_agency', 'ajax_get_agency');
add_action('wp_ajax_get_agency', 'ajax_get_agency');
add_action('wp_ajax_nopriv_get_client', 'ajax_get_client');
add_action('wp_ajax_get_client', 'ajax_get_client');
add_action('wp_ajax_nopriv_get_project', 'ajax_get_project');
add_action('wp_ajax_get_project', 'ajax_get_project');
add_action('wp_ajax_nopriv_get_projects', 'ajax_get_projects');
add_action('wp_ajax_get_projects', 'ajax_get_projects');




/** 
 * AJAX Request Validation 
 */
function validate_ajax_request() {
	$nonce = $_REQUEST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'fiveleft-ajax-request' ) ) {
		return false;
	}
	return true;
}

/**
 * AJAX Request: Get Agency
 */
function ajax_get_agency() {
	$response = validate_ajax_request() ? get_agency( $_REQUEST["id"]) : array('error' => 'error', 'action' => 'get_agency', 'message' => 'invalid request');
	header( "Content-Type: application/json" );
  	echo json_encode( $response );
	die();
}

/**
 * AJAX Request: Get Client
 */
function ajax_get_client() {
	$response = validate_ajax_request() ? get_client( $_REQUEST["id"] ) : array('error' => 'error', 'action' => 'get_client', 'message' => 'invalid request');
	header( "Content-Type: application/json" );
  	echo json_encode( $response );
	die();
}

/**
 * AJAX Request: Get Project By ID
 */
function ajax_get_project() {
	$response = validate_ajax_request() ? get_project( $_REQUEST["id"] ) : array('error' => 'error', 'action' => 'get_project', 'message' => 'invalid request');
	header( "Content-Type: application/json" );
  	echo json_encode( $response );
	die();
}

/**
 * AJAX Request: Get Project List
 */
function ajax_get_projects() {
	$response = validate_ajax_request() ? get_projects() : array('error' => 'error', 'action' => 'get_projects', 'message' => 'invalid request');
	header( "Content-Type: application/json" );
  	echo json_encode( $response );
	die();
}





function get_agencies( $fields ) {
	$fields = isset($fields) ? $fields : "*";
	$query = "
		SELECT " . $fields . " FROM wp_posts 
		LEFT JOIN wp_postmeta 
			ON (wp_posts.ID = wp_postmeta.post_id) 
			WHERE wp_posts.post_type = 'fiveleft_agency' 
			AND wp_postmeta.meta_key = '_meta'
		";
	global $wpdb;
	return $wpdb->get_results($query);
}

function get_agency( $id ) {
	$query = "
		SELECT * FROM wp_posts
		LEFT JOIN wp_postmeta
			ON (wp_posts.ID = wp_postmeta.post_id)
			WHERE wp_posts.post_type = 'fiveleft_agency'
			AND wp_postmeta.meta_key = '_meta'";
	$query .= isset( $id ) ? "AND wp_posts.ID = '$id'" : "";
	global $wpdb;
	return $wpdb->get_results($query);
}

function get_clients( $fields ) {
	$fields = isset($fields) ? $fields : "*";
	$query = "
		SELECT " . $fields . " FROM wp_posts 
		LEFT JOIN wp_postmeta 
			ON (wp_posts.ID = wp_postmeta.post_id) 
			WHERE wp_posts.post_type = 'fiveleft_client' 
			AND wp_postmeta.meta_key = '_meta'
		";
	global $wpdb;
	return $wpdb->get_results($query);
}

function get_client( $id ) {
	$query = "
		SELECT * FROM wp_posts
		LEFT JOIN wp_postmeta
			ON (wp_posts.ID = wp_postmeta.post_id)
			WHERE wp_posts.post_type = 'fiveleft_client'
			AND wp_postmeta.meta_key = '_meta'";
	$query .= isset( $id ) ? "AND wp_posts.ID = '$id'" : "";
	global $wpdb;
	return $wpdb->get_results($query);
}

function get_project( $id ) {
	
	return array( "project" => $id );
}

function get_projects() {
	
	return array( "projects" => "all" );
}

function get_disciplines( $id ) {
	
	return array( "disciplines" => $id );
}

function get_technologies( $id ) {
	
	return array( "technologies" => $id );
}





// ---------------------------------------------------------------------------------------------------------
// Utility Methods
// ---------------------------------------------------------------------------------------------------------

/**
 * Create Project Info
 * @param $post {WP_Post} 
 */
function create_project_info( $post ) {

	$post_info = array(
		"id" => $post->ID
		, "name" => $post->post_title
	);
	
	
	return (object)$post_info;
}







function ajax_get_post() {
	if( !validate_ajax_request() ) die( 'Invalid Request' );
	
	// Get the Service Detail Requested
	$post_id = $_REQUEST['post_id'];
	$post_detail = get_post( $post_id );
	$post_custom = get_post_meta( $post_id );
	$post_meta = get_post_meta( $post_id, "_meta", true );
	
  // generate the response
  $response = json_encode( array( 'data' => $post_detail, 'custom' => $post_custom, 'meta' => $post_meta ) );
 
  // response output
  
}





function ajax_response_template() {
	
	if( !validate_ajax_request() ) {
		$response = json_encode( array('error' => 'error', 'message' => 'invalid request') );
	}else{
		// CODE GOES HERE
	}
	
	header( "Content-Type: application/json" );
  	echo $response;
	die();
}