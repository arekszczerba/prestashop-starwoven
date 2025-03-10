# ./vendor/bin/behat -c tests/Integration/Behaviour/behat.yml -s discount --tags full-ux-discount-test-free-gift
@restore-all-tables-before-feature
@full-ux-discount-test-free-gift

Feature: Full UX discount test
  PrestaShop allows BO users to create discounts
  As a BO user
  I must be able to create discounts using the new discounts

  Background:
    Given I have an empty default cart
    Given shop "shop1" with name "test_shop" exists
    And there is a currency named "usd" with iso code "USD" and exchange rate of 0.92
    And shop configuration for "PS_CART_RULE_FEATURE_ACTIVE" is set to 1
    And there is a product in the catalog named "product1" with a price of 19.812 and 1000 items in stock
    And language with iso code "en" is the default one

  Scenario: Create a complete discount with free gift using new CQRS
    When I create a free gift discount "complete_free_gift_discount" with following properties:
      | name[en-US]       | Promotion              |
      | active            | true                   |
      | valid_from        | 2025-01-01 11:05:00    |
      | valid_to          | 2025-12-01 00:00:00    |
      | code              | FREE_GIFT_2025         |
    And discount "complete_free_gift_discount" should have the following properties:
      | name[en-US]       | Promotion              |
      | active            | true                   |
      | valid_from        | 2025-01-01 11:05:00    |
      | valid_to          | 2025-12-01 00:00:00    |
      | code              | FREE_GIFT_2025         |

  Scenario: One product in cart, one discount offering only free gift
    Given discount "complete_free_gift_discount" should have the following properties:
      | name[en-US]   | Promotion      |
      | free_gift     | true           |
      | code          | FREE_GIFT_2025 |
    And I add 1 items of product "product1" in my cart
    When I apply the voucher code "complete_free_gift_discount"
    Then I should have 2 products in my cart
