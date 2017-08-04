@javascript @manual @1953
Feature: Test "Add to Basket" feature

  Scenario Outline: As a Guest user
  I should be able to add multiple product quantities to the basket
    Given I am on "/testsimple"
    And I select "<number>" quantity
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I should see a message for the product being added to cart "testsimple"
    When I follow "view basket"
    And I wait for the page to load
    Then I should see the link "testsimple"
    Examples:
    |number|
    |1     |
    |5     |

  @arabic
  Scenario Outline: As a Guest user on Arabic site
  I should be able to select a particular size
  and add multiple product quantities to the basket
    Given I am on "/grey-navy-and-yellow-jersey-shorts-3-pack"
    And I follow "عربية"
    And I wait for the page to load
    And I follow "12-9‏ شهر"
    And I wait for AJAX to finish
    And I select "<number>" quantity
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    Then I should see a message for the product being added to cart in arabic "Grey, Navy and Yellow Jersey Shorts - 3 Pack"
    When I follow "عرض سلة التسوق"
    And I wait for the page to load
    Then I should see the link "شورت من قماش الجورسيه باللون الرمادي والأزرق الداكن والأصفر – عبوة من 3 قطع"
    Examples:
      |number|
      |1     |
      |8     |

  Scenario Outline: As an authenticated user
    I should be able to add products to the basket
    Given I am logged in as an authenticated user "shweta+2@axelerant.com" with password "Alshaya123$"
    And I am on "/baby-clothing"
    And I follow "testsimple"
    And I select "<number>" quantity
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I should see a message for the product being added to cart "testsimple"
    When I follow "view basket"
    And I wait for the page to load
    And I should see the link "testsimple"
    Examples:
    |number|
    |1|
    |10|

  Scenario:
    As a Guest user
    I should be able to add multiple products to the basket
    with multiple quantities
    Given I am on "/testsimple"
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I am on "/tot-fox-platinum-digital-monitor"
    And I wait for the page to load
    And I select "10" from "edit-quantity"
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I follow "view basket"
    And I wait for the page to load
    Then the url should match "/cart"
    And I should see the link "testsimple"
    And I should see the link "Tot-fox PLATINUM DIGITAL MONITOR"
