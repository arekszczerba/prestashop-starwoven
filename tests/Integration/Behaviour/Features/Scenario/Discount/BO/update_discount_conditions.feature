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
    Given there is a currency named "usd" with iso code "USD" and exchange rate of 0.92
    Given group "visitor" named "Visitor" exists
    Given group "guest" named "Guest" exists
    Given I add new zone "zone1" with following properties:
      | name    | zone1 |
      | enabled | true  |
    Given I add new zone "zone2" with following properties:
      | name    | zone2 |
      | enabled | true  |
    And language "en" with locale "en-US" exists
    And language with iso code "en" is the default one

  Scenario: Create discount with minimum products quantity
    When I create a "free_shipping" discount "discount_with_min_products" with following properties:
      | name[en-US] | Promotion |
    Then discount "discount_with_min_products" should have the following properties:
      | name[en-US] | Promotion     |
      | type        | free_shipping |
    When I update discount "discount_with_min_products" with the condition it requires at least 23 products
    Then discount "discount_with_min_products" should have the following properties:
      | name[en-US]              | Promotion     |
      | type                     | free_shipping |
      | minimum_product_quantity | 23            |
    And discount "discount_with_min_products" should have no product conditions

  Scenario: Create discount with restricted list of products
    Given I add product "beer_product" with following information:
      | name[en-US] | bottle of beer |
      | type        | standard       |
    And I add product "potato_chips_product" with following information:
      | name[en-US] | potato chips |
      | type        | standard     |
    When I create a "free_shipping" discount "discount_with_restricted_products" with following properties:
      | name[en-US] | Promotion |
    Then discount "discount_with_restricted_products" should have the following properties:
      | name[en-US] | Promotion     |
      | type        | free_shipping |
    When I update discount "discount_with_restricted_products" with following conditions matching at least 42 products:
      | condition_type | items                              |
      | products       | beer_product, potato_chips_product |
    Then discount "discount_with_restricted_products" should have the following properties:
      | name[en-US]              | Promotion     |
      | type                     | free_shipping |
      | minimum_product_quantity | 0             |
    Then discount "discount_with_restricted_products" should have the following product conditions matching at least 42 products:
      | condition_type | items                              |
      | products       | beer_product, potato_chips_product |

  Scenario: Create discount with minimum amount
    When I create a "free_shipping" discount "discount_with_min_amount" with following properties:
      | name[en-US] | Promotion |
    Then discount "discount_with_min_amount" should have the following properties:
      | name[en-US] | Promotion     |
      | type        | free_shipping |
    When I update discount "discount_with_min_amount" with the condition of a minimum amount:
      | minimum_amount                   | 12.56 |
      | minimum_amount_currency          | usd   |
      | minimum_amount_tax_included      | true  |
      | minimum_amount_shipping_included | true  |
    Then discount "discount_with_min_amount" should have the following properties:
      | name[en-US]                      | Promotion     |
      | type                             | free_shipping |
      | minimum_product_quantity         | 0             |
      | minimum_amount                   | 12.56         |
      | minimum_amount_currency          | usd           |
      | minimum_amount_tax_included      | true          |
      | minimum_amount_shipping_included | true          |
    And discount "discount_with_min_amount" should have no product conditions

  Scenario: Create discount with restricted list of combinations
    Given attribute group "Size" named "Size" in en language exists
    And attribute "S" named "S" in en language exists
    And attribute "M" named "M" in en language exists
    And attribute "L" named "L" in en language exists
    Given I add product "metal_tshirt" with following information:
      | name[en-US] | metal tshirt |
      | type        | combinations |
    When I generate combinations for product metal_tshirt using following attributes:
      | Size | [S,M,L] |
    Then product "metal_tshirt" should have following combinations:
      | id reference | combination name | reference | attributes | impact on price | quantity | is default |
      | metalTshirtS | Size - S         |           | [Size:S]   | 0               | 0        | true       |
      | metalTshirtM | Size - M         |           | [Size:M]   | 0               | 0        | false      |
      | metalTshirtL | Size - L         |           | [Size:L]   | 0               | 0        | false      |
    When I create a "free_shipping" discount "discount_with_restricted_combinations" with following properties:
      | name[en-US] | Promotion |
    Then discount "discount_with_restricted_combinations" should have the following properties:
      | name[en-US] | Promotion     |
      | type        | free_shipping |
    When I update discount "discount_with_restricted_combinations" with following conditions matching at least 42 products:
      | condition_type | items                      |
      | combinations   | metalTshirtM, metalTshirtL |
    Then discount "discount_with_restricted_combinations" should have the following properties:
      | name[en-US]              | Promotion     |
      | type                     | free_shipping |
      | minimum_product_quantity | 0             |
    Then discount "discount_with_restricted_combinations" should have the following product conditions matching at least 42 products:
      | condition_type | items                      |
      | combinations   | metalTshirtM, metalTshirtL |

  Scenario: Create discount with conditions on carriers
    Given I create carrier "carrier1" with specified properties:
      | name             | Carrier 1                          |
      | grade            | 1                                  |
      | trackingUrl      | http://example.com/track.php?num=@ |
      | active           | true                               |
      | max_width        | 1454                               |
      | max_height       | 1234                               |
      | max_depth        | 1111                               |
      | max_weight       | 3864                               |
      | group_access     | visitor, guest                     |
      | delay[en-US]     | Shipping delay                     |
      | shippingHandling | false                              |
      | isFree           | true                               |
      | shippingMethod   | weight                             |
      | zones            | zone1                              |
      | rangeBehavior    | disabled                           |
    And I create carrier "carrier2" with specified properties:
      | name             | Carrier 2                          |
      | grade            | 1                                  |
      | trackingUrl      | http://example.com/track.php?num=@ |
      | active           | true                               |
      | max_width        | 1454                               |
      | max_height       | 1234                               |
      | max_depth        | 1111                               |
      | max_weight       | 3864                               |
      | group_access     | visitor, guest                     |
      | delay[en-US]     | Shipping delay                     |
      | shippingHandling | false                              |
      | isFree           | true                               |
      | shippingMethod   | weight                             |
      | zones            | zone1                              |
      | rangeBehavior    | disabled                           |
    When I create a "free_shipping" discount "discount_with_carriers" with following properties:
      | name[en-US] | Promotion |
    Then discount "discount_with_carriers" should have the following properties:
      | name[en-US] | Promotion     |
      | type        | free_shipping |
    Then discount "discount_with_carriers" should have the following properties:
      | name[en-US]              | Promotion     |
      | type                     | free_shipping |
      | minimum_product_quantity | 0             |
    And discount "discount_with_carriers" should have no product conditions
    When I update discount "discount_with_carriers" with conditions based on carriers "carrier1,carrier2"
    Then discount "discount_with_carriers" should have the following properties:
      | name[en-US] | Promotion         |
      | carriers    | carrier1,carrier2 |
