<?php


namespace Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Model;

class Order extends Model
{
    const MESSAGE_ORDER_ERROR = "ORDER_ERROR";
    const MESSAGE_ORDER = "ORDER_MESSAGE";

    public function save(){
        $sql = new Sql();
        $results = $sql->select("CALL sp_orders_save(:idorder,:idcart,:iduser,:idstatus,:idaddress,:vltotal)",
        [
            ":idorder"=>$this->getidorder() !=null?$this->getidorder():0,
            ":idcart"=>$this->getidcart(),
            ":iduser"=>$this->getiduser(),
            ":idstatus"=>$this->getidstatus(),
            ":idaddress"=>$this->getidaddress(),
            ":vltotal"=>$this->getvltotal()
        ]);

        if(count($results)> 0){
            $this->setData($results[0]);
        }
    }

    public function get($idorder){
        $sql = new Sql();

        $results = $sql->select("
                                SELECT * FROM tb_orders a 
                                INNER JOIN tb_ordersstatus b USING(idstatus) 
                                INNER JOIN tb_carts c USING(idcart)
                                INNER JOIN tb_users d ON d.iduser = a.iduser
                                INNER JOIN tb_addresses e USING(idaddress)
                                INNER JOIN tb_persons f ON f.idperson = d.idperson
                                WHERE a.idorder = :idorder 
                                ",
            [
                ":idorder"=>$idorder
            ]);

        if(count($results)>0){
            $this->setData($results[0]);
        }
    }

    public static function listAll(){
        $sql = new Sql();

        return $sql->select("
         SELECT * FROM tb_orders a 
                                INNER JOIN tb_ordersstatus b USING(idstatus) 
                                INNER JOIN tb_carts c USING(idcart)
                                INNER JOIN tb_users d ON d.iduser = a.iduser
                                INNER JOIN tb_addresses e USING(idaddress)
                                INNER JOIN tb_persons f ON f.idperson = d.idperson
                                ORDER BY a.dtregister DESC");
    }

    public function delete(){
        $sql = new Sql();

        $sql->query("DELETE FROM tb_orders WHERE idorder = :idorder",
            [":idorder"=>$this->getidorder()]);
    }


    public function getCart():Cart{
        $cart = new Cart();

        $cart->get($this->getidcart());

        return $cart;

    }

    public static function setMsgError(string $msg){
        $_SESSION[Order::MESSAGE_ORDER_ERROR] = $msg;
    }

    public static function getMsgError(){
        $msg = (isset($_SESSION[Order::MESSAGE_ORDER_ERROR])) ? $_SESSION[Order::MESSAGE_ORDER_ERROR] : '';

        User::clearMsgError();

        return $msg;
    }

    public static function clearMsgError(){
        unset($_SESSION[Order::MESSAGE_ORDER_ERROR]);
    }

    public static function setMsg(string $msg){
        $_SESSION[Order::MESSAGE_ORDER] = $msg;
    }

    public static function getMsg(){
        $msg = (isset($_SESSION[Order::MESSAGE_ORDER])) ? $_SESSION[Order::MESSAGE_ORDER] : '';

        Order::clearMsgError();

        return $msg;
    }
    public static function clearMsg(){
        unset($_SESSION[Order::MESSAGE_ORDER]);
    }
}