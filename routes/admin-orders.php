<?php
use Hcode\Model\User;
use \Hcode\PageAdmin;
use Hcode\Model\Product;
use \Hcode\Model\Address;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

$app->get("/admin/orders/:idorder/status",function (int $idorder){
    User::verifyLogin();

    $order = new Order();

    $order->get($idorder);

    $page = new PageAdmin();

    $page->setTpl("order-status",
        [
            "order"=>$order->getValues(),
            "status"=>OrderStatus::listAll(),
            "msgError"=>Order::getMsgError(),
            "msgSuccess"=>Order::getMsg()
        ]);
});

$app->post("/admin/orders/:idorder/status",function(int $idorder){
   User::verifyLogin();

   Order::clearMsgError();
   Order::clearMsgError();

   $order = new Order();

   $order->get($idorder);

   if(!isset($_POST['idstatus']) || !$_POST['idstatus'] >0){
       Order::setMsgError("Informe o status atual do pedido!");
       header("Location: /admin/orders/$idorder/status");
       exit();
   }

    $order->setidstatus($_POST['idstatus']);


   $order->save();

   Order::setMsg("Status atualizado");
   header("Location: /admin/orders/$idorder/status");
   exit();

});


$app->get("/admin/orders/:idorder/delete",function (int $idorder){
   User::verifyLogin();

   $order = new Order();

   $order->get($idorder);

   $order->delete();

   header("Location: /admin/orders");
   exit;
});

$app->get("/admin/orders/:idorder",function (int $idorder){
   User::verifyLogin();

   $order = new Order();

   $order->get($idorder);

   $cart = $order->getCart();

   $page = new PageAdmin();

   $page->setTpl("order",
       [
           "order"=>$order->getValues(),
           "cart"=>$cart->getValues(),
           "products"=>$cart->getProducts()
       ]);
});

$app->get("/admin/orders",function (){
    User::verifyLogin(true);

    $page = new PageAdmin();

    $page->setTpl("orders",[
       "orders"=>Order::listAll()
    ]);

});