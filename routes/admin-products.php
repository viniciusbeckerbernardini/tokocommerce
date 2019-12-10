<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products",function(){
	User::verifyLogin();

    $search = $_GET['search']??"";

    $page = $_GET['page']??1;

    if($search != ''){
        $pagination = Product::getPageSearch($search,$page);
    }else{
        $pagination = Product::getPage($page);
    }

    $pages = array();

    for ($i = 0; $i < $pagination['pages']; $i++){
        array_push($pages, [
            "href"=>"/admin/products?".http_build_query(
                    [
                        "page"=>$i+1,
                        "search"=>$search
                    ]),
            "text"=>$i+1
        ]);
    }



    $page = new PageAdmin();


	$page->setTpl("products",
            [
                "products"=>$pagination['data'],
                "search"=>$search,
                "pages"=>$pages
            ]
	);

});

$app->get("/admin/products/create",function(){
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("products-create");
});

$app->post("/admin/products/create",function(){
	User::verifyLogin();
	$product = new Product();
	$product->setData($_POST);
	$product->save();
	header("Location: /admin/products");
	exit;
});


$app->get("/admin/products/:idproduct",function(int $idproduct){
	User::verifyLogin();
	$product = new Product();
	$product->get($idproduct);
	$page = new PageAdmin();
	$page->setTpl("products-update", [
		'product'=>$product->getValues()
	]);
});

$app->post("/admin/products/:idproduct",function(int $idproduct){
	User::verifyLogin();
	$product = new Product();
	$product->get($idproduct);
	$product->setData($_POST);
	$product->save();
	$product->setPhoto($_FILES["file"]);
	header("Location: /admin/products");
	exit;
});

$app->get("/admin/products/:idproduct/delete",function(int $idproduct){
	User::verifyLogin();
	$product = new Product();
	$product->get($idproduct);
	$product->delete();
	header("Location: /admin/products");
	exit;
});