<?php 

namespace ItScholarBd\Api\Classes;

use ItScholarBd\Api\Classes\Utility;

use ItScholarBd\Api\Models\Shop;
use ItScholarBd\Api\Models\Order;
use ItScholarBd\Api\Models\OrderLog;
use ItScholarBd\Api\Models\ShopOrder;
use ItScholarBd\Api\Models\Voucher;

use Auth;
use DB;
class Action
{

    public static function approveShop($id){
       $shop = Shop::find($id);
       $shop->status = 1;
       $shop->update();
    }    
    public static function blockShop($id){
       $shop = Shop::find($id);
       $shop->status = 2;
       $shop->update();
    }

    public static function approveOrder($id){
       $order = Order::find($id);
       $order->status = 1;
       $order->update();
    }  
    public static function deliverOrder($id){
       $order = Order::find($id);
       $order->status = 2;
       $order->update();
    } 
    public static function completeOrder($id){
       $order = Order::find($id);
       $order->status = 3;
       $order->update();
       self::updateShopOrder($order);
       self::updateOrderLog($order);
    } 
    public static function cancelOrder($id){
       $order = Order::find($id);
       $order->status = 100;
       $order->update();
    } 
    
    public static function updateShopOrder($order){
      $shopOrder = ShopOrder::where(['shop_id'=>$order->shop_id]);

      if(!$shopOrder->count()){
          $shopOrder = new ShopOrder;
          $shopOrder->shop_id = $order->shop_id;
          $shopOrder->total_order = 1;
          $shopOrder->save();
      }else{
         $shopOrder = $shopOrder->first();
         $shopOrder->total_order += 1;
         $shopOrder->update();

      }
    }
    public static function updateOrderLog($order){
     //  dd($order->toArray()); exit;
       $orderLog = OrderLog::where(['user_id'=>$order->customer_id]);

       if(!$orderLog->count()){
           $orderLog = new OrderLog;
           $orderLog->user_id = $order->customer_id;
           $orderLog->total_order = 1;
           $orderLog->total_amount = Utility::getSubTotalByOrderId($order->id) + $order->tax + $order->delivery_charge;
           $orderLog->save();
       }else{
          $orderLogId = $orderLog->get()->toArray();
          $orderLog = OrderLog::find($orderLogId[0]['id']);
          $orderLog->total_order += 1;
          $orderLog->total_amount += Utility::getSubTotalByOrderId($order->id) + $order->tax + $order->delivery_charge;
          $orderLog->update();

       }

    }

    public static function assignVoucher($data){

       $data['validity'] = date("Y-m-d 23:59:59", strtotime($data['validity']));
       $voucher = new Voucher;
       $voucher->title = $data['title'];
       $voucher->code = $data['code'];
       $voucher->discount = $data['discount'];
       $voucher->min_order = $data['min_order'];
       $voucher->user_id = $data['customerId'];
       $voucher->validity = $data['validity'];
       $voucher->save();
    }

    public static function updateVoucher($data){
       
       $data['validity'] = date("Y-m-d 23:59:59", strtotime($data['validity']));
      // print_r($data); exit;
       $voucher = Voucher::find($data['voucher_id']);
       $voucher->title = $data['title'];
       $voucher->code = $data['code'];
       $voucher->discount = $data['discount'];
       $voucher->min_order = $data['min_order'];
       $voucher->user_id = $data['customerId'];
       $voucher->validity = $data['validity'];
       $voucher->update();
    }



}

