<?php
include_once (dirname(__FILE__) . '/user_api.php');

function api_register($args){
	return register($args["username"], $args["password"]);
}
function api_login($args){
	return login($args["username"], $args["password"]);
}
function api_get_user($args){
	return get_user($args["token"], $args["id"]);
}
function api_get_id($args){
	return get_id($args["token"], $args["username"]);
}
function api_delete_token($args){
	return delete_token($args["token"]);
}
if(!isset($_POST["type"])) exit('{"success":false}');
$type = $_POST["type"];
$handlers = array("register" => "api_register","login" => "api_login", "get_user" => "api_get_user", "get_id" => "api_get_id", "delete_token" => "api_delete_token");
print(json_encode($handlers[$type]($_POST)));
?>
