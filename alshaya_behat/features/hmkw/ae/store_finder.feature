@javascript
Feature: Test Store finder page
  Background:
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I follow "English"
    When I follow "Find Store"
    And I wait for the page to load

  Scenario: As a Guest user,
  I should be navigated to Store detail page
  On clicking a link from the list on Store finder page
    When I follow "Abu Dhabi Mall"
    And I wait for the page to load
    Then I should see "Abu Dhabi Mall"
    And I should see text matching "Abu Dhabi Mall-Tourist club-Abu Dhabi"
    And I should see "+971-26958101"
    And I should see "Opening Hours"
    And I should see the link "Get directions"
    But I should not see "H&M Grand Avenue"

  Scenario: As a Guest user,
  I should be able to see the opening hours
  On Store detail page
    When I follow "Abu Dhabi Mall"
    And I wait for the page to load
    And I click the label for ".hours--label"
    And I wait for AJAX to finish
    Then I should see "Monday"
    Then I should see "Tuesday"
    And I should see "Wednesday"
    Then I should see "Thursday"
    And I should see "Friday"
    Then I should see "Saturday"
    And I should see "Sunday"
    When I click the label for ".hours--label.open"
    And I wait for AJAX to finish
    Then I should not see "Monday"
    And I should not see "Sunday"

  Scenario: As a Guest user,
  I should be navigated to Google Maps
  On clicking Get Direction from Store detail page
    When I follow "Abu Dhabi Mall"
    And I wait for the page to load
    And I follow "Get directions"
    And I wait for the page to load
    Then I should be redirected to Google Maps Window

  Scenario: As a Guest user,
  I should see a sorted list of Kuwait stores
  On the list view mode which is by default selected on Store finder page
    Then the "List view" tab should be selected
    And the list should be sorted in alphabetical order

  Scenario: As a Guest user,
  I should be able to search for nearby stores
    When I wait for the page to load
    When I select the first autocomplete option for "Dubai" on the "edit-geolocation-geocoder-google-places-api" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the number of stores displayed should match the count displayed on the page

  Scenario: As a Guest user
    I should be able to search a nearby store
    on Map view
    When I follow "Map view"
    And I wait for AJAX to finish
    When I wait 3 seconds
    When I select the first autocomplete option for "Dubai" on the "edit-geolocation-geocoder-google-places-api" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the number of stores displayed should match the pointer displayed on map
