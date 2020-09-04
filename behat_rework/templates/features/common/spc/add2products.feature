@javascript @twoProduct
Feature: Test basket page

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load

  Scenario: As a Guest, I should be able to add more quantity into basket
    When I select multiple products in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"