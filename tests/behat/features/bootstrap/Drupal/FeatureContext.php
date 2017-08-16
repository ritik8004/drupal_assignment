<?php

namespace Drupal;

define("ORDER_ASC", 1);
define("ORDER_DSC", 0);

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Context\Context;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements Context, SnippetAcceptingContext {

  private $quantity;

  private $product;

  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * @BeforeScenario @javascript */
  public function before(BeforeScenarioScope $scope) {
    $this->getSession()->getDriver()->resizeWindow(1440, 900, 'current');
  }

  /**
   * @Given /^I wait for the page to load$/
   */
  public function iWaitForThePageToLoad() {
    $this->getSession()->wait(5000, '(0 === jQuery.active)');
  }

  /**
   * @Then /^I should be able to see the header$/
   */
  public function iShouldBeAbleToSeeTheHeader() {
    $page = $this->getSession()->getPage();
    $create_account = $page->hasLink('create an account');
    if ($create_account == NULL) {
      throw new \Exception('Link to create an account is missing in the header');
    }

    $sign_in = $page->hasLink('Sign in');
    if ($sign_in == NULL) {
      throw new \Exception('Link to Sign in is missing in the header');
    }

    $find_store = $page->hasLink('Find Store');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the header');
    }

    $language = $page->hasLink('عربية');
    if ($language == NULL) {
      throw new \Exception('Link to switch to Arabic language is missing in the header');
    }
  }

  /**
   * @Given /^I should be able to see the footer$/
   */
  public function iShouldBeAbleToSeeTheFooter() {
    $page = $this->getSession()->getPage();
    $footer_region_categories = $page->find('css', '.footer--menu .footer--categories');
    $footer_region_categories->hasLink('Baby Clothing' and 'Toddler Clothing' and 'Maternity' and 'Bathing & Care' and 'Car Seats');
    $footer_region_categories = $footer_region_categories and $footer_region_categories->hasLink('Feeding' and 'Nursery & Bedroom' and 'Pushchairs');

    if ($footer_region_categories == NULL) {
      throw new \Exception('Main menu is not being displayed in the footer');
    }

    $about_brand = $page->find('css', '.footer--menu .footer--abouthelp');
    $about_brand->hasLink('Corporate information' and 'Delivery information' and 'Exchange & refund' and 'Terms and Conditions');
    if ($about_brand == NULL) {
      throw new \Exception('About brand section missing in the footer');
    }

    $help = $page->find('css', '.footer--abouthelp');
    $help->hasLink('contact' and 'faq' and 'sitemap' and 'store');
    if ($help == NULL) {
      throw new \Exception('Help section missing in the footer');
    }

    $text = 'connect with us' and 'get email offers and the latest news from Mothercare Kuwait';
    $subscription = $page->find('css', '.alshaya-newsletter-subscribe');
    $subscription->find('named_partial', array('content', $text));
    if ($subscription == NULL) {
      throw new \Exception('Text related to Subscription is missing in the footer');
    }

    $sub_button = $subscription->hasButton('sign up');
    if ($sub_button == NULL) {
      throw new \Exception('Sign up button to subscribe to newsletters is missing in the footer');
    }

    $copyright1 = '© Copyright Mothercare UK Limited 2016 Registered in England no. 533087, VAT Reg no 440 6445 66';
    $copyright1 = $copyright1 and 'Registered ofﬁce: Cherry Tree Road, Watford, Hertfordshire, WD24 6SH';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', array('content', $copyright1));

    if ($copyright == NULL) {
      throw new \Exception('Copyright information is missing in the footer');
    }

    $payment = $page->find('css', '.c-footer-secondary');
    $payment->hasLink('Mastercard' and 'Verision' and 'Visa');
    if ($payment == NULL) {
      throw new \Exception('Payment links are missing in the footer');
    }

  }

  /**
   * @When /^I subscribe using a valid Email ID$/
   */
  public function iSubscribeUsingAValidEmailID() {
    $length = 5;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);

    $randomString = 'tempemail' . rand(0, $charactersLength - 1);
    $email_id = $randomString . '@gmail.com';
    $this->getSession()->getPage()->fillField("edit-email", $email_id);
  }

  /**
   * @Then /^I should see Search results page for "([^"]*)"$/
   */
  public function iShouldSeeSearchResultsPageFor($arg1) {
    $page = $this->getSession()->getPage();
    $page->hasContent("search results for " . $arg1);

    $expected_text = explode(' ', $arg1);
    $actual_text = $page->findAll('css', 'h2.field--name-name');
    $flag = FALSE;

    if (!empty($actual_text)) {
      foreach ($actual_text as $text) {
        $actual_values1 = $text->find('css', 'a')->getText();
        foreach ($expected_text as $a) {
          if (stripos($actual_values1, $a) !== FALSE) {
            $flag = TRUE;
          }
        }
        if (!$flag) {
          throw new \Exception('Search results are not correct');
        }
      }
    }

    else {
      echo 'Search passed. But, Search term did not yield any results';
    }
  }

  /**
   * @Then /^I should see Search results page in Arabic for "([^"]*)"$/
   */
  public function iShouldSeeSearchResultsPageInArabicFor($arg1) {

    $page = $this->getSession()->getPage();
    $page->hasContent("نتائج البحث عن " . $arg1);

    $expected_text = explode(' ', $arg1);
    $actual_text = $page->findAll('css', 'h2.field--name-name');
    $flag = FALSE;

    if (!empty($actual_text)) {
      foreach ($actual_text as $text) {
        $actual_values1 = $text->find('css', 'a')->getText();
        foreach ($expected_text as $a) {
          if (stripos($actual_values1, $a) !== FALSE) {
            $flag = TRUE;
          }
        }
        if (!$flag) {
          throw new \Exception('Search results are not correct');
        }
      }
    }

    else {
      echo 'Search passed. But, Search term did not yield any results';
    }
  }

  /**
   * @Given /^I select a product$/
   */
  public function iSelectAProduct() {

    $page = $this->getSession()->getPage();
    $this->product = $page->find('css', 'h2.field--name-name')->getText();
    $page->clickLink($this->product);
  }

  /**
   * @Then /^I should be able to view the product in the basket$/
   */
  public function iShouldBeAbleToViewTheProductInTheBasket() {

    $page = $this->getSession()->getPage();
    if ($page->hasLink($this->product) == FALSE) {

      throw new \Exception('Product not found in the basket');
    };
  }

  /**
   * @Given /^the url should be correct "([^"]*)"$/
   */
  public function theUrlShouldBeCorrect($arg1) {
    $current_url = $this->getSession()->getCurrentUrl();
    $base_url = 'https://local.alshaya.com/en';
    if ($current_url == NULL) {
      throw new \Exception('URL not found');
    }
    else {
      $expected_url = $base_url . "/search?keywords=$arg1";
    }

    if ($expected_url !== $current_url) {
      throw new \Exception("Incorrect URL! Expected:$expected_url | Actual:$current_url ");
    }

  }

  /**
   * @Then /^I should be able to see the header in Arabic$/
   */
  public function iShouldBeAbleToSeeTheHeaderInArabic() {
    $page = $this->getSession()->getPage();
    $create_account = $page->hasLink('تسجيل مستخدم جديد');
    if ($create_account == NULL) {
      throw new \Exception('Link for creating account is missing in the header on Arabic site');
    }

    $sign_in = $page->hasLink('تسجيل الدخول');
    if ($sign_in == NULL) {
      throw new \Exception('Link to Sign in is missing in the header on Arabic site ');
    }

    $find_store = $page->hasLink('البحث عن محلاتنا');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the header on Arabic site');
    }

    $language = $page->hasLink('English');
    if ($language == NULL) {
      throw new \Exception('Link to switch to English language is missing in the header');
    }
  }

  /**
   * @Given /^I should be able to see the footer in Arabic$/
   */
  public function iShouldBeAbleToSeeTheFooterInArabic() {
    $page = $this->getSession()->getPage();
    $footer_region_categories = $page->find('css', '.footer--menu .footer--categories');
    $footer_region_categories->hasLink('ملابس الرضع' and 'الإستبدال و الر' and 'اتصل بنا');

    if ($footer_region_categories == NULL) {
      throw new \Exception('Main menu is not being displayed in the footer');
    }

    $text = 'تواصل معنا' and 'مذركير الكويت احصل على أحدث العروض الحصرية عبر عنوان البريد الإكتروني';
    $subscription = $page->find('css', '.alshaya-newsletter-subscribe');
    $subscription->find('named_partial', array('content', $text));
    if ($subscription == NULL) {
      throw new \Exception('Text related to Subscription is missing in the footer');
    }

    $sub_button = $subscription->hasButton('سجل الآن');
    if ($sub_button == NULL) {
      throw new \Exception('Sign up button to subscribe to newsletters is missing in the footer');
    }

    $copyright1 = '© حقوق النشر محفوظة لشركة مذركير المحدودة المملكة المتحدة 2015 | مذركير المحدودة المملكة المتحدة (شركة خاصة محدودة)';
    $copyright1 = $copyright1 and 'مسجلة في إنجلترا برقم 533087 . رقم تسجيل ضريبة القيمة المضافة 66 6445 440 ';
    $copyright1 = $copyright1 and 'مكتب التسجيل: شيري تري رود، واتفورد، هيرتفوردشاير، WD24 6SH';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', array('content', $copyright1));

    if ($copyright == NULL) {
      throw new \Exception('Copyright information is missing in the footer');
    }

    $payment = $page->find('css', '.c-footer-secondary');
    $payment->hasLink('Mastercard' and 'Verision' and 'Visa');
    if ($payment == NULL) {
      throw new \Exception('Payment links are missing in the footer');
    }
  }

  /**
   * @Given /^I see the header for checkout$/
   */
  public function iSeeTheHeaderForCheckout() {
    $page = $this->getSession()->getPage();
    $logo = $page->has('css', '.logo') and $page->hasLink('Home');
    if (!$logo) {
      throw new \Exception('Logo is not displayed on secure checkout page');
    }
    $text = $page->find('css', '.secure__checkout--label')->getText();
    if ($text !== 'Secure Checkout') {
      throw new \Exception('Text Secure Checkout is not displayed');
    }
    $lock = $page->has('css', '.icon-ic_login');
    if (!$lock) {
      throw new \Exception('Lock icon is not displayed secure checkout page');
    }
  }

  /**
   * @Given /^I select a payment option "([^"]*)"$/
   */
  public function iSelectAPaymentOption($payment) {
    $parent = $this->getSession()->getPage()->findById($payment);
    $parent->click();
  }

  /**
   * @Given /^I accept terms and conditions$/
   */
  public function iAcceptTermsAndConditions() {
    $this->getSession()
      ->getPage()
      ->findById('edit-checkout-terms-terms')
      ->getParent()
      ->find('css', '.option')
      ->click();
  }

  /**
   * @Given I select the first autocomplete option for :prefix on the :field field
   */
  public function iSelectFirstAutocomplete($prefix, $field) {
    $field = str_replace('\\"', '"', $field);
    $session = $this->getSession();
    $page = $session->getPage();
    $element = $page->findField($field);
    if (!$element) {
      throw new ElementNotFoundException($session, NULL, 'named', $field);
    }
    $page->fillField($field, $prefix);
    $xpath = $element->getXpath();
    $driver = $session->getDriver();
    $prefix = str_replace('\\"', '"', $prefix);
    $chars = str_split($prefix);
    $last_char = array_pop($chars);
    // autocomplete.js uses key down/up events directly.
    $driver->keyDown($xpath, 8);
    $driver->keyUp($xpath, 8);
    $driver->keyDown($xpath, $last_char);
    $driver->keyUp($xpath, $last_char);
    // Wait for AJAX to finish.
    $this->getSession()->wait(500, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
    // Press the down arrow to select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);
    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);
    // Wait for AJAX to finish.
    $this->getSession()->wait(500, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
    $all_results = $page->findById('click-and-collect-list-view');
    if ($all_results == NULL) {
      $message = $page->hasContent('Sorry, No store found for your location.');
      if (!$message) {
        throw new \Exception('No stores message is not displayed');
      }
    }
  }

  /**
   * @Given /^I select an element having class "([^"]*)"$/
   */
  public function iSelectAnElementHavingClass($arg1) {
    $this->getSession()->getPage()->find('css', $arg1)->click();
  }

  /**
   * @Given /^I should see "([^"]*)" in the cart area$/
   */
  public function iShouldSeeInTheCartArea($arg1) {
    $price = $this->getSession()->getPage()->findById('block-cartminiblock')->find('css', '.price')->hasLink($arg1);

    if (!$price) {
      throw new \Exception('Product of the price is not displayed on the mini cart');
    }

  }

  /**
   * @Then /^I should see the number of stores displayed$/
   */
  public function iShouldSeeTheNumberOfStoresDisplayed() {
    $page = $this->getSession()->getPage();
    $all_stores = $page->findAll('css', '#click-and-collect-list-view ul li');
    $count = count($all_stores);
    $value = $page->findById('edit-store-location')->getValue();
    $text = $page->hasContent("'Available at' .$count 'stores near' .$value");
    if (!$text) {
      throw new \Exception('Number of stores not displayed on Click and Collect page');
    }

  }

  /**
   * @Given /^I am logged in as an authenticated user "([^"]*)" with password "([^"]*)"$/
   */
  public function iAmLoggedInAsAnAuthenticatedUserWithPassword($arg1, $arg2) {

    $this->visitPath('/user/login');
    $this->getSession()->getPage()->fillField('edit-name', $arg1);
    $this->getSession()->getPage()->fillField('edit-pass', $arg2);
    $this->getSession()->getPage()->pressButton('sign in');
    $username = $this->getSession()->getPage()->find('css', 'h3.my-account-title')->getText();
    if ($username == NULL) {
      throw new \Exception('Authenticated user could not login. Please check the credentials entered.');
    }
  }

  /**
   * @Given /^I select "([^"]*)" quantity$/
   */
  public function iSelectQuantity($quantity) {

    $this->getSession()->getPage()->selectFieldOption('quantity', $quantity);
    $this->quantity = $quantity;
  }

  /**
   * @Then /^I should see a message for the product being added to cart "([^"]*)"$/
   */
  public function iShouldSeeAMessageForTheProductBeingAddedToCart($arg1) {
    $page = $this->getSession()->getPage();
    $actual = $page->find('css', '.col-2')->getText();
    $expected = $arg1 . ' has been added to your basket. view basket';
    if ($actual !== $expected) {
      throw new \Exception('Product has been added to your cart message is not displayed');
    }
  }

  /**
   * @Then /^I should see a message for the product being added to cart in arabic "([^"]*)"$/
   */
  public function iShouldSeeAMessageForTheProductBeingAddedToCartInArabic($arg1) {
    $page = $this->getSession()->getPage();
    $actual = $page->find('css', '.col-2')->getText();
    $expected = $arg1 . ' تم إضافته إلى سلتك عرض سلة التسوق';
    if ($actual !== $expected) {
      throw new \Exception('On arabic site, product has been added to your cart message is not displayed');
    }
  }

  /**
   * @When /^I click the label for "([^"]*)"$/
   */
  public function iClickTheLabelFor($arg1) {
    $this->getSession()->getPage()->find('css', $arg1)->click();
  }

  /**
   * @Then /^I should be redirected to Google Maps Window$/
   */
  public function iShouldBeRedirectedToGoogleMapsWindow() {

    $windowNames = $this->getSession()->getWindowNames();
    if (count($windowNames) > 1) {
      $this->getSession()->switchToWindow($windowNames[1]);
    }
    else {
      throw new \Exception('Get directions did not open a new window');
    }
  }

  /**
   * @Given /^the "([^"]*)" tab should be selected$/
   */
  public function theTabShouldBeSelected($tab_name) {
    $tab = $this->getSession()
      ->getPage()
      ->findLink($tab_name)
      ->has('css', '.active');
    if (!$tab) {
      throw new \Exception($tab_name . 'is not selected by default');
    }
  }

  /**
   * @Given /^the list should be sorted in alphabetical order$/
   */
  public function theListShouldBeSortedInAlphabeticalOrder() {
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', '.views-field.views-field-title');
    if ($elements == NULL) {
      throw new \Exception('Find store is not displaying any stores in Kuwait');
    }
    foreach ($elements as $element) {
      if ($element !== NULL) {
        if ($element->find('css', '.field-content a') == NULL) {
          continue;
        }
        $value = $element->find('css', '.field-content a')->getText();
        $actual_values[] = $value;
      }
      else {
        throw new \Exception('Element is returning null');
      }
    }
    if (!$this->is_array_ordered($actual_values, ORDER_ASC)) {
      throw new \Exception('Store list is not sorted on Store finder page');
    }
  }

  /**
   *
   */
  public function is_array_ordered($array, $sort_order) {
    $i = 0;
    $total_elements = count($array);

    if ($sort_order == ORDER_ASC) {
      // Check for ascending order.
      while ($total_elements > 1) {
        if ($array[$i] < $array[$i + 1]) {
          $i++;
          $total_elements--;
        }
        else {
          return FALSE;
        }
      }
    }
    elseif ($sort_order == ORDER_DSC) {
      // Check for descending order.
      while ($total_elements > 1) {
        if ($array[$i] > $array[$i + 1]) {
          $i++;
          $total_elements--;
        }
        else {
          return FALSE;
        }
      }
    }

    return TRUE;
  }

  /**
   * @Then /^the number of stores displayed should match the count displayed on the page$/
   */
  public function theNumberOfStoresDisplayedShouldMatchTheCountDisplayedOnThePage() {
    $page = $this->getSession()->getPage();
    $results = $page->findAll('css', '.list-view-locator');
    $actual_count = count($results);
    $count = (string) $actual_count;
    $actual_text = $page->find('css', '.view-header')->getText();
    if (strpos($actual_text, $count) === FALSE) {
      throw new \Exception('Count is incorrect');
    }
  }

  /**
   * @Then /^the "([^"]*)" tab should be highlighted$/
   */
  public function theTabShouldBeHighlighted($arg1) {
    $tab = $this->getSession()
      ->getPage()
      ->findLink($arg1)
      ->getParent()
      ->has('css', '.active');
    if ($tab == FALSE) {
      throw new \Exception($arg1 . ' tab is not selected');
    }
  }

  /**
   * @When /^I click a pointer on the map$/
   */
  public function iClickAPointerOnTheMap() {
    $this->getSession()
      ->getPage()
      ->find('css', 'div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(3) > div:nth-child(2) > img')
      ->click();
  }

  /**
   * @When /^I click pointer against it on the map$/
   */
  public function iClickPointerAgainstItOnTheMap() {
    $page = $this->getSession()->getPage();
    $page->find('css', '#map-canvas-598812ece5cac > div.geolocation-google-map.geolocation-processed > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(3) > div > img')
      ->click();
  }

  /**
   * @Given /^I wait (\d+) seconds$/
   */
  public function iWaitSeconds($seconds) {
    sleep($seconds);
  }

  /**
   * @When /^I click a pointer on the map on arabic site$/
   */
  public function iClickAPointerOnTheMapOnArabicSite() {
    $this->getSession()
      ->getPage()
      ->find('css', 'div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(3) > div:nth-child(3) > img')
      ->click();
  }

  /**
   * @Then /^I should see title, address, Opening hours and Get directions link on the popup$/
   */
  public function iShouldSeeTitleAddressOpeningHoursAndGetDirectionsLinkOnThePopup() {
    $page = $this->getSession()->getPage();
    $title = $page->has('css', 'div.views-field.views-field-title > span.field-content');
    if (!$title) {
      throw new \Exception('Title is not displayed on the Map popup');
    }
    $address = $page->has('css', 'div.views-field.views-field-field-store-address > div.field-content > p');
    if (!$address) {
      throw new \Exception('Address is not displayed on the map popup');
    }
    $opening_hours = $page->has('css', 'div.hours--label');
    if (!$opening_hours) {
      throw new \Exception('Opening hours is not displayed on map popup');
    }
    $directions = $page->has('css', 'div.get--directions');
    if (!$directions) {
      throw new \Exception('Get directions link is not displayed on map popup');
    }
  }

  /**
   * @Then /^the number of stores displayed should match the pointer displayed on map$/
   */
  public function theNumberOfStoresDisplayedShouldMatchThePointerDisplayedOnMap() {
    $page = $this->getSession()->getPage();
    $all_pointers = $page->findAll('css', '.gmnoprint');
    $actual_count = count($all_pointers);
    $count = (string) $actual_count;
    $actual_text = $page->find('css', '.view-header')->getText();
    if (strpos($actual_text, $count) === FALSE) {
      throw new \Exception('Count displayed for number of stores is incorrect on Map view');
    }
  }

}
