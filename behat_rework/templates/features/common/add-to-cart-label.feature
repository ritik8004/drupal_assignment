@javascript
Feature: To verify add to cart label

  Scenario: As a Guest
  I should be able to see correct label for add to cart button
    Given I go to "{url_product_page}"
    And I wait for the page to load
    Then I should see the "{lang_add_to_cart_label}" button
