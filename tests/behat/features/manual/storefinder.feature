@javascript @manual @mmcpa-2081
Feature: Test Store finder page

  Background:
    Given I am on "/store-finder"

  Scenario: As a Guest user,
    I should be navigated to Store detail page
    On clicking a link from the list on Store finder page
    When I follow "M.H. Alshaya Building"
    And I wait for the page to load
    Then I should see "M.H. Alshaya Building"
    And I should see text matching "M.H. Alshaya Bldg, AlDabbous St, Fahaheel, Kuwait"
    Then I should see "Fahaheel (First Floor)"
    And I should see "+965 22081332"
    Then I should see "Opening Hours"
    And I should see the link "Get directions"
    But I should not see "Al Mughateer Mall"
    But I should not see "Rawda Co-op."

  Scenario: As a Guest user,
    I should be able to see the opening hours
    On Store detail page
    When I follow "M.H. Alshaya Building"
    And I wait for the page to load
    When I click the label for ".hours--label"
    And I wait for AJAX to finish
    Then I should see "Monday"
    And I should see "Tuesday"
    Then I should see "Wednesday"
    And I should see "Thursday"
    Then I should see "Friday"
    And I should see "Saturday"
    Then I should see "Sunday"
    And I should see "9am to 6pm"

  Scenario: As a Guest user,
    I should be navigated to Google Maps
    On clicking Get Direction from Store detail page
    When I follow "M.H. Alshaya Building"
    And I wait for the page to load
    When I follow "Get directions"
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
    When I select the first autocomplete option for "shuwaikh" on the "edit-geolocation-geocoder-google-places-api" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the number of stores displayed should match the count displayed on the page

  Scenario: As a Guest user
    when I search for nearby stores
    then each store should display information on title, address, opening hours and get directions link
    When I follow "Map view"
    And I wait for AJAX to finish
    Then the "Map view" tab should be highlighted
    And I wait for AJAX to finish
    When I click a pointer on the map
    And I wait for AJAX to finish
    Then I should see "M.H. Alshaya Building"
    And I should see "M.H. Alshaya Bldg, AlDabbous St, Fahaheel, Kuwait"
    Then I should see " Fahaheel (First Floor)"
    And I should see "Opening Hours"

  Scenario: As a Guest user
    on clicking pointer on map on store detail page
    all store details should be displayed
    When I follow "M.H. Alshaya Building"
    And I wait for the page to load
    When I click pointer against it on the map
    And I wait for AJAX to finish
    Then I should see "M.H. Alshaya Bldg, AlDabbous St, Fahaheel, Kuwait"
    And I should see "Opening Hours"
