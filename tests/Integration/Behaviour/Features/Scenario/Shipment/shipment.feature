# ./vendor/bin/behat -c tests/Integration/Behaviour/behat.yml -s shipment
@restore-all-tables-before-feature
Feature: Retrieving shipment for orders
  As a BO users
  I want to retrieve the list of shipments associated with a specific order
  In order to be able to track the shipment of this order

  Scenario: Retrieve shipmets for existing order
    Given I add new shipment with the following properties
      | order_id            | 1 |
      | carrier_id            | 1 |
      | delivery_address_id            | 9 |
      | shipping_cost_tax_excl            | 2.00 |
      | shipping_cost_tax_incl            | 4.00 |
      | packed_at            | 0 |
      | shipped_at            | 0 |
      | delivered_at            | 0 |
      | tracking_number            | qwertyuiop123456789 |
    And I update shipment with following products
      |               |   |
