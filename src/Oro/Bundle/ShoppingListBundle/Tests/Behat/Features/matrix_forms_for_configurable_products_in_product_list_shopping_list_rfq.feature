@fixture-OroProductBundle:configurable_products.yml
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentTermBundle:PaymentTermIntegration.yml
@fixture-OroCheckoutBundle:Checkout.yml
@regression
@ticket-BB-10500

Feature: Matrix forms for configurable products in product list, shopping list, RFQ
  In order to quickly add and update specific configurations of a complex product to the shopping list and RFQ
  As a Buyer
  I want to add a complex product to the shopping list via matrix form displayed in the product views

  Scenario: Create sessions
    Given sessions active:
      | User  | first_session  |
      | Admin | second_session |

  Scenario: Prepare product attributes
    Given I proceed as the Admin
    Given I login as administrator

    # Create attribute 1
    And I go to Products / Product Attributes
    And I click "Create Attribute"
    And I fill form with:
      | Field Name | Attribute_1 |
      | Type       | Select      |
    And I click "Continue"
    And I fill form with:
      | Label      | Attribute 1 |
    And set Options with:
      | Label    |
      | Value 11 |
      | Value 12 |
      | Value 13 |
      | Value 14 |
    And I save form
    Then I should see "Attribute was successfully saved" flash message

    # Create attribute 2
    And I go to Products / Product Attributes
    And I click "Create Attribute"
    And I fill form with:
      | Field Name | Attribute_2 |
      | Type       | Select      |
    And I click "Continue"
    And I fill form with:
      | Label      | Attribute 2 |
    And set Options with:
      | Label    |
      | Value 21 |
      | Value 22 |
      | Value 23 |
    And I save form
    Then I should see "Attribute was successfully saved" flash message

    # Create attribute 3
    And I go to Products / Product Attributes
    And I click "Create Attribute"
    And I fill form with:
      | Field Name | Attribute_3 |
      | Type       | Select      |
    And I click "Continue"
    And I fill form with:
      | Label      | Attribute 3 |
    And set Options with:
      | Label    |
      | Value 31 |
      | Value 32 |
    And I save form
    Then I should see "Attribute was successfully saved" flash message

    # Update schema
    And I go to Products / Product Attributes
    And I confirm schema update

    # Update attribute family
    And I go to Products / Product Families
    And I click Edit Attribute Family in grid
    And set Attribute Groups with:
      | Label           | Visible | Attributes |
      | Attribute group | true    | [SKU, Name, Is Featured, New Arrival, Brand, Description, Short Description, Images, Inventory Status, Meta title, Meta description, Meta keywords, Product prices, Attribute 1, Attribute 2, Attribute 3] |
    And I save form
    Then I should see "Successfully updated" flash message

  Scenario: Prepare configurable products

    # Variants for CNF_A
    Given I go to Products / Products
    And filter SKU as is equal to "PROD_A_1"
    And I click Edit PROD_A_1 in grid
    And I fill in product attribute "Attribute_1" with "Value 11"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_A_2"
    And I click Edit PROD_A_2 in grid
    And I fill in product attribute "Attribute_1" with "Value 12"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_A_4"
    And I click Edit PROD_A_4 in grid
    And I fill in product attribute "Attribute_1" with "Value 14"
    And I save form
    Then I should see "Product has been saved" flash message

    # Variants for CNF_B
    And I go to Products / Products
    And filter SKU as is equal to "PROD_B_11"
    And I click Edit PROD_B_11 in grid
    And I fill in product attribute "Attribute_1" with "Value 11"
    And I fill in product attribute "Attribute_2" with "Value 21"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_B_12"
    And I click Edit PROD_B_12 in grid
    And I fill in product attribute "Attribute_1" with "Value 11"
    And I fill in product attribute "Attribute_2" with "Value 22"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_B_21"
    And I click Edit PROD_B_21 in grid
    And I fill in product attribute "Attribute_1" with "Value 12"
    And I fill in product attribute "Attribute_2" with "Value 21"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_B_23"
    And I click Edit PROD_B_23 in grid
    And I fill in product attribute "Attribute_1" with "Value 12"
    And I fill in product attribute "Attribute_2" with "Value 23"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_B_31"
    And I click Edit PROD_B_31 in grid
    And I fill in product attribute "Attribute_1" with "Value 13"
    And I fill in product attribute "Attribute_2" with "Value 21"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_B_32"
    And I click Edit PROD_B_32 in grid
    And I fill in product attribute "Attribute_1" with "Value 13"
    And I fill in product attribute "Attribute_2" with "Value 22"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_B_43"
    And I click Edit PROD_B_43 in grid
    And I fill in product attribute "Attribute_1" with "Value 14"
    And I fill in product attribute "Attribute_2" with "Value 23"
    And I save form
    Then I should see "Product has been saved" flash message

    # Variants for CNF_C
    And I go to Products / Products
    And filter SKU as is equal to "PROD_C_111"
    And I click Edit PROD_C_111 in grid
    And I fill in product attribute "Attribute_1" with "Value 11"
    And I fill in product attribute "Attribute_2" with "Value 21"
    And I fill in product attribute "Attribute_3" with "Value 31"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_C_121"
    And I click Edit PROD_C_121 in grid
    And I fill in product attribute "Attribute_1" with "Value 11"
    And I fill in product attribute "Attribute_2" with "Value 22"
    And I fill in product attribute "Attribute_3" with "Value 31"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_C_231"
    And I click Edit PROD_C_231 in grid
    And I fill in product attribute "Attribute_1" with "Value 12"
    And I fill in product attribute "Attribute_2" with "Value 23"
    And I fill in product attribute "Attribute_3" with "Value 31"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_C_311"
    And I click Edit PROD_C_311 in grid
    And I fill in product attribute "Attribute_1" with "Value 13"
    And I fill in product attribute "Attribute_2" with "Value 21"
    And I fill in product attribute "Attribute_3" with "Value 31"
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "PROD_C_232"
    And I click Edit PROD_C_232 in grid
    And I fill in product attribute "Attribute_1" with "Value 12"
    And I fill in product attribute "Attribute_2" with "Value 23"
    And I fill in product attribute "Attribute_3" with "Value 32"
    And I save form
    Then I should see "Product has been saved" flash message

  Scenario: Save configurable products with simple products selected
    And I go to Products / Products
    And filter SKU as is equal to "CNF_A"
    And I click Edit CNF_A in grid
    And I should see "No records found"
    And I fill "ProductForm" with:
      | Configurable Attributes | [Attribute 1] |
    And I check PROD_A_1 and PROD_A_2 in grid
    And I check PROD_A_4 record in grid
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "CNF_B"
    And I click Edit CNF_B in grid
    And I should see "No records found"
    And I fill "ProductForm" with:
      | Configurable Attributes | [Attribute 1, Attribute 2] |
    And I check PROD_B_11 and PROD_B_12 in grid
    And I check PROD_B_21 and PROD_B_23 in grid
    And I check PROD_B_31 and PROD_B_32 in grid
    And I check PROD_B_43 record in grid
    And I save form
    Then I should see "Product has been saved" flash message

    And I go to Products / Products
    And filter SKU as is equal to "CNF_C"
    And I click Edit CNF_C in grid
    And I should see "No records found"
    And I fill "ProductForm" with:
      | Configurable Attributes | [Attribute 1, Attribute 2, Attribute 3] |
    And I check PROD_C_111 and PROD_C_121 in grid
    And I check PROD_C_231 and PROD_C_311 in grid
    And I check PROD_C_232 record in grid
    And I save form
    Then I should see "Product has been saved" flash message

  Scenario: Check prices container on configurable product view is visible only when there are prices
    Given I proceed as the User
    Given I signed in as AmandaRCole@example.org on the store frontend
    And type "CNF_A" in "search"
    And click "Search Button"
    And click "View Details" for "CNF_A" product
    Then I should see an "One Dimensional Matrix Grid Form" element
    And I should not see an "Default Page Prices" element
    Then type "CNF_B" in "search"
    And click "Search Button"
    And click "View Details" for "CNF_B" product
    Then I should see an "Matrix Grid Form" element
    And I should see an "Default Page Prices" element
    And I should see "Item 1 $ 12.00" in the "Default Page Prices" element

  Scenario: Check clear all button and totals container
    Then type "CNF_B" in "search"
    And click "Search Button"
    And click "View Details" for "CNF_B" product
    Then I should see an "Matrix Grid Form" element
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 1        | 1        | -        |
      | Value 12 | 1        | -        | 1        |
      | Value 13 |          |          | -        |
      | Value 14 | -        | -        | 1        |
    Then I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 1        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    And I should see "Clear All Button" element inside "Matrix Grid Form Totals" element
    And I should see "Total QTY 5 | Total $60.00" in the "Matrix Grid Form Totals" element
    And I click "Clear All"
    Then I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      |          |          | N/A      |
      |          | N/A      |          |
      |          |          | N/A      |
      | N/A      | N/A      |          |
    And I should see "Total QTY 0 | Total $0.00" in the "Matrix Grid Form Totals" element
    Then I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 1        | 1        | -        |
      | Value 12 | 1        | -        | 1        |
      | Value 13 |          |          | -        |
      | Value 14 | -        | -        | 1        |
    And I focus on "matrix_collection[rows][0][columns][0][quantity]" field and press Enter key
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"

    Then I should see an "Matrix Grid Form" element
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 1        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    And I should see "Clear All Button" element inside "Matrix Grid Form Totals" element
    And I should see "Total QTY 5 | Subtotal $60.00" in the "Matrix Grid Form Totals" element
    And I click "Clear All"
    Then I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      |          |          | N/A      |
      |          | N/A      |          |
      |          |          | N/A      |
      | N/A      | N/A      |          |
    And I should see "Total QTY 0 | Subtotal $0.00" in the "Matrix Grid Form Totals" element

    Then type "CNF_B" in "search"
    And click "Search Button"
    Then I should see an "Matrix Grid Form" element
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 1        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    And I should see "Clear All Button" element inside "Matrix Grid Form Totals" element
    And I should see "Total QTY 5 | Total $60.00" in the "Matrix Grid Form Totals" element
    And I click "Clear All"
    Then I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      |          |          | N/A      |
      |          | N/A      |          |
      |          |          | N/A      |
      | N/A      | N/A      |          |
    And I should see "Total QTY 0 | Total $0.00" in the "Matrix Grid Form Totals" element
    And I am on the homepage
    Then I open shopping list widget
    And I click "View Details"
    And I click "Delete"
    And I click "Yes, Delete"
    Then I should see "You do not have available Shopping Lists"

#  TODO: Uncomment after BB-13368 is fixed
#  Scenario: Check product view is working after changing to No Matrix Form for guest user
#    Given I click "Sign Out"
#    And I proceed as the Admin
#    And I go to System/ Configuration
#    And I follow "Commerce/Sales/Shopping List" on configuration sidebar
#    And uncheck "Use default" for "Enable guest shopping list" field
#    And check "Enable guest shopping list"
#    And I save form
#    And I proceed as the User
#    And type "CNF_B" in "search"
#    And click "Search Button"
#    And click "View Details" for "CNF_B" product
#    Then I should see an "Matrix Grid Form" element
#    And I proceed as the Admin
#    And I go to System/ Configuration
#    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
#    And uncheck "Use default" for "Product Views" field
#    And I fill in "Product Views" with "No Matrix Form"
#    And I save form
#    And I should see "Configuration saved" flash message
#    And I proceed as the User
#    And I reload the page
#    Then I should not see "Error occurred during layout update. Please contact system administrator."
#    And I should not see an "Configurable Product Shopping List Form" element
#    And I proceed as the Admin
#    And I go to System/ Configuration
#    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
#    And check "Use default" for "Product Views" field
#    And I save form
#    And I should see "Configuration saved" flash message
#    And I proceed as the User

  Scenario: Check related products are clickable on the configurable product page
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Catalog/Related Items" on configuration sidebar
    And I fill "RelatedProductsConfig" with:
      | Minimum Items Use Default | false |
      | Minimum Items             | 1     |
    And I save form
    Then I go to Products / Products
    And filter SKU as is equal to "CNF_B"
    And I click Edit CNF_B in grid
    And I click "Select related products"
    And I select following records in "SelectRelatedProductsGrid" grid:
      | SKU123 |
    And I click "Select products"
    And I save and close form
    And I proceed as the User
    And type "CNF_B" in "search"
    And click "Search Button"
    And click "View Details" for "CNF_B" product
    And I should see "400-Watt Bulb Work Light" in related products
    Then I click "400-Watt Bulb Work Light"
    And I should see "All Products / 400-Watt Bulb Work Light"

  Scenario: Order empty matrix form
    And type "CNF_B" in "search"
    And click "Search Button"
    Then I should see an "Matrix Grid Form" element
    And I click "Add to Shopping List"
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    Then I should see an "Matrix Grid Form" element
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      |          |          | N/A      |
      |          | N/A      |          |
      |          |          | N/A      |
      | N/A      | N/A      |          |
    Given I click "Create Order"
    Then I should see "Cannot create order because Shopping List has no items" flash message

  Scenario: Create request for quote with empty matrix form
    Given I click "Request Quote"
    Then I should not see "Confirmation This shopping list contains configurable products with no variations. Proceed to RFQ without these products?"
    And I should see "Products with no quantities have not been added to this request."
    And I should see "Request A Quote"

  Scenario: Order empty matrix form and a simple product
    Given type "SKU123" in "search"
    And click "Search Button"
    And I click "Add to Shopping List"
    And I should see "Product has been added to \"Shopping list\""
    And I click "Shopping list"
    Given I click "Create Order"
    Then I should see "Confirmation This shopping list contains configurable products with no variations. Proceed to checkout without these products?"
    And I click "Proceed"
    Then I should see "Some products have not been added to this order." flash message
    And I should see "Checkout"
    And I should see "400-Watt Bulb Work Light"
    And I should not see "Configurable Product B"

  Scenario: Create request for quote with empty configurable product and a simple product
    Given I open shopping list widget
    And I click "View Details"
    And I click "Request Quote"
    Then I should see "Confirmation This shopping list contains configurable products with no variations. Proceed to RFQ without these products?"
    And I click "Proceed"
    Then I should see "Request A Quote"
    And I should see "400-Watt Bulb Work Light" in the "RequestAQuoteProducts" element
    And I should not see "Configurable Product B" in the "RequestAQuoteProducts" element

  Scenario: Update empty matrix form in the shopping list and create order
    Given I open shopping list widget
    And I click "View Details"
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 1        | 1        | -        |
      | Value 12 | 1        | -        | 1        |
      | Value 13 |          |          | -        |
      | Value 14 | -        | -        | 1        |
    And I click "Update"
    Then I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 1        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    And I click "Create Order"
    Then I should not see "Confirmation This shopping list contains configurable products with no variations. Proceed to checkout without these products?"
    And I should see "Checkout"
    And I should see "Configurable Product B"

  Scenario: Create request for quote with configurable product
    Given I open shopping list widget
    And I click "View Details"
    And I click "Request Quote"
    Then I should see "Request A Quote"
    And I should see "400-Watt Bulb Work Light" in the "RequestAQuoteProducts" element
    And I should see "Product B 11" in the "RequestAQuoteProducts" element
    And I should see "Product B 12" in the "RequestAQuoteProducts" element
    And I should see "Product B 21" in the "RequestAQuoteProducts" element
    And I should see "Product B 23" in the "RequestAQuoteProducts" element
    And I should see "Product B 43" in the "RequestAQuoteProducts" element
    And I should not see "Configurable Product B" in the "RequestAQuoteProducts" element
    Then I open shopping list widget
    And I click "View Details"
    And I click "Remove Line Item"
    And I click "Yes, Delete"
    And I click "Remove Line Item"
    And I click "Yes, Delete"
    Then I should see "The Shopping List is empty"

  Scenario: Empty matrix form disabled
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And uncheck "Use default" for "Allow to add empty products" field
    And I uncheck "Allow to add empty products"
    And I save form
    Given I proceed as the User
    And type "CNF_B" in "search"
    And click "Search Button"
    Then I should see an "Matrix Grid Form" element
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      |          |          | N/A      |
      |          | N/A      |          |
      |          |          | N/A      |
      | N/A      | N/A      |          |
    And I click "Add to Shopping list" for "CNF_B" product
    Then I should see "Please provide at least one value before adding the product to your shopping list"
    Then I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 1        | 1        | -        |
      | Value 12 | 1        | -        | 1        |
      | Value 13 |          |          | -        |
      | Value 14 | -        | -        | 1        |
    And I click "Add to Shopping list" for "CNF_B" product
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    Then I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 1        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    Given I click "Create Order"
    Then I should not see "Confirmation This shopping list contains configurable products with no variations. Proceed to checkout without these products?"
    And I should see "Checkout"
    And I should see "Configurable Product B"
    And I open shopping list widget
    And I click "View Details"
    And I click "Remove Line Item"
    And I click "Yes, Delete"
    Then I should see "The Shopping List is empty"
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And check "Use default" for "Allow to add empty products" field
    And I save form

  Scenario: Matrix form with single attribute
    Given I proceed as the User
    And type "Configurable Product A" in "search"
    And click "Search Button"
    And I click "List View"
    Then I should see "One Dimensional Matrix Grid Form" for "CNF_A" product
    And I click "No Image View"
    Then I should see "One Dimensional Matrix Grid Form" for "CNF_A" product
    And click "View Details" for "CNF_A" product
    Then I should see an "One Dimensional Matrix Grid Form" element
    And I fill "One Dimensional Matrix Grid Form" with:
      | Value 11 | Value 12 | Value 13 | Value 14 |
      | 1        | -        | -        | 1        |
    And I click "Add to Shopping list"
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    Then I should see an "One Dimensional Matrix Grid Form" element
    And I should see next rows in "One Dimensional Matrix Grid Form" table
      | Value 11 | Value 12 | Value 13 | Value 14 |
      | 1        |          | N/A      | 1        |
    And I fill "One Dimensional Matrix Grid Form" with:
      | Value 11 | Value 12 | Value 13 | Value 14 |
      | -        | 2        | -        |          |
    And I click "Update"
    And I click "Configurable Product A"
    Then I should see an "One Dimensional Matrix Grid Form" element
    And I should see next rows in "One Dimensional Matrix Grid Form" table
      | Value 11 | Value 12 | Value 13 | Value 14 |
      | 1        | 2        | N/A      |          |
    And type "CNF_A" in "search"
    And click "Search Button"
    And I click "List View"
    Then I should see an "One Dimensional Matrix Grid Form" element
    And I should see next rows in "One Dimensional Matrix Grid Form" table
      | Value 11 | Value 12 | Value 13 | Value 14 |
      | 1        | 2        | N/A      |          |
    And I click "No Image View"
    Then I should see an "One Dimensional Matrix Grid Form" element
    And I should see next rows in "One Dimensional Matrix Grid Form" table
      | Value 11 | Value 12 | Value 13 | Value 14 |
      | 1        | 2        | N/A      |          |
    And I click on "Shopping List Dropdown"
    And I click "Remove From Shopping List"
    Then I should see "Product has been removed from \"Shopping list\""
    And I click "Shopping list"
    Then I should see "The Shopping List is empty"

  Scenario: Matrix form with two attributes
    Given type "Configurable Product B" in "search"
    And click "Search Button"
    And I click "List View"
    Then I should see "Matrix Grid Form" for "CNF_B" product
    And I click "No Image View"
    Then I should see "Matrix Grid Form" for "CNF_B" product
    And click "View Details" for "CNF_B" product
    Then I should see an "Matrix Grid Form" element
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 1        | 1        | -        |
      | Value 12 | 1        | -        | 1        |
      | Value 13 |          |          | -        |
      | Value 14 | -        | -        | 1        |
    And I click "Add to Shopping List"
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    Then I should see an "Matrix Grid Form" element
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 1        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    And I click "Remove Line Item"
    And I click "Yes, Delete"
    Then I should see "The Shopping List is empty"

  Scenario: Matrix form with three attributes
    Given type "Configurable Product C" in "search"
    And click "Search Button"
    And I click "List View"
    Then I should not see "Matrix Grid Form" for "CNF_C" product
    And I click "No Image View"
    Then I should not see "Matrix Grid Form" for "CNF_C" product
    And click "View Details" for "CNF_C" product
    Then I should see an "Configurable Product Shopping List Form" element
    And I fill in "Attribute 1" with "Value 12"
    And I fill in "Attribute 2" with "Value 23"
    And I fill in "Attribute 3" with "Value 32"
    And I click "Add to Shopping List"
    Then I should see "Product has been added to \"Shopping list\""
    And I click "Shopping list"
    #next 6 lines related to @ticket-BB-10500
    And I should see text matching "Attribute 1: Value 12"
    And I should see text matching "Attribute 2: Value 23"
    And I should see text matching "Attribute 3: Value 32"
    And I should not see text matching "Attribute_1"
    And I should not see text matching "Attribute_2"
    And I should not see text matching "Attribute_3"
    And I click "Delete"
    And I click "Yes, Delete"
    Then I should see "You do not have available Shopping Lists"

  Scenario: Disabled matrix form in Product List View
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And uncheck "Use default" for "Product Listings" field
    And I fill in "Product Listings" with "No Matrix Form"
    And I save form
    Given I proceed as the User
    And type "Configurable Product" in "search"
    And click "Search Button"
    And I click "List View"
    Then I should not see "Matrix Grid Form" for "CNF_A" product
    And I should not see "Matrix Grid Form" for "CNF_B" product
    And I should not see "Matrix Grid Form" for "CNF_C" product
    And I click "No Image View"
    Then I should not see "Matrix Grid Form" for "CNF_A" product
    And I should not see "Matrix Grid Form" for "CNF_B" product
    And I should not see "Matrix Grid Form" for "CNF_C" product
    Then I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And check "Use default" for "Product Listings" field
    And I save form

  Scenario: Popup matrix form in Product List View and Shopping Lists
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And uncheck "Use default" for "Product Listings" field
    And I fill in "Product Listings" with "Popup Matrix Form"
    And uncheck "Use default" for "Shopping Lists" field
    And I fill in "Shopping Lists" with "Popup Matrix Form"
    And I save form
    Given I proceed as the User
    And type "CNF_B" in "search"
    And click "Search Button"
    And I click "Gallery View"
    Then I should see "Add to Shopping list" for "CNF_B" product
    And I should not see an "Matrix Grid Form" element
    And I click "Add to Shopping list" for "CNF_B" product
    Then I should see an "Matrix Grid Form" element
    And I click "Matrix Grid Popup Close Button"
    And I click "List View"
    Then I should see "Add to Shopping list" for "CNF_B" product
    And I should not see an "Matrix Grid Form" element
    And I click "Add to Shopping list" for "CNF_B" product
    Then I should see an "Matrix Grid Form" element
    # Check popup close button and product name in popup title
    And I should see "Configurable Product B Item #: CNF_B" in the "Matrix Grid Popup" element
    And I should see "Matrix Grid Popup Close Button" element inside "Matrix Grid Popup" element
    And I click "Matrix Grid Popup Close Button"
    Then I should not see an "Matrix Grid Popup" element
    And I click "Add to Shopping list" for "CNF_B" product
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 1        | 1        | -        |
      | Value 12 | 1        | -        | 1        |
      | Value 13 |          |          | -        |
      | Value 14 | -        | -        | 1        |
    And I click "Add to Shopping list"
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    Then I should not see an "Matrix Grid Form" element
    And I should see "Update"
    And I click "Update"
    # Check popup close button and product name in popup title
    And I should see "Configurable Product B Item #: CNF_B" in the "Matrix Grid Popup" element
    And I should see "Matrix Grid Popup Close Button" element inside "Matrix Grid Popup" element
    And I click "Matrix Grid Popup Close Button"
    Then I should not see an "Matrix Grid Popup" element
    And I click "Update"
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 1        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | -        | -        | -        |
      | Value 12 | -        | -        | 3        |
      | Value 13 | -        | -        | -        |
      | Value 14 | -        | -        | -        |
    And I click "Update Shopping list"
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And type "CNF_B" in "search"
    And click "Search Button"
    And I should see "Update Shopping list" for "CNF_B" product
    And I click "Update Shopping list"
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 3        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 5        | -        | -        |
      | Value 12 | -        | -        | -        |
      | Value 13 | -        | -        | -        |
      | Value 14 | -        | -        | -        |
    And I click "Update Shopping list"
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    And I should see "Update"
    And I click "Update"
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 5        | 1        | N/A      |
      | 1        | N/A      | 3        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
    And I click "Close"
    And I click "Remove Line Item"
    And I click "Yes, Delete"
    Then I should see "The Shopping List is empty"
    Then I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And check "Use default" for "Product Listings" field
    And check "Use default" for "Shopping Lists" field
    And I save form

  Scenario: Disabled matrix form in Shopping List View
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And uncheck "Use default" for "Shopping Lists" field
    And I fill in "Shopping Lists" with "Group Single Products"
    And I save form
    Given I proceed as the User
    And type "Configurable Product B" in "search"
    And click "Search Button"
    And click "View Details" for "CNF_B" product
    Then I should see an "Matrix Grid Form" element
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 1        |          | -        |
      | Value 12 |          | -        | 1        |
      | Value 13 |          |          | -        |
      | Value 14 | -        | -        |          |
    And I click "Add to Shopping List"
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    Then I should not see an "Matrix Grid Form" element
    And I should see text matching "Attribute 1: Value 11"
    And I should see text matching "Attribute 2: Value 21"
    And I should see text matching "Attribute 1: Value 12"
    And I should see text matching "Attribute 2: Value 23"
    And I click "Delete"
    And I click "Yes, Delete"
    Then I should see "You do not have available Shopping Lists"

  Scenario: Enable popup matrix form in Product View
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And uncheck "Use default" for "Product Views" field
    And I fill in "Product Views" with "Popup Matrix Form"
    And I save form
    Given I proceed as the User
    Given type "Configurable Product B" in "search"
    And click "Search Button"
    And click "View Details" for "CNF_B" product
    Then I should not see an "Matrix Grid Form" element
    And I press "Add to Shopping List"
    Then I should see an "Matrix Grid Form" element
    # Check popup close button and product name in popup title
    And I should see "Configurable Product B Item #: CNF_B" in the "Matrix Grid Popup" element
    And I should see "Matrix Grid Popup Close Button" element inside "Matrix Grid Popup" element
    And I click "Matrix Grid Popup Close Button"
    Then I should not see an "Matrix Grid Popup" element
    And I reload the page

  Scenario: Disabled matrix form in Product View
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And uncheck "Use default" for "Product Views" field
    And I fill in "Product Views" with "No Matrix Form"
    And I save form
    Given I proceed as the User
    And type "Configurable Product B" in "search"
    And click "Search Button"
    And click "View Details" for "CNF_B" product
    Then I should not see an "Matrix Grid Form" element
    And I should see an "Configurable Product Shopping List Form" element

  Scenario: Order with matrix form in Product List View
    Given I proceed as the Admin
    And I go to System/ Configuration
    And I follow "Commerce/Product/Configurable Products" on configuration sidebar
    And check "Use default" for "Shopping Lists" field
    And I save form
    Given I proceed as the User
    And type "CNF_B" in "search"
    And click "Search Button"
    Then I should see an "Matrix Grid Form" element
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | 1        | 1        | -        |
      | Value 12 | 1        | -        | 1        |
      | Value 13 |          |          | -        |
      | Value 14 | -        | -        | 1        |
    And I click "Add to Shopping list" for "CNF_B" product
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    Then I should see an "Matrix Grid Form" element
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 1        | N/A      |
      | 1        | N/A      | 1        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |

  Scenario: Update order with matrix form in Product List View
    Given type "CNF_B" in "search"
    And click "Search Button"
    Then I should see an "Matrix Grid Form" element
    And I fill "Matrix Grid Form" with:
      |          | Value 21 | Value 22 | Value 23 |
      | Value 11 | -        | 2        | -        |
      | Value 12 | -        | -        | 3        |
      | Value 13 | -        | -        | -        |
      | Value 14 | -        | -        | -        |
    And I click "Update Shopping list"
    Then I should see "Shopping list \"Shopping list\" was updated successfully"
    And I click "Shopping list"
    Then I should see an "Matrix Grid Form" element
    And I should see next rows in "Matrix Grid Form" table
      | Value 21 | Value 22 | Value 23 |
      | 1        | 2        | N/A      |
      | 1        | N/A      | 3        |
      |          |          | N/A      |
      | N/A      | N/A      | 1        |
