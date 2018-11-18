# Restaurant api

## About
...

### Requirements
* [Docker](https://www.docker.com/)
* [Gradle](https://gradle.org/)
* [PHPUnit](https://phpunit.de/getting-started/phpunit-7.html)

### Installation
```bash
// build and run the app
gradle build run

// stop the app
gradle stop

// migrate database
gradle flywayMigrate -i 
```

## Usage

##### Endpoint
```
http://localhost:8000
```

##### GET /customers?firstName={first_name}&lastName={last_name}

###### response
```json
[
    {
        "id": 1,
        "firstName": "Lucas",
        "lastName": "Maxwell"
    },
    {
        "id": 2,
        "firstName": "Samantha",
        "lastName": "Carpenter"
    },
    {
        "id": 3,
        "firstName": "Sonya",
        "lastName": "Sandoval"
    }
]
```

##### GET /restaurants?restaurant={restaurant}&item={item}&available={available}

###### response
```json
[
    {
        "id": 1,
        "restaurant": "dominos",
        "menu": [
            {
                "id": 1,
                "item": "hawaiian pizza",
                "price": 9.99,
                "available": true
            },
            {
                "id": 2,
                "item": "bbq pizza",
                "price": 10.99,
                "available": false
            },
            {
                "id": 3,
                "item": "margherita pizza",
                "price": 5.99,
                "available": false
            },
            {
                "id": 4,
                "item": "pepperoni pizza",
                "price": 7.99,
                "available": true
            }
        ]
    }
]
```

##### GET /orders?orderId={order_id}&customerId={customer_id}&closed={closed}

###### response
```json
[
  {
    "id": 1,
    "state": "closed",
    "customer": "Lucas Maxwell",
    "items": 
    [
        {
            "id": 1,
            "item": {
                "id": 1,
                "name": "hawaiian pizza",
                "originalPrice": 9.99
            },
            "priceCharged": 9.99,
            "discount": 0
        },
        {
            "id": 2,
            "item": {
                "id": 2,
                "name": "bbq pizza",
                "originalPrice": 10.99
            },
            "priceCharged": 9.99,
            "discount": 0
        }
    ]
  },
  {
    "id": 2,
    "state": "open",
    "customer": "Samantha Carpenter",
    "items": 
    [
        {
            "id": 3,
            "item": {
                "id": 3,
                "name": "margherita pizza",
                "originalPrice": 5.99
            },
        "priceCharged": 1,
        "discount": 0
        }
    ]
  }
]
```

##### POST /order

###### body
```json
{
	"customerId": 1,
	"itemId": 4,
	"discount": 0.25
}
```

###### response
```json
{
    "id": 1,
    "state": "open",
    "customer": "Lucas Maxwell",
    "items": [
        {
            "id": 7,
            "item": {
                "id": 4,
                "name": "pepperoni pizza",
                "originalPrice": 7.99
            },
            "priceCharged": 5.99,
            "discount": 0.25
        }
    ]
}
```

##### POST /order/item?orderId={order_id}

###### body
```json
{
	"itemId": 4,
	"discount": 0.10
}
```

###### response
```json
{

}
```

##### DELETE /order/item?item_id={item_id}

###### response
```json
{

}
```

##### DELETE /order?orderId={order_id}

###### response
```json
{
    "info": "Order #10 deleted."
}
```

