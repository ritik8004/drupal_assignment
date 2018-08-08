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

  Scenario: Verify if the super categories
    are available on the sites
    Given I am on homepage
    And I wait for the page to load
    Then I should see "Victoria secret"
    And I should see "Pink"
    And I should see "Victoria sport"

  Scenario: Verify the super categories
    navigate to correct page
    Given I am on homepage
    And I wait for the page to load
    When I follow "Pink"
    Then the url should match "/en/pink"
    When I follow "Victoria sport"
    Then the url should match "/en/victoria-sport"
    When I follow "Victoria secret"
    Then the url should match "/en"

  Scenario: Verify the super categories
    are available on arabic site
    Given I am on homepage
    And I wait for the page to load
    Then I should see "فيكتوريا سيكريت الإمارات العربية المتحدة"
    Then I should see "بينك الإمارات العربية المتحدة"
    And I should see "فيكتوريا الرياضية الإمارات العربية المتحدة"

  Scenario: Verify the super categories
  navigate to correct page on arabic site
    Given I am on homepage
    And I wait for the page to load
    When I follow "فيكتوريا سيكريت الإمارات العربية المتحدة"
    Then the url should match "/ar"
    When I follow "بينك الإمارات العربية المتحدة"
    Then the url should be correct "/ar/بينك"
    When I follow "فيكتوريا الرياضية الإمارات العربية المتحدة"
    Then the url should match "/ar/فيكتوريا-الرياضية"

  Scenario: Verify the footer links for
    Victoria secret
    Given I am on homepage
    And I wait for the page to load
    Then I should see "Victoria secret"
    Then I should see "Bras"
    And I should see "PANTIES"
    And I should see "LINGERIE"
    And I should see "SPORT"
    And I should see "SLEEP"
    And I should see "BEAUTY"
    And I should see "ACCESSORIES"

  Scenario: Verify the footer links for
    Pink
    Given I am on homepage
    And I wait for the page to load
    When I follow "Pink"
    Then the url should match "/en/pink"
    And I should see "BRAS"
    And I should see "PANTIES"
    And I should see "TOPS & BOTTOMS"
    And I should see "BEAUTY"
    And I should see "SWIM"
    And I should see "ACCESSORIES"

  Scenario: Verify the footer links for
  Victoria Sport
    Given I am on homepage
    And I wait for the page to load
    When I follow "Victoria secret"
    Then the url should match "/en/victoria-sport"
    And I should see "SPORT BRAS"
    And I should see "All Sport"
    And I should see "New Arrivals"
    And I should see "SPORT APPAREL"





