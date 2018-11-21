Feature: Order
  To order items from restaurants
  As a customer
  I want to open an order

  Scenario: create customer
    Given the request body is:
    """
    {
      "firstName": "john",
      "lastName": "smith"
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/customer" using HTTP POST
    Then the response code is 201
    Then the response body contains JSON:
    """
    {
        "meta": {
            "status": "@variableType(string)"
        },
        "data": {
            "id": "@variableType(integer)",
            "firstName": "@variableType(string)",
            "lastName": "@variableType(string)"
        }
    }
    """

  Scenario: create restaurant
    Given the request body is:
    """
    {
      "restaurant": "dominos",
      "cuisine": "pizza",
      "items": [
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
    """
    And the "Content-Type" request header is "application/json"
    When I request "/restaurant" using HTTP POST
    Then the response code is 201
    Then the response body contains JSON:
    """
    {
        "meta": {
            "status": "@variableType(string)"
        },
        "data": {
            "id": "@variableType(integer)",
            "firstName": "@variableType(string)",
            "lastName": "@variableType(string)"
        }
    }
    """

  Scenario: append available menu item
    Given the request body is:
    """
    {
      "restaurantId": 1,
      "item": "margherita pizza",
      "price": 5.99",
      "available": true"
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/restaurant/item" using HTTP POST
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
        "meta": {
            "status": "@variableType(string)"
        },
        "data": {
            "id": "@variableType(integer)",
            "firstName": "@variableType(string)",
            "lastName": "@variableType(string)"
        }
    }
    """

  Scenario: append unavailable menu item
    Given the request body is:
    """
    {
      "restaurantId": 1,
      "item": "pepperoni pizza",
      "price": 9.99",
      "available": false"
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/restaurant/item" using HTTP POST
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
        "meta": {
            "status": "@variableType(string)"
        },
        "data": {
            "id": "@variableType(integer)",
            "firstName": "@variableType(string)",
            "lastName": "@variableType(string)"
        }
    }
    """

  Scenario: create order
    Given the request body is:
    """
    {
      "customerId": 1,
      "itemId": 1,
      "discount": 0.0
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/order" using HTTP POST
    Then the response code is 201
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": {
        "id": "@variableType(integer)",
        "customer": "@variableType(string)",
        "items": [
          {
            "id": "@variableType(integer)",
            "item": {
                "id": "@variableType(integer)",
                "name": "@variableType(string)",
                "originalPrice": "@variableType(float)"
            },
            "priceCharged": "@variableType(float)",
            "discount": "@variableType(integer)"
          }
        ]
      }
    }
    """

  Scenario: append item
    Given the request body is:
    """
    {
      "orderId":1,
      "itemId": 2,
      "discount": 0.0
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/order/item" using HTTP POST
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": {
        "id": "@variableType(integer)",
        "customer": "@variableType(string)",
        "items": [
          {
            "id": "@variableType(integer)",
            "item": {
                "id": "@variableType(integer)",
                "name": "@variableType(string)",
                "originalPrice": "@variableType(float)"
            },
            "priceCharged": "@variableType(float)",
            "discount": "@variableType(integer)"
          },
          {
            "id": "@variableType(integer)",
            "item": {
                "id": "@variableType(integer)",
                "name": "@variableType(string)",
                "originalPrice": "@variableType(float)"
            },
            "priceCharged": "@variableType(float)",
            "discount": "@variableType(integer)"
          }
        ]
      }
    }
    """

  Scenario: remove item
    Given the "Content-Type" request header is "application/json"
    When I request "/order/item?orderId=1&itemId=1" using HTTP DELETE
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": {
        "info": "@variableType(string)"
      }
    }
    """

  Scenario: create order with unavailable item
    Given the request body is:
    """
    {
      "customerId":1,
      "itemId": 1,
      "discount": 0.0
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/order" using HTTP POST
    Then the response code is 400
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": {
        "errors": {
          "itemId": "@variableType(array)"
        }
      }
    }
    """

  Scenario: append unavailable item
    Given the request body is:
    """
    {
      "orderId":1,
      "itemId": 2,
      "discount": 0.0
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/order/item" using HTTP POST
    Then the response code is 400
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": {
        "errors": {
          "itemId": "@variableType(array)"
        }
      }
    }
    """

  Scenario: cancel order
    Given the "Content-Type" request header is "application/json"
    When I request "/order?orderId=1" using HTTP DELETE
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": {
        "info": "@variableType(string)"
      }
    }
    """