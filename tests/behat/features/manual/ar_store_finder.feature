@javascript @arabic @manual
Feature: Test Store finder on Arabic site

  Background:
    Given I am on "ar/store-finder"
    And I wait for the page to load

  Scenario: As a Guest user,
  I should be navigated to Store detail page
  On clicking a link from the list on Store finder page
    When I follow "سوق شرق"
    And I wait for AJAX to finish
    Then I should see "سوق شرق"
    And I should see "شارع الخليج، سوق شرق،"
    Then I should see " الشرق (الدور الأول)"
    And I should see "+965 22214817"
    Then I should see "ساعات العمل"
    And I should see the link "احصل على الإتجاهات"
    But I should not see "شارع سالم المبارك - مقابل الفنار"

  Scenario: As a Guest user,
  I should be able to see the opening hours
  On Store detail page
    When I follow "سوق شرق"
    And I wait for AJAX to finish
    When I click the label for ".hours--label"
    And I wait for AJAX to finish
    Then I should see "الإثنين"
    And I should see "الثلاثاء"
    Then I should see "الأربعاء"
    And I should see "الخميس"
    Then I should see "الجمعة"
    And I should see "السبت"
    Then I should see "الأحد"
    And I should see "9am to 6pm"
    When I click the label for ".hours--label.open"
    And I wait for AJAX to finish
    Then I should not see "الإثنين"
    And I should not see "الأحد"
    Then I should not see "9am to 6pm"

  Scenario: As a Guest user,
  I should be navigated to Google Maps
  On clicking Get Direction from Store detail page
    When I follow "سوق شرق"
    And I wait for the page to load
    When I follow "احصل على الإتجاهات"
    And I wait for the page to load
    Then I should be redirected to Google Maps Window

  Scenario: As a Guest user,
  I should be able to search for nearby stores
    Given the "عرض القائمة" tab should be selected
    When I select the first autocomplete option for "shuwaikh " on the "edit-geolocation-geocoder-google-places-api" field
    And I wait for the page to load
    And I wait 5 seconds
    Then the number of stores displayed should match the count displayed on the page

  Scenario: As a Guest user
  when I search for nearby stores
  then each store should display information on title, address, opening hours and get directions link
    Given I follow "عرض الخريطة"
    And I wait for AJAX to finish
    Then the "عرض الخريطة" tab should be highlighted
    And I wait for AJAX to finish
    When I click a pointer on the map on arabic site
    And I wait for AJAX to finish
    Then I should see title, address, Opening hours and Get directions link on the popup
    When I wait 2 seconds
    When I click the label for "div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(4) > div > div.gm-style-iw > div:nth-child(1) > div > div > div > div.views-field.views-field-field-store-open-hours > div > div.hours--wrapper.selector--hours > div > div.hours--label"
    And I wait for AJAX to finish
    Then I should see "الإثنين"
    Then I should see "الأحد"
    And I should see "9am to 6pm"
    When I click the label for "div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(4) > div > div.gm-style-iw > div:nth-child(1) > div > div > div > div.views-field.views-field-field-store-open-hours > div > div.hours--wrapper.selector--hours > div > div.hours--label.open"
    And I wait for AJAX to finish
    Then I should not see "الإثنين"
    Then I should not see "الأحد"
    And I should not see "9am to 6pm"
    When I click the label for "div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(4) > div > div.gm-style-iw > div:nth-child(1) > div > div > div > div.views-field.views-field-field-store-open-hours > div > div.get--directions > div > a.device__desktop"
    And I wait for the page to load
    Then I should be redirected to Google Maps Window

  Scenario: As a Guest user
  on clicking pointer on map on store detail page
  all store details should be displayed on Arabic site
    When I follow "سوق شرق"
    And I wait for the page to load
    When I click the label for "div.geolocation-google-map.geolocation-processed > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(3) > div > img"
    And I wait for AJAX to finish
    Then I should see "سوق شرق"
    And I should see "ساعات العمل"
    Then I should see the link "احصل على الإتجاهات"

  Scenario: As a Guest user
  I should be able to search a nearby store
  on Map view
    When I follow "عرض الخريطة"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I select the first autocomplete option for "shuwaikh " on the "edit-geolocation-geocoder-google-places-api" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the number of stores displayed should match the pointer displayed on map
