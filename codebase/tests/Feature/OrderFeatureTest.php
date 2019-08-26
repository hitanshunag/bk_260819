<?php

use Tests\TestCase;

class OrderFeatureTest extends TestCase
{
    public function testCreationSuccessCase()
    {
        echo "\n ***** Running Integration Test Cases *****  \n\n";
        echo "\n ***** Order create Success Case ***** \n";

        $validData = [
            'origin' => ['28.704061', '77.102493'],
            'destination' => ['28.535517','77.391029']
        ];

        $response = $this->json('POST', '/orders', $validData);
        $data = (array) $response->getData();
        echo "\n\t <----- Order create success - should have status 200 -----> \n";
        $response->assertStatus(200);
        echo "\n\t <----- Order create success - Response should have key id, status and distance ------> \n";
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('distance', $data);
    }
    
    public function testCreateWithWrongParameters()
    {
        echo "\n <----- Creating order with Invalid Parameters keys - should get 406 as response code ----> \n";

        $invalidData1 = [
            'origin' => ['28.704060', '77.102493'],
            'destination1' => ['28.535517','77.391029']
        ];

        $response = $this->json('POST', '/orders', $invalidData1);
        $data = (array) $response->getData();
        $response->assertStatus(406);
        
        echo "\n <---- Creating order with Invalid data - should get 406 as response code -------> \n";
        $invalidData = [
            'origin' => ['44.968046', 'xyz1', '44.345565'],
            'destination' => ['27.535517','78.455677']
        ];

        $response = $this->json('POST', '/orders', $invalidData);
        $response->assertStatus(406);
    }

    public function testCreateWithEmptyParameters()
    {
        echo "\n <----- Creating order with Empty Parameters - should get 406 as response code -----> \n";

        $invalidData1 = [
            'origin' => ['28.704060', ''],
            'destination' => ['','77.391029']
        ];

        $response = $this->json('POST', '/orders', $invalidData1);
        $response->assertStatus(406);
    }

    
    public function testOrderUpdateSuccessCase()
    {
        echo "\n \n ****** Executing Order Update Test Cases ****** \n";

        echo "\n <----- Order Update valid case ------> \n";

        $validData = [
            'origin' => ['28.704060', '77.102493'],
            'destination' => ['28.535517','77.391029']
        ];

        $updateData = ['status' => 'TAKEN'];
        $response = $this->json('POST', '/orders', $validData);
        $data = (array) $response->getData();
        $orderId = $data['id'];

        $response = $this->json('PATCH', '/orders/'. $orderId, $updateData);
        $data = (array) $response->getData();

        echo "\n \t <----- Order taken success - should have status 200 ------> \n";
        $response->assertStatus(200);

        echo "\n \t <----- Order update - response contain the status key  -------> \n";
        $this->assertArrayHasKey('status', $data);

        echo "\n <---- Trying to update order which is already taken - should get error 409 status code ----> \n";

        $updateData = ['status' => 'TAKEN'];

        $response = $this->json('PATCH', '/orders/'. $orderId, $updateData);
        $data = (array) $response->getData();

        $response->assertStatus(409);

        echo "\n \t <--- response should contain error key ---> \n";
        $this->assertArrayHasKey('error', $data);

        echo "\n <---- Order update invalid test cases -----> \n";
        echo "\n <--- Invalid Params key (stat)---> \n";
        $this->orderTakeFailureInvalidParams($orderId, ['stat' => 'TAKEN'], 406);

        echo "\n <--- Invalid Param value of status key as (WRONG) ---> \n";
        $this->orderTakeFailureInvalidParams($orderId, ['status' => 'WRONG'], 406);

        echo "\n <--- Empty Param value ---> \n";
        $this->orderTakeFailureInvalidParams($orderId, ['status' => ''], 406);

        echo "\n <--- Non numeric order id ---> \n";
        $this->orderTakeFailureInvalidParams('23A', ['status' => 'TAKEN'], 406);

        echo "\n <--- Invalid Order id (Order not available in DB) ---> \n";
        $this->orderTakeFailureInvalidParams(9999999, ['status' => 'TAKEN'], 404);
    }

    protected function orderTakeFailureInvalidParams($orderId, $params, $expectedCode)
    {
        $response = $this->json('PATCH', '/orders/'. $orderId, $params);
        $data = (array) $response->getData();

        echo "\n \t <--- update order - Response should have status $expectedCode ---> \n";
        $response->assertStatus($expectedCode);

        echo "\n \t <---- update order - Response should contain `error` as key ----> \n";
        $this->assertArrayHasKey('error', $data);
    }

    public function testOrderListSuccessCases()
    {
        echo "\n \n ***** Executing Order fetch test cases ***** \n";

        echo "\n <----- Order List Success cases ------> \n";

        $query = 'page=1&limit=3';
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();
        echo "\n \t <----- Order list success - should get status as 200 ----> \n";
        $response->assertStatus(200);

        echo "\n \t <----- Order list success case - for (page=1 and limit=3), count of data should less than or equal to 3  \n";
        $this->assertLessThan(4, count($data));
        
        echo "\n \t <--- Response should contain (id, distance, status) keys -- > \n";
        foreach ($data as $order) {
            $order = (array) $order;
            $this->assertArrayHasKey('id', $order);
            $this->assertArrayHasKey('distance', $order);
            $this->assertArrayHasKey('status', $order);
        }
    }


    public function testOrderFetchFailureCases()
    {
        echo "\n <---- Order list invalid test - Invalid params (page1) - should get 406 ---> \n";
        $query = 'page1=1&limit=4';
        $this->orderListFailure($query, 'Invalid params (page1)', 406);

        echo "\n <---- Order list invalid test - Invalid params (limit1) - should get 406 ---> \n";
        $query = 'page=1&limit1=4';
        $this->orderListFailure($query, 'Invalid params (limit1)', 406);

        echo "\n <---- Order list invalid test - Invalid param value (page=0) - should get 406  ---->\n";
        $query = 'page=0&limit=4';
        $this->orderListFailure($query, 'Invalid param value (page=0)', 406);

        echo "\n <---- Order list invalid test - Invalid param value (limit=0) - should get 406  ----> \n";
        $query = 'page=1&limit=0';
        $this->orderListFailure($query, 'Invalid param value (limit=0)', 406);

        echo "\n <----- Order list invalid test - Invalid param value (limit=-1) - should get 406 ----> \n ";
        $query = 'page=1&limit=-1';
        $this->orderListFailure($query, 'Invalid param value (limit=-1)', 406);

        echo "\n <----- Order list invalid test - Extra parameters (test=1) - should get 406 ---->  \n";
        $query = 'page=1&limit=2&test=1';
        $this->orderListFailure($query, 'Extra parameters (test=1)', 406);
    }

    protected function orderListFailure($query, $paramVal, $expectedCode)
    {
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        $response->assertStatus($expectedCode);
        $data = (array) $response->getData();
        echo "\n <---- Order list invalid test - $paramVal - Response should contain `error` as key ----> \n";
        $this->assertArrayHasKey('error', $data);
    }
}
