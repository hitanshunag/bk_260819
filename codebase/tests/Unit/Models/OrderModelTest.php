<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Http\Models\Order;

class OrderModelTest extends Tests\TestCase {

    use WithoutMiddleware;

    function testupdateOrderStatusMethod() {
        echo "\n <---- Unit Test - Models::Order - Method:updateOrderStatus ----> \n";
        $distance = new \App\Http\Models\Distance();
        $distance->source_latitude = '26.54325';
        $distance->source_longitude = '75.64321';
        $distance->destination_latitude = '26.78956';
        $distance->destination_longitude = '75.98765';
        $distance->distance = 100;
        $distance->save();

        $order = new \App\Http\Models\Order();
        $order->status = Order::UNASSIGNED_ORDER_STATUS;
        $order->distance_id = $distance->id;
        $order->distance_value = $distance->distance;
        $order->save();

        echo "\n \t <---- updating order which is in `unassigned` should return true ----> \n";
        $this->assertTrue($order->updateOrderStatus($order->id));
        echo "\n \t <---- updating order again - should return false ----> \n";
        $this->assertFalse($order->updateOrderStatus($order->id));
    }

}
