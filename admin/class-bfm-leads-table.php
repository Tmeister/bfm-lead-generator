<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Bfm_Leads_Table extends WP_List_Table {

	var $table;

    function __construct(){

        global $status, $page, $wpdb;

        $this->table = $wpdb->prefix . 'bfm_leads';

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'lead',     //singular name of the listed records
            'plural'    => 'leads',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

    }

    function column_default($item, $column_name){
        switch($column_name){
            case 'first_name':
            case 'last_name':
            case 'email':
            case 'phone':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_email($item){

        //Build row actions
        $actions = array(
            'profile'      => sprintf('<a href="?page=%s&action=%s&lead=%s">Full Profile</a>',$_REQUEST['page'],'view_profile',$item['id'])
        );

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['email'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns(){
        $columns = array(
			'cb'    => '<input type="checkbox" />', //Render a checkbox instead of text
			'email' => 'Email',
			'first_name'  => 'First Name',
			'last_name'  => 'Last Name',
			'phone' => 'Phone'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
			'first_name' => array('first_name',false),
			'last_name'  => array('last_name',false),
			'email'      => array('email',false)
        );
        return $sortable_columns;
    }

    /*function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }*/

    function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }

    }

    function prepare_items() {

        global $wpdb;

        $per_page = 15;

        $columns = $this->get_columns();

        $hidden = array();

        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id';

		$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'DESC';

        $current_page = $this->get_pagenum();

        $limit = '';

        if( $current_page > intval( 1 ) ){
        	$init = ($current_page-1)*$per_page;
        	$limit = sprintf('LIMIT %d, %d', $init, $per_page );
        }else{
        	$limit = sprintf('LIMIT 0, %d', $per_page );
        }

        $sql = sprintf(
        	'SELECT id, first_name, last_name, email, phone FROM %s ORDER BY %s %s %s',
        	$this->table,
        	$orderby,
        	$order,
        	$limit
        );

        var_dump($sql);

        $data = $wpdb->get_results( $sql, ARRAY_A );

        //TOTAL
        $sql = sprintf(
        	'SELECT COUNT(id) as total_items FROM %s',
        	$this->table
        );

        $row = $wpdb->get_row( $sql );

        $total_items = $row->total_items;

        $this->items = $data;

        $this->set_pagination_args(

        	array(

            	'total_items' => $total_items,

            	'per_page'    => $per_page,

            	'total_pages' => ceil($total_items/$per_page)

        	)
        );
    }
}