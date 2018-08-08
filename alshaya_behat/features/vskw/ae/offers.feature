@javascript
Feature: To verify todays offer block on the page

  Scenario: To verify todays offer block on home page
    Given I am on homepage
    And I wait for the page to load
    Then I should see "Todays offer"
    When I click the label for ".promo-panel-label"
    And I wait for AJAX to finish
    Then I should see "Buy 1 get 1 free"
    And I should see "Discounts on more than 100 products"
    When I click the label for ".promo-panel-label"
    And I wait for AJAX to finish
    Then I should not see "Buy 1 get 1 free"

  Scenario: To verify todays offer block on home page
    Given I am on homepage
    And I wait for the page to load
    Then I should see "Today's offers"
    When I click the label for ".promo-panel-label"
    And I wait for AJAX to finish
    Then I should see "Limited Time"
    And I should see "Beauty Bag"
    And I should see "Shop the collection"
    When I follow "Shop the collection"
    Then the url should match "/en"

  Scenario: Verify the super categories
    Given I am on homepage
    And I wait for the page to load
    Then I should see "Victoria secret"
    And I should see "Pink"
    And I should see "Victoria sport"

  Scenario: Verify the super categories
    Given I am on homepage
    And I wait for the page to load
    When I follow "Pink"
    Then the url should match "/en/pink"
    When I follow "Victoria sport"
    Then the url should match "/en/victoria-sport"
    When I follow "Victoria secret"
    Then the url should match "/en"
