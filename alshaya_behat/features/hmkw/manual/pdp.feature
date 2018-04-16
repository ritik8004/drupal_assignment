@javascript @manual @pdp
Feature: Test the product detail page

#  Scenario: As a Guest
#  I should be able to see all the various sections
#  on a simple product detail page
#    Given I am on a simple product page
#    And I wait for the page to load
#    Then I should be able to see the header
#    Then it should display title, price and item code
#    Then I should see the button "Add to cart"
#    Then I should see "Product Description"
#    And I should see the link for ".read-more-description-link"
#    Then I should see buttons for facebook, Twitter and Pinterest
#    And I should see "Delivery Options"
#    Then I should see "Home Delivery"
#    And I should see "For orders over KWD99"
#    Then I should see "Click and Collect"
#    And I should see "Collect the order from store in 99 days"
#    Then I should be able to see the footer
#
#  Scenario: As a Guest user
#  I should be able to expand HD and CC on a simple PDP
#    Given I am on a simple product page
#    And I wait for the page to load
#    When I click the label for "#ui-id-2"
#    Then I should see "Standard Delivery"
#    And I should see "(within 4 working days)"
#    When I click the label for "#ui-id-2"
#    When I click the label for "#ui-id-4"
#    Then I should see "This service is "
#    And I should see "FREE"
#    Then I should see " of charge."
#    And I should see "Check in-store availability"
#    When I select the first autocomplete option for "shuwaikh" on the "edit-location" field
#    And I wait for AJAX to finish
#    And I wait 5 seconds
#    Then I should see "Shuwaikh Industrial 1, Shuwaikh Industrial, Kuwait"
#    And I should see the link for ".change-location-link"
#    Then I should see "Other stores nearby"
#    When I click the label for ".change-location-link"
#    Then I select the first autocomplete option for "kuwait" on the "store-location" field
#    And I wait for AJAX to finish
#    And I wait 5 seconds
#    Then I should see "Sharq, Kuwait City, Kuwait"
#    When I click the label for ".other-stores-link"
#    And I wait for AJAX to finish
#    Then I should see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
#    When I scroll to x "0" y "0" coordinates of page
#    When I click the label for ".close-inline-modal"
#    Then I should not see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
#    When I click the label for ".read-more-description-link"
#    And I wait for AJAX to finish
#    Then I should see the inline modal for ".description-wrapper.desc-open"
#    When I click the label for ".close"
#    Then I should not see the inline modal for ".description-wrapper.desc-open"

  @prod
  Scenario: As a Guest
    I should be able to see all the various sections
    on a configurable product detail page
    Given I am on a configurable product
    And I wait for the page to load
    Then I should be able to see the header
    Then it should display title, price and item code
    And it should display color
    And it should display size
    Then I should see the button "Add to basket"
    Then I should see "Product Description"
    And I should see the link for ".read-more-description-link"
    Then I should see buttons for facebook, Twitter and Pinterest
    And I should see "Delivery Options"
    Then I should see "Home Delivery"
    And I should see "Free delivery for orders over KWD 25"
    Then I should see "Click and Collect"
    And I should see "Collect the order from store in 1-2 days"
    Then I should be able to see the footer

  @prod
  Scenario: As a Guest user
    I should be able to expand HD and CC on a configurable PDP
    Given I am on a configurable product
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I click the label for "#ui-id-2"
    Then I should see "Standard Delivery"
    And I should see "(within 1-2 working days)"
    When I click the label for "#ui-id-2"
    When I click the label for "#ui-id-4"
    Then I should see "This service is "
    And I should see "FREE"
    Then I should see " of charge."
    And I should see "Check in-store availability"
    When I select the first autocomplete option for "shuwaikh" on the "edit-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see "Shuwaikh Industrial 1, Shuwaikh Industrial, Kuwait"
    And I should see the link for ".change-location-link"
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

#  @media
#  Scenario Outline:
#    As an User
#    I should be able to connect via Social media
#    Given I am on a simple product page
#    And I wait for the page to load
#    When I click the label for "<social_media_link>"
#    And I wait 5 seconds
#    Then I should be directed to window having "<text>"
#    Examples:
#    |social_media_link|text|
#    |.st_facebook_custom|Log in to your Facebook account to share.|
#    |.st_twitter_custom|Share a link with your followers|

  @media @prod
  Scenario Outline:
  As an User
  I should be able to connect via Social media
    Given I am on a configurable product
    And I wait for the page to load
    When I click the label for "<social_media_link>"
    And I wait 10 seconds
    Then I should be directed to window having "<text>"
    Examples:
      |social_media_link|text|
      |.st_facebook_custom|Log into your Facebook account to share.|
      |.st_twitter_custom|Share a link with your followers|

#  @arabic
#  Scenario: As a Guest on Arabic site
#  I should be able to see all the various sections
#  on a simple product detail page
#    Given I am on a simple product page
#    And I wait for the page to load
#    When I follow "عربية"
#    And I wait for the page to load
#    Then I should be able to see the header in Arabic
#    Then it should display title, price and item code
#    Then I should see the button "أضف إلى سلة التسوق"
#    Then I should see "وصف المنتج"
#    And I should see the link for ".read-more-description-link"
#    Then I should see buttons for facebook, Twitter and Pinterest
#    And I should see "خيارات التوصيل"
#    Then I should see "خدمة التوصيل للمنزل"
#    Then I should see "اختر واستلم"
#    And I should see "استلم طلبيتك من المحل خلال 99 أيام"
#    Then I should be able to see the footer in Arabic

#  @arabic
#  Scenario: As a Guest user
#  I should be able to expand HD and CC on a simple PDP
#    Given I am on a simple product page
#    And I wait for the page to load
#    When I follow "عربية"
#    And I wait for the page to load
#    When I click the label for "#ui-id-2"
#    Then I should see "التوصيل العادي"
#    And I should see "(خلال 4 أيام عمل)"
#    When I click the label for "#ui-id-2"
#    When I click the label for "#ui-id-4"
#    Then I should see "استلم من المحل مجاناً"
#    And I should see "تحقق من توفر الكمية في المحلات"
#    When I select the first autocomplete option for "الشويخ" on the "edit-location" field
#    And I wait for AJAX to finish
#    And I wait 10 seconds
#    And I should see the link for ".change-location-link"
#    Then I should see "محلات أخرى قريبة إليك"
#    When I click the label for ".change-location-link"
#    Then I select the first autocomplete option for "الكويت" on the "store-location" field
#    And I wait for AJAX to finish
#    And I wait 5 seconds
#    When I click the label for ".other-stores-link"
#    And I wait for AJAX to finish
#    Then I should see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
#    And I scroll to x "0" y "0" coordinates of page
#    When I click the label for ".close-inline-modal"
#    Then I should not see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
#    When I click the label for ".read-more-description-link"
#    And I wait for AJAX to finish
#    Then I should see the inline modal for ".description-wrapper.desc-open"
#    And I scroll to x "0" y "0" coordinates of page
#    When I click the label for ".close"
#    Then I should not see the inline modal for ".description-wrapper.desc-open"

  @arabic @prod
  Scenario: As a Guest on Arabic site
  I should be able to see all the various sections
  on a configurable product detail page
    Given I am on a configurable product
    And I wait for the page to load
    When I scroll to x "0" y "0" coordinates of page
    When I follow "عربية"
    And I wait for the page to load
    Then I should be able to see the header in Arabic
    Then it should display title, price and item code
    And it should display size
    Then it should display color
    Then I should see the button "أضف إلى سلة التسوق"
    Then I should see "وصف المنتج"
    And I should see the link for ".read-more-description-link"
    Then I should see buttons for facebook, Twitter and Pinterest
    And I should see "خيارات التوصيل"
    Then I should see "خدمة التوصيل للمنزل"
    Then I should see "الاستلام من محلاتنا"
    And I should see "التوصيل المجاني للطلبيات التي تزيد على 25 د.ك."
    Then I should be able to see the footer in Arabic

  @arabic @prod
  Scenario: As a Guest user on Arabic site
  I should be able to expand HD and CC on a configurable PDP
    Given I am on a configurable product
    And I wait for the page to load
    When I scroll to x "0" y "0" coordinates of page
    When I follow "عربية"
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I click the label for "#ui-id-2"
    Then I should see "التوصيل العادي"
    And I should see "(خلال 1-2 أيام عمل)"
    When I click the label for "#ui-id-2"
    When I click the label for "#ui-id-4"
    Then I should see "استلم من المحل مجاناً"
    And I should see "تحقق من توفر الكمية في المحلات"
    When I select the first autocomplete option for "الشويخ" on the "edit-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I should see the link for ".change-location-link"
    Then I should see "السالمية - شارع سالم المبارك"
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

#  @media @arabic
#  Scenario Outline:
#  As an User on Arabic site
#  I should be able to connect via Social media
#    Given I am on a simple product page
#    And I wait for the page to load
#    When I follow "عربية"
#    And I wait for the page to load
#    When I click the label for "<social_media_link>"
#    And I wait 5 seconds
#    Then I should be directed to window having "<text>"
#    Examples:
#      |social_media_link|text|
#      |.st_facebook_custom|Log in to your Facebook account to share.|
#      |.st_twitter_custom|Share a link with your followers|

  @media @arabic @prod
  Scenario Outline:
  As an User on Arabic site
  I should be able to connect via Social media
    Given I am on a configurable product
    And I wait for the page to load
    When I scroll to x "0" y "0" coordinates of page
    When I follow "عربية"
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I click the label for "<social_media_link>"
    And I wait 10 seconds
    Then I should be directed to window having "<text>"
    Examples:
      |social_media_link|text|
      |.st_facebook_custom|Log into your Facebook account to share.|
      |.st_twitter_custom|Share a link with your followers|

  @config @prod
  Scenario: As an user
  I should be able to view the size guide on a configurable PDP
    Given I am on a configurable product
    And I wait for the page to load
    When I follow "Size Guide"
    And I wait for AJAX to finish
    Then I should see "Please select the category you require"
    When I press "Close"
    Then I should not see "Please select the category you require"
    And I should see the link "Size Guide"

  @config @arabic @prod
  Scenario: As an user on Arabic site
  I should be able to view the size guide on a configurable PDP
    Given I am on a configurable product
    And I wait for the page to load
    When I follow "العربية"
    And I wait for the page to load
    When I follow "دليل المقاسات"
    And I wait for AJAX to finish
    When I press "Close"
    Then I should not see "يرجى اختيار القسم المطلوب"
    And I should see the link "دليل المقاسات"
