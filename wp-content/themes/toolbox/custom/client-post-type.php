<?php

/*-------------------------------------------------------------------------------------------*/
/* fiveleft_client Post Type */
/*-------------------------------------------------------------------------------------------*/
class fiveleft_client {

	private $nonce;
	
	function fiveleft_client() {
		$this->nonce = wp_create_nonce( 'fiveleft_client_meta' );
		add_action('init',array($this,'create_post_type'));
		add_action('admin_init',array($this,'meta_init'));
		add_filter('manage_fiveleft_client_posts_columns', array($this,'column_headers'), 10 );
		add_action('manage_fiveleft_client_posts_custom_column', array($this,'custom_columns'), 10, 2);
		add_filter('manage_edit-fiveleft_client_sortable_columns', array($this,'sortable_columns'), 10, 2 );	
	}



	
	
	function create_post_type() {
		$labels = array(
		    'name' => 'Clients',
		    'singular_name' => 'Client',
		    'add_new' => 'New Client',
		    'all_items' => 'All Clients',
		    'add_new_item' => 'Create a New Client',
		    'edit_item' => 'Edit Client',
		    'new_item' => 'New Client',
		    'view_item' => 'View Client',
		    'search_items' => 'Search Clients',
		    'not_found' =>  'No Clients found',
		    'not_found_in_trash' => 'No Clients found in trash',
		    'parent_item_colon' => '',
		    'menu_name' => 'Clients'
		);
		$args = array(
			'labels' => $labels,
			'description' => "",
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
//			'show_in_nav_menus' => true, 
//			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 25,
			'menu_icon' => null,
			'capability_type' => 'post',
			'hierarchical' => true,
			'supports' => array('title'),
			'has_archive' => false,
			'rewrite' => false,
			'query_var' => true,
			'can_export' => true
		); 
		register_post_type('fiveleft_client',$args);
	}

	function meta_init()
	{
	    // review the function reference for parameter details
	    // http://codex.wordpress.org/Function_Reference/add_meta_box
		add_meta_box( 'fiveleft_client_meta', 'Business Details', array($this,'meta_details_setup'), 'fiveleft_client', 'normal', 'high');
     
	    // add a callback function to save any data a user enters in
	    add_action('save_post', array($this,'meta_save') );
	}

	function meta_details_setup() 
	{
		global $post;
			
	    // using an underscore, prevents the meta variable
	    // from showing up in the custom fields section
	    $meta = get_post_meta($post->ID,'_meta',TRUE);
		// error_log( print_r( $meta, true) );
		
	    // instead of writing HTML here, lets do an include
	    include('business-meta.php');
	  
	    // create a custom nonce for submit verification later
	    echo '<input type="hidden" name="' . $this->nonce . '" value="' . wp_create_nonce(__FILE__) . '" />';
	}
	
	function meta_save($post_id) 
	{
	    // authentication checks
	 
	    // make sure data came from our meta box
	    if (!wp_verify_nonce($_POST[ $this->nonce ],__FILE__)) return $post_id;
	 
	    // check user permissions
	    if ($_POST['post_type'] == 'page') {
	        if (!current_user_can('edit_page', $post_id)) return $post_id;
	    }else {
	        if (!current_user_can('edit_post', $post_id)) return $post_id;
	    }
	
	    // authentication passed, save data
	    // var types
	    // single: _my_meta[var]
	    // array: _my_meta[var][]
 		foreach( $_POST as $key=>$value ) {
 			// The regex match ensures we only capture POST properties starting with "_" that are not owned by WP "_wp"
 			if( preg_match("/^(_(?!wp))/", $key ) ){
 				$this->handlePostDataField($post_id, $key, $value);
 			}
 		}
		
		$timestamp = null;
		if( !empty($_POST["_start_date"]) ) {
			$timestamp = strtotime($_POST["_date"]);
			error_log(" DATE OUTPUT : " . $timestamp );
		}
		$this->handlePostDataField($post_id, "_timestamp", $timestamp);
		
		return $post_id;
	}


	function handlePostDataField ( $post_id, $key, $value ) 
	{
		$current_data = get_post_meta($post_id, $key, TRUE);  
   		$new_data = $value;
		$this->meta_clean($new_data);
		//error_log( "updating post " . $post_id . " data on " . $key . " from " . $current_data . " to " . $new_data );
		
	    if ($current_data) 
	    {
	      if (is_null($new_data)) {
	      	delete_post_meta($post_id,$key);
	      }else{
		      update_post_meta($post_id,$key,$new_data);
	      } 
	    }
	    elseif (!empty($new_data))
	    {
	      add_post_meta($post_id,$key,$new_data,true);
	    }else{
	    	//
	    }
	}
	
	
	
	/** CLEAN THE _meta CONTENT **/
	function meta_clean(&$arr) 
	{
		if (is_array($arr))
    {
        foreach ($arr as $i => $v)
        {
            if (is_array($arr[$i])) 
            {
                $this->meta_clean($arr[$i]);
 
                if (!count($arr[$i])) 
                {
                    unset($arr[$i]);
                }
            }
            else
            {
                if (trim($arr[$i]) == '') 
                {
                    unset($arr[$i]);
                }
            }
        }
 
        if (!count($arr)) 
        {
            $arr = NULL;
        }
    }
	}
	
	
	
	/**
	 * 
	 */
	function column_headers( $defaults ) {
		$defaults = array(
			'cb' => '<input type="checkbox" />'
			, 'title' => 'Client Name'
			, 'priority' => 'Priority'
			, 'city' => 'City'
			, 'state' => 'State'
			, 'website' => 'Website'
			, 'date' => 'Date Published'
		);
	  return $defaults;
	}
	
	function custom_columns($column_name, $post_id) {
		$meta = get_post_meta($post_id,'_meta',TRUE);
		switch( $column_name ) {
			case "priority" : 
				echo ((!empty($meta["priority"])) ? $meta["priority"] : "0");
				break;
			case "city" : 
				echo ((!empty($meta["city"])) ? $meta["city"] : "");
				break;
			case "state" : 
				echo ((!empty($meta["state"])) ? $meta["state"] : "");
				break;
			case "website" : 
				echo ((!empty($meta["website"])) ? "<a href='" . $meta["website"] . "' target='_blank'>" . $meta["website"] . "</a>" : "");
				break;
		}  
	}	
	
	function sortable_columns( $columns ) {
		$columns['priority'] = 'priority';
		$columns['city'] = 'city';
		$columns['state'] = 'state';
		return $columns;
	}
	


}

$fiveleft_client = new fiveleft_client();					


?>