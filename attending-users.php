<?php
/*
Plugin Name: Attending Users
Plugin URI: http://menian-lee.eu/wordpress/attending-users/
Description: Give the option of adding attending lists for registered users under posts
Author: Yasen Vasilev
Author URI: http://menian-lee.eu/
Version: 1.0
Text Domain: attending-users
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/* Version check */
global $wp_version;      
$exit_msg='Attending Users requires WordPress 2.7 or newer.  
<a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
   
if (version_compare($wp_version,"2.7","<")) {
    exit ($exit_msg);
  }
  
global $att_users_db_version;
$att_users_db_version = "1.0";

global $att_global_settings;
$att_global_settings = get_option('att_reg_settings');


	
class ML_Att_users {

		// foreign key tutorial: http://www.1keydata.com/sql/sql-foreign-key.html
		// installs 3 tables in the database
		function att_users_install() {

			global $wpdb, $att_users_db_version, $att_global_settings;

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$sql = "CREATE TABLE " . $wpdb->prefix . "att_users (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				user_id mediumint(9) NOT NULL,
				att_post_id mediumint(9) NOT NULL,
				UNIQUE KEY id (id),
				Foreign Key (user_id) references " . $wpdb->prefix . "users(ID),
				Foreign Key (att_post_id) references " . $wpdb->prefix . "posts(ID)
				) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

				CREATE TABLE " . $wpdb->prefix . "att_titles (
				att_title mediumint(9) NOT NULL,
				att_post_id mediumint(9) NOT NULL,
				UNIQUE KEY att_post_id (att_post_id),
				Foreign Key (att_title) references " . $wpdb->prefix . "att_list_titles(att_list_id)
				) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
				
				CREATE TABLE " . $wpdb->prefix . "att_list_titles (
				att_title text NOT NULL,
				att_list_id mediumint(9) NOT NULL AUTO_INCREMENT,
				UNIQUE KEY att_list_id (att_list_id)
				) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
			";

			dbDelta($sql);
			
			$att_options = array(
				'att_btn_sub' => 'Submit', 
				'att_btn_unsub' => 'unsubscribe', 
				'att_msg_not_logged' => 'If you want to particiate, please log in or register!',
				'att_post_types' => array('page', 'post')
			);
			
			add_option("att_reg_settings", $att_options);
			
			add_option("att_users_db_version", $att_users_db_version);
		}

		// adds Attending Users as page under Settings
		function att_admin_actions() {  
			add_options_page ("Attending Users", "Att Users Settings", 8, "att-user-titles", array(&$this, att_admin));
		}

		// content of options page
		function att_admin() { 
		
			global $wpdb, $att_global_settings;
			$att_global_settings = get_option('att_reg_settings');

			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			// adds new title to the list of titles
			if ($_POST['add_title']) {
				$add_title = $_POST['add_title'];
				$wpdb->query("
					INSERT INTO ".$wpdb->prefix."att_list_titles (att_title) 
					VALUE ('$add_title')
				");
			}
			
			// delete a title from the list of titles
			if ($_GET['delete_title']) {
				$delete_title = $_GET['delete_title'];
				$wpdb->query("
					DELETE FROM ".$wpdb->prefix."att_list_titles 
					WHERE att_title = '$delete_title'
				");
			}
			
			// deletes post types from the list of post types
			$delete_post_type = $_GET['delete_post_type'];
			$that_post_type = $att_global_settings['att_post_types'];
		 
			if ( isset($_GET['delete_post_type'])) {
				unset($att_global_settings['att_post_types'][$delete_post_type]);
			}
			
			
			echo '
				<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2>Titles for Attending Users Lists</h2>
				<p>From this page you can add new titles for Attending Users Lists, or delete existing</p>
			';

			// view for form for adding new titles
			echo '
				<h3>Add Title</h3>
				<form method="post" action="options-general.php?page=att-user-titles">
					<input type="text" name="add_title" value="New Title" />
					<input type="submit" value="Add Title" />
				</form>'
			;
			
			// view listing titles and giving option to delete them
			echo '<h3>Titles</h3>';

			$titles = $wpdb->get_results("
				SELECT att_title 
				FROM ".$wpdb->prefix."att_list_titles
			");
			
			foreach ( $titles as $title ) {
				$titles_listed = $title->att_title;
				
				echo '
					<li>'.$titles_listed.' <a href="?page=att-user-titles&delete_title='.$titles_listed.'" title="delete">X</a></li>
				';		
			}
			
			echo '<hr />';

			
			/* the folloring statements check if there are comming new values for the supported options
			* name of subscribe button
			* name of unsubscribe button
			* name of message for not logged in users
			* name of new content type	*/
			
			if ($_POST['att_btn_sub'] != null) {
				$att_global_settings['att_btn_sub'] = $_POST['att_btn_sub'];
			}
			
			if ($_POST['att_btn_unsub'] != null ) {
				$att_global_settings['att_btn_unsub'] = $_POST['att_btn_unsub'];
			}
			
			if ($_POST['att_msg_not_logged'] != null) {
				$att_global_settings['att_msg_not_logged'] = $_POST['att_msg_not_logged'];
			}
			
			if ($_POST['att_post_types'] != null) {
				$att_global_settings['att_post_types'][] = $_POST['att_post_types'];
			}
			
			update_option('att_reg_settings', $att_global_settings);

			// view of form for adding new post types
			?>
			<h3>Add Post Type Support</h3>
			<form method="post" action="options-general.php?page=att-user-titles">
				<input type="text" name="att_post_types" value="page" />
				<input type="submit" value="<?php _e('Add post type') ?>" />
			</form>

			<h3>Post Types</h3>
			<?php
			$att_post_types = $att_global_settings['att_post_types'];

			foreach ( $att_post_types as $att_post_type => $key ) {
				
				echo '
					<li>'.$key.' <a href="?page=att-user-titles&delete_post_type='.$att_post_type.'" title="delete">X</a></li>
				';		
			}
			?>

			<hr />

			<form method="post" action="options-general.php?page=att-user-titles">
				<h3>Labels and messages</h3>
				Subscribe button value: 
				<input type="text" name="att_btn_sub" value="<?php echo $att_global_settings['att_btn_sub']; ?>" /><br />
				Unsubscribe button value: 
				<input type="text" name="att_btn_unsub" value="<?php echo $att_global_settings['att_btn_unsub']; ?>" /><br />
				Message if user not logged in: 
				<input type="text" name="att_msg_not_logged" value="<?php echo $att_global_settings['att_msg_not_logged']; ?>" /><br />
				<input type="submit" value="<?php _e('Save Changes') ?>" /><br />
			</form></div>
			<?php
		}


		// code for meta-box in Edit / Add New post
		function att_list_add_box() {
			global $wpdb, $post, $current_user;
			
			$custom = get_post_custom($post->ID);
			$att_add_title = $custom["att_add_title"][0];
			
			if ($att_add_title) {
			
				// establish database connection between the post and the selected title
				$wpdb->query ("
					INSERT INTO ".$wpdb->prefix."att_titles (att_title, att_post_id)
					VALUES ('".$att_add_title."', '".$post->ID."')
				");
				
				// update the title - no matter if changed or not
				$wpdb->query ("
					UPDATE ".$wpdb->prefix."att_titles
					SET att_title='".$att_add_title."'
					WHERE att_post_id='".$post->ID."'
				");
			} 
			
			// if $att_add_title == null - delete all users from the list and the title reference
			// ps. may be I should change that code, because it adds extra queries to the system
			else {
				$wpdb->query ("
					DELETE 
					FROM ".$wpdb->prefix."att_users
					WHERE att_post_id = ".$post->ID."
				");
				
				$wpdb->query ("
					DELETE 
					FROM ".$wpdb->prefix."att_titles
					WHERE att_post_id = ".$post->ID."
				");
			}

			// get all titles from the database
			$titles = $wpdb->get_results("
				SELECT * 
				FROM ".$wpdb->prefix."att_list_titles
			");

			echo '
				<br />Choose title for Attending List: 
				<select type="select" name="att_add_title"><option></option>
			';
			
			// list all titles from the database as options to select
			foreach ( $titles as $title ) {
				$titles_listed = $title->att_title;
				$title_id = $title->att_list_id;
				?>
				<option name="result_title" <?php	if($title->att_list_id == $att_add_title) {	?> selected="selected"<?php } ?> value="<?php echo $title_id; ?>"> <?php echo $titles_listed; ?></option> <?php		
			}
			echo '</select>';
		}

		// save post preferences
		function save_details($post_ID = 0) {
			$post_ID = (int) $post_ID;
			$post_type = get_post_type( $post_ID );
			$post_status = get_post_status( $post_ID );

			if ($post_type) {
				update_post_meta($post_ID, "att_add_title", $_POST["att_add_title"]);
			}
		   return $post_ID;
		} 

		// adds support for the user selected custom content types saved in the database
		function att_list_add_box_admin() {
			global $att_global_settings;
			$att_post_types = $att_global_settings['att_post_types'];
			
			foreach ( $att_post_types as $att_post_type ) {
				add_meta_box( 'Attending List', 'Attending List', array(&$this, 'att_list_add_box'), ''.$att_post_type.'', 'normal', 'high' );
			}
		}
		
		// view - adds the list under a post
		function att_users_show() {
			global $post, $wpdb, $current_user, $att_global_settings;

			$db_post_id = $wpdb->get_var("
				SELECT att_post_id
				FROM ".$wpdb->prefix."att_titles
				WHERE att_post_id = ".get_the_ID()."
			");
			
			switch ($_POST['att_button']) {
			
				// remove user from database if s/he clicked on unsubscribe
				case $att_global_settings['att_btn_unsub']:
					$wpdb->query("
						DELETE 
						FROM ".$wpdb->prefix."att_users
						WHERE att_post_id = ".$post->ID."
						AND user_id = ".$current_user->id."
					");
				break;
				
				// add user to database if s/he clicked on attend
				case $att_global_settings['att_btn_sub']:
					$wpdb->query("
						INSERT INTO ".$wpdb->prefix."att_users (user_id, att_post_id)
						VALUES ('".$current_user->id."', '".$post->ID."')
					");
				break;
			}
			
			if(get_the_ID() == $db_post_id ) {
				
				// creates a variable if users is already added to attending list
				$user_added = $wpdb->get_var("
					SELECT user_id 
					FROM   ".$wpdb->prefix."att_users
					WHERE  att_post_id = ".$post->ID."
					AND    user_id = ".$current_user->id.""
				);

				// gets Attending list title for that post
				$title_att = $wpdb->get_var("
					SELECT ".$wpdb->prefix."att_list_titles.att_title
					FROM   ".$wpdb->prefix."att_list_titles
					JOIN   ".$wpdb->prefix."att_titles
					ON     ".$wpdb->prefix."att_list_titles.att_list_id = ".$wpdb->prefix."att_titles.att_title
					WHERE att_post_id = ".get_the_ID()."
				");

				$att_output .= '<h3>'.$title_att.'</h3><ul>';

				// gets an array of users signed under that post
				$users = $wpdb->get_results("
					SELECT ".$wpdb->prefix."att_users.user_id
					FROM   ".$wpdb->prefix."att_users
					JOIN   ".$wpdb->prefix."users
					ON     ".$wpdb->prefix."users.id = ".$wpdb->prefix."att_users.user_id
					WHERE  ".$wpdb->prefix."att_users.att_post_id = ".get_the_ID()."
				");

				// outputs the list of siged users, adds link to their authors pages
				foreach ( $users as $user ) {
					$user_info = get_userdata( $user->user_id );
					$user_login = $user_info->user_login;
					
					// display first and last user name if added - else output login name
					if (!($user_info->user_firstname == '') && !($user_info->user_lastname == '') ) {
						$user_names = $user_info->user_firstname . ' ' .$user_info->user_lastname;
					} else {
						$user_names = $user_login;
					}
					
					$att_output .= '<li><a href="' . get_bloginfo(url) . '/?author='. $user_info->ID . '/">'.$user_names.'</a></li>';
				}

				$att_output .= '</ul>';

				if (!is_user_logged_in()) {
						$att_output .=  '<p>'.$att_global_settings['att_msg_not_logged'].'</p>';
					} elseif ($user_added) {
						$att_output .= '
							<form method="post" action="http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '">
								<input type="submit" name="att_button" value="'.$att_global_settings['att_btn_unsub'].'" />
							</form>
						';
					} else {
						$att_output .= '
							<form method="post" action="http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '">
								<input name="att_user_add" type="hidden"  size="40">
								<p>
									<input type="submit" name="att_button" id="seatt_register" value="'.$att_global_settings['att_btn_sub'].'">
								</p>
							</form>
						';
					}
				
				if(is_single()) {
					return $att_output;
				}
			}
		}

		// part of the display filter - adds att_output at the end of post content
		function att_users_show_filter($content) {
			return $content.$this->att_users_show();
		}
	}

$ML_Attending_users = new ML_Att_users();



/* HOOKS */

// install database
register_activation_hook(__FILE__, array($ML_Attending_users, 'att_users_install')); 

// display the plugin at the end of post content
add_filter('the_content', array($ML_Attending_users, 'att_users_show_filter'));

// displays plugin settings page for admin users
if (is_admin()){
		add_action('admin_menu', array($ML_Attending_users, 'att_admin_actions'));
	}

// adds meta box to the post edit page
add_action('admin_init', array($ML_Attending_users, 'att_list_add_box_admin'));
	
// save post preferences
add_action('save_post', array($ML_Attending_users, 'save_details'));


/* End of plugin code */

/*
* Pleace the following code in your
* author.php template file
* it will output the titles and links
* to all posts where that user is attending

// code start
global $wpdb;

$att_at = $wpdb->get_results("
		SELECT ".$wpdb->prefix."att_users.att_post_id
		FROM   ".$wpdb->prefix."att_users
		WHERE  ".$wpdb->prefix."att_users.user_id = ".get_the_author_meta( 'ID' )."
	");

echo "<h3>".get_the_author_meta( 'first_name' )." is attending at: </h3>";
foreach ($att_at as $at_att) {
		$att_post_id = $at_att->att_post_id;
		echo "<a href=".get_permalink($att_post_id).">".get_the_title($att_post_id)."</a><br />";
	}
*/
?>