<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
	
	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");
});


$app->get('/admin/login', function(){
	
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");
});

$app->post('/admin/login',function(){
	
	User::login(filter_input(INPUT_POST, 'login'),filter_input(INPUT_POST, 'password'));

	header('Location: /admin');

	exit;
});

$app->get('/admin/logout',function(){
	User::logout();
});

$app->get('/admin/users',function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("user");

});

$app->get('/admin/users/create',function(){
	
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("user-create");

});

$app->get('/admin/users/:idUser',function($idUser){
	
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("user-update");

});


$app->run();

?>