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

// Try to get a user by username
function get_user_by_username($raw_username){
	global $db;
	$filtered_username=$db->filter($raw_username);
	return $db->query_getline("SELECT * FROM users where username='$filtered_username'");
}

// Try to get a user by id
function get_user_by_id($raw_id){
	global $db;
	$id=(int)$raw_id;
	return $db->query_getline("SELECT * FROM users where id=$id");
}

// Return if a token is valid.
function is_token_valid($raw_token, &$username=null){
	global $db;
	$filtered_token=$db->filter($raw_token);
	$token = $db->query_getline("SELECT * FROM tokens where token='$filtered_token' and valid=true");
	if($token) $username=$token["username"];
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
	
	$user = get_user_by_username($filtered_username);
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
	$user = get_user_by_id($raw_id);
	if(!$user) return gen_err_ret("The ID is not valid.");
	return array("success"=>true,"username"=>$user["username"],"score"=>$user["score"]);
}
# Try to get a user's id by the username.
function get_id($token, $raw_username){
	global $db;
	if(!is_token_valid($token)) return gen_err_ret("The token is not valid.");
	$user = get_user_by_username($raw_username);
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

# Try to get the friends by a token.
function get_friends($raw_token){
	global $db;
	$token=$db->filter($raw_token);
	if(!is_token_valid($token, $username)) return gen_err_ret("The token is not valid.");
	$user = get_user_by_username($username);
	return array("success"=>true,"friends"=>$user["friends"]);
}

# Try to add a friend by a token and a id.
function add_friend($raw_token, $raw_id){
	global $db;
	$token=$db->filter($raw_token);
	if(!is_token_valid($token, $username)) return gen_err_ret("The token is not valid.");
	$user = get_user_by_username($username);
	$friend = get_user_by_id($raw_id);
	if(!$friend) return gen_err_ret("The user does not exist!");
	$friends = json_decode($user["friends"]);
	if(in_array($friend["id"], $friends)) return gen_err_ret("Already your friend.");
	array_push($friends, $friend["id"]);
	$user_id=$user["id"];
	$new_friends=json_encode($friends);
	$db->query("UPDATE users SET friends='$new_friends' WHERE id = '$user_id'");
	return array("success"=>true);
}

# Try to delete a friend by a token and a id.
function delete_friend($raw_token, $raw_id){
	global $db;
	$token=$db->filter($raw_token);
	if(!is_token_valid($token, $username)) return gen_err_ret("The token is not valid.");
	$user = get_user_by_username($username);
	$friends = json_decode($user["friends"]);
	$key = array_search((int)$raw_id, $friends);
	if ($key === false) return gen_err_ret("Not your friend.");
	array_splice($friends, $key, 1);
	$user_id=$user["id"];
	$new_friends=json_encode($friends);
	$db->query("UPDATE users SET friends='$new_friends' WHERE id = '$user_id'");
	return array("success"=>true);
}

# Try to change the score by a token.
function change_score($raw_token, $raw_score){
	global $db;
	$token=$db->filter($raw_token);
	if(!is_token_valid($token, $username)) return gen_err_ret("The token is not valid.");
	$user = get_user_by_username($username);
	$new_score = (int)$raw_score;
	$score = (int)$user["score"];
	if($score>$new_score)  return gen_err_ret("Unknown error.");
	$user_id = $user["id"];
	$db->query("UPDATE users SET score='$new_score' WHERE id = '$user_id'");
	return array("success"=>true);
}
?>
