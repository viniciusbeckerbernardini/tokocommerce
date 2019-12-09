<?php 

use \Hcode\Model\User;


function formatPrice($vlprice){

	if(!$vlprice > 0) $vlprice = 0;
	return number_format($vlprice,2,",",".");
}

function checkLogin($inAdmin = true){
    return User::checkLogin($inAdmin);
}

function getUserName(){
    $user = User::getFromSession();

    return $user->getdesperson();
}

function getCartNrQtd(){
    $cart = \Hcode\Model\Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return $totals['nrqtd'];
}

function getCartVlSubtotal(){
    $cart = \Hcode\Model\Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return formatPrice($totals['vlprice']);
}
