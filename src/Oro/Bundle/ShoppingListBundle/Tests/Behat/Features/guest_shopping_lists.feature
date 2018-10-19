@regression
@ticket-BB-10050
@fixture-OroShoppingListBundle:ProductFixture.yml
Feature: Guest Shopping Lists
  In order to allow unregistered customers to select goods they want to purchase
  As a Sales rep
  I want to enable shopping lists for guest customers

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |
      | Guest | system_session |

  Scenario: Create configurable attributes
    Given I proceed as the Admin
    And I login as administrator
    And I go to Products/ Product Attributes
    And click "Create Attribute"
    And fill form with:
      | Field Name | Color  |
      | Type       | Select |
    And click "Continue"
    And set Options with:
      | Label |
      | Black |
      | White |
    And save and close form
    And I click "Create Attribute"
    And fill form with:
      | Field Name | Size   |
      | Type       | Select |
    And click "Continue"
    And set Options with:
      | Label |
      | L     |
      | M     |
    When I save and close form
    And click update schema
    Then I should see Schema updated flash message

  Scenario: Add new attributes to product family
    Given I go to Products/ Product Families
    And I click Edit Default Family in grid
    And fill "Product Family Form" with:
      | Attributes | [Color, Size] |
    When I save and close form
    Then I should see "Successfully updated" flash message

  Scenario: Set new attributes values in simple products
    Given I go to Products/ Products
    When I click Edit "1GB81" in grid
    And I fill "ProductForm" with:
      | Color | Black |
      | Size  | L     |
    And I save and close form
    Then I should see "Product has been saved" flash message
    When I go to Products/ Products
    And I click Edit "1GB82" in grid
    And I fill "ProductForm" with:
      | Color | White |
      | Size  | M     |
    And I save and close form
    Then I should see "Product has been saved" flash message

  Scenario: Set configurable product variants
    Given I go to Products/ Products
    And I click Edit "1GB83" in grid
    And I check "Color Product Attribute" element
    And I check "Size Product Attribute" element
    And I save form
    And I check 1GB81 and 1GB82 in grid
    When I save and close form
    Then I should see "Product has been saved" flash message

  Scenario: Check Shopping List is not available for a guest on frontend
    Given I proceed as the User
    When I am on homepage
    Then I should not see "Shopping list"
    When type "SKU003" in "search"
    And I click "Search Button"
    Then I should see "Product3"
    But I should not see "Add to Shopping List"

  Scenario: Configurable product variants and matrix button shouldn't be available on front store
    And I open product with sku "1GB83" on the store frontend
    Then I should not see "Color"
    And I should not see "Size"
    And I should not see "Order with Matrix Grid"

  Scenario: Check default status of guest shopping list in configurations
    Given I proceed as the Admin
    And I go to System/Configuration
    When I follow "Commerce/Sales/Shopping List" on configuration sidebar
    Then the "Enable guest shopping list" checkbox should not be checked
    When uncheck "Use default" for "Enable guest shopping list" field
    And I check "Enable guest shopping list"
    And I save setting
    Then I should see "Configuration saved" flash message
    And the "Enable guest shopping list" checkbox should be checked

  Scenario: Empty shopping list shouldn't be created automatically for unauthorized user
    Given I proceed as the User
    And I am on homepage
    And I should see "No Shopping Lists"

  Scenario: Enable "Create Guest Shopping Lists Immediately"
    Given I proceed as the Admin
    And uncheck "Use default" for "Create Guest Shopping Lists Immediately" field
    And I check "Create Guest Shopping Lists Immediately"
    And I save setting
    Then I should see "Configuration saved" flash message

  Scenario: Configurable product variants and matrix button should be available on front store
    Given I proceed as the User
    When I open product with sku "1GB83" on the store frontend
    Then I should see an "Matrix Grid Form" element
    And I should see "Add to Shopping List"

  Scenario: Create Shopping List as unauthorized user from product view page
    Given I type "SKU003" in "search"
    And I click "Search Button"
    Then I should see "Product3"
    When I hover on "Shopping List Widget"
    And I should see "No Items" in the "Shopping List Widget" element
    And I should see "Add to Shopping List"
    When I click "View Details" for "SKU003" product
    Then I should see "Add to Shopping List"
    When I click "Add to Shopping List"
    Then I should see "Product has been added to" flash message
    And I should see "In shopping list"
    And I hover on "Shopping List Widget"
    And I should see "1 Item | $0.00" in the "Shopping List Widget" element

  Scenario: Check Update Shopping List
    Given I should see "Update Shopping list"
    When I fill "FrontendLineItemForm" with:
      | Quantity | 10   |
      | Unit     | each |
    And I click "Update Shopping List"
    Then I should see "Record has been successfully updated" flash message
    Then type "SKU003" in "search"
    And I click "Search Button"
    Then I should see "In shopping list"

  Scenario: Add more products to shopping list from list page (search)
    Given I type "CONTROL1" in "search"
    And I click "Search Button"
    And I should see "Control Product"
    When I click "Add to Shopping List" for "CONTROL1" product
    Then I should see "Product has been added to" flash message

  Scenario: Check added products available in Guest Shopping List
    Given I click "Shopping List"
    Then  I should see "Control Product"
    And  I should see "Product3"
    And I should not see following buttons:
      | Delete        |
      | Create Order  |
      | Request Quote |

  Scenario: Check Shopping list button in Guest mode when product remove from shopping list
    Given I type "CONTROL1" in "search"
    And I click "Search Button"
    And I should see "Control Product"
    And I click on "Shopping List Dropdown"
    And I click "Remove From Shopping List"
    And I should not see "Shopping List Dropdown"

  Scenario: Check shopping list count
    Given I proceed as the Admin
    When I go to Sales/ Shopping Lists
    Then number of records should be 1

  Scenario: Disable guest shopping list
    Given I go to System/ Configuration
    And I follow "Commerce/Sales/Shopping List" on configuration sidebar
    And uncheck "Use default" for "Enable guest shopping list" field
    And I uncheck "Enable guest shopping list"
    When I save setting
    And I should see "Configuration saved" flash message

  Scenario: Open homepage as a guest
    Given I proceed as the Guest
    When I am on homepage
    Then I should not see "Shopping list"

  Scenario: Re-check shopping list count
    Given I proceed as the Admin
    When I go to Sales/ Shopping Lists
    Then number of records should be 1
