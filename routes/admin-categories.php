<?php 

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\User;


$app->get("/admin/categories",function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories",['categories' => $categories ]);
});

$app->get("/admin/categories/create",function(){

	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("categories-create");

});

$app->post("/admin/categories/create",function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");

	exit;

});

$app->get("/admin/categories/:idcategory/delete",function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");

	exit;
});

$app->get("/admin/categories/:idcategory",function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();
	$page->setTpl("categories-update",["category"=>$category->getValues()]);
});

$app->post("/admin/categories/:idcategory",function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");

	exit;

});

$app->get('/admin/categories/:idcategory/products',function(int $idcategory){
	User::verifyLogin();

	$category = new Category();

	$category->get($idcategory);

	$page  = new PageAdmin();

	$page->setTpl("categories-products",
		[
			"category" => $category->getValues(),
			"productsRelated" => $category->getProducts(),
			"productsNotRelated" => $category->getProducts(false)
		]
	);
});

$app->get('/admin/categories/:idcategory/products/:idproduct/add',function(int $idcategory,int $idproduct){
	User::verifyLogin();

	$category = new Category();
	
	$category->get($idcategory);
	
	$product = new Product();

	$product->get($idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");

	exit;
});

$app->get('/admin/categories/:idcategory/products/:idproduct/remove',function(int $idcategory,int $idproduct){
	User::verifyLogin();

	$category = new Category();
	
	$category->get($idcategory);
	
	$product = new Product();

	$product->get($idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");

	exit;
});