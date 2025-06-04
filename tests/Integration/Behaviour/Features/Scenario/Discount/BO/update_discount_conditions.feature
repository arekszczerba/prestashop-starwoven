# ./vendor/bin/behat -c tests/Integration/Behaviour/behat.yml -s discount --tags update-discount-conditions
@restore-all-tables-before-feature
@restore-languages-after-feature
@update-discount-conditions
Feature: Update discount condition
  PrestaShop allows BO users to update discounts conditions
  As a BO user
  I must be able to update conditions on discounts

  Background:
    Given shop "shop1" with name "test_shop" exists

  Scenario: Create discount with minimum products quantity
    When I create a "free_shipping" discount "discount_with_min_products" with following properties:
      | name[en-US] | Promotion |
    Then discount "discount_with_min_products" should have the following properties:
      | name[en-US] | Promotion     |
      | type        | free_shipping |
    When I update discount "discount_with_min_products" with following conditions:
      | minimum_products_quantity | 23 |
    Then discount "discount_with_min_products" should have the following properties:
      | name[en-US]               | Promotion     |
      | type                      | free_shipping |
      | minimum_products_quantity | 23            |
