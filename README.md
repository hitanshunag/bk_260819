#  RESTful API for orderapp
Rest API for creating, fetching and taking the order.

## Docker, language, Framework, Database and server requirement

- [Docker](https://www.docker.com/) as the container service to isolate the environment.
- [Php](https://php.net/) to develop backend support.
- [Laravel](https://laravel.com) as the server framework / controller layer
- [MySQL](https://mysql.com/) as the database layer
- [NGINX](https://docs.nginx.com/nginx/admin-guide/content-cache/content-caching/) as a proxy / content-caching layer

## Installation steps to run the APP

1.  Clone the repo. `codebase` folder contains the complete application code.
2.  As we have used the google distance matrix api for distance calculation you need API key for the same. 
    Go to https://cloud.google.com/maps-platform/routes/ after login create new project and get the API key. 
    update 'GOOGLE_API_KEY' in environment file located in ./codebase/.env file
3.  Within the folder you will find "start.sh" file. Run `sh start.sh` to build docker containers, executing migration and PHPunit test cases
4.  After starting container following will be executed automatically:
	- Table migrations using artisan migrate command.
	- Dummy Data imports using artisan db:seed command.
	- Unit and Integration test cases execution.

## For Migrating tables and Data Seeding

1. For running migrations manually `docker exec rest_order_app_php php artisan migrate`
2. For seeding the database with dummy data `docker exec rest_order_app_php php artisan db:seed`

## For manually running the docker and test Cases

1. You can run `docker-compose up` from terminal
2. Server is accessible at `http://localhost:8080`
3. Run manual testcase suite:
	- Integration Tests: `docker exec rest_order_app_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Feature/OrderFeatureTest.php` &
	- Unit Tests: `docker exec rest_order_app_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Unit`

## Swagger integration

1. Open URL for API demo `http://localhost:8080/api-docs`
2. Here you can perform all API operations like GET, UPDATE, POST

## Code Structure
codebase folder contain application code.

**./tests**

- this folder contains features and unit folders that contains the test case files.

**./app**

- Folder contains all the framework configuration file, controllers and models
- migration files are present inside the database/migrations/ folder
	- To run manually migrations use this command `docker exec rest_order_app_php php artisan migrate`
- For seeding DB with dummy dataset under the database/seeds we have the seeder files 
	- To run manually data import use this command `docker exec rest_order_app_php php artisan db:seed`
- `OrderController` contains all the api's methods :
    1. localhost:8080/orders?page=1&limit=4 - GET url to fetch orders with page and limit
    2. localhost:8080/orders - POST method to insert new order with origin and distination
    3. localhost:8080/orders - PATCH method to update status for taken.(handled the concurrent request for taking the order. If order already taken then other request will get response status 409)
- `OrderService` contains the business logic.    
- Created index on "created_at" column of order table. This is useful when we fetch the reports on the orders.


**.env**

- env file contain all project configuration, you can configure database, session and custom configuration. We have set the GOOGLE_API_KEY here in the env file so that it is configurable.


## API Reference Documentation
-  Find below API documentation for the help.
- `localhost:8080/orders?page=:page&limit=:limit` :

    GET Method - to fetch orders with page number and limit
    1. Header :
        - GET /orders?page=1&limit=5 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    2. Responses :

    ```
            - Response
            [
              {
                "id": 1,
                "distance": 2023,
                "status": "TAKEN"
              },
              {
                "id": 2,
                "distance": 46731,
                "status": "UNASSIGNED"
              },
              {
                "id": 3,
                "distance": 3004,
                "status": "TAKEN"
              },
              {
                "id": 4,
                "distance": 49132,
                "status": "UNASSIGNED"
              },
              {
                "id": 5,
                "distance": 46732,
                "status": "TAKEN"
              }
            ]
    ```

        Code                    Description
        - 200                   successful operation
        - 406                   Invalid Request Parameter
        - 500                   Internal Server Error

- `localhost:8080/orders` :

    POST Method - to create new order with origin and distination
    1. Header :
        - POST /orders HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    2. Post-Data :
    ```
         {
            "source" :["27.514501", "77.102493"],
            "destination" :["27.515517", "77.102513"]
         }
    ```

    3. Responses :
    ```
            - Response
            {
              "id": 7601,
              "distance": 3000,
              "status": "UNASSIGNED"
            }
    ```

        Code                    Description
        - 200                   successful operation
        - 400                   Api request denied or not responding
        - 406                   Invalid Request Parameter

- `localhost:8080/orders/:id` :

    PATCH method to update status for taken.(Handled simultaneous update request from multiple users at the same time with response status 409)
    1. Header :
        - PATCH /orders/2 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json
    2. Post-Data :
    ```
         {
            "status" : "TAKEN"
         }
    ```

    3. Responses :
    ```
            - Response
            {
              "status": "SUCCESS"
            }
    ```

        Code                    Description
        - 200                   successful operation
        - 406                   Invalid Request Parameter
        - 409                   Order already taken
        - 404                   Invalid Order Id
