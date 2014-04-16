<?php

/*-------------------------------------------------------------------------------------------*/
/* fiveleft_taxonomies */
/*-------------------------------------------------------------------------------------------*/
class fiveleft_taxonomies {
	
	function fiveleft_taxonomies() {
		add_action( 'init',array($this,'create_fiveleft_taxonomies') );
	}
	
	function create_fiveleft_taxonomies() 
	{
		// Discipline Taxonomy
		register_taxonomy( 'discipline', array('fiveleft_project', 'page'), array(
		    'labels' => array(
			    'name' => _x( 'Disciplines', 'taxonomy general name' ),
			    'singular_name' => _x( 'Discipline', 'taxonomy singular name' ),
			    'search_items' =>  __( 'Search Disciplines' ),
			    'all_items' => __( 'All Disciplines' ),
			    'edit_item' => __( 'Edit Discipline' ), 
			    'update_item' => __( 'Update Discipline' ),
			    'add_new_item' => __( 'Add New Discipline' ),
			    'new_item_name' => __( 'New Discipline Name' ),
			    'menu_name' => __( 'Disciplines' ),
			  ),
			'hierarchical' => false,
		    'show_ui' => true,
		    'query_var' => true,
		    'rewrite' => false,
		  ));
		
		// Position	Taxonomy
	  	register_taxonomy( 'technology', array('fiveleft_project', 'page'), array(
	    	'labels' => array(
			    'name' => _x( 'Technology', 'taxonomy general name' ),
			    'singular_name' => _x( 'Technology', 'taxonomy singular name' ),
			    'search_items' =>  __( 'Search Technologies' ),
			    'all_items' => __( 'All Technologies' ),
			    'edit_item' => __( 'Edit Technology' ), 
			    'update_item' => __( 'Update Technology' ),
			    'add_new_item' => __( 'Add New Technology' ),
			    'new_item_name' => __( 'New Technology Name' ),
			    'menu_name' => __( 'Technologies' ),
			  ),
		    'hierarchical' => false,
		    'show_ui' => true,
		    'query_var' => true,
		    'rewrite' => false,
		  ));
	}
}

$fiveleft_taxonomies = new fiveleft_taxonomies();					

?>