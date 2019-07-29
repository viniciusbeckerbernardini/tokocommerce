<?php

use Hcode\Model\User;
use \Hcode\Page;
use Hcode\Model\Product;
use Hcode\Model\Category;
use Hcode\Model\Cart;
use \Hcode\Model\Address;

$app->get('/', function() {

	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index",[
		'products'=>Product::checkList($products)
	]);

});

$app->get("/category/:idcategory",function(int $idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get($idcategory);

	$pagination = $category->getProductsPage($page);	

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) { 
		array_push($pages,[
			'link' => '/categories/'.$category->getidcategory().'?page='.$i,
			'page' => $i
		]);
	}

	$page = new Page();

	$page->setTpl("category",
		[
			'category'=>$category->getValues(),
			'products'=>$pagination['data'],
			'pages' => $pages
		]);
});


$app->get("/product/:desurl",function($desurl){
	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail",
		[
			'product'=>$product->getValues(),
			'categories' => $product->getCategories()
		]
	);
});

$app->get("/cart",function(){
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl("cart",
		[
			'cart' => $cart->getValues(),
			'products'=>$cart->getProducts(),
			'error'=>Cart::getMsgError()
		]
	);

});

$app->get("/cart/:idproduct/add",function(int $idproduct){
	$product = new Product();

	$product->get($idproduct);

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	$cart = Cart::getFromSession();

	for($i = 0; $i < $qtd; $i++){
		$cart->addProduct($product);
	}

	
	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/minus",function(int $idproduct){
	$product = new Product();

	$product->get($idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/remove",function(int $idproduct){
	$product = new Product();

	$product->get($idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;
});


$app->post('/cart/freight',function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header('Location: /cart');

	exit;

});

$app->get("/checkout",function(){

    User::verifyLogin(false);

    $cart = Cart::getFromSession();

    $address = new Address();

    $page = new Page();

    $page->setTpl("checkout",
        [
            "cart"=>$cart->getValues(),
            "address"=>$address->getValues()
        ]);
});

$app->get("/login",function(){

    $page = new Page();

    $page->setTpl("login",[
        "error"=>User::getMsgError()
    ]);
});

$app->post("/login",function(){
    try {

        $login = filter_input(INPUT_POST, 'login');
        $password = filter_input(INPUT_POST, 'password');

        User::login($login, $password);
    }catch (Exception $e){
        User::setMsgError($e->getMessage());
    }
    header("Location: /checkout");
    exit();
});












