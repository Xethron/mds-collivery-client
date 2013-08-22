<?php
/******************************************************************
 * MDS Collivery API :: Client Example                            *
 *                                                                *
 * It is very important that you first read the Documentation!    *
 * @link http://www.collivery.co.za/wsdocs/                       *
 ******************************************************************/
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>MDS Collivery API :: Client Example</title>
</head>
<body>
	<pre><?php
	
	if (!isset($_SESSION)) {
		session_start();
	}
	
	try{
		$client = new SoapClient("http://www.collivery.co.za/wsdl/v2");
	} catch (SoapFault $e){
		print_r($e);
	}
	$email = "demo@collivery.co.za";
	$password = "demo";
	$info = array(
		'name' => 'Default Application Name', // Unique Identifier for your application
		'version'=> '0.0.1', // Current version of your application
		'host' => 'Wordpress 3.6 - PHP 5.3.3', // Any extra information on what your application (Especially useful for plugins) are running on.
		// You can add any additional information here like the OS it is running on, etc...
	);

	/**
	 * Authenticate
	 * 
	 * Authenticate using your account email and password.
	 * Once authenticated, store the returned token in a session and use that token on every query.
	 * Tokens expire after 1 hour of inactivity.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#authenticate
	 */
	try{
		$authenticate = $client->authenticate($email, $password, $_SESSION['token'], $info);
	} catch (SoapFault $e){
		print_r($e);
	}

	print_r($authenticate);
	
	if (!$authenticate['token']) {
		exit("Authentication Error : " . $authenticate['error']);
	} else {
		$_SESSION['token'] = $authenticate['token'];
	}

	/**
	 * Get Towns
	 * 
	 * Returns a list of towns and their ID's for creating new addresses.
	 * Town can be filtered by country or province.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_towns
	 */
	echo "<h1>get_towns:</h1>";
	try{
		$result = $client->get_towns($_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Search Towns
	 * 
	 * Allows you to search for town and suburb names starting with the given string.
	 * The minimum string length to search is two characters.
	 * Returns a list of towns, suburbs, and the towns the suburbs belong to with their ID's for creating new addresses.
	 * The idea is that this could be used in an auto complete function.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#search_towns
	 */
	echo "<h1>search_towns:</h1>";
	try{
		$result = $client->search_towns('Pre', $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get Suburbs
	 * 
	 * Returns all the suburbs of a specific town and their ID's.
	 * Use this ID when creating a new address.
	 * Some towns only have a single suburb "CBD".
	 * This is mainly because the town is small enough to not require any additional information.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_suburbs
	 */
	echo "<h1>get_suburbs:</h1>";
	try{
		$result = $client->get_suburbs(248, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get Location Types
	 * 
	 * Returns the different location types (Private House, Business Premises, Mine) and their ID.
	 * This is important when you need an accurate price as some location types may incur a surcharge
	 * due to time spent during the delivery.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_location_types
	 */
	echo "<h1>get_location_types:</h1>";
	try{
		$result = $client->get_location_types($_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get Address
	 * 
	 * Returns all the information of a single address ID.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_address
	 */
	echo "<h1>get_address:</h1>";
	try{
		$result = $client->get_address(605978, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get Addresses
	 *
	 * Returns all the addresses belonging to a client and their Information.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_addresses
	 */
	echo "<h1>get_addresses:</h1>";
	try{
		$result = $client->get_addresses($_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get Contacts
	 * 
	 * Returns all the contacts belonging to a spesific address. 
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_contacts
	 */
	echo "<h1>get_contacts:</h1>";
	try{
		$result = $client->get_contacts(605978, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Add Address
	 * 
	 * Adds a new address to the your account on the collivery system. 
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#add_address
	 */
	/*echo "<h1>add_address:</h1>";
	$data = array(
		"company_name" => 'MDS Collivery',
		"building" => 'MDS House',
		"street" => '58c Webber St',
		"location_type" => 1, // Business Premises
		"suburb_id" => 1936, // Selby
		"town_id" => 147, // Johannesburg
		"custom_id" => 'CUST005',
		"full_name" => 'Bernhard Breytenbach',
		"phone" => '0123456789',
		"cellphone" => '0834567912',
		"email" => 'name@domain.co.za'
	);
	print_r($data);
	
	try{
		$result = $client->add_address($data, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Add Contact
	 * 
	 * Adds a new contact to an already existing address.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#add_address
	 */
	/*echo "<h1>add_contact:</h1>";
	$data = array(
		"address_id" => 605978,
		"full_name" => 'Bernhard Breytenbach',
		"phone" => '0123456789',
		"cellphone" => '0834567912',
		"email" => 'name@domain.co.za'
	);
	print_r($data);
	
	try{
		$result = $client->add_contact($data, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get POD
	 * 
	 * Returns the POD image for a given Waybill Number.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_pod
	 */
	/*echo "<h1>get_pod:</h1>";
	try{
		$result = $client->get_pod(, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);
	file_put_contents($result['pod']['filename'],base64_decode($result['pod']['file']));//*/

	/**
	 * Get a list of parcel images
	 * 
	 * Returns a list of available parcel images and their info for a given Waybill Number.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_parcel_image_list
	 */
	/*echo "<h1>get_parcel_image_list:</h1>";
	try{
		$result = $client->get_parcel_image_list(, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get Parcel Image
	 * 
	 * Returns a image encoded in BASE 64 and its information for a given parcel ID.
	 * 
	 * http://www.collivery.co.za/wsdocs/#get_parcel_image
	 */
	/*echo "<h1>get_parcel_image:</h1>";
	try{
		$result = $client->get_parcel_image('', $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);
	file_put_contents($result['image']['filename'],base64_decode($result['image']['file']));//*/

	/**
	 * Get Collivery Status
	 * 
	 * Returns the status tracking detail of a given Waybill number.
	 * If the collivery is still active, the estimated time of delivery will be provided.
	 * If delivered, the time and receivers name (if available) is returned. 
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_collivery_status
	 */
	/*echo "<h1>get_collivery_status:</h1>";
	try{
		$result = $client->get_collivery_status(, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get Parcel Types
	 * 
	 * Returns the available Parcel Type ID and value array for use in adding a collivery.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_parcel_types
	 */
	echo "<h1>get_parcel_types:</h1>";
	try{
		$result = $client->get_parcel_types($_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/

	/**
	 * Get Price
	 * 
	 * Returns an price for the current delivery.
	 * Very similar to the Validate Function, however,
	 * address ID, and Contact ID can be replaced with Town ID and Location Type.
	 * Although this method is extremely accurate,
	 * it isn't as accurate as the Validate function as time surcharges aren't calculated
	 * (Weekends, Holidays and After Hour Colliveries)
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#get_price
	 */
	echo "<h1>get_price:</h1>";
	$data = array(
		"collivery_from" => 951,
		"to_town_id" => 248,
		"to_location_type" => 15,
		"collivery_type" => 2,
		//"parcel_count" => 1,
		//"weight" => 6,
		"service" => 2,
		"cover" => 1,
		"custom_id" => "My_Cust_ID_Test007",
		"parcels" => array(
			array(
				"weight" => 1,
				"height" => 30,
				"length" => 17,
				"width" => 19,
			),
		),
	);
	print_r($data);
	$result = $client->get_price($data, $_SESSION['token']);
	print_r($result);//*/

	/**
	 * Validate Collivery
	 * 
	 * Returns the validated data array of all details pertaining to a collivery.
	 * This process validates the information based on services, time frames and parcel information.
	 * Dates and times may be altered during this process based on the collection and delivery towns service parameters.
	 * Certain towns are only serviced on specific days and between certain times.
	 * This function automatically alters the values.
	 * The parcels volumetric calculations are also done at this time.
	 * It is important that the data is first validated before a collivery can be added.
	 * 
	 * @link http://www.collivery.co.za/wsdocs/#validate_collivery
	 */
	echo "<h1>validate_collivery:</h1>";
	$data = array(
		"collivery_from" => 605978,
		"contact_from" => 629614,
		"collivery_to" => 605536,
		"contact_to" => 629151,
		"collivery_type" => 2,
		"parcel_count" => 2,
		"weight" => 6,
		"service" => 5,
		"cover" => 0,
		"parcels" => array(
			array(
				"weight" => 5,
				"height" => 10,
				"length" => 10,
				"width" => 10,
			),
			array(
				"weight" => 1,
				"height" => 30,
				"length" => 17,
				"width" => 19,
			),
		),
	);
	print_r($data);
	try{
		$result = $client->validate_collivery($data, $_SESSION['token']);
	} catch (SoapFault $e){
		print_r($e);
	}
	print_r($result);//*/
	
	?></pre>
</body>
</html>
