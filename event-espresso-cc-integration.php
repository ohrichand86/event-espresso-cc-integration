<?php
/**
 * Plugin Name: Event Espresso CC Integration
 * Author: Codinggang (Gaurav Shukla)
 * Version: 1.0.0
 */


function ee_cc_hook( EE_SPCO_Reg_Step_Attendee_Information $spco_obj, $spc_data ){

	$spco_registrations = $spco_obj->checkout->transaction->registrations( $spco_obj->checkout->reg_cache_where_params, TRUE );
	
	if( !empty ( $spco_registrations ) ){
		 foreach( $spco_registrations as $registration ){
			if ( $registration instanceof EE_Registration ){
			
				// get the answer to the custom CC optin question
				$answers = $registration->answers();
				
				
				
				if( !empty ( $answers ) ){
					
					foreach( $answers as $answer ){
						if ( $answer instanceof EE_Answer ){
							$question = $answer->question();
							$question_answers[ $question->ID() ] = $answer->pretty_value();
						}
					}
				}
			//	$optin = $question_answers['44'];
				
		/*	if( $optin === "Yes" ){ */
					
					// get the attendee
					$attendee = $registration->attendee();

					if ( $attendee instanceof EE_Attendee ){
						
						// event information
						$EVT_ID = $registration->event_ID(); // not needed, but left for reference
						$event_name = $registration->event()->name();

						// attendee information
						$att_id = $attendee->ID(); // not needed, but left for reference
						$att_email = $attendee->email();
						$fname = $attendee->fname();
						$lname = $attendee->lname();
						
						$data = array(
							'evt_name' => $event_name,
							'att_email' => $att_email,
							'fname' => $fname,
							'lname' => $lname
						);
						
						
						cc_integration_attendee( $data );
					
					}
				/* } */
			}
		}
	}
}
add_action(  'AHEE__EE_Single_Page_Checkout__process_attendee_information__end', 'ee_cc_hook', 10, 2 );



function cc_integration_attendee( $details ){
    
   
$api_key = 'sc8etqy3htwqfcqwkgf6zzrx';
$access_token = 'd41e2e18-c878-489e-9ec4-bb6c7a8efc78';

    
    $data['confirmed'] = false;
    $data['email_addresses'][0]['email_address'] = $details['att_email'];
    $data['first_name'] = $details['fname'];
    $data['last_name'] = $details['lname'];
   
	$list_member_id = integration_ftech_list_detail($details['evt_name']);
	
	if($list_member_id)
	{
		
		$data['lists'][0]['id'] = $list_member_id;
	}
	else
	{
	
		$list_member_id = integration_create_new_list( $details['evt_name'] );
		$data['lists'][0]['id'] = $list_member_id;
	}
	//echo $list_member_id;
	
	$result = check_email_existence($details['att_email']);
	//echo $result.'check';
	if($result)
	{
	
	$posturl = "https://api.constantcontact.com/v2/contacts/".$result."?action_by=ACTION_BY_OWNER&api_key=".$api_key;
	$method = 'PUT';
	}
	else
	{
	$posturl = "https://api.constantcontact.com/v2/contacts?action_by=ACTION_BY_OWNER&api_key=".$api_key;
	$method = 'POST';
	}
    $jsonstring = json_encode($data);



	
	

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $posturl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json",'Authorization: Bearer '.$access_token.'')); //check change here
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// this need to be 1

$response = curl_exec($ch);
if (FALSE === $response)
throw new Exception(curl_error($ch), curl_errno($ch));
//echo '<pre>'; print_r($response);
//die;
curl_close($ch);
 


	
	
}


function check_email_existence($useremail){
$api_key = 'sc8etqy3htwqfcqwkgf6zzrx';
$access_token = 'd41e2e18-c878-489e-9ec4-bb6c7a8efc78';
$posturl = "https://api.constantcontact.com/v2/contacts?email=".$useremail."&status=ALL&modified_since=2017&api_key=".$api_key."";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $posturl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json",'Authorization: Bearer '.$access_token.'')); //check change here
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// this need to be 1

$response = curl_exec($ch);

$response_data =json_decode($response);
$contact_status ="";
	
foreach ($response_data  as $key => $value) {

foreach($value as $value1)
{

$contact_status = $value1->id;

}

}

	
return $contact_status;	
	
	
}



function integration_ftech_list_detail($evt_name_detail){
$api_key = 'sc8etqy3htwqfcqwkgf6zzrx';
$access_token = 'd41e2e18-c878-489e-9ec4-bb6c7a8efc78';
$posturl = "https://api.constantcontact.com/v2/lists?modified_since=2019&api_key=".$api_key."";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $posturl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json",'Authorization: Bearer '.$access_token.'')); //check change here
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// this need to be 1

$response = curl_exec($ch);
//echo '<pre>'; print_r($response);
//die;
$response_data =json_decode($response);
$list_member_id="";
	
foreach ($response_data  as $list_data) {
	
	if($list_data->name == $evt_name_detail)
	{
	
		$list_member_id = $list_data->id;
	}
}

	
return $list_member_id;	
	
	
}



function integration_create_new_list($evt_name_detail){
$api_key = 'sc8etqy3htwqfcqwkgf6zzrx';
$access_token = 'd41e2e18-c878-489e-9ec4-bb6c7a8efc78';
$data['name'] = $evt_name_detail;
$data['status'] = "ACTIVE";
$jsonstring = json_encode($data);
$posturl = "https://api.constantcontact.com/v2/lists?api_key=".$api_key."";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $posturl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json",'Authorization: Bearer '.$access_token.'')); //check change here
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// this need to be 1
$response = curl_exec($ch);
$response_data =json_decode($response);
$list_member_id="";
$list_member_id = $response_data->id;
return $list_member_id;	
}