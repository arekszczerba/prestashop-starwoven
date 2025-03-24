# ./vendor/bin/behat -c tests/Integration/Behaviour/behat.yml -s discount --tags full-ux-discount-test-free-gift
@restore-all-tables-before-feature
@full-ux-discount-test-free-gift

Feature: Full UX discount test
  PrestaShop allows BO users to create discounts
  As a BO user
  I must be able to create discounts using the new discounts

  Background:
    Given there is a customer named "testCustomer" whose email is "pub@prestashop.com"
    Given there is a customer named "testCustomer2" whose email is "pub2@prestashop.com"
    Given language with iso code "en" is the default one
    Given shop "shop1" with name "test_shop" exists
    And there is a currency named "usd" with iso code "USD" and exchange rate of 0.92
    And shop configuration for "PS_CART_RULE_FEATURE_ACTIVE" is set to 1

  Scenario: Create a complete discount with free gift using new CQRS
    Given I create an empty cart "dummy_cart" for customer "testCustomer"
    And there is a product in the catalog named "product1" with a price of 20.0 and 1000 items in stock
    When I create a free gift discount "complete_free_gift_discount" with following properties:
      | name[en-US]       | Promotion              |
      | active            | true                   |
      | valid_from        | 2025-01-01 11:05:00    |
      | valid_to          | 2025-12-01 00:00:00    |
      | code              | FREE_GIFT_2025         |
      | gift_product      | 1                      |
      | gift_combination  | 2                      |
    And discount "complete_free_gift_discount" should have the following properties:
      | name[en-US]       | Promotion              |
      | active            | true                   |
      | valid_from        | 2025-01-01 11:05:00    |
      | valid_to          | 2025-12-01 00:00:00    |
      | code              | FREE_GIFT_2025         |
      | gift_product      | 1                      |
      | gift_combination  | 2                      |
    And I add 1 product "product1" to the cart "dummy_cart"
    And cart "dummy_cart" total with tax included should be '$27.00'
    And I use a voucher "complete_free_gift_discount" on the cart "dummy_cart"
    And cart "dummy_cart" total with tax included should be '$27.00'
    Then my cart "dummy_cart" should have the following details:
      | total_products | $20.00  |
      | total_discount | $0.00 |
      | shipping       | $7.00   |
      | total          | $27.00  |
    Then gifted product "Hummingbird printed t-shirt" quantity in cart "dummy_cart" should be 1
