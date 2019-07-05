<?php
/**
* Plugin Name: WP Locations|Phone Numbers
* Plugin URI: paradisetechsoft.com
* Description: This plugin is created to add different location Entries with it's Phone numbers and get the current location of the user and according to that swap the Phone number in the header location of the website.
* Version: 1.0
* Author: Paradise TechSoft Solutions Pvt. Ltd.
* Author URI: https://www.paradisetechsoft.com/
**/



/****************************************************/
/****Create table at the time of install plugin******/
/****************************************************/

global $jal_db_version;
$jal_db_version = '1.0';
global $wpdb;
global $table_name;
$table_name = $wpdb->prefix . 'locations';

function create_location_table() {
	global $wpdb;
	global $jal_db_version;
	global $table_name;

	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		city varchar(100) DEFAULT '',
		state varchar(100) DEFAULT '',
		zip varchar(10) DEFAULT '',
		phone varchar(15) DEFAULT '',
		create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

register_activation_hook( __FILE__, 'create_location_table' );

/****************************************************/
/****Add admin menu on the wordpress backend ********/
/****************************************************/

add_action('admin_menu', 'wp_locations');

function wp_locations() {
    add_menu_page('WP Locations', 'Locations/Phone', 'manage_options', 'wp-locations', 'wp_locations_page','dashicons-location');
}


/****************************/
/****Create plugin Page******/
/****************************/

function wp_locations_page() {
	global $wpdb;
	global $jal_db_version;
	global $table_name;
   
   echo '<div class="tablenav top"><div class="sedate-title "><h2>Add New Location and Phone Number</h2></div>  
			<form class="form-horizontal sf-column-names" action="" method="post" name="add_excel">
				<input type="text" name="zip" id="zip" class="input-large" placeholder="Zipcode" required>
				<input type="text" name="city" id="city" class="input-large" placeholder="City" required>
				<input type="text" name="state" id="state" class="input-large" placeholder="State" required>
				<input type="text" name="phone" id="phone" class="input-large" placeholder="Phone Number" required>
				<button type="submit" id="submit" name="addpost" class="button button-loading" data-loading-text="Loading...">Add</button> 
			 </form>
			 <button id="deletePostcodesTriger" class="button">
				Delete </button>
		<div></div>';
		
/*******insert zip code start***************/
if(isset($_POST["addpost"])){

		$zip=$_POST["zip"];
		$city=$_POST["city"];
		$state=$_POST["state"];
		$phone=$_POST["phone"];
	
		$postcodes_get = $wpdb->get_col($wpdb->prepare("SELECT zip  FROM $table_name WHERE zip = %s",$zip));	
		
		$postcodes_get_cunt = count($postcodes_get);
		 if($postcodes_get_cunt){
			 $message = 'Zip code already exist';
			 $code = 'error';
		 }else{			   
				$wpdb->insert($table_name, array('zip' => $zip, 'city' => $city, 'state' =>$state, 'phone' =>$phone, 'create_date'=>date('Y-m-d h:i:s')) );
			 $message = 'Location added successfully';
			 $code = 'updated';
			 
		 } 
 
    }
echo '<div class="response">'; 
if($message){
		echo '<div class="wpaas-notice notice '.$code.'">
			<p><strong>Note: &nbsp;</strong>'.$message.'</strong>.</p>
		</div>';
		
	}
	echo '</div>'; 
	/*******insert zip code start***************/
	
	
	/*************location list start***************/
	echo '<div class="table-responsive">
			<table class="wp-list-table widefat fixed striped posts" id="providers-zip"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
		<thead>
			<tr><th width="4%"><input type="checkbox" id="del_deletePostcodes"></th>
			<th width="6%">ID</th>
			<th>Zip Code</th>
			<th>City Name</th>
			<th>State</th>
			<th class="manage-column column-date sortable asc">Phone Number</th>
			<th class="manage-column column-date sortable asc">Date</th>
			<th>Action</th></tr>
		</thead>
		<tbody>';

	
$results = $wpdb->get_results('SELECT * FROM '.$table_name);

 if ( $results ){  
		foreach($results as $result) {        
				echo '<tr id="row-id-'.$result->id.'">
				<td><input type="checkbox" class="deletePostcodesRow" value="'.$result->id.'"></td>
				<td >'.$result->id.'</td>
				<td>'.$result->zip.'</td>
				<td>'.$result->city.'</td>
				<td>'.$result->state.'</td>
				<td>'.$result->phone.'</td>
				<td>'.$result->create_date.'</td>
				<td><a id="'.$result->id.'" class="btn_custom dashicons dashicons-trash" onClick="reply_click(this.id)"></a></td>
				</tr>';
		}
}else{
     _e( 'Sorry, no location found.' );
 }
echo '</tbody>
</table></div><div class="wpaas-notice notice updated">
			<p><strong>Note: &nbsp;</strong>To Display the phone number, based on the user current location you can use this shortcode :  [city_phone_number]</p>
		</div></div>';	
}


/***********************************************/
/****Delete the wp location function Start******/
/***********************************************/


function DWPL()
{
global $wpdb;
global $table_name;
if($_POST['all'] == 1){
 foreach($_POST['Postcodes'] as $key=>$val){	
		$wpdb->query('DELETE  FROM '.$table_name.' WHERE id = "'.$val.'"');
	 }
}else{
	$id = $_POST['post_id'];
	$wpdb->query('DELETE  FROM '.$table_name.' WHERE id = "'.$id.'"');
}
$data = array('status'=>'success','message'=>'Successfully Deleted');
echo json_encode($data); exit;
}

add_action('wp_ajax_DWPL', 'DWPL');
add_action('wp_ajax_nopriv_DWPL', 'DWPL');




/***********************************************/
/****Add jquery file in admin backend **********/
/***********************************************/



function location_scripts() {

    wp_enqueue_script( 'location-script', plugin_dir_url( __FILE__ ) . 'assets/js/wp-locations.js', array( 'jquery' ), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'location_scripts' );


/****************************************************/
/*************add geo location code on header********/
/****************************************************/
add_action('wp_head', 'get_current_location');
function get_current_location(){
global $wpdb;
global $table_name;

	echo '<div style="display:none">';
	echo do_shortcode('[gmw_current_location]');
	echo '</div>';		
}

/*************************************************/
/********get location by google map api***********/
/*************************************************/
function googleAPI(){
	if(isset($_COOKIE['gmw_ul_city'])){	
	$gmw_ul_city = $_COOKIE['gmw_ul_city'];
	$gmw_ul_region_name = $_COOKIE['gmw_ul_region_name'];
	$gmw_ul_postcode = $_COOKIE['gmw_ul_postcode'];
	$gmw_ul_country_name = $_COOKIE['gmw_ul_country_name'];	
	return $key = array('zip'=>$gmw_ul_postcode);
	}
}

/*************************************************/
/********get location by lookup api***************/
/*************************************************/
function lookupAPI(){
	$user_ip = getenv('REMOTE_ADDR');
	 $geo = json_decode(file_get_contents("http://extreme-ip-lookup.com/json/$user_ip"));
	 $country = $geo->country;
	 $city = $geo->city;
	 $ipType = $geo->ipType;
	 $businessName = $geo->businessName;
	 $businessWebsite = $geo->businessWebsite;
	
	return $key = array('city'=>$city);
}

/*************************************************/
/********get phone number by key******************/
/*************************************************/
function getPhone($key){

	global $wpdb;
	global $table_name;
	$phone = '';
	if(isset($key['city'])){
		$column = 'city';
		$value = $key['city'];
	}
	if(isset($key['zip'])){
		$column = 'zip';
		$value = $key['zip'];
	}
	 $postcodes_get = $wpdb->get_results($wpdb->prepare("SELECT phone FROM $table_name WHERE ".$column." = %s limit 1",$value));	
		
		$postcodes_get_cunt = count($postcodes_get);
		 if($postcodes_get_cunt){
			  $phone =  $postcodes_get[0]->phone;			
		 } 
		 return $phone;
}

/*******************************************************/
/********show phone number by city shortcode************/
/*******************************************************/
 function SPN() {	 
	 
	$key =  googleAPI();	
	if(isset($key['zip'])){
		 $phone =  getPhone($key);
	}
	if(empty($phone)){
		$key = lookupAPI();
		$phone =  getPhone($key);
	}
	
	if(empty($phone)){		
		$phone =  '(949) 234-6034';
	}
	
    return '<a href="tel:+'.$phone.'">'.$phone.'</a>';
}
add_shortcode('city_phone_number', 'SPN');

