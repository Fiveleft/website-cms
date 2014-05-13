<?php


error_log( "executing 'getSiteData' at " . time() );

// $project_id = isset($_GET["id"]) ? $_GET["id"] : 0;
$uncache = isset($_GET["uncache"]) ? $_GET["uncache"] : 0;
$cache_timeout = isset($_GET["uncache"]) ? 0 : 60 * 60 * 24; // Measured in seconds = 1 day
$cache_file = "cache/sitedata-cache.json";

if( !$uncache && file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_timeout) ) {

	$json = file_get_contents($cache_file);


}else{
	define('WP_USE_THEMES', false);
	require_once( "wordpress/wp-load.php");
	require_once( "includes/config.php" );
	require_once( "includes/db_connect.class.php" );


	//error_log( " ** Rewrite Cache ** ");

	// Set up JSON output
	$json_array = array( 
		"projects" => array() 
		, "pages" => array()
		// , "taxonomies" => array() 
		, "agencies" => array()
		, "clients" => array()
		, "media" => home_url() . "../media"
		, "_cachetime" => date('m/d/Y h:i:s a', time())
	);


	$dbc = $config->db;
	$connection = new db_connect( $dbc["host"], $dbc["user"], $dbc["pass"], $dbc["database"] );


	function get_project_data() 
	{
		global $json_array, $connection;



		/**
		 * 1. First query merges Posts of type 'fiveleft_project' with their postmeta, taxonomy, taxonomy term, and p2p connection rows
		 *   When a result is returned, we will create unique projects for each result
		 */
		$query = "SELECT p.ID as 'id', p.post_date as 'date', p.post_content as 'orig_content', p.post_title as 'title', p.post_name as 'name', p.menu_order as 'order', 
				m.meta_value as 'info', 
				tt.taxonomy, 
				t.term_id as 'termId', t.name as 'termName', t.slug as 'termSlug', 
				att.guid as '_att_src', att.post_mime_type as '_att_type', att.post_name as '_att_name', att.ID as '_att_id',
				att_m.meta_value as '_att_info',
				-- thumb.guid as '_thumb_src', thumb.post_mime_type as '_thumb_type', thumb.post_name as '_thumb_name', thumb.ID as '_thumb_id',
				thumb_m.meta_value as '_thumb_id',
				p_client.post_title as 'client', p_client.ID as 'clientId', 
				p_agency.post_title as 'agency', p_agency.ID as 'agencyId'
				FROM wp_posts p 
				LEFT JOIN wp_postmeta m ON m.post_id = p.ID 
					AND m.meta_key = '_meta'	
				LEFT OUTER JOIN wp_term_relationships tr ON tr.object_id = p.ID 
				LEFT OUTER JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				LEFT OUTER JOIN wp_terms t ON t.term_id = tr.term_taxonomy_id
				LEFT OUTER JOIN wp_p2p p2p ON p2p.p2p_from = p.ID
				LEFT OUTER JOIN wp_posts p_client ON p_client.post_type ='fiveleft_client' 
					AND p_client.ID = p2p.p2p_to 
					AND p2p.p2p_type = 'project_to_client'
				LEFT OUTER JOIN wp_posts p_agency ON p_agency.post_type ='fiveleft_agency' 
					AND p_agency.ID = p2p.p2p_to 
					AND p2p.p2p_type = 'project_to_agency'
				LEFT OUTER JOIN wp_posts att ON att.post_type = 'attachment'
					AND att.post_parent = p.ID 
				LEFT JOIN wp_postmeta att_m on att_m.post_id = att.ID
					AND att_m.meta_key = '_wp_attachment_metadata'
				LEFT JOIN wp_postmeta thumb_m on thumb_m.post_id = p.ID
					AND thumb_m.meta_key = '_thumbnail_id'
				WHERE p.post_type = 'fiveleft_project' 
					AND p.post_status = 'publish'
				ORDER BY p.menu_order ASC 
			 ";



		// Get results of query
		$result = $connection->query($query);

		// Unique projects
		$uniqueproj = array();

		// Project Attachments
		$proj_attach = array();
		$proj_taxonomies = array();

		// Taxomony Lists
		$taxonomies = array();

		// Media Pattern in post content
		$pattern_media = '/\[(video|image|gallery)\s*((?:\w+="[^"]*"\s?+)*)\s?+\](\[\/\w+\])?/';

		// Iterate through results
		if( $result ) {
			while($row = $result->fetch_assoc()) {

				// Make sure the project doesn't already exist
				$p = empty( $uniqueproj[$row["id"]] ) ? $row : $uniqueproj[$row["id"]];

				// Info
				$p["info"] = !empty($row["info"]) ? unserialize($row["info"]) : $p["info"];

				// Attchments
				$p["attachments"] = empty($p["attachments"]) ? array() : $p["attachments"];


				// Check if "$proj_attach[ _{pID}-{aID} ]" exists;
				if( !empty($row["_att_id"]) && !array_key_exists( "_".$p["id"]."-".$row["_att_id"], $proj_attach ) ){

					$a = array( 
							"id" => $row["_att_id"]
							,"src" => $row["_att_src"]
							,"type" => $row["_att_type"]
							,"name" => $row["_att_name"]
							,"info" => unserialize($row["_att_info"])
						);
					if( !empty($a["info"]["image_meta"]) ) unset( $a["info"]["image_meta"] );
					
					$p["attachments"][] = $a;
					$proj_attach[ "_".$p["id"]."-".$row["_att_id"] ] = $a;
				}
				
				if( !empty($row["_thumb_id"]) && empty($p["thumb"]) && array_key_exists( "_".$p["id"]."-".$row["_thumb_id"], $proj_attach ) ) {
					$p["thumb"] = $proj_attach[ "_".$p["id"]."-".$row["_thumb_id"] ];
				}

				$taxType = $row["taxonomy"];
				$term = $row["termName"];
				$termId = $row["termId"];
				$termSlug = $row["termSlug"];

				//
				$taxType_assoc = "_" . $taxType;
				$p_tax = empty( $proj_taxonomies[$p["id"]] ) ? array() : $proj_taxonomies[$p["id"]];

				// Add Taxonomies 
				switch( true ) {
					case $taxType_assoc && empty($p_tax[$taxType_assoc]) :
						$p_tax[$taxType_assoc][$termId] = array( "id" => $termId, "title" => $term, "name" => $termSlug );
						break;
					case $taxType && !array_key_exists($termId, $p_tax[$taxType_assoc]) :
						$p_tax[$taxType_assoc][$termId] = array( "id" => $termId, "title" => $term, "name" => $termSlug );
						break;
				}
				if( !empty($p_tax[$taxType_assoc]) ) {
					$p[$taxType] = array_values( $p_tax[$taxType_assoc] );
				}
				$proj_taxonomies[$p["id"]] = $p_tax;

				// t_arr = $taxonomies["technology"]
				$t_arr = !empty($taxonomies[$taxType]) ? $taxonomies[$taxType] : array();			
				
				// term_arr = $taxomonies["technology"]["html"] 
				$term_arr = !empty($t_arr[$termSlug]) ? $t_arr[$termSlug] : array( "title" => $term, "id" => $termId, "name" => $termSlug, "projects" => array(), "_projects" => array() );

				// Add Project 
				if( !array_key_exists( $p["id"], $term_arr["_projects"] ) ) {
					$term_arr["_projects"][$p["id"]] = true;
					array_push( $term_arr["projects"], array( "projectId" => $p["id"], "projectTitle" => $p["title"] ) ); 
				}

				$t_arr[$termSlug] = $term_arr;
				$taxonomies[$taxType] = $t_arr;

				// Clean up content
				if( empty($p["content"]) && !empty($row["orig_content"]) ) {

					// MATCH MEDIA
					preg_match( $pattern_media, $row["orig_content"], $media );

					$p["media"] = (count($media) > 0) ? array() : false;

					if(count($media) > 0) {

						$mediatype = $media["1"];
						$media_values = explode( " ", $media["2"] );

						$p[ "media" ] = $mediatype;
						$p[ $mediatype ] = array();

						foreach( $media_values as $kv ) {
							$kvpair = explode( "=", $kv );
							$p[$mediatype][$kvpair["0"]] = trim($kvpair["1"],'"');
						}	
					}
					$p["content"] = preg_replace( $pattern_media, "", $row["orig_content"] );
				}
				

				$p["agency"] = !empty($row["agency"]) ? $row["agency"] : $p["agency"];
				$p["agencyId"] = !empty($row["agencyId"]) ? $row["agencyId"] : $p["agencyId"];
				$p["client"] = !empty($row["client"]) ? $row["client"] : $p["client"];
				$p["clientId"] = !empty($row["clientId"]) ? $row["clientId"] : $p["clientId"];

				unset( $taxType, $term, $termId, $termSlug, $p["taxonomy"], $p["termName"], $p["termId"], $p["termSlug"],
					$p["_att_id"], $p["_att_info"], $p["_att_name"], $p["_att_src"], $p["_att_type"] ,$p["_thumb_id"] );

				$uniqueproj[$row["id"]] = $p;
		    }
		    $result->close();
		}
		unset( $query, $result );

		foreach( $taxonomies as $key => $value ) {
			$taxonomies[$key] = array_values( $value );
		}

		// Finally, add results to $json_array
		$json_array["projects"] = array_values($uniqueproj);
		// $json_array["taxonomies"] = $taxonomies;
	}

	function get_partnership_data() 
	{
		global $connection, $json_array;

		// Unique partners (clients or agencies)
		$unique_partner = array();

		/**
		 * 2. Query finds client and agency partnerships and adds them to their respective arrays
		 */
		$query = "SELECT p.ID as 'id', p.post_content as 'content', p.post_title as 'title', p.post_name as 'name', 
					p.menu_order as 'order', p.post_type as 'type',
					m.meta_value as 'info'
					FROM wp_posts p 
					LEFT JOIN wp_postmeta m ON m.post_id = p.ID 
						AND m.meta_key = '_meta'
					WHERE p.post_type IN( 'fiveleft_agency', 'fiveleft_client' )
						AND p.post_status = 'publish'
					ORDER BY p.menu_order ASC 
				";
				
		
		// Get results of query
		$result = $connection->query($query);

		// Iterate through results
		if( $result ) {
			while($row = $result->fetch_assoc()) {

				// Make sure the partner doesn't already exist
				$item = empty( $unique_partner[$row["id"]] ) ? $row : $unique_partner[$row["id"]];

				// Create the partner info
				$item["info"] = !empty($row["info"]) ? unserialize($row["info"]) : $item["info"];

				// Fix the type to remove the "fiveleft_" prefix
				$item["type"] = preg_replace( '/^fiveleft_/', "", $row["type"] );

				// Add to list
				$unique_partner[$row["id"]] = $item;
			}
			$result->close();
		}
		unset( $query, $result );

		foreach( $unique_partner as $key=>$value ) {
			if( $value["type"] == "client" ) {
				$json_array["clients"][] = $value;
			}
			if( $value["type"] == "agency" ) {
				$json_array["agencies"][] = $value; //array_values( $value );
			}
		}
	}

	function get_page_content()
	{
		global $connection, $json_array;

		$query = "SELECT p.ID as 'id', p.post_content as 'content', p.post_title as 'title', p.post_name as 'name', p.menu_order as 'order'
				  FROM wp_posts p 
					LEFT JOIN wp_postmeta m ON m.post_id = p.ID 
						AND m.meta_key = '_wp_page_template'
					WHERE p.post_type = 'page'
						AND p.post_status = 'publish'
						AND m.meta_value = 'page-site-content.php'
					ORDER BY p.menu_order ASC 
				";
		
		// Get results of query
		$result = $connection->query($query);

		// Iterate through results
		if( $result ) {
			while($row = $result->fetch_assoc()) {
				$post_object = get_post( $row['id'] );
				$row['content'] = apply_filters('the_content', $post_object->post_content);
				$json_array['pages'][$row['name']] = $row;
			}
		}
	}


	// Retrieve data sets
	get_project_data();
	get_partnership_data();
	get_page_content();

	// Create the JSON output
	$json = json_encode( $json_array );


	// If the query produces results, cache the JSON
	if( count($json_array["projects"]) ) {

		$fp = fopen($cache_file, 'w+'); // open or create cache
	    if ($fp) {
	      if (flock($fp, LOCK_EX)) {
	        fwrite($fp, $json);
	        flock($fp, LOCK_UN);
	      }
	      fclose($fp);
	    }
	}
}
// header("access-control-allow-origin: *");
header( "Content-Type: application/json" );

if( $_GET['callback'] ) {
	echo $_GET['callback'] . "(" . $json . ")";
}else{
	echo $json;
}
