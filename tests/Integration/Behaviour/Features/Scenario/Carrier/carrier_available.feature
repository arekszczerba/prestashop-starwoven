# ./vendor/bin/behat -c tests/Integration/Behaviour/behat.yml -s carrier --tags carrier-available
@restore-all-tables-before-feature
@clear-cache-before-feature
@carrier-available
Feature: Carrier available
  As a BO user i want to recover the common carriers of different products, as well as those filtered

  Background:
    Given shop "shop1" with name "test_shop" exists
    And I set up shop context to single shop shop1
    And language "fr" with locale "fr-FR" exists
    And language "en" with locale "en-US" exists
    And language with iso code "en" is the default one
    And I add product "product1" with following information:
      | name[fr-FR] | bouteille de bière |
      | name[en-US] | bottle of beer     |
      | type        | standard           |
    And I add product "product2" with following information:
      | name[fr-FR] | bouteille de rhum |
      | name[en-US] | bottle of rhum    |
      | type        | standard          |
    And I add product "product3" with following information:
      | name[en-US] | bottle of whiskey |
      | type        | standard       |
    And I create carrier "standard_carrier" with specified properties:
      | name | Standard |
    And I create carrier "express_carrier" with specified properties:
      | name | Express |
    And I create carrier "pickup_carrier" with specified properties:
      | name | Pickup |

  Scenario: Get available carriers for existing order
    Given I assign product "product1" with following carriers:
      | standard_carrier |
      | pickup_carrier |
      | express_carrier |
    And I assign product "product2" with following carriers:
      | standard_carrier |
      | pickup_carrier |
    Then the products "product1, product2, product3" should have the following carriers:
      | carrier           | state     | products                          |
      | Standard          | available |                                   |
      | Pickup            | available |                                   |
      | Express           | filtered  | bottle of beer, bottle of whiskey |
      | My cheap carrier  | filtered  | bottle of whiskey                 |
      | My light carrier  | filtered  | bottle of whiskey                 |
      | Click and collect | filtered  | bottle of whiskey                 |
      | My carrier        | filtered  | bottle of whiskey                 |
