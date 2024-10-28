<?php
global $wpdb;
	$table = array( 
		"att_titles", 
		"att_list_titles", 
		"att_users"
	); 

	foreach ( $table as $att_table ) {
		$wpdb->query("
		DROP TABLE IF EXISTS ".$wpdb->prefix."$att_table");
	}
	
	delete_option("att_reg_settings");
	delete_option("att_users_db_version");
?>