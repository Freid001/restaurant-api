Feature: Customer is billed
   To purchase ordered items
   As a customer
   I want to fully or partially pay my bill
   I want to optionally leave a tip

  Scenario: create customer
    Given the request body is:
    """
    {
      "firstName": "john",
      "lastName": "smith"
    }
    """
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
      "menu": [
        {
          "item": "margherita pizza",
          "price": 5.99,
          "available": true
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
        "restaurant": "@variableType(string)",
        "menu": [
          {
            "id": "@variableType(integer)",
            "item": "@variableType(string)",
            "price": "@variableType(float)",
            "available": "@variableType(boolean)"
          },
          {
            "id": "@variableType(integer)",
            "item": "@variableType(string)",
            "price": "@variableType(float)",
            "available": "@variableType(boolean)"
          }
        ]
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

  Scenario: fetch bills
    Given the "Content-Type" request header is "application/json"
    When I request "/bills" using HTTP GET
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": [
        {
          "id": "@variableType(integer)",
          "state": "open",
          "customer": "@variableType(string)",
          "ordered": [
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
          ],
          "transactions": "@variableType(array)",
          "totalOriginalPrice": "@variableType(float)",
          "totalDiscount": "@variableType(integer)",
          "totalSavings": "@variableType(integer)",
          "totalCharged": "@variableType(float)",
          "totalDue": "@variableType(float)",
          "totalTip": "@variableType(integer)",
          "totalPaid": "@variableType(integer)"
        }
      ]
    }
    """

  Scenario: part pay bill
    Given the request body is:
    """
    {
      "orderId": 1,
      "orderedId": 1,
      "customerId": 1,
      "pay": 3.00
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/bill/pay" using HTTP POST
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": [
        {
          "id": "@variableType(integer)",
          "state": "open",
          "customer": "@variableType(string)",
          "ordered": [
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
          ],
          "transactions": [
            {
              "id": "@variableType(integer)",
              "payee": "@variableType(string)",
              "ordered": [
                {
                  "id": "@variableType(integer)",
                  "name":"@variableType(string)"
                }
              ],
              "paid": "@variableType(integer)",
              "tip": "@variableType(integer)"
            }
          ],
          "totalOriginalPrice": "@variableType(float)",
          "totalDiscount": "@variableType(integer)",
          "totalSavings": "@variableType(integer)",
          "totalCharged": "@variableType(float)",
          "totalDue": "@variableType(float)",
          "totalTip": "@variableType(integer)",
          "totalPaid": "@variableType(integer)"
        }
      ]
    }
    """

  Scenario: create another customer
    Given the request body is:
    """
    {
      "firstName": "david",
      "lastName": "jones"
    }
    """
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

  Scenario: fully pay bill with another customer
    Given the request body is:
    """
    {
      "customerId": 2,
      "orderId": 1,
      "orderedId": 1,
      "pay": 2.99
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/bill/pay" using HTTP POST
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": [
        {
          "id": "@variableType(integer)",
          "state": "closed",
          "customer": "@variableType(string)",
          "ordered": [
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
          ],
          "transactions": [
            {
              "id": "@variableType(integer)",
              "payee": "@variableType(string)",
              "ordered": [
                {
                  "id": "@variableType(integer)",
                  "name":"@variableType(string)"
                }
              ],
              "paid": "@variableType(integer)",
              "tip": "@variableType(integer)"
            },
            {
              "id": "@variableType(integer)",
              "payee": "@variableType(string)",
              "ordered": [
                {
                  "id": "@variableType(integer)",
                  "name":"@variableType(string)"
                }
              ],
              "paid": "@variableType(float)",
              "tip": "@variableType(integer)"
            }
          ],
          "totalOriginalPrice": "@variableType(float)",
          "totalDiscount": "@variableType(integer)",
          "totalSavings": "@variableType(integer)",
          "totalCharged": "@variableType(float)",
          "totalDue": "@variableType(integer)",
          "totalTip": "@variableType(integer)",
          "totalPaid": "@variableType(float)"
        }
      ]
    }
    """

  Scenario: try to over pay bill
    Given the request body is:
    """
    {
      "customerId": 1,
      "orderId": 1,
      "orderedId": 1,
      "pay": 5.00
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/bill/pay" using HTTP POST
    Then the response code is 400
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": {
        "errors": {
          "pay": "@variableType(array)"
        }
      }
    }
    """

  Scenario: pay tip
    Given the request body is:
    """
    {
      "customerId": 1,
      "orderId": 1,
      "orderedId": 1,
      "tip": 5.00
    }
    """
    And the "Content-Type" request header is "application/json"
    When I request "/bill/tip" using HTTP POST
    Then the response code is 200
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": [
        {
          "id": "@variableType(integer)",
          "state": "closed",
          "customer": "@variableType(string)",
          "ordered": [
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
          ],
          "transactions": [
            {
              "id": "@variableType(integer)",
              "payee": "@variableType(string)",
              "ordered": [
                {
                  "id": "@variableType(integer)",
                  "name":"@variableType(string)"
                }
              ],
              "paid": "@variableType(integer)",
              "tip": "@variableType(integer)"
            },
            {
              "id": "@variableType(integer)",
              "payee": "@variableType(string)",
              "ordered": [
                {
                  "id": "@variableType(integer)",
                  "name":"@variableType(string)"
                }
              ],
              "paid": "@variableType(float)",
              "tip": "@variableType(integer)"
            },
            {
              "id": "@variableType(integer)",
              "payee": "@variableType(string)",
              "ordered": [
                {
                  "id": "@variableType(integer)",
                  "name":"@variableType(string)"
                }
              ],
              "paid": "@variableType(integer)",
              "tip": "@variableType(integer)"
            }
          ],
          "totalOriginalPrice": "@variableType(float)",
          "totalDiscount": "@variableType(integer)",
          "totalSavings": "@variableType(integer)",
          "totalCharged": "@variableType(float)",
          "totalDue": "@variableType(integer)",
          "totalTip": "@variableType(integer)",
          "totalPaid": "@variableType(float)"
        }
      ]
    }
    """

  Scenario: try to cancel order
    Given the "Content-Type" request header is "application/json"
    When I request "/order?orderId=1" using HTTP DELETE
    Then the response code is 400
    Then the response body contains JSON:
    """
    {
      "meta": {
        "status": "@variableType(string)"
      },
      "data": {
        "errors": "@variableType(array)"
      }
    }
    """