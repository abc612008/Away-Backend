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
function api_get_friends($args){
	return get_friends($args["token"]);
}
function api_add_friend($args){
	return add_friend($args["token"],$args["id"]);
}
function api_delete_friend($args){
	return delete_friend($args["token"],$args["id"]);
}
function api_change_score($args){
	return change_score($args["token"],$args["score"]);
}
if(!isset($_POST["type"])) exit('{"success":false}');
$type = $_POST["type"];
$handlers = array("register" => "api_register","login" => "api_login", "get_user" => "api_get_user", "get_id" => "api_get_id", "delete_token" => "api_delete_token", "get_friends" => "api_get_friends", "add_friend"=>"api_add_friend", "delete_friend"=>"api_delete_friend", "change_score"=>"api_change_score");
print(json_encode($handlers[$type]($_POST)));
?>
