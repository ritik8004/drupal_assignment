@javascript
Feature: Test generic features on the site
  like Header, footer

  @prod
  Scenario: As a Guest user
    I should be able to view the header and the footer
    Given I am on "/store-finder"
    And I wait for the page to load
    Then I should be able to see the header
    And I should be able to see the footer

  @arabic @prod
  Scenario: On Arabic site,
  As a Guest user
  I should be able to view the header and the footer
    Given I am on "/store-finder"
    And I wait for the page to load
    When I follow "عربية"
    Then I should be able to see the header in Arabic
    And I should be able to see the footer in Arabic

  @prod
  Scenario: As a Guest user
    I should be able to view the Join the Club block in Footer
    Given I am on "/store-finder"
    And I wait for the page to load
    Then I should be able to see the Jointheclub in footer

  @arabic @prod
  Scenario: As a Guest user
  I should be able to view the Join the Club block in Footer
    Given I am on "/store-finder"
    And I wait for the page to load
    When I follow "عربية"
    Then I should be able to see the Jointheclub in footer in Arabic

  @prod
  Scenario: As a Guest user
    I should be able to see the menus and submenus
    Given I am on "/store-finder"
    And I wait for the page to load
    Then I should be able to see all the menus in the header
    And I should be able to see all the submenus

