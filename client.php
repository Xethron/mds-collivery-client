<html>
<body>

<?php

if (!isset($_SESSION)) {
	session_start();
}

function put_options_key_data($ARRAY, $KEY_FOR_DISPLAY = null, $selected = null) {
	if (is_array($ARRAY)) {
		foreach ($ARRAY as $ID => $DATA) {
			if ($KEY_FOR_DISPLAY != null) {
				if ($selected == $ID) {
					print "<option value=\"$ID\" selected>" . htmlspecialchars($DATA[$KEY_FOR_DISPLAY]) . "</option>";
				} else {
					print "<option value=\"$ID\">" . htmlspecialchars($DATA[$KEY_FOR_DISPLAY]) . "</option>";
				}
			} else {
				if ($selected == $ID) {
					print "<option value=\"$ID\" selected>" . htmlspecialchars($DATA) . "</option>";
				} else {
					print "<option value=\"$ID\">" . htmlspecialchars($DATA) . "</option>";
				}
			}
		}
	}
}

error_reporting(E_ALL & ~E_NOTICE);
$quote_url = "http://quote.collivery.co.za";
// Please create a new user in your account for the web service (Login to quote.collivery.co.za and add a user in User Admin)
$email = "youremail@yourdomain.co.za";
$password = "yourpassword";

// you can also use the demo account for testing of adding colliverys as these are ignored by our system.
$email = "demo@collivery.co.za";
$password = "demo";

$client = new SoapClient("http://www.collivery.co.za/webservice.php?wsdl");
// prevent caching of the wsdl
ini_set("soap.wsdl_cache_enabled", "0");

// The collivery system uses a normaized database. getting prices and and adding colliverys requires passing existing
// address id's to the functions. The first part of this example explains how to get lists of those existing addresses
// or services and also how to add new addresses.

//print_r($_SESSION);
print("<pre>");
$authenticate = $client -> Authenticate($email, $password, $_SESSION['token']);
// When you authenticate, a token is returned. Store this token in a $_SESSION and use it for every Function Call.
$_SESSION['token'] = $authenticate['token'];
//print_r($authenticate);
if (!$authenticate['token']) {
	exit("Authentication Error : " . $authenticate['access']);
}

// To get a quick price using the town BriefName instead of an existing AddressID.
$data = array("from_town_brief" => "JNB", "to_town_brief" => "PTA", "service_type" => 3, "num_package" => 1, "clientID" => 116, "collection_time" => 1312192800, "delivery_time" => 1312466400);
$price = $client -> GetPricing($data, $_SESSION['token']);
print_r($price);

//$client_id = $authenticate['client_id'];
// Default Collection address to be used for adding colliverys.
// You can change this default in quote.collivery.co.za under Profile menu.
$default_address_id = $authenticate['DefaultAddressID'];
// Once you have the default address, please use the getCpContacts function to get a list of contacts for this address.
// Contacts are a master->detail realtionship to an address. You can not add a collivery for a contact that does not belong
// to the selected address.
$default_contacts = $client -> getCpContacts($default_address_id, $_SESSION['token']);
//print_r($default_contacts['results']);
$first_contact_id = each($default_contacts['results']);
$default_contact_id = $first_contact_id[0];
print("</pre>");
// Now you have the default collection or deivery point and contact for this client.

//Function to Add a new address from POST form at the end of this example
if ($_POST['AddAddr']) {
	print("<pre>");
	print_r($_POST);
	print("</pre>");
	$cpid = $client -> AddAddress($_POST, $authenticate['token']);

	if ($cpid['error_message']) {
		print("Error - " . $cpid['error_message']);
	} else {
		print("Address added with ID $cpid[results]");
		// Now add contact to the addres using the returned address ID
		$_POST['cpid'] = $cpid['results'];
		$ctid = $client -> AddContact($_POST, $authenticate['token']);
		print("Contact added with ID $ctid[results]");
		unset($_POST);
	}
}

if ($_POST['ShowPrice']) {
	print("<pre>");
	// Test Array of parcels to get price
	$parcels[0] = array("length" => 20, "width" => 20, "height" => 10, "weight" => 5);
	$parcels[1] = array("length" => 20, "width" => 15, "height" => 30, "weight" => 7);
	$parcels[2] = array("length" => 15, "width" => 25, "height" => 40, "weight" => 12);
	$_POST['parcels'] = $parcels;
	print_r($_POST);

	// 1 = exclude, -1 = Don't exclude. This variable is used to control the use of weekends in the calculation
	// when a collivery's delivery time needs to be changed, must weekends be used as an optional delivery time
	// Saturday deliverys incure surcharges, so this option allows you to exclude weekends. This will only affect
	// Colliverys where the system needs to move the delivery time, not when you request a delivery on a Saturday
	// and there is sufficient time and the Town is serviced on that day.

	$_POST['exlude_weekend'] = 1;

	// First, validate the collivery data
	// Be aware that collection or delivery dates may change due to service contraints of Towns or Holidays
	// There is also a minimum time frame for every From -> To Town combiniation.

	$VALIDATE = $client -> CheckColliveryData($_POST, $authenticate['token']);
	// check if it passed validation. Only request pricing if validation passed.
	if ($VALIDATE['results']['REJECTED']) {
		print("<div style=\"font-size:10pt; color:red;\">Collivery rejected : " . $VALIDATE['results']['REJECTED_REASON'] . "</div>");
	} else {
		$PRICING = $client -> GetPricing($VALIDATE['results'], $authenticate['token']);
	}
	print_r($VALIDATE['results']);
	//print_r($PRICING);
	print("</pre>");
}

if ($_POST['AddCollivery']) {

	// First, validate the collivery data again ncase of any data changing.
	// Dimmensions are in cm, Weight is in Kg's
	$parcels[0] = array("length" => 20, "width" => 20, "height" => 10, "weight" => 5);
	$parcels[1] = array("length" => 20, "width" => 15, "height" => 30, "weight" => 7);
	$parcels[2] = array("length" => 15, "width" => 25, "height" => 40, "weight" => 12);
	$_POST['parcels'] = $parcels;
	print("<pre>");
	print_r($_POST);
	print("</pre>");
	$VALIDATE = $client -> CheckColliveryData($_POST, $authenticate['token']);
	// check if it passed validation. Only add collivery if validation passed.
	if ($VALIDATE['results']['REJECTED']) {
		print("<div style=\"font-size:10pt; color:red;\">Collivery rejected : " . $VALIDATE['results']['REJECTED_REASON'] . "</div>");
	} else {
		// POSTED data sent to the Validate process is returned in the array. Use this returned array to add your
		// collivery, and not the POSTED data as some data may have changed, such as delivery time.
		$NEW_COLLIVERY = $client -> AddCollivery($VALIDATE['results'], $authenticate['token']);
		if ($NEW_COLLIVERY['results']) {
			$collivery_id = $NEW_COLLIVERY['results']['collivery_id'];
			// Collivery is added, but in waiting clients acceptance. Update to quote accepeted.
			$send_emails = 1;
			// -1 = Don't send, 1 = Send Standard Acceptance emails to requestor, collection contact
			// and delivery contact with information regarding this collivery.
			$client -> AcceptCollivery($collivery_id, $send_emails, $authenticate['token']);
			print("<div align=\"center\">");
			print("<span style=\"color:green; font-size:12pt;\">Collivery Added : Waybill No. $collivery_id</span>");
			print("<pre>");
			print_r($NEW_COLLIVERY);
			print("</pre>");
			// You can optionally show the waybill for download if the person requesting is the collection point.
			// Please note the "noprint=1" in the request is required.
			print("&nbsp;&nbsp;<input type=\"image\" width=\"20\" height=\"20\" src=\"ico/waybill.png\" onClick=\"window.open('" . $quote_url . "/waybillmap.php?colliveryid=$collivery_id&noprint=1','ColliveryWaybill','width=800,height=600,toolbar=yes,directories=no,status=no,menubar=no,scrollbars=yes')\">");
			print("</div>");

			unset($_POST);
		}
	}
	//print("</pre>");
}

print("<form name=\"AddColliveryForm\" method=\"POST\" action=\"$_SERVER[PHP_SELF]\">");
print("<div style=\"font-size:10pt;\">");
$MyAddress = $client -> getClientAddresses(null, null, $default_address_id, null, $authenticate['token']);
//print_r($MyAddress);
print("<fieldset>");
print("<legend style=\"font-weight:bold;\">Collection Details</legend>");
print("Default Collection address : " . $MyAddress['results']['nice_address']);
print("<input type=\"hidden\" name=\"collivery_from\" value=\"" . $MyAddress['results']['colliveryPoint_PK'] . "\">");

/*$MyContacts = $client->getCpContacts($MyAddress['results']['colliveryPoint_PK'],$authenticate['token']);

 print_r($MyContacts);*/
// You should hard code one of these contacts. Recommended to add a new user and contact to your account
// to identify the collivery has been added by the web service, and not manaully via the website.
print("<br>Default Collection contact : " . $default_contacts['results'][$default_contact_id]['nice_contact']);
print("<input type=\"hidden\" name=\"contact_from\" value=\"" . $default_contact_id . "\">");
print("</fieldset>");

// $townbrief and $suburb are optional filters for getting a client existing addresses
// $cpid Returns only the requested Collivery Point by it's ID
// $customID Returns all address matching this customID (If your client has multiple delivery points)
$getAddressArray = $client -> getClientAddresses($townbrief = null, $suburb = null, $cpid, $customID, $authenticate['token']);
$Addresses = $getAddressArray['results'];
//print_r($getAddressArray);
print("<fieldset>");
print("<legend style=\"font-weight:bold;\">Delivery Details</legend>");
print("<select name=\"collivery_to\" onchange=\"submit();\">");
put_options_key_data($Addresses, "nice_address", $_POST['collivery_to']);
print("</select>");

$cpContacts = $client -> getCpContacts($_POST['collivery_to'], $authenticate['token']);
//print_r($cpContacts);

print("<br>Delivery contact : ");
if (is_array($cpContacts)) {
	print("<select name=\"contact_to\">");
	put_options_key_data($cpContacts['results'], "nice_contact", $_POST['contact_to']);
	print("</select>");
} else {
	print(" Select Delivery address first");
}
print("</fieldset>");

print("<fieldset>");
print("<legend style=\"font-weight:bold;\">Package Information</legend>");
print("<div>");
$ParcelType = $client -> getParcelTypes($authenticate['token']);
foreach ($ParcelType['results'] as $key => $value) {
	if ($_POST['collivery_type'] == $key) { $checked = "checked";
	} else { $checked = "";
	}
	print("<input type=\"radio\" name=\"collivery_type\" value=\"$key\" $checked>$value[type_text]&nbsp;&nbsp;&nbsp;");
}
print("</div>");
print("<div>");
print("Num Packages : <input type=\"text\" size=\"6\" name=\"num_package\" value=\"$_POST[weight]\">");
print("&nbsp;&nbsp;&nbsp; Weight : <input type=\"text\" size=\"6\" name=\"weight\" value=\"$_POST[weight]\">");
print("&nbsp;&nbsp;&nbsp; Volumetric Weight : <input type=\"text\" size=\"6\" name=\"vol_weight\" value=\"$_POST[vol_weight]\">");
print("</div>");
print("</fieldset>");

// You may also pass an array of parcel dimensions into the Validation and Add collivery functions.
// The validation process will calculate the volumetric sizes of each box and return to you in an array
// The total weight, volumetric and number of parcels calculated will be used to calculate the price of the collivery.
$parcels[0] = array("length" => 20, "width" => 20, "height" => 10, "weight" => 5);
$parcels[1] = array("length" => 20, "width" => 15, "height" => 30, "weight" => 7);
$parcels[2] = array("length" => 15, "width" => 25, "height" => 40, "weight" => 12);

//print_r($ParcelType);
/* print("Parcel Type : ");
 print("<select name=\"parcel_type\">");
 put_options_key_data($ParcelType['results'],"type_text",$_POST['parcel_type']);
 print("</select>");
 print("</fieldset>"); */

$ServicesType = $client -> getServices($authenticate['token']);
if (!$_POST['service']) { $_POST['service'] = 3;
}
print("<fieldset>");
print("<legend style=\"font-weight:bold;\">Service Type</legend>");
foreach ($ServicesType['results'] as $key => $value) {
	if ($_POST['service'] == $key) { $checked = "checked";
	} else { $checked = "";
	}
	print("<input type=\"radio\" name=\"service\" value=\"$key\" $checked >");
	print("$value &nbsp;&nbsp;&nbsp;");
}
print("</fieldset>");

print("<fieldset>");
print("<legend>Extra Info</legend>");
print("Cover to value of :&nbsp;");
// Standard Risk Cover is R12 per R1000 value
$insur_val = $client -> getCoverValues($authenticate['token']);
print("<select name=\"insurance\">");
put_options_key_data($insur_val['results'], null, $_POST['insurance']);
print("</select>");
print("&nbsp;&nbsp;Customer Ref : ");
print("<input size=\"20\" type=\"text\" name=\"cust_ref\" value=\"$_POST[cust_ref]\">");
print("Your Waybill No. <input size=\"20\" type=\"text\" name=\"ColCustomID\" value=\"$_POST[ColCustomID]\">");
print("</fieldset>");
print("<div align=\"center\">");
if ($PRICING['results']['Total'] > 1) {
	print("<input type=\"submit\" name=\"AddCollivery\" value=\"Add Collivery\">");
}
print("<input type=\"submit\" name=\"ShowPrice\" value=\"Show Price\">");
print("</div>");

print("</div>");
print("</form>");

if ($PRICING['results']['Total']) {
	print("<div align=\"center\" style=\"font-size:12pt; color:green;\">Collivery Price : " . sprintf("R%2.2f", $PRICING['results']['Total']) . "</div>");
}

// getTowns($client_id, $token)
// if you pass the $client_id, only the towns of the clients existing addresses are shown, else all Towns are show.
$Towns = $client -> getTowns(null, $authenticate['token']);
//print_r($Towns['results']);

// getSuburbs($client_id,$townbrief, $token)
// if you pass the $client_id, only the suburbs of the clients existing addresses are shown, else all Suburbs of the Town are show.
// $townbrief is required, but optional if $client_id is given.
if ($_POST['TownBrief']) {
	$Suburbs = $client -> getSuburbs(null, $_POST['TownBrief'], $authenticate['token']);
	//print_r($Suburbs['results']);
}

$CP_Type = $client -> getCPTypes($authenticate['token']);

?>

	<form name="addAddress" method="POST" action="<?=$_SERVER['PHP_SELF'] ?>">
		<div align="left" style="width:450pt;">
			<fieldset>
				<legend>New Address</legend>
				<table cellpadding=2 cellspacing=2> <!-- Delivery Table -->
					<tr>
						<td>Company Name:</td>
						<td><input type=text size=50 name="companyName" value="<?=$_POST['companyName'] ?>"></td>
					</tr>
					<tr>
						<td>Location Type</td>
						<td>
							<select name="CP_Type">
							<?php
							put_options_key_data($CP_Type['results'], null, $_POST['CP_Type']);
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Address ID:</td>
						<td>
							<input type="text" size="20" name="customID" value="<?=$_POST['customID'] ?>">
						</td>
					</tr>
					<tr>
						<td>Town:</td>
						<td>
							<select name="TownBrief" onchange="form.mapID.value=''; submit()">
								<?php
								put_options_key_data($Towns['results'], null, $_POST['TownBrief']);
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Suburb:</td>
						<td>
							<select name="mapID">
				
								<?php
								if ($_POST['TownBrief'] != '') {
									put_options_key_data($Suburbs['results'], null, $_POST['mapID']);
								} else {
									print("<option>Please select Town First</option>");
								}
								?>
							</select>
						</td>
					</tr>
				
					<tr>
						<td>Building Details:</td>
						<td><input size=50 type=text name="building" value="<?=$_POST['building'] ?>"></td>
					</tr>
					<tr>
						<td>Street No.</td>
						<td><input size=5 type=text name="streetnum" value="<?=$_POST['streetnum'] ?>"></td>
					</tr>
					<tr>
						<td>Street</td>
						<td><input size=50 type=text name="street" value="<?=$_POST['street'] ?>"></td>
					</tr>
				</table>  <!-- Delivery Table -->
			</fieldset>
		</div>
				
		<div align="left" style="width:450pt;">
			<fieldset>
				<legend>New contact</legend>
				<table cellpadding=2 cellspacing=2> <!-- Contact Table -->
					<tr>
						<td>Contact Person</td>
						<td>
							<input type="text" size="30" name="fname" value="<?print($_POST['fname']); ?>">
						</td>
					</tr>
					<tr>
						<td>Work number:</td>
						<td><input type="text" name="workNo" value="<?print($_POST['workNo']); ?>"></td>
					</tr>
					<tr>
						<td>Cell number:</td>
						<td><input type="text" name="cellNo" value="<?print($_POST['cellNo']); ?>"></td>
					</tr>
					<tr>
						<td>Email Address:</td>
						<td><input type=text size="30" name="emailAddr" value="<?print($_POST['emailAddr']); ?>"></td>
					</tr>
				</table> <!-- Contact Table  -->
			</fieldset>
		</div>
		
		<div align="center" style="width:450pt;">
			<input type=submit value="Add Address" name="AddAddr" class="textbut" onclick="return colpntValidate(GetQuoteForm);">
		</div>

	</form>
</body>
</html>
