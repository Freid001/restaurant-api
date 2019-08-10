# Restaurant api

## About
Simple api which allows customers to split their bill, see [SLO](SLO.md)

### Requirements
* [Docker](https://www.docker.com/)
* [PHPUnit](https://phpunit.de/getting-started/phpunit-7.html)
* [Behat](http://behat.org/en/latest/)

### Installation
```bash
// build and run the app
./gradlew build run

// stop the app
./gradlew stop

// migrate database
./gradlew flywayMigrate -i 

// run unit tests
./gradlew unitTest

// run component tests
./gradlew componentTest
```

## Usage

##### Endpoint
```
http://localhost:8000
```
##### POST /customer

###### body
```json
{
    "firstName": "john",
    "lastName": "smith"
}
```
``
###### response
```json
{
    "meta": {
        "status": "created"
    },
    "data": {
        "id": 1,
        "firstName": "john",
        "lastName": "smith"
    } 
}
```

##### GET /customers?firstName={first_name}&lastName={last_name}

###### response
```json
{
    "meta": {
        "status": "ok"
    },
    "data": [
        {
            "id": 1,
            "firstName": "john",
            "lastName": "smith"
        },
        {
            "id": 2,
            "firstName": "david",
            "lastName": "jones"
        }
    ]
}
```

##### POST /restaurant

###### body
```json
{
    "restaurant": "dominos",
    "cuisine": "pizza",
    "menu": [
        {
            "item": "margherita pizza",
            "price": 5.99,
            "available": true
        },
        {
            "item": "pepperoni pizza",
            "price": 9.99,
            "available": false
        }
    ]
}
```

###### response
```json
{
    "meta": {
        "status": "created"
    },
    "data": {
        "restaurant": "dominos",
        "cuisine": "pizza",
        "menu": [
            {
                "item": "margherita pizza",
                "price": 5.99,
                "available": true
            },
            {
                "item": "pepperoni pizza",
                "price": 9.99,
                "available": false
            }
        ]
    }
}
```

##### GET /restaurants?restaurant={restaurant}&item={item}&&available={last_name}

###### response
```json
{
    "meta": {
        "status": "ok"
    },
    "data": [
        {
            "restaurant": "dominos",
            "cuisine": "pizza",
            "menu": [
                {
                    "item": "margherita pizza",
                    "price": 5.99,
                    "available": true
                },
                {
                    "item": "pepperoni pizza",
                    "price": 9.99,
                    "available": false
                }
            ]
        }
    ]
}
```

##### POST /order

###### body
```json
{
    "customerId": 1,
    "itemId": 1,
    "discount": 0.0
}
```

###### response
```json
{
    "meta": {
        "status": "created"
    },
    "data": {
        "id": 1,
        "customer": "john smith",
        "items": [
          {
            "id": 1,
            "item": {
                "id": 1,
                "name": "margherita pizza",
                "originalPrice": 5.99
            },
            "priceCharged": 5.99,
            "discount": 0.0
          }
        ]
    }
}
```

##### GET /orders

###### response
```json
{
    "meta": {
        "status": "ok"
    },
    "data": [
        {
            "id": 1,
            "customer": "john smith",
            "items": [
                {
                    "id": 1,
                    "item": {
                        "id": 1,
                        "name": "margherita pizza",
                        "originalPrice": 5.99
                    },
                    "priceCharged": 5.99,
                    "discount": 0.0
                }
            ]
        }
    ]
}
```

##### POST /order/item

###### body
```json
{
    "customerId": 1,
    "itemId": 1,
    "discount": 0.5
}
```

###### response
```json
{
    "meta": {
        "status": "created"
    },
    "data": {
        "id": 1,
        "customer": "john smith",
        "items": [
            {
                "id": 1,
                "item": {
                    "id": 1,
                    "name": "margherita pizza",
                    "originalPrice": 5.99
                },
                "priceCharged": 5.99,
                "discount": 0.0
            },
            {
                "id": 2,
                "item": {
                    "id": 1,
                    "name": "margherita pizza",
                    "originalPrice": 5.99
                },
                "priceCharged": 2.99,
                "discount": 0.5
            }
        ]
    }
}
```

##### DELETE /order/item?orderId={orderId}&itemId={itemId}

###### response
```json
{
    "meta": {
        "status": "created"
    },
    "data": {
      "info": "Item #1 removed from order #1."
    }
}
```

##### DELETE /order?orderId={orderId}

###### response
```json
{
    "meta": {
        "status": "created"
    },
    "data": {
      "info": "Order #1 deleted."
    }
}
```

##### POST /bill/pay

###### body
```json
{
    "customerId": 1,
    "orderId": 1,
    "orderedId": 1,
    "pay": 5.99
}
```

###### response
```json
{
    "meta": {
        "status": "created"
    },
    "data": [
        {
            "id": 1,
            "customer": "john smith",
            "ordered": [
                {
                    "id": 1,
                    "item": {
                        "id": 1,
                        "name": "margherita pizza",
                        "originalPrice": 5.99
                    },
                    "priceCharged": 5.99,
                    "discount": 0.0
                }
            ],
            "transactions": [
                {
                    "id": 1,
                    "payee": "john smith",
                    "ordered": [
                        {
                          "id": 1,
                          "name": "margherita pizza"
                        }
                    ],
                    "paid": 5.99,
                    "tip": 0.0
                }
            ],
            "totalOriginalPrice": 5.99,
            "totalDiscount": 0,
            "totalSavings": 0,
            "totalCharged": 5.99,
            "totalDue": 0,
            "totalTip": 0,
            "totalPaid": 5.99
        }
    ]
}
```

##### POST /bill/tip

###### body
```json
{
    "customerId": 1,
    "orderId": 1,
    "orderedId": 1,
    "pay": 0.99
}
```

###### response
```json
{
    "meta": {
        "status": "created"
    },
    "data": [
        {
            "id": 1,
            "customer": "john smith",
            "ordered": [
                {
                    "id": 1,
                    "item": {
                        "id": 1,
                        "name": "margherita pizza",
                        "originalPrice": 5.99
                    },
                    "priceCharged": 5.99,
                    "discount": 0.0
                }
            ],
            "transactions": [
                {
                    "id": 1,
                    "payee": "john smith",
                    "ordered": [
                        {
                          "id": 1,
                          "name": "margherita pizza"
                        }
                    ],
                    "paid": 5.99,
                    "tip": 0.0
                },
                {
                    "id": 2,
                    "payee": "john smith",
                    "ordered": [
                        {
                          "id": 1,
                          "name": "margherita pizza"
                        }
                    ],
                    "paid": 0.0,
                    "tip": 0.99
                }
            ],
            "totalOriginalPrice": 5.99,
            "totalDiscount": 0,
            "totalSavings": 0,
            "totalCharged": 5.99,
            "totalDue": 0,
            "totalTip": 0.99,
            "totalPaid": 6.98
        }
    ]
}
```

##### GET /bills?orderId={orderId}&customerId={customerId}&payeeId={payeeId}

###### response
```json
{
    "meta": {
        "status": "ok"
    },
    "data": [
        {
            "id": 1,
            "customer": "john smith",
            "ordered": [
                {
                    "id": 1,
                    "item": {
                        "id": 1,
                        "name": "margherita pizza",
                        "originalPrice": 5.99
                    },
                    "priceCharged": 5.99,
                    "discount": 0.0
                }
            ],
            "transactions": [
                {
                    "id": 1,
                    "payee": "john smith",
                    "ordered": [
                        {
                          "id": 1,
                          "name": "margherita pizza"
                        }
                    ],
                    "paid": 5.99,
                    "tip": 0.0
                },
                {
                    "id": 2,
                    "payee": "john smith",
                    "ordered": [
                        {
                          "id": 1,
                          "name": "margherita pizza"
                        }
                    ],
                    "paid": 0.0,
                    "tip": 0.99
                }
            ],
            "totalOriginalPrice": 5.99,
            "totalDiscount": 0,
            "totalSavings": 0,
            "totalCharged": 5.99,
            "totalDue": 0,
            "totalTip": 0.99,
            "totalPaid": 6.98
        }
    ]
}
```