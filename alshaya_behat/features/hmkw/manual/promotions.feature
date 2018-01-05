@javascript @manual @promotions
Feature: Test various scenarios for promotions

  Scenario: As a guest
    I should be able to get discount on total cart value
    by applying a coupon code
    Given I am on a simple product page
    And I wait for the page to load
    When I select "2" quantity
    And I press "Add to basket"
    When I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-2"
    And I wait 2 seconds
    When I fill in "edit-coupon" with "FIXED"
    And I click the label for "#apply_coupon"
    When I wait for the page to load
    Then I should see "Promotional code applied successfully"
    And the order total price should be reflected as per the coupon discount of "10.000" KWD

  Scenario: As a guest
    I should be able to get 2 products free on buying 5
    after applying a coupon code
    Given I am on a simple product page
    And I wait for the page to load
    When I select "5" quantity
    And I press "Add to basket"
    When I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-2"
    And I wait 2 seconds
    When I fill in "edit-coupon" with "ZZZ345"
    And I click the label for "#apply_coupon"
    When I wait for the page to load
    Then I should see "Promotional code applied successfully"
    Then I should get "2" products free on buying "5"

  Scenario: As a guest
    I should be able to avail a discount if the subtotal total is greater than or equal to a value
    by applying a coupon code
    Given I am on a simple product page
    And I wait for the page to load
    When I select "5" quantity
    And I press "Add to basket"
    When I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-2"
    And I wait 2 seconds
    When I fill in "edit-coupon" with "ZZZ456"
    And I click the label for "#apply_coupon"
    When I wait for the page to load
    Then I should see "Promotional code applied successfully"
    Then I should get a discount of "6" KWD when the cart subtotal is greater than or equal to "15" KWD

  Scenario: As a guest
    I should able to avail certain percentage of discount
    by applying a coupon code
    Given I am on a simple product page
    And I wait for the page to load
    When I select "5" quantity
    And I press "Add to basket"
    When I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-2"
    And I wait 2 seconds
    When I fill in "edit-coupon" with "DISCOUNT5"
    And I click the label for "#apply_coupon"
    When I wait for the page to load
    Then I should see "Promotional code applied successfully"
    Then I should get "50" percent discount if the cart value is greater than "5" KWD

  Scenario: As a guest
  I should able to avail certain percentage of discount
  by applying a coupon code
    Given I am on a simple product page
    And I wait for the page to load
    When I select "10" quantity
    And I press "Add to basket"
    When I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-2"
    And I wait 2 seconds
    When I fill in "edit-coupon" with "DISCOUNT5"
    And I click the label for "#apply_coupon"
    When I wait for the page to load
    Then I should see "Promotional code applied successfully"
    Then I should get "50" percent discount if the cart value is greater than "5" KWD

  Scenario Outline: As a guest (rule ID = 17)
    I should be able to avail fixed price discount without applying a COUPON code
    when buying products for a particular category
    Given I am on "<category-page>"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    Then I should get a discount of "<price>" KWD on the order total
    Examples:
    |category-page|price|
    |/ladies|3.000|

  Scenario Outline: As a guest (rule ID = 18)
    I should be able to avail fixed percentage discount without applying a COUPON code
    when buying products from a particular category
    Given I am on "<category-page>"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    Then I should get a discount of "<discount-percent>" percent on the order total
    Examples:
    |category-page|discount-percent|

  Scenario Outline: As a guest (rule ID = 19)
    I should be able to avail fixed price discount without applying a COUPON code
    when buying at least some number of quantity from the same category
    Given I am on "<category-page>"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to cart"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I select "<number>" from the dropdown
    And I wait for AJAX to finish
    Then I should get a discount of "<price>" KWD on the order total when quantity is atleast "<quantity>"
    Examples:
      |category-page|number|price|quantity|
      |/ladies|3|5.000|3|

  Scenario Outline: As a guest (rule ID = 20)
  I should be able to avail fixed price discount without applying a COUPON code
  when buying at least some number of quantity from a group of categories
    Given I am on "<category-page-1>"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to cart"
    And I wait for AJAX to finish
    Given I am on "<category-page-2>"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to cart"
    And I wait for AJAX to finish
    Given I am on "<category-page-3>"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to cart"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should get a discount of "<price>" KWD on the order total when quantity is atleast "<quantity>"
    Examples:
    |category-page-1|category-page-2|category-page-3|price|quantity|
    |/ladies|/kids|/ladies|10|3                                        |

  Scenario: As a guest (rule ID = 21)
    I should be able to get a discount without applying a COUPON code
    when the cart value is greater than X amount
    Given I am on a configurable product
    And I press "Add to cart"
    When I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I select "10" from the dropdown
    And I wait for AJAX to finish
    Then I should get a discount of "5" KWD when the cart subtotal is greater than or equal to "30" KWD

  @arabic
  Scenario: As a guest
  I should be able to get discount on total cart value
  by applying a coupon code
    Given I am on a simple product page
    And I wait for the page to load
    When I select "2" quantity
    And I press "Add to basket"
    When I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I click the label for "#ui-id-2"
    And I wait 2 seconds
    When I fill in "edit-coupon" with "FIXED"
    And I click the label for "#apply_coupon"
    When I wait for the page to load
    Then I should see "Promotional code applied successfully"
    And the order total price should be reflected as per the coupon discount of "10.000" KWD
