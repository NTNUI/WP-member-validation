<?php

function getMemberInfo($phone, $password){
	$url = ("https://api.ntnui.no/users/profile/");

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$auth = base64_encode($phone . ":" . $password);

	$headers = array(
	   "Authorization: Basic " . $auth,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$resp = curl_exec($curl);
	curl_close($curl);

	$data = json_decode($resp, true);

	return $data;
}

//For debugging
//  function console_log( $data ) {
//      $output  = "<script>console.log( '";
//      $output .= json_encode(print_r($data, true));
//      $output .= "' );</script>";
//      echo $output;
//  }

function main(){
	//User with correct premissions has to be configured before use
	if(!get_option('login_username') || !get_option('group_slug')){
		return "Looks like something is wrong with the configuration!";
	}

	if ( isset( $_POST['registerForm'] ) ) {

		$memberInfo = getMemberInfo($_POST['phone'], $_POST['password']);
		
		if(isset($memberInfo["memberships"])){
			foreach ($memberInfo["memberships"] as $membership){
				//If NTNUI membership is active (expires after today) and part of group
				if($membership["group"] == get_option('group_slug') && $memberInfo["contract_expiry_date"] > date("Y-m-d")){
					//Access to rent
					//Register user if not in database
					$username = strtolower($memberInfo["first_name"].".".$memberInfo["last_name"]);
					if ( !username_exists($username)  && !email_exists($memberInfo["email"]) ) {
						$userdata = array(
							'user_login' =>  $username,
							'user_pass'  =>  $_POST['password'],
							'user_email'  =>  $memberInfo["email"],
							'first_name'  =>  $memberInfo["first_name"],
							'last_name'  =>  $memberInfo["last_name"],
							'show_admin_bar_front'  =>  "false",
							'role'  =>  get_option('access_type', 'subscriber'), #Preferred role fetched from preferences, default to subscriber if none is set
						);
						 
						$user_id = wp_insert_user( $userdata );
					}
					//Login to user with the correct permissions
					$user_login = $username; 
					$user = get_userdatabylogin($user_login);
					$user_id = $user->ID; 
					wp_set_current_user($user_id, $user_login);
					wp_set_auth_cookie($user_id); 
					do_action('wp_login', $user_login); 

					$response = "Login valid";
					//Redirect to home page on login
					header("location:". home_url());
				}
			}
			if(!isset($response)){
				$response = "No valid membership in NTNUI/Seiling";
			}
		}
		else{
			$response = "Invalid username or password";
		}
		}
    
	ob_start();
	?>
	 <style> 
	 	#NTNUI-login {
			width: 100%;
			text-align: center;
		 }

		input[type=text], input[type=password]  {
		max-width: 30vw;
		padding: 12px 20px;
		margin: 8px 0;
		box-sizing: border-box;
		border: 3px solid #ccc;
		-webkit-transition: 0.5s;
		transition: 0.5s;
		outline: none;
		}

		input[type=text]:focus, input[type=password]:focus {
		border: 3px solid #555;
		}

		input[type=submit] {
			background-color: rgb(0,128,55);
			border: none;
			color: white;
			padding: 15px 25px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 16px;
			margin: 4px 2px;
			cursor: pointer;
		}
	 </style>
    <form method = "post" id= "NTNUI-login">
        <input type="text" name="phone" autocomplete="off" placeholder="Phone (+0012345678)"><br>
        <input type="password" name="password" autocomplete="off" placeholder="password"><br>
        <input type="submit" name="registerForm" value="Log in">
		<?php if(isset($response)){ echo $response; } ?>
    </form>
    <?php
	return ob_get_clean();
}
add_shortcode( 'NTNUI-login', 'main' );
?>
