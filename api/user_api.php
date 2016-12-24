<?php
include_once (dirname(__FILE__) . '/internal/database.php');
// Generate a string randomly.
function get_rand_string($length){
	$str = "";
	$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	$max = strlen($strPol)-1;

	for($i=0;$i<$length;$i++) $str.=$strPol[rand(0,$max)];
	return $str;
}

// Generate a error return value.
function gen_err_ret($reason){
	return array("success"=>false,"reason"=>$reason);
}

// Generate a new token.
function new_token($username){
	return sha1(date('Y-m-d H:i:s',time()).get_rand_string(6));
}

// Return if a token is valid.
function is_token_valid($raw_token){
	global $db;
	$filtered_token=$db->filter($raw_token);
	$token = $db->query_getline("SELECT * FROM tokens where token='$filtered_token' and valid=true");
	return $token!=null;
}

// Register a new account.
function register($raw_username, $raw_password){
	global $db;
	$filtered_username=$db->filter($raw_username);
	
	if($db->num_rows($db->query("SELECT * FROM users where username='$filtered_username'"))!=0)
		return gen_err_ret("The username already exists.");
	
	$salt=get_rand_string(10);
	$hashed_pwd=sha1($salt.$raw_password);
	
	$db->query("INSERT INTO users (username, password, salt, friends) VALUES ('$filtered_username', '$hashed_pwd', '$salt', '[]')");
	return array("success"=>true);
}

// Log in. Return the token.
function login($raw_username, $raw_password){
	global $db;
	$username_or_password_incorrect=gen_err_ret("Username or password is incorrect!");
	
	$filtered_username=$db->filter($raw_username);
	
	$user = $db->query_getline("SELECT * FROM users where username='$filtered_username'");
	if(!$user) return $username_or_password_incorrect;
	
	$hashed_pwd=sha1($user["salt"].$raw_password);
	if($hashed_pwd!=$user["password"]) return $username_or_password_incorrect;
	
	// username and password are correct
	$token = $db->query_getline("SELECT * FROM tokens where username='$filtered_username' and valid=true");
	if(!$token){
		do{
			$tok=new_token($filtered_username);
		}while(is_token_valid($tok));
		$db->query("INSERT INTO tokens (username, token) VALUES ('$filtered_username', '$tok')");
		return array("success"=>true,"token"=>$tok,"id"=>$user["id"]);
	}
	else{
		return array("success"=>true,"token"=>$token["token"],"id"=>$user["id"]);
	}
}
# Try to get the information of a user.
function get_user($token, $raw_id){
	global $db;
	if(!is_token_valid($token)) return gen_err_ret("The token is not valid.");
	$id=(int)$raw_id;
	$user = $db->query_getline("SELECT * FROM users where id='$id'");
	if(!$user) return gen_err_ret("The ID is not valid.");
	return array("success"=>true,"username"=>$user["username"],"score"=>$user["score"]);
}
# Try to get a user's id by the username.
function get_id($token, $raw_username){
	global $db;
	if(!is_token_valid($token)) return gen_err_ret("The token is not valid.");
	$username=$db->filter($raw_username);
	$user = $db->query_getline("SELECT * FROM users where username='$username'");
	if(!$user) return gen_err_ret("The username does not exist.");
	return array("success"=>true,"id"=>$user["id"]);
}

# Try to make a token invalid.
function delete_token($raw_token){
	global $db;
	$token=$db->filter($raw_token);
	if(!is_token_valid($token)) return gen_err_ret("The token is not valid.");
	$db->query("UPDATE tokens SET valid=0 WHERE token = '$token'");
	return array("success"=>true);
}
?>
