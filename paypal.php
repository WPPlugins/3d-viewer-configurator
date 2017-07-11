<?php

/********************************************
paypal.php

This file is called after the user clicks on a button during
the checkout process to use PayPal's Express Checkout. The 
user logs in to their PayPal account.

This file is called three times.

On the first pass, the code executes the if statement:

if (! isset ($token))

The code collects transaction parameters from the form 
displayed by SetExpressCheckout.html then constructs and 
sends a SetExpressCheckout request string to the PayPal 
server. The paymentType variable becomes the PAYMENTACTION 
parameter of the request string. The RETURNURL parameter 
is set to this file; this is how ReviewOrder.php is called 
twice.

On the second pass, the code executes the else statement.

On the first pass, the buyer completed the authorization in 
their PayPal account; now the code gets the payer details 
by sending a GetExpressCheckoutDetails request to the PayPal 
server. Then the code calls GetExpressCheckoutDetails.php.

Note: Be sure to check the value of PAYPAL_URL. The buyer is 
sent to this URL to authorize payment with their PayPal 
account. For testing purposes, this should be set to the 
PayPal sandbox.

Called by SetExpressCheckout.html.

On the third pass, it finishes the payment.

********************************************/

require_once 'paypal_callerservice.php';
require_once 'paypal_settings.php';

session_start();


// ============== First pass ===================
$token = $_REQUEST['token'];
if(! isset($token) && ! isset($_GET['subaction'])) {

	/* The servername and serverport tells PayPal where the buyer
	   should be directed back to after authorizing payment.
	   In this case, its the local webserver that is running this script
	   Using the servername and serverport, the return URL is the first
	   portion of the URL that buyers will return to after authorizing payment
	   */
	   $url = $main_link;

	   // this script is configured for the payment of only 1 item	
	   $totalAmount = isset($_POST['totalAmount']) ? (float) $_POST['totalAmount'] : 0; // total amount including tax
	   $currencyCodeType = $_POST['currencyCodeType'];
	   $paymentType = $_POST['paymentType']; // = Sale in this case
	   $amountNet = round($totalAmount / 1.19, 2);
	   $taxAmt = round($amountNet*0.19, 2);
			
	 /* The returnURL is the location where buyers return when a
		payment has been succesfully authorized.
		The cancelURL is the location buyers are sent to when they hit the
		cancel button during authorization of payment during the PayPal flow
		*/
	   $returnURL = urlencode($url.'&action=paypal&currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType.'&totalAmount='.$totalAmount);
	   $cancelURL = urlencode("$url");

	 /* Construct the parameter string that describes the PayPal payment
		the varialbes were set in the web form, and the resulting string
		is stored in $nvpstr
		*/
	   $nvpstr = "&ReturnUrl=".$returnURL."&CANCELURL=".$cancelURL ."&CURRENCYCODE=".$currencyCodeType
	   		.'&PAYMENTREQUEST_0_PAYMENTACTION='.$paymentType
			.'&PAYMENTREQUEST_0_CURRENCYCODE='.$currencyCodeType
			.'&PAYMENTREQUEST_0_ITEMAMT='.$amountNet
			.'&PAYMENTREQUEST_0_TAXAMT='.$taxAmt
			.'&PAYMENTREQUEST_0_SHIPPINGAMT=0.00' // no shipping
			.'&PAYMENTREQUEST_0_AMT='.$totalAmount
			.'&PAYMENTREQUEST_0_ALLOWNOTE=1'
			.'&L_PAYMENTREQUEST_0_NAME0=3D Produkt Viewer PRO' // First item
			.'&L_PAYMENTREQUEST_0_AMT0='.$amountNet
			.'&L_PAYMENTREQUEST_n_ITEMCATEGORYm=Digital'
			.'&L_PAYMENTREQUEST_0_QTY0=1';

	 /* Make the call to PayPal to set the Express Checkout token
		If the API call succeded, then redirect the buyer to PayPal
		to begin to authorize payment.  If an error occured, show the
		resulting errors
		*/
	   $resArray=hash_call("SetExpressCheckout",$nvpstr);
	   $_SESSION['reshash']=$resArray;

	   $ack = strtoupper($resArray["ACK"]);

	   if($ack=="SUCCESS"){
			// Redirect to paypal.com here
			$token = urldecode($resArray["TOKEN"]);
			$payPalURL = PAYPAL_URL.$token;
			die("<meta http-equiv='refresh' content='0;url=$payPalURL' />"); // use this alternative way for wordpress
		} else  {
			// An error occurred
			displayError($resArray);
			die;
		}
}


// ============== Second pass ===================
elseif (! isset($_GET['subaction'])) {
	
 /* At this point, the buyer has completed in authorizing payment
	at PayPal.  The script will now call PayPal with the details
	of the authorization, incuding any shipping information of the
	buyer.  Remember, the authorization is not a completed transaction
	at this state - the buyer still needs an additional step to finalize
	the transaction
	*/

   $token =urlencode( $_REQUEST['token']);

 /* Build a second API request to PayPal, using the token as the
	ID to get the details on the payment authorization
	*/
   $nvpstr="&TOKEN=".$token;

 /* Make the API call and store the results in an array.  If the
	call was a success, show the authorization details, and provide
	an action to complete the payment.  If failed, show the error
	*/
   $resArray=hash_call("GetExpressCheckoutDetails",$nvpstr);
   $_SESSION['reshash']=$resArray;
   $ack = strtoupper($resArray["ACK"]);

   if($ack=="SUCCESS") {
		/* Display the  API response back to the browser .
		   If the response from PayPal was a success, display the response parameters
		   */
	   	$_SESSION['token']=$_REQUEST['token'];
		$_SESSION['payer_id'] = $_REQUEST['PayerID'];
		
		$_SESSION['totalAmount']=$_REQUEST['totalAmount'];
		$_SESSION['currCodeType']=$_REQUEST['currencyCodeType'];
		$_SESSION['paymentType']=$_REQUEST['paymentType'];
		
		$resArray=$_SESSION['reshash'];
		$_SESSION['customer'] = $resArray['SHIPTONAME'];
?>
		<div class="wrap">
			<h2><img src="<?php echo WP_PLUGIN_URL . '/3d-viewer-configurator/img/3d-produkt-viewer.png'; ?>" alt="3D Produkt Viewer" /></h2>
				<table>
		            <tr>
		                <td style="width: 100px;"><b><?php _e('Total amount', $this->localizationDomain); ?>:</b></td>
		                <td><b><?=$_REQUEST['currencyCodeType'] ?> <?=$_REQUEST['totalAmount']?></b></td>
		            </tr>
		            <tr>
		            	<td><?php _e('Customer name', $this->localizationDomain); ?>:</b></td>
		            	<td><?= $resArray['SHIPTONAME']; ?></td>
		            </tr>
		            <tr>
		            	<td>&nbsp;</td>
		            </tr>
					<tr>
					    <td><b><?php _e('Address', $this->localizationDomain); ?>:</b></td>
					</tr>
		            <tr>
		                <td><?php _e('Street', $this->localizationDomain); ?>:</td>
		                <td><?=$resArray['SHIPTOSTREET'] ?><br/><?=$resArray['SHIPTOSTREET2'] ?></td>
		            </tr>
		            <tr>
		                <td><?php _e('City', $this->localizationDomain); ?>:</td>
		                <td><?=$resArray['SHIPTOCITY'] ?></td>
		            </tr>
		            <tr>
		                <td><?php _e('Zipcode', $this->localizationDomain); ?>:</td>
		                <td><?=$resArray['SHIPTOZIP'] ?></td>
		            </tr>
		            <tr>
		                <td><?php _e('Country', $this->localizationDomain); ?>:</td>
		                <td><?=$resArray['SHIPTOCOUNTRYNAME'] ?></td>
		            </tr>
		            <tr>
		                <td colspan="2" style="text-align: center; padding-top: 30px;">
		                	<a class="button-secondary" href="<?php echo $main_link; ?>&action=paypal&subaction=finish"><?php _e('Finish Purchase - Get PRO Version', $this->localizationDomain); ?></a>
						</td>
		            </tr>
		        </table>
		</div>
		<?php
			die;
	  } else  {
		displayError($resArray);
		die;
	  }
} // End second pass



// ============== Third pass ===================
if (isset($_GET['subaction']) && $_GET['subaction'] == 'finish')
{
	// This is the final payment call, run in the third pass of this file.
	
	/* Gather the information to make the final call to
   finalize the PayPal payment.  The variable nvpstr
   holds the name value pairs
   */
	$token =urlencode( $_SESSION['token']);
	$totalAmount =urlencode ($_SESSION['totalAmount']);
	$paymentType = urlencode($_SESSION['paymentType']);
	$currCodeType = urlencode($_SESSION['currCodeType']);
	$payerID = urlencode($_SESSION['payer_id']);
	$serverName = urlencode($_SERVER['SERVER_NAME']);
	$customer = $_SESSION['customer'];
	
	$nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$totalAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName ;
	
	 /* Make the call to PayPal to finalize payment
	    If an error occured, show the resulting errors
	    */
	$resArray=hash_call("DoExpressCheckoutPayment",$nvpstr);
	
	/* Display the API response back to the browser.
	   If the response from PayPal was a success, display the response parameters'
	   If the response was an error, display the errors received using APIError.php.
	   */
	$ack = strtoupper($resArray["ACK"]);
	
	
	if($ack!="SUCCESS"){
		$_SESSION['reshash']=$resArray;
		displayError($resArray);
		die;
	}
	else
	{
		// payment successful, generate license data and write new license file
		$user_id = $customer;
		$serial = $this->licensefactory->generate_licensekey($user_id);
		$fh = fopen($this->plugin_path.'/'.$this->licensekeyfile, 'w');
		fwrite($fh, $user_id."\n");
		fwrite($fh, $serial);
		fclose($fh);
		die("<meta http-equiv='refresh' content='0;url=$main_link' />"); // use this alternative way for wordpress
	}
} // end third pass



/**
 * Helper function to display an error.
 */
function displayError($resArray) {
?>
	<h1>Paypal Error</h1>
<table>
	<tr>
		<td>Ack:</td>
		<td><?= $resArray['ACK'] ?></td>
	</tr>
	<tr>
		<td>Correlation ID:</td>
		<td><?= $resArray['CORRELATIONID'] ?></td>
	</tr>
	<tr>
		<td>Version:</td>
		<td><?= $resArray['VERSION']?></td>
	</tr>
<?php
	$count=0;
	while (isset($resArray["L_SHORTMESSAGE".$count])) {		
		  $errorCode    = $resArray["L_ERRORCODE".$count];
		  $shortMessage = $resArray["L_SHORTMESSAGE".$count];
		  $longMessage  = $resArray["L_LONGMESSAGE".$count]; 
		  $count=$count+1; 
?>
	<tr>
		<td>Error Number:</td>
		<td><?= $errorCode ?></td>
	</tr>
	<tr>
		<td>Short Message:</td>
		<td><?= $shortMessage ?></td>
	</tr>
	<tr>
		<td>Long Message:</td>
		<td><?= $longMessage ?></td>
	</tr>
</table>
<?php }//end while
} // end error function
?>

