<?php
	// DEBUG:
	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

	use Patreon\API;
	use Patreon\OAuth;

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	require __DIR__ . '/vendor/autoload.php';
	
	$redirect_uri = "https://(PUT_DOMAIN_HERE)/patreon.php";
	$api_client = null;
	$access = "";
	
	if (isset($_GET['change'])){
		?>
		<script>
			var d = new Date();
			d.setTime(d.getTime() + (-1 * 24 * 60 * 60 * 1000));
			var expires = "expires="+d.toUTCString();
			document.cookie = "patreon_access_token=;" + expires + ";";
		</script>
		<?PHP
		LogMeIn();
	}
	
	function LogMeIn() {
		global $redirect_uri, $api_client, $access;
		$client_id = "YOUR PATREON API CLIENT ID HERE";
		$client_secret = "YOUR PATREON API CLIENT SECRET HERE";
		if (!isset($_GET['code']) && !isset($_GET['my_token'])){
			echo 'Redirecting to Patreon auth site... ';
			?><script>window.location = "https://www.patreon.com/oauth2/authorize?response_type=code&client_id=<?PHP echo $client_id; ?>&redirect_uri=<?PHP echo $redirect_uri; ?>";</script><?PHP
			return false;
		} else {
			if (isset($_GET['code'])) {
				$oauth_client = new OAuth($client_id, $client_secret);
				$tokens = $oauth_client->get_tokens($_GET['code'], $redirect_uri);
				if (!isset($tokens['error'])){
					$access = $tokens['access_token'];
					
				}
				if ($access != "") {
					?>
					<script>
						var d = new Date();
						d.setTime(d.getTime() + (29 * 24 * 60 * 60 * 1000));
						var expires = "expires="+d.toUTCString();
						document.cookie = "patreon_access_token=" + escape("<?PHP echo $access; ?>") + ";" + expires + ";";
					</script>
					<?PHP
				}
				else {
					echo 'Access token was blank?!';
					//return false;
					?><script>window.location = "patreon.php";</script><?PHP
				}
			}
			else if (isset($_REQUEST['patreon_access_token']) && !isset($_GET['my_token']))
				$access = $_REQUEST['patreon_access_token'];
			else if (isset($_GET['my_token'])){
				if ($_GET['my_token'] == '')
					exit('No Patreon Access Token given.');
				$access = $_GET['my_token'];
			}
			else {
				echo 'ERROR no access_token was given.';
				return false;
			}
			if ($api_client == null){
				$api_client = new API($access);
			}
			return true;
		}
		return false; //default
	}

	function CheckPatreonLevel() {
		global $redirect_uri, $api_client, $access;
		if (LogMeIn()) {
			$patron_response = $api_client->fetch_user();
			$user = "";
			try {
				$user = $patron_response['data'];
			} catch (Exception $e) {
				echo 'ERROR: Authentication token not good.'; exit();
				return 0;
			}
			
			try {
				if ($patron_response['included'][0]['attributes']['currently_entitled_amount_cents'] > 0) {
					return $patron_response['included'][0]['attributes']['currently_entitled_amount_cents'];
				} else
					return 0;
			} catch (Exception $e) {
				return 0;
			}
		} else 
			return 0;
	}
	
	function GetPatronName() {
		global $api_client, $access;
		if (LogMeIn()) {
			try {
				$patron_response = $api_client->fetch_user();
				$user = $patron_response['data']['attributes']['full_name'];
				return ($user);
			} catch (Exception $e) {
				return '';
			}
		}
	}
	
	if (!isset($no_info)) {
		$patron = GetPatronName();
		if (CheckPatreonLevel() > 0/* || $patron == 'White List Patreon Member Name Here' || $patron == 'Your Name Here'*/) {
			$token = $access;
			echo "Thank you for being a Patron $patron!";
			if (!isset($_GET['my_token'])){
				$patron_response = $api_client->fetch_user();
				$user = $patron_response['data']['attributes'];

				$mail = new PHPMailer();
				$mail->isSMTP();
				$mail->Host = 'SMTP_DOMAIN_HERE';
				$mail->SMTPAuth = true;
				$mail->Username = 'EMAIL_ADDRESS_HERE';
				$mail->Password = 'EMAIL_PASSWORD_HERE';
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
				$mail->Port = 465;

				$mail->setFrom('EMAIL_ADDRESS_HERE', 'YOUR_NAME_HERE');
				$mail->addAddress($user['email'], $user['full_name']);
				$mail->addReplyTo('EMAIL_ADDRESS_HERE', 'YOUR_NAME_HERE');

				$mail->Subject = 'Your Patreon Token';
				$mail->Body    = "Here is your Patreon token to use with any (FILL_INFO_HERE) software: ".$token;

				$mail->send();

				echo "<p><b>Your Access Token Is:</b> <br>
					<input type=\"text\" value=\"$token\" style=\"font-size:18px;font-weight:bold;width:470px;\"></p>
					<p><a href=\"patreon.php?change\">Change Accounts</a></p>";
			}
		} else
			echo "You are currently not a Patron $patron.<p><a href=\"patreon.php?change\">Change Accounts</a></p>";
	}
?>