@ticket-BB-15968
@regression
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroCheckoutBundle:Checkout.yml
@fixture-OroCheckoutBundle:InventoryLevel.yml
@fixture-OroPromotionBundle:promotions-with-coupons-basic.yml

Feature: Checkout with shipping rule based on subtotal
  In order to process checkout
  As a Buyer
  I need to have an ability to use shipping rules based on subtotal, without additional costs like discounts

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Create shipping rule based on subtotal value and check usage during checkout
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    When I open page with shopping list List 1
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    When I scroll to "I have a Coupon Code"
    And I click "I have a Coupon Code"
    And I type "coupon-1" in "CouponCodeInput"
    And I click "Apply"
    Then I should see "Coupon code has been applied successfully, please review discounts" flash message
    Then I should see Checkout Totals with data:
      | Subtotal | $10.00 |
      | Discount | -$1.00 |
      | Shipping | $3.00  |
    And I should see "Total $12.00"

    When I proceed as the Admin
    And I login as administrator
    And I go to System / Shipping Rules
    And I click "edit" on first row in grid
    And I fill "Shipping Rule" with:
      | Expression | subtotal.value != 10.00 |
    And I save form
    Then I should see "Shipping rule has been saved" flash message
    When I proceed as the User
    And I reload the page
    Then I should see "The selected shipping method is not available. Please return to the shipping method selection step and select a different one." flash message
    And I should see "No shipping methods are available, please contact us to complete the order submission."

    When I proceed as the Admin
    And I fill "Shipping Rule" with:
      | Expression | subtotal.value = 10.00 |
    And I save form
    When I proceed as the User
    And I reload the page
    Then I should not see "The selected shipping method is not available. Please return to the shipping method selection step and select a different one." flash message
    And I should not see "No shipping methods are available, please contact us to complete the order submission."
    And I should see "SELECT A SHIPPING METHOD"
    And I should see "Flat Rate: $3.00"
