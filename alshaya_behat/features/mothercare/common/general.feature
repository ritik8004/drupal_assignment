@javascript @manual @mmcpa-2389
Feature: Test generic features on the site
  like Header, footer and subscription

  @prod
  Scenario: As a Guest user
    I should be able to view the header and the footer
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I follow "English"
    Then I should be able to see the header
    And I should be able to see the footer

  @arabic @prod
  Scenario: On Arabic site,
  As a Guest user
  I should be able to view the header and the footer
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    Then I should be able to see the header in Arabic
    And I should be able to see the footer in Arabic

  Scenario: As a Guest user
  I should be able to subscribe with Mothercare
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I follow "English"
    When I enter a valid Email ID in field "edit-email"
    And I press "sign up"
    And I wait for AJAX to finish
    Then I should see "Thank you for your subscription."

  @arabic
  Scenario: As a Guest user
  I should be able to subscribe with Mothercare
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I enter a valid Email ID in field "edit-email"
    And I press "سجل الآن"
    And I wait for AJAX to finish
    Then I should see "شكراً لاشتراككم في نشرتنا الاخبارية"

  @prod
  Scenario: As a Guest user
    I should be displayed a warning message
    If I try to subscribe with subscribed Email ID
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I follow "English"
    And I fill in "edit-email" with "kanchan.patil+test@qed42.com"
    And I press "sign up"
    And I wait for AJAX to finish
    Then I should see "This email address is already subscribed."

  @arabic @prod
  Scenario: As a Guest user
  I should be displayed a warning message
  If I try to subscribe with subscribed Email ID
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I fill in "edit-email" with "kanchan.patil+test@qed42.com"
    And I press "سجل الآن"
    And I wait for AJAX to finish
    Then I should see "هذا العنوان البريد الإلكتروني مستعمل مسبقاً"

  Scenario: As a visitor
    I should be able to subscribe to the newsletter
    from the popup displayed
    Given I am on homepage
    When I wait for the page to load
    Then I should be able to subscribe to the newsletter displayed on the popup
