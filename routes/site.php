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
        "error"=>User::getMsgError(),
        "registerValues"=>(isset($_SESSION['registerValues']))? $_SESSION['registerValues'] : ['name'=>'','email'=>'','phone'=>'']
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


$app->get("/logout",function (){
   User::logout();
    header("Location: /login");
    exit();
});


$app->post('/register',function(){

    $_SESSION['registerValues'] = $_POST;

    if(!isset($_POST['name']) || $_POST['name'] == ''){
        User::setMsgError("Cadastro: preencha o seu nome");
        header("Location: /login");
        exit();
    }

    if(!isset($_POST['email']) || $_POST['email'] == ''){
        User::setMsgError("Cadastro: preencha o seu email");
        header("Location: /login");
        exit();
    }

    if(!isset($_POST['password']) || $_POST['password'] == ''){
        User::setMsgError("Cadastro: preencha a sua senha");
        header("Location: /login");
        exit();
    }

    if(User::checkLoginExists($_POST['email'])){
        User::setMsgError("E-mail já cadastrado.");
        header("Location: /login");
        exit();
    }

    $user = new User();

   $user->setData([
      'inadmin'=>0,
       'deslogin'=>filter_input(INPUT_POST, 'email'),
       'desperson'=>filter_input(INPUT_POST,'name'),
       'desemail'=>filter_input(INPUT_POST, 'email'),
       'despassword'=>filter_input(INPUT_POST, 'password'),
       'nrphone'=>filter_input(INPUT_POST, 'phone')
   ]);

   $user->save();

   User::login(filter_input(INPUT_POST, 'email'),
       filter_input(INPUT_POST, 'password')
   );


   header('Location: /checkout');
   exit();
});

$app->get("/forgot",function(){

	$page = new Page();

	$page->setTpl("forgot");

});

$app->post("/forgot",function(){

	$user = User::getForgotten($_POST["email"],false);

	header("Location: /forgot/sent");
	exit;
});

$app->get("/forgot/sent",function(){
	$page = new Page();

	$page->setTpl("forgot-sent");
});

$app->get("/forgot/reset",function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset",
		["name"=>$user['desperson'],"code"=>$_GET['code']]
	);
});


$app->post("/forgot/reset",function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot['iduser']);

	$password = password_hash($_POST['password'], PASSWORD_DEFAULT, ['cost' => 12]);

	$user->setPassword($password);


	$page = new Page();

	$page->setTpl("forgot-reset-success");
});

$app->get("/profile",function (){
    User::verifyLogin(false);

    $user = User::getFromSession();

    $page = new Page();

    $page->setTpl("profile",[
        'user'=>$user->getValues(),
        'profileMsg'=>User::getMsg(),
        'profileError'=>User::getMsgError()
    ]);
});

$app->post("/profile",function(){
    User::verifyLogin(false);

    $user = User::getFromSession();

    if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){
        User::setMsgError("Preencha seu nome");
        header('Location: /profile');
        exit();
    }

    if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){
        User::setMsgError("Preencha seu email");
        header('Location: /profile');
        exit();
    }

    if ($_POST['desemail'] !== $user->getdesemail()){

        if(User::checkLoginExists($_POST['desemail'])){
            User::setMsgError("Endereço de email já cadastrado");
            header('Location: /profile');
            exit();
        }

    }
    $_POST['iduser'] = $user->getiduser();
    $_POST['inadmin'] = $user->getinadmin();
    $_POST['despassword'] = $user->getdespassword();
    $_POST['deslogin'] = $_POST['desemail'];

    $upUser = new User();

    $upUser->setData($_POST);

    $upUser->update();

    $upUser->setToSession();

    User::setMsg("Dados atualizados com sucesso!");

    header('Location: /profile');
    exit();
});










