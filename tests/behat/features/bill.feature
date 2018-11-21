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