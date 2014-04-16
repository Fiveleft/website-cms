<?php

/*-------------------------------------------------------------------------------------------*/
/* fiveleft_project Post Type */
/*-------------------------------------------------------------------------------------------*/
class fiveleft_project {

	private $nonce;
	
	function fiveleft_project() {
		$this->nonce = wp_create_nonce( 'fiveleft_project_meta' );
		add_action('init',array($this,'create_post_type'));
		add_action('admin_init',array($this,'meta_init'));
		add_filter('manage_fiveleft_project_posts_columns', array($this,'column_headers'), 10 );
		add_action('manage_fiveleft_project_posts_custom_column', array($this,'custom_columns'), 10, 2);
		add_filter('manage_edit-fiveleft_project_sortable_columns', array($this,'sortable_columns'), 10, 2 );	
	}



	
	
	function create_post_type() {
		$labels = array(
		    'name' => 'Projects',
		    'singular_name' => 'Project',
		    'add_new' => 'New Project',
		    'all_items' => 'All Projects',
		    'add_new_item' => 'Create a New Project',
		    'edit_item' => 'Edit Project',
		    'new_item' => 'New Project',
		    'view_item' => 'View Project',
		    'search_items' => 'Search Projects',
		    'not_found' =>  'No Projects found',
		    'not_found_in_trash' => 'No Projects found in trash',
		    'parent_item_colon' => '',
		    'menu_name' => 'Projects'
		);
		$args = array(
			'labels' => $labels,
			'description' => "",
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
//			'show_in_nav_menus' => true, 
//			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 25,
			'menu_icon' => null,
			'capability_type' => 'post',
			'hierarchical' => true,
			'supports' => array('title','editor','thumbnail'),
			'has_archive' => false,
			'rewrite' => false,
			'query_var' => true,
			'can_export' => true
		); 
		register_post_type('fiveleft_project',$args);
	}

	function meta_init()
	{
	    // review the function reference for parameter details
	    // http://codex.wordpress.org/Function_Reference/add_meta_box
		add_meta_box( 'fiveleft_project_meta', 'Project Details', array($this,'meta_details_setup'), 'fiveleft_project', 'normal', 'high');
     
	    // add a callback function to save any data a user enters in
	    add_action('save_post', array($this,'meta_save') );
	}

	function meta_details_setup() 
	{
		global $post;
		
	    // using an underscore, prevents the meta variable
	    // from showing up in the custom fields section
	    $meta = get_post_meta($post->ID,'_meta',TRUE);
		
	    // instead of writing HTML here, lets do an include
	    include('project-meta.php');
	  
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
			, 'title' => 'Project Name'
			, 'p2p-from-project_to_client' => 'Client'
			, 'p2p-from-project_to_agency' => 'Agency'
			, 'priority' => 'Priority'
			, 'date_launched' => 'Date Launched'
			, 'date' => 'Date Published'
		);
	  return $defaults;
	}
	
	function custom_columns($column_name, $post_id) {
		$meta = get_post_meta($post_id,'_meta',TRUE);
		switch( $column_name ) {
			case "priority" : 
				echo ((!empty($meta['priority'])) ? $meta['priority'] : 0);
				break;
			case "date_launched" : 
				echo ((!empty($meta['launchdate'])) ? $meta['launchdate'] : 0);
				break;
		}  
	}	
	
	function sortable_columns( $columns ) {
		$columns['p2p-from-project_to_agency'] = 'p2p-from-project_to_agency';
		$columns['p2p-from-project_to_client'] = 'p2p-from-project_to_client';
		$columns['date_launched'] = 'date_launched';
		$columns['priority'] = 'priority';
		return $columns;
	}



}

$fiveleft_project = new fiveleft_project();					


?>