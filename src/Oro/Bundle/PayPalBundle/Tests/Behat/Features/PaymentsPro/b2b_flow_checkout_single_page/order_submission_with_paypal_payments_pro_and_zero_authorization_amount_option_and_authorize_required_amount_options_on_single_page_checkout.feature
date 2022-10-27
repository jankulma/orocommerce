@regression
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
@ticket-BB-13932

Feature: Order submission with PayPal Payments Pro and zero "authorization amount" option and "authorize required amount" options on single page checkout

  In order to check that PayPal Payments Pro with zero "authorization amount" and "authorize required amount" options works on single page checkout
  As a user
  I want to finish checkout and save credit card data
  I want to finish checkout using already saved credit card data

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I activate "Single Page Checkout" workflow
    And I create PayPal PaymentsPro integration with following settings:
      | zeroAmountAuthorization        | true |
      | authorizationForRequiredAmount | true |
    And I create payment rule with "PayPalPro" payment method

  Scenario: Error from Backend when pay order with PayPal Payments Pro
    Given There are products in the system available for order
    And I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I open page with shopping list List 2
    And I click "Create Order"
    And I fill credit card form with next data:
      | CreditCardNumber | 5105105105105100 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    When I click "Submit Order"
    Then I should see "We were unable to process your payment. Please verify your payment information and try again." flash message

  Scenario: Successful first order payment with PayPal Payments Pro and enabled "zero authorization amount" option
    Given I open page with shopping list List 2
    And I click "Create Order"
    And I fill credit card form with next data:
      | CreditCardNumber | 5424000000000015 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    When I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title

  Scenario: Check that payment can be captured for the first order
    Given I proceed as the Admin
    And I login as administrator
    And I go to Sales/Orders
    And I click View Payment authorized in grid
    When I click "Capture"
    And I click "Yes, Charge" in modal window
    Then I should see "The payment of $13.00 has been captured successfully" flash message

  Scenario: Successful second order payment and amount capture with PayPal Payments Pro and already saved credit card data
    Given I proceed as the Buyer
    And I open page with shopping list List 1
    And I click "Create Order"
    When I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
    And I should not see "Payment has not been processed."

  Scenario: Check that payment can be captured for the second order
    Given I proceed as the Admin
    And I go to Sales/Orders
    And I click View Payment authorized in grid
    When I click "Capture"
    And I click "Yes, Charge" in modal window
    Then I should see "The payment of $13.00 has been captured successfully" flash message
