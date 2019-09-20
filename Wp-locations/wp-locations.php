<?php
/**
* Plugin Name: WP Locations|Phone Numbers
* Plugin URI: paradisetechsoft.com
* Description: This plugin is created to add different location Entries with it's Phone numbers and get the current location of the user and according to that swap the Phone number in the header location of the website.
* Version: 1.2
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
if (!function_exists('create_location_table')) {
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
		url varchar(255) DEFAULT '',
		create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

register_activation_hook( __FILE__, 'create_location_table' );
}
/****************************************************/
/****Add admin menu on the wordpress backend ********/
/****************************************************/

if (!function_exists('wp_locations')) {
	add_action('admin_menu', 'wp_locations');
	
	function wp_locations() {
		add_menu_page('WP Locations', 'Locations/Phone', 'manage_options', 'wp-locations', 'wp_locations_page','dashicons-location');
	}
}


/****************************/
/****Create plugin Page******/
/****************************/
if (!function_exists('wp_locations_page')) {
	
	
function wp_locations_page() {
	global $wpdb;
	global $jal_db_version;
	global $table_name;
   
   $hrmlData = '
   <div class="tablenavv top">
   <div class="row">
   <div class="col-lg-12 col-md-12">
   <div class="sedate-title "><h2>Add New Location and Phone Number</h2></div>  
			<form class="form-horizontal sf-column-names" action="" method="post" name="add_excel">
				
				<input type="text" name="city" id="city" class="input-large" placeholder="City" >
				<input type="text" name="state" id="state" class="input-large" placeholder="State" required>
				<input type="text" name="phone" id="phone" class="input-large" placeholder="Phone Number" >
				<input type="text" name="url" id="url" class="input-large" placeholder="URL" >
				<button type="submit" id="submit" name="addpost" class="button button-loading" data-loading-text="Loading...">Add</button> 
			 </form>
		</div>
		<div class="col-lg-12 col-md-12">
		<div class="import">
		<form class="form-horizontal sf-by-provider" action="" method="post" name="upload_excel" enctype="multipart/form-data">                                      
		<label>Import CSV File</label>                       
		<div class="form-group">
				<label class="custom-file-upload"><input type="file" name="file" id="file" class="input-large" required><div class="wp-menu-image dashicons-before dashicons-download"><br></div></label>
				<input type="submit" id="submit" name="Import" value="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">
		</div>
        </form>
		<form class="form-horizontal sf-by-provider" action="" method="post" name="upload_excel" enctype="multipart/form-data">                                      
		<label>Export CSV File</label>                       
		<div class="form-group">
				<input type="submit" id="submit" name="Export" value="Export" class="btn btn-primary button-loading" data-loading-text="Loading...">	
		</div>
        </form>
		</div></div> <button id="deletePostcodesTriger" class="button">
				Delete </button>
		</div>';
		
/*******insert zip code start***************/


if(isset($_POST["Import"])){	
				$num=0;
				$msg ='';  
				$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
				if(in_array($_FILES['file']['type'],$mimes)){
                    
				$filename=$_FILES["file"]["tmp_name"];
				if (($handle = fopen($filename, "r")) !== FALSE) {
						fgetcsv($handle, 1000, ",");
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					
					$city = $data[0];
					$state = $data[1];

					if($city){
	$postcitycodes_get = $wpdb->get_col($wpdb->prepare("SELECT city  FROM $table_name WHERE  city ='%s' and state ='%s' ",$data[0],$data[1]));

	$postcodes_get_cunt = count($postcitycodes_get);
					
					 if(!$postcodes_get_cunt){						 
						$wpdb->insert($table_name, array(
							 'city' => $data[0],	
							 'state' => $data[1],
							 'phone' => $data[2],
							 'url' => $data[3],
							 'create_date' => date('Y-m-d h:i:s')
						));
						if($num==0){ $ide = $wpdb->insert_id; }
						$num++;
						}else{
							$cityAlready .= $data[0].', ';
						}
	}else{
	$postcodes_get = $wpdb->get_col($wpdb->prepare("SELECT state  FROM $table_name WHERE  state ='%s' and city=''",$data[1]));
		$postcodes_get_cunt = count($postcodes_get);
					
					 if(!$postcodes_get_cunt){						 
						$wpdb->insert($table_name, array(
							 'city' => $data[0],	
							 'state' => $data[1],
							 'phone' => $data[2],
							 'url' => $data[3],
							 'create_date' => date('Y-m-d h:i:s')
						));
						if($num==0){ $ide = $wpdb->insert_id; }
						$num++;
						}else{
							$cityAlready .= $data[1].', ';
						}

	}
					
					
					 
						
					}
					if($cityAlready){
						$msg = $cityAlready. ' already added';
						}
					fclose($handle);
				}				
					$message = $num .' Location added successfully. '.$msg;
					$code = 'updated';
                    }else{
					$message = ' Please upload only csv file';
					$code = 'error';
				}
			}
			
			
if(isset($_POST["edit_post"])){
	
		$city=$_POST["city"];
		$state=$_POST["state"];
		$phone=$_POST["phone"];
		$url=$_POST["url"];
		$id=$_POST["id"];
		
	if($city){
	$postcitycodes_get = $wpdb->get_col($wpdb->prepare("SELECT city FROM $table_name WHERE city = %s and state = %s and id !=%s",$city,$state,$id));
		$cityname = count($postcitycodes_get);
		if($cityname){
			 $message = 'city already exist';
			 $code = 'error';
		}else{

			$wpdb->update($table_name, array('city' => $city, 'state' =>$state, 'phone' =>$phone, 'url'=> $url), array('id'=>$id),array('%s','%s', '%s','%s','%s'));
				$message = 'Location updated successfully';
				$code = 'updated';
			}
}else{
	$postcodes_get = $wpdb->get_col($wpdb->prepare("SELECT state FROM $table_name WHERE state = %s and city ='' and id !=%s",$state,$id));	
	$statename = count($postcodes_get);
		if($statename){
			 $message = 'State already exist';
				 $code = 'error';
		}else{
			$wpdb->update($table_name, array('city' => $city, 'state' =>$state, 'phone' =>$phone, 'url'=> $url), array('id'=>$id),array('%s','%s', '%s','%s','%s'));
				$message = 'Location updated successfully';
				$code = 'updated';

		}
}
}


if(isset($_POST["addpost"])){

		
		$city=$_POST["city"];
		$state=$_POST["state"];
		$phone=$_POST["phone"];
		$url=$_POST["url"];



if($city){
	$postcitycodes_get = $wpdb->get_results("SELECT city FROM $table_name WHERE city = '".$city."' and state = '".$state."'");
		$cityname = count($postcitycodes_get);
		if($cityname){
			 $message = 'city already exist';
			 $code = 'error';
		}else{

			$wpdb->insert($table_name, array('city' => $city, 'state' =>$state, 'phone' =>$phone, 'url'=> $url,'create_date'=>date('Y-m-d h:i:s')) );
				$message = 'Location added successfully';
				$code = 'updated';
		}
}else{
$postcodes_get = $wpdb->get_results("SELECT state FROM $table_name WHERE state = '".$state."' and city =''");	
	$statename = count($postcodes_get);
		if($statename){
			 $message = 'State already exist';
				 $code = 'error';
		}else{
			$wpdb->insert($table_name, array('city' => $city, 'state' =>$state, 'phone' =>$phone, 'url'=> $url,'create_date'=>date('Y-m-d h:i:s')) );
				$message = 'Location added successfully';
				$code = 'updated';

		}
}

	
		/*$postcodes_get = $wpdb->get_col($wpdb->prepare("SELECT city  FROM $table_name WHERE city = %s",$city));	
		
		$postcodes_get_cunt = count($postcodes_get);
		 if($postcodes_get_cunt){
				 $message = 'city already exist';
				 $code = 'error';
		 }else{			   
				$wpdb->insert($table_name, array('city' => $city, 'state' =>$state, 'phone' =>$phone, 'url'=> $url,'create_date'=>date('Y-m-d h:i:s')) );
				$message = 'Location added successfully';
				$code = 'updated';
			 
		 } */
 
    }
$hrmlData .= '<div class="response">'; 
if($message){
		$hrmlData .= '<div class="wpaas-notice notice '.$code.'">
			<p><strong>Note: &nbsp;</strong>'.$message.'</strong>.</p>
		</div>';
		
	}
	$hrmlData .= '</div>'; 
	/*******insert zip code start***************/
	
	
	/*************location list start***************/
	$hrmlData .= '<div class="table-responsive">
			<table class="wp-list-table widefat fixed striped posts" id="providers-zip"  cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
		<thead>
			<tr><th width="4%"><input type="checkbox" id="del_deletePostcodes"></th>
			<th width="6%">ID</th>
			
			<th width="18%">City Name</th>
			<th width="10%">State</th>
			<th width="12%">Phone Number</th>
			<th>URL</th>
			<th width="15%">Date</th>
			<th  width="10%">Action</th></tr>
		</thead>
		<tbody>';

	
$results = $wpdb->get_results('SELECT * FROM '.$table_name);

 if ( $results ){  
		foreach($results as $result) {        
				$hrmlData .= '<tr id="row-id-'.$result->id.'">
				<td><input type="checkbox" class="deletePostcodesRow" value="'.$result->id.'"></td>
				<td >'.$result->id.'</td>
				
				<td class="city">'.$result->city.'</td>
				<td class="state">'.$result->state.'</td>
				<td class="phone">'.$result->phone.'</td>
				<td class="urlss">'.$result->url.'</td>
				<td>'.$result->create_date.'</td>
				<td><a id="'.$result->id.'" class="btn_custom dashicons dashicons-trash" onClick="reply_click(this.id)"></a> | <a id="'.$result->id.'" class="btn_custom dashicons dashicons-edit" onClick="edit_click(this.id)"></a></td>
				</tr>';
		}
}else{
     _e( 'Sorry, no location found.' );
 }
$hrmlData .= '</tbody>
</table></div><div class="wpaas-notice notice updated">
			<p><strong>Note: &nbsp;</strong>To Display the phone number, based on the user current location you can use this shortcode :  [location_phone_number default-number="XXX-XXX-XXX"]</p>
		</div></div>';	
		
		//Edit popup
	$hrmlData .='<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">      
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Location</h4>
        </div>
        <div class="modal-body">
          <form class="edit_form" method="post">
		  <div class="forms-group">
			<label>City Name : </label>
			<input type="text" name="city" value="" class="ecity"/>
		  </div>
		  
		  <div class="forms-group">			
			<label>State : </label>
			<input type="text" name="state" value="" class="estate"/>
		  </div>
		  
		  <div class="forms-group">			
			<label>Phone Number : </label>
			<input type="text" name="phone" value="" class="ephone"/>
		  </div>
		  
		  <div class="forms-group">			
			<label>URL : </label>
			<input type="text" name="url" value="" class="eurl"/>
			<input type="hidden" name="id" value="" class="eid"/>
		  </div>
		  
		  <div class="forms-group">
			<input type="submit" name="edit_post" value="Submit" class="submitBtn btn btn-success"/>
		  </div>
		  
		  </form>
        </div>
        
      </div>
      
    </div>
  </div>';
		
		echo $hrmlData;
}

/**
* Converting data to CSV
*/

add_action('admin_init','download_csv');
function download_csv()
{
	if(isset($_POST["Export"])){
		generate_csv();
	}
}

function generate_csv()
{
	$data_rows = array();
    global $wpdb;
    global $table_name;
	ob_start();
    $domain = $_SERVER['SERVER_NAME'];
    $filename = 'Location-' . $domain . '-' . time() . '.csv';
	
	 $header_row = array(
        'City',
        'State',
        'Phone',
        'URL',
        'Created Date'
    );
   
    $sql = 'SELECT * FROM ' . $table_name;
    $users = $wpdb->get_results( $sql, 'ARRAY_A' );
	if($users){	
			header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Pragma: no-cache');
            header('Expires: 0');
			$file = fopen('php://output', 'w');
			fputcsv($file, $header_row);
			foreach ( $users as $user ) {
				$row = array(
					$user['city'],
					$user['state'],
					$user['phone'],
					$user['url'],
					$user['create_date']
				);
				 fputcsv($file, $row);				
			}
	}
	 ob_end_flush();
	exit;
    
   
}


}
/***********************************************/
/****DELETE THE WP LOCATION FUNCTION START******/
/***********************************************/

if (!function_exists('DWPL')) {
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

}


/***********************************************/
/****ADD JQUERY FILE IN ADMIN BACKEND **********/
/***********************************************/

if (!function_exists('location_scripts')) {

function location_scripts() {

    wp_enqueue_script( 'location-script', plugin_dir_url( __FILE__ ) . 'assets/js/wp-locations.js', array( 'jquery' ), time(), true );
    wp_enqueue_style( 'location-style', plugin_dir_url( __FILE__ ) . 'assets/css/wp-locations.css' );
}
add_action( 'admin_enqueue_scripts', 'location_scripts' );
}

function add_datatables_scripts() {
wp_register_script('bootstraps', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js', array('jquery'), true);
wp_enqueue_script('bootstraps');
  
wp_register_script('datatables', 'https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js', array('jquery'), true);
wp_enqueue_script('datatables');
  
wp_register_script('datatables_bootstrap', 'https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js', array('jquery'), true);
wp_enqueue_script('datatables_bootstrap');
}
  
function add_datatables_style() {
wp_register_style('bootstrap_style', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
wp_enqueue_style('bootstrap_style');
  
wp_register_style('datatables_style', 'https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css');
wp_enqueue_style('datatables_style');
}
  
add_action('admin_enqueue_scripts', 'add_datatables_scripts');
add_action('admin_enqueue_scripts', 'add_datatables_style');


/*******************************************************/
/********IMPORT ZIPCODE CITY STATE DATA*****************/
/*******************************************************/

if (!function_exists('importData')) {
 function importData() {
 global $wpdb;
 global $table_name;
 $msg ='';
			if(isset($_POST["Import"])){	
				$num=0;
				$cityAlready = '';
				$filename=$_FILES["file"]["tmp_name"];
				if (($handle = fopen($filename, "r")) !== FALSE) {
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					
					$postcodes_get = $wpdb->get_col($wpdb->prepare("SELECT city  FROM $table_name WHERE city = %s",$data[0]));
					//print_r($postcodes_get);
					 $postcodes_get_cunt = count($postcodes_get);
					
					 if($postcodes_get_cunt){		 
						$wpdb->insert($table_name, array(
							 'city' => $data[0],	
							 'state' => $data[1],
							 'phone' => $data[2],
							 'url' => $data[3],
							 'create_date' => date('Y-m-d h:i:s')
						));
						if($num==0){ $ide = $wpdb->insert_id; }
						$num++;
					 }else{
							$cityAlready .= $data[0].', ';
						}
					}
					if($cityAlready){
						$msg = $cityAlready.' Already Added';
						}
					fclose($handle);
				}				
				$html = array('status'=>'success',
								'message'=> $num.' rows added.'. $msg);
				echo json_encode($html);
			}
}

}
function states(){

	$statesname = array(
	'AL'=>'ALABAMA',
	'AK'=>'ALASKA',
	'AS'=>'AMERICAN SAMOA',
	'AZ'=>'ARIZONA',
	'AR'=>'ARKANSAS',
	'CA'=>'CALIFORNIA',
	'CO'=>'COLORADO',
	'CT'=>'CONNECTICUT',
	'DE'=>'DELAWARE',
	'DC'=>'DISTRICT OF COLUMBIA',
	'FM'=>'FEDERATED STATES OF MICRONESIA',
	'FL'=>'FLORIDA',
	'GA'=>'GEORGIA',
	'GU'=>'GUAM GU',
	'HI'=>'HAWAII',
	'ID'=>'IDAHO',
	'IL'=>'ILLINOIS',
	'IN'=>'INDIANA',
	'IA'=>'IOWA',
	'KS'=>'KANSAS',
	'KY'=>'KENTUCKY',
	'LA'=>'LOUISIANA',
	'ME'=>'MAINE',
	'MH'=>'MARSHALL ISLANDS',
	'MD'=>'MARYLAND',
	'MA'=>'MASSACHUSETTS',
	'MI'=>'MICHIGAN',
	'MN'=>'MINNESOTA',
	'MS'=>'MISSISSIPPI',
	'MO'=>'MISSOURI',
	'MT'=>'MONTANA',
	'NE'=>'NEBRASKA',
	'NV'=>'NEVADA',
	'NH'=>'NEW HAMPSHIRE',
	'NJ'=>'NEW JERSEY',
	'NM'=>'NEW MEXICO',
	'NY'=>'NEW YORK',
	'NC'=>'NORTH CAROLINA',
	'ND'=>'NORTH DAKOTA',
	'MP'=>'NORTHERN MARIANA ISLANDS',
	'OH'=>'OHIO',
	'OK'=>'OKLAHOMA',
	'OR'=>'OREGON',
	'PW'=>'PALAU',
	'PA'=>'PENNSYLVANIA',
	'PR'=>'PUERTO RICO',
	'RI'=>'RHODE ISLAND',
	'SC'=>'SOUTH CAROLINA',
	'SD'=>'SOUTH DAKOTA',
	'TN'=>'TENNESSEE',
	'TX'=>'TEXAS',
	'UT'=>'UTAH',
	'VT'=>'VERMONT',
	'VI'=>'VIRGIN ISLANDS',
	'VA'=>'VIRGINIA',
	'WA'=>'WASHINGTON',
	'WV'=>'WEST VIRGINIA',
	'WI'=>'WISCONSIN',
	'WY'=>'WYOMING',
	'AE'=>'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
	'AA'=>'ARMED FORCES AMERICA (EXCEPT CANADA)',
	'AP'=>'ARMED FORCES PACIFIC',
	'CHD' => 'CHANDIGARH',
	'PB' => 'PUNJAB'
);
	
	return $statesname;
}

/*************************************************/
/********GET LOCATION BY LOOKUP API***************/
/*************************************************/
if (!function_exists('lookupAPI')) {
function lookupAPI(){
	//$user_ip = $_GET['ip'];
	$user_ip = getenv('REMOTE_ADDR');
 //$user_ip = '134.201.250.155';
	
			 $geo = json_decode(file_get_contents("http://extreme-ip-lookup.com/json/$user_ip"));
			 //print_r($geo); exit;
			 $country = $geo->country;
			$city = $geo->city;
			 $region = $geo->region;
			 $statesabb = states();
			$statenames = array_search(strtoupper($region),$statesabb);
			 $ipType = $geo->ipType;
			 $businessName = $geo->businessName;
			 $businessWebsite = $geo->businessWebsite;

	return $key = array('state'=>$statenames,'city'=>$city);
}
}


/*************************************************/
/********GET PHONE NUMBER BY KEY******************/
/*************************************************/
if (!function_exists('getPhone')) {
function getPhone($key){

	global $wpdb;
	global $table_name;
	$data = '';
	if(isset($key['city'])&&isset($key['state'])){
		//$column = 'city';
		$cvalue = $key['city'];
		$city = str_replace('+',' ',$cvalue);
		$svalue = $key['state'];
		$state = str_replace('+',' ',$svalue);
	}
	
	
		$postcodes_get = $wpdb->get_results($wpdb->prepare("SELECT phone,url FROM $table_name WHERE city = %s and state= %s limit 1",$city,$state));	
		//var_dump($city.','.$state);exit;

		$postcodes_get_cunt = count($postcodes_get);

		 if($postcodes_get_cunt){
			  $data =  array('phone'=>$postcodes_get[0]->phone,'url'=>$postcodes_get[0]->url);			
		 }else{
		$poststcodes_get = $wpdb->get_results($wpdb->prepare("SELECT phone,url FROM $table_name WHERE state = %s and city = '' limit 1",$state));	
		
		$poststcodes_get_cunt = count($poststcodes_get);
		if($poststcodes_get_cunt){
		$data =  array('phone'=>$poststcodes_get[0]->phone,'url'=>$poststcodes_get[0]->url);
		}	
		 }
		
		 return $data;
}
}


/*************************************************/
/********CHANGE PHONE NUMBER FORMAT***************/
/*************************************************/
if (!function_exists('phone_number_format')) {
function phone_number_format($number) {
  // Allow only Digits, remove all other characters.
  $number = preg_replace("/[^\d]/","",$number);
 
  // get number length.
  $length = strlen($number);
 
 // if number = 10
 if($length == 10) {
  $number = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "($1) $2-$3", $number);
 }
  
  return $number;
 
}
}


/*******************************************************/
/********SHOW PHONE NUMBER BY CITY SHORTCODE************/
/*******************************************************/
if (!function_exists('SPN')) {
 function SPN($atts ) {	 
			$key = lookupAPI();
			$phone =  getPhone($key);

		
if ( is_front_page() && isset($phone['url']) ) { 

	wp_redirect( $phone['url'] );	
}
     
			if(empty($phone['phone'])){	
				if($atts['default-number']){
					$phoneNumber =  $atts['default-number'];
				}else{
					$phoneNumber =  $phone['phone'];
				}		
			}else{
            $phoneNumber =  $phone['phone'];
            }
	 
	$phoneNumber = phone_number_format($phoneNumber);
    return '<a href="tel:+1'.$phoneNumber.'">'.$phoneNumber.'</a>';
}
add_shortcode('location_phone_number', 'SPN');
}