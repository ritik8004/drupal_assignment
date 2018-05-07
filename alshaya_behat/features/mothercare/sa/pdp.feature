@javascript
Feature: Test the product detail page

  Scenario: As a Guest user
  I should be able to expand HD and CC on a configurable PDP
    Given I am on a configurable product
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I click the label for "#ui-id-2"
    Then I should see "to all areas"
    And I should see "(Kingdom of Saudi Arabia)"
    When I click the label for "#ui-id-2"
    When I click the label for "#ui-id-4"
    And I wait for AJAX to finish
    When I enter a location in "store-location"
    And I wait for AJAX to finish
    Then I should see text matching "Shuwaikh Industrial 1, Shuwaikh Industrial, Kuwait"
    And I should see the link for ".change-location-link"
    Then I should see "Other stores nearby"
    When I click the label for ".change-location-link"
    Then I select the first autocomplete option for "kuwait" on the "store-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see "Sharq, Kuwait City, Kuwait"
    When I click the label for ".other-stores-link"
    And I wait for AJAX to finish
    Then I should see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
    When I scroll to x "0" y "0" coordinates of page
    When I click the label for ".close-inline-modal"
    Then I should not see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
    When I click the label for ".read-more-description-link"
    And I wait for AJAX to finish
    Then I should see the inline modal for ".description-wrapper.desc-open"
    When I click the label for ".close"
    Then I should not see the inline modal for ".description-wrapper.desc-open"

  Scenario: As a Guest
    I should be able to see all the various sections
    on a configurable product detail page
    Given I am on a configurable product
    And I wait for the page to load
    Then I should be able to see the header
    Then it should display title, price and item code
    Then I should see "Size : "
    And I should see "quantity"
    Then I should see the button "Add to basket"
    Then I should see "product description"
    And I should see the link for ".read-more-description-link"
    Then I should see buttons for facebook, Twitter and Pinterest
    And I should see "delivery options"
    Then I should see "Home Delivery"
    And I should see "delivered in 1-3 days ( main cities) and 1-5 (other areas) for just sar 25"
    Then I should see "click and collect"
    And I should see "free delivery to stores across kingdom of saudi arabia in 1-3 days ( main cities) and 1-5 ( other areas)"
    When I click the label for "#ui-id-4"
    Then I should see "This service is "
    And I should see "FREE"
    Then I should see " of charge."
    And I should see "Select a size"
    And I should see " to check stock availability near to you"
    Then I should be able to see the footer

  Scenario Outline:
  As an User
  I should be able to connect via Social media
    Given I am on a configurable product
    And I wait for the page to load
    When I click the label for "<social_media_link>"
    And I wait 5 seconds
    Then I should be directed to window having "<text>"
    Examples:
      |social_media_link|text|
      |.st_facebook_custom|Log into your Facebook account to share.|
      |.st_twitter_custom|Share a link with your followers|


  Scenario: As a Guest user on Arabic site
  I should be able to expand HD and CC on a configurable PDP
    Given I am on a configurable product
    And I wait for the page to load
    When I scroll to x "0" y "0" coordinates of page
    When I follow "عربية"
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I click the label for "#ui-id-2"
    Then I should see "لجميع المناطق"
    And I should see "(الكويت)"
    When I click the label for "#ui-id-2"
    When I click the label for "#ui-id-4"
    And I wait for AJAX to finish
    Then I should see "استلم من المحل مجاناً"
    And I scroll to the "#search-stores-button" element
    When I enter a location in "store-location"
    And I wait for AJAX to finish
    And I should see the link for ".change-location-link"
    Then I should see text matching "محلات أخرى قريبة إليك"
    When I click the label for ".change-location-link"
    Then I select the first autocomplete option for "الكويت" on the "store-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click the label for ".other-stores-link"
    And I wait for AJAX to finish
    Then I should see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
    And I scroll to x "0" y "0" coordinates of page
    When I click the label for ".close-inline-modal"
    Then I should not see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
    When I click the label for ".read-more-description-link"
    And I wait for AJAX to finish
    Then I should see the inline modal for ".description-wrapper.desc-open"
    And I scroll to x "0" y "0" coordinates of page
    When I click the label for ".close"
    Then I should not see the inline modal for ".description-wrapper.desc-open"

  Scenario: As a Guest on Arabic site
  I should be able to see all the various sections
  on a configurable product detail page
    Given I am on a configurable product
    And I wait for the page to load
    When I scroll to x "0" y "0" coordinates of page
    When I follow "عربية"
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    Then I should be able to see the header in Arabic
    Then it should display title, price and item code
    Then I should see "الكمية"
    Then I should see the button "أضف إلى سلة التسوق"
    Then I should see "وصف المنتج"
    And I should see the link for ".read-more-description-link"
    Then I should see buttons for facebook, Twitter and Pinterest
    And I should see "خيارات التوصيل"
    Then I should see "خدمة التوصيل للمنزل"
    Then I should see "اختر واستلم"
    And I should see "التوصيل من 2 – 5 أيام فقط بـ 1 دينار"
    Then I should be able to see the footer in Arabic

  Scenario Outline:
  As an User on Arabic site
  I should be able to connect via Social media
    Given I am on a configurable product
    And I wait for the page to load
    When I scroll to x "0" y "0" coordinates of page
    When I follow "عربية"
    And I wait for the page to load
    When I click the label for "<social_media_link>"
    And I wait 5 seconds
    Then I should be directed to window having "<text>"
    Examples:
      |social_media_link|text|
      |.st_facebook_custom|Log into your Facebook account to share.|
      |.st_twitter_custom|Share a link with your followers|