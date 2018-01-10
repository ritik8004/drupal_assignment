@javascript @arabic @store @prod
Feature: Test Store finder on Arabic site

  Background:
    Given I am on "/store-finder"
    And I wait for the page to load
    When I follow "العربية"
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
    When I click the label for ".hours--label.open"
    And I wait for AJAX to finish
    Then I should not see "الإثنين"
    And I should not see "الأحد"

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
  I should be able to search a nearby store
  on Map view
    When I follow "عرض الخريطة"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I select the first autocomplete option for "shuwaikh " on the "edit-geolocation-geocoder-google-places-api" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the number of stores displayed should match the pointer displayed on map
