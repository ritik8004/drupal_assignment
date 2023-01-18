@aura @javascript @vsaeuat @vskwuat @tbskwuat
Feature: AURA Rewards Activity
  In order to earn points
  As a user
  I want to see the Aura banner

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  @desktop
  Scenario: To validate the Rewards Activity page for first-time users who is logged in
    When I click on "#block-alshayamyaccountlinks .my-aura-link a" element
    Then I should be on "/user/loyalty-club" page
    And I wait for the page to load
    And I wait for AJAX to finish
    And I wait for element "#my-loyalty-club .banner-title"
    Then I should see text matching "Already an Aura member?"
    Then I should see text matching "Ready to be rewarded?"
    Then I should see a ".link-your-card .btn" element
    Then I should see a ".sign-up .btn" element
    Then I should see a "#my-loyalty-club .loyalty-club-tabs-content" element

  @desktop @language
  Scenario: To validate the Rewards Activity page for first-time users who is logged in
    When I follow "{language_link}"
    When I click on "#block-alshayamyaccountlinks .my-aura-link a" element
    Then I should be on "/user/loyalty-club" page
    And I wait for the page to load
    And I wait for AJAX to finish
    And I wait for element "#my-loyalty-club .banner-title"
    Then I should see text matching "هل أنت عضو في أورا؟"
    Then I should see text matching "هل أنت جاهز للمكافآت؟"
    Then I should see a ".link-your-card .btn" element
    Then I should see a ".sign-up .btn" element
    Then I should see a "#my-loyalty-club .loyalty-club-tabs-content" element

  @mobile
  Scenario: To validate the Rewards Activity page for first-time users who is logged in
    When I click on "#block-alshayamyaccountlinks .my-aura-link a" element
    Then I should be on "/user/loyalty-club" page
    And I wait for the page to load
    And I wait for AJAX to finish
    And I wait for element "#my-loyalty-club .banner-title"
    Then I should see text matching "Already an Aura member?"
    Then I should see text matching "Ready to be rewarded?"
    Then I should see a ".link-your-card .btn" element
    Then I should see a ".sign-up .btn" element
    Then I should see a "#my-loyalty-club .loyalty-club-tabs-content" element
