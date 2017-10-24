<?php

namespace Drupal;

define("ORDER_ASC", 1);
define("ORDER_DSC", 0);

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Context\Context;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements Context, SnippetAcceptingContext {

  private $quantity;

  private $product;

  private $address_count;

  private $item_count;

  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * @BeforeScenario @javascript
   */
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
    $subscription->find('named_partial', ['content', $text]);
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
    $copyright->has('named', ['content', $copyright1]);

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
   * @When /^I enter a valid Email ID in field "([^"]*)"$/
   */
  public function iEnterAValidEmailID($field) {
    $randomString = 'randemail' . rand(2, getrandmax());
    $email_id = $randomString . '@gmail.com';
    $this->getSession()->getPage()->fillField($field, $email_id);
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
    $subscription->find('named_partial', ['content', $text]);
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
    $copyright->has('named', ['content', $copyright1]);

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
      throw new \ElementNotFoundException($session, NULL, 'named', $field);
    }
    $page->fillField($field, $prefix);
    $this->iWaitSeconds(3);
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
    $this->getSession()
      ->wait(5000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
    // Press the down arrow to select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);
    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);
    // Wait for AJAX to finish.
    $this->getSession()
      ->wait(5000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
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
    $price = $this->getSession()
      ->getPage()
      ->findById('block-cartminiblock')
      ->find('css', '.price')
      ->hasLink($arg1);

    if (!$price) {
      throw new \Exception('Product of the price is not displayed on the mini cart');
    }

  }

  /**
   * @Then /^I should see the number of stores displayed$/
   */
  public function iShouldSeeTheNumberOfStoresDisplayed() {
      $page = $this->getSession()->getPage();
      $all_stores = $page->findAll('css', 'li.select-store');
      $actual_count = count($all_stores);
      $count = (string)$actual_count;
      $text = $page->find('css', 'div.all-stores-info');
      if ($text !== null) {
          $actual_text = $text->find('css', 'h3')->getText();
      } else {
          throw new \Exception('Store search did not work on Click and collect');
      }
      if (strpos($actual_text, $count) === FALSE) {
          throw new \Exception('Count is incorrect');
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
      $element = $this->getSession()->getPage()->find('css', $arg1);
      if ($element !== NULL) {
          $element->click();
      } else {
          throw new \Exception('Element not clickable at this point');
      }
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
        if (strtolower($array[$i]) <= strtolower($array[$i + 1])) {
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
        if (strtolower($array[$i]) >= strtolower($array[$i + 1])) {
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

  /**
   * @Then /^I should see at most "([^"]*)" recent orders listed$/
   */
  public function iShouldSeeAtMostThreeRecentOrdersListed($count) {
    $page = $this->getSession()->getPage();
    $all_rows = count($page->findAll('css', '.order-summary-row'));
    if ($all_rows > $count) {
      throw new \Exception('More than three orders displayed on my account page');
    }
    $all_orders = $page->findAll('css', '.order-transaction');
    $number = [];
    foreach ($all_orders as $order) {
      $order_id = $order->find('css', '.dark')->getText();
      $number[] = substr($order_id, 7);
    }
    if (!$this->is_array_ordered($number, ORDER_DSC)) {
      throw new \Exception('Orders are not displayed in descending order');
    }
  }

  /**
   * @Given /^the order status should be visible for all products$/
   */
  public function theOrderStatusShouldBeVisibleForAllProducts() {
    $page = $this->getSession()->getPage();
    $all_rows = $page->findAll('css', '.order-summary-row');
    foreach ($all_rows as $row) {
      $status_button = $row->find('css', 'td.desktop-only > div')
        ->getText();
      $a = 'Processing';
      $b = 'Cancelled';
      $c = 'Confirmed';
      $d = 'Dispatched';
      if (!($status_button == $a or $status_button == $b or $status_button == $c or $status_button == $d)) {
        throw new \Exception('Status for order is not displayed on My account page');
      }
    }

  }

  /**
   * @Then /^I should see at most "([^"]*)" recent orders listed on orders tab$/
   */
  public function iShouldSeeAtMostRecentOrdersListedOnOrdersTab($arg1) {
    $page = $this->getSession()->getPage();
    $actual_count = count($page->findAll('css', '.order-item'));
    if ($actual_count > $arg1) {
      throw new \Exception('More than 10 orders are listed on Orders tab');
    }
    $all_orders = $page->findAll('css', '.first-second.wrapper > div.first');
    $number = [];
    foreach ($all_orders as $order) {
      $order_id = $order->find('css', '.dark.order-id')->getText();
      $number[] = substr($order_id, 7);
    }
    if (!$this->is_array_ordered($number, ORDER_DSC)) {
      throw new \Exception('Orders are not displayed in descending order');
    }
  }

  /**
   * @Then /^I should see all "([^"]*)" orders$/
   */
  public function iShouldSeeAllOrders($arg1) {
    $page = $this->getSession()->getPage();
    $all_orders = $page->findAll('css', '.order-item');
    foreach ($all_orders as $order) {
      $title = $order->find('css', 'a div.second-third.wrapper > div.second > div.dark.order-name')
        ->getText();
      if ($title !== $arg1) {
        throw new \Exception('Filter by name is not working on Orders tab in my account section');
      }
    }
  }

  /**
   * @Given /^I should see all orders for "([^"]*)"$/
   */
  public function iShouldSeeAllOrdersFor($arg1) {
    $page = $this->getSession()->getPage();
    $all_orders = $page->findAll('css', '.order-item');
    foreach ($all_orders as $order) {
      $order_id = $order->find('css', '.dark.order-id')->getText();
      $actual_order_id = substr($order_id, 0, 7);
    }
    if ($actual_order_id !== $arg1) {
      throw new \Exception('Filter for Order ID is not working');
    }
  }

  /**
   * @When /^I select Cancelled from the status dropdown$/
   */
  public function iSelectCancelledFromTheStatusDropdown() {
    $page = $this->getSession()->getPage();
    $page->find('css', '.select2-selection__arrow')->click();
    $page->find('css', 'ul.select2-results__options li:nth-child(2)')->click();
  }

  /**
   * @When /^I select Dispatched from the status dropdown$/
   */
  public function iSelectDispatchedFromTheStatusDropdown() {
    $page = $this->getSession()->getPage();
    $page->find('css', '.select2-selection__arrow')->click();
    $page->find('css', 'ul.select2-results__options li:nth-child(3)')->click();
  }

  /**
   * @When /^I select Processing from the status dropdown$/
   */
  public function iSelectProcessingFromTheStatusDropdown() {
    $page = $this->getSession()->getPage();
    $page->find('css', '.select2-selection__arrow')->click();
    $page->find('css', 'ul.select2-results__options li:nth-child(4)')->click();
  }

  /**
   * @Then /^I should see all "([^"]*)" orders listed on orders tab$/
   */
  public function iShouldSeeAllOrdersListedOnOrdersTab($arg1) {
    $page = $this->getSession()->getPage();
    $all_statuses = $page->findAll('css', '.order-item');
    foreach ($all_statuses as $status) {
      $status_name = $status->find('css', '.second-third.wrapper div.third > div')
        ->getText();
    }
    if ($status_name !== $arg1) {
      throw new \Exception('Order list did not get sorted on' . $arg1);
    }
  }

  /**
   * @Then /^I get the total count of address blocks$/
   */
  public function iGetTheTotalCountOfAddressBlocks() {
    $page = $this->getSession()->getPage();
    $this->address_count = count($page->findAll('css', '.address'));
  }

  /**
   * @Given /^the new address block should be displayed on address book$/
   */
  public function theNewAddressBlockShouldBeDisplayedOnAddressBook() {
    $page = $this->getSession()->getPage();
    $new_address_count = count($page->findAll('css', '.address'));
    if ($this->address_count + 1 !== $new_address_count) {
      throw new \Exception('Newly added address is not being displayed on address book');
    }
  }

  /**
   * @When /^I click Edit Address$/
   */
  public function iClickEditAddress() {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', '#block-content > div.views-element-container > div > div > div > div.views-row.clearfix.row-1 > div:nth-child(1) > div > span > div > div.address--options > div.address--edit.address--controls > a');
    if ($element !== NULL) {
      $element->click();
    }
  }

  /**
   * @Then /^I should not see the delete button for primary address$/
   */
  public function iShouldNotSeeTheDeleteButtonForPrimaryAddress() {
    $page = $this->getSession()->getPage();
    $delete_button = $page->find('css', '.address.default .address--options')
      ->hasLink('Delete');
    if ($delete_button) {
      throw new \Exception('Primary address is displaying Delete button');
    }
  }

  /**
   * @When /^I confirm deletion of address$/
   */
  public function iConfirmDeletionOfAddress() {
    $page = $this->getSession()->getPage();
    $button = $page->find('css', '.ui-dialog-buttonset.form-actions > button > span.ui-button-text')
      ->click();
  }

  /**
   * @Given /^the address block should be deleted from address book$/
   */
  public function theAddressBlockShouldBeDeletedFromAddressBook() {
    $page = $this->getSession()->getPage();
    $new_address_count = count($page->findAll('css', '.address'));
    if ($this->address_count - 1 !== $new_address_count) {
      throw new \Exception('Address did not get deleted from the address book');
    }
  }

  /**
   * @When /^I check the "([^"]*)" checkbox$/
   */
  public function iCheckTheCheckbox($option) {
    $page = $this->getSession()->getPage();
    $page->find('css', $option)->click();
  }

  /**
   * @Then /^I should see the link "([^"]*)" in "([^"]*)" section$/
   */
  public function iShouldSeeTheLinkInSection($arg1, $arg2) {
    $link = $this->getSession()
      ->getPage()
      ->find('css', $arg2)
      ->hasLink($arg1);
    if (!$link) {
      throw new \Exception($arg1 . 'link is not visible on my account section');
    }
  }

  /**
   * @When /^I select a value from Area dropdown$/
   */
  public function iSelectAValueFromTheAreaDropdown() {
    $page = $this->getSession()->getPage();
    $page->find('css', '.select2-selection__arrow')->click();
    $page->find('css', 'ul.select2-results__options li:nth-child(1)')->click();
  }

  /**
   * @Given /^I should be able to see the header for checkout$/
   */
  public function iShouldBeAbleToSeeTheHeaderForCheckout() {
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
   * @Then /^I should see store name and location for all the listed stores$/
   */
  public function iShouldSeeStoreNameAndLocationForAllTheListedStores() {
    $page = $this->getSession()->getPage();
    $all_stores = $page->findAll('css', 'li.select-store');
    foreach ($all_stores as $store) {
      $name_address = $store->has('css', 'div.store-name-and-address');
      if (!$name_address) {
        throw new \Exception('Name and address not displayed for a store');
      }
    }
  }

  /**
   * @Given /^I should see opening hours for all the listed stores$/
   */
  public function iShouldSeeOpeningHoursForAllTheListedStores() {
    $page = $this->getSession()->getPage();
    $all_stores = $page->findAll('css', 'li.select-store');
    foreach ($all_stores as $store) {
      $text = $store->find('css', 'div.hours--label')->getText();
      if ($text !== 'Opening Hours') {
        throw new \Exception('Opening hours not found on CC Stores listing page');
      }
    }
  }

  /**
   * @Then /^I should see collect in store info for all the listed stores$/
   */
  public function iShouldSeeCollectInStoreInfoForAllTheListedStores() {
    $page = $this->getSession()->getPage();
    $all_stores = $page->findAll('css', 'li.select-store');
    foreach ($all_stores as $store) {
      $delivery_time = $store->has('css', 'div.store-delivery-time span.delivery--time--value');
      $value = "'Collect in store from'.$delivery_time";
      if (!$value) {
        throw new \Exception('Delivery time is not displayed on CC Store listing page');
      }
    }
  }

  /**
   * @Given /^I should see select this store for all the listed stores$/
   */
  public function iShouldSeeSelectThisStoreForAllTheListedStores() {
    $page = $this->getSession()->getPage();
    $all_stores = $page->findAll('css', 'li.select-store');
    foreach ($all_stores as $store) {
      $select_store = $store->hasLink('select this store');
      if (!$select_store) {
        throw new \Exception('Select this store button is missing on CC store listing page');
      }
    }
  }

  /**
   * @Then /^I should see view on map button for all the listed stores$/
   */
  public function iShouldSeeViewOnMapButtonForAllTheListedStores() {
    $page = $this->getSession()->getPage();
    $all_stores = $page->findAll('css', 'li.select-store');
    foreach ($all_stores as $store) {
      $select_store = $store->hasLink('View on map');
      if (!$select_store) {
        throw new \Exception('View on map link is missing on CC store listing page');
      }
    }
  }

  /**
   * @Then /^I should see the price doubled for the product$/
   */
  public function iShouldSeeThePriceDoubledForTheProduct() {
    $page = $this->getSession()->getPage();
    $original_price = $page->find('css', '.subtotal.blend.dark .price-amount')
      ->getText();
    $original_price = floatval($original_price);
    $expected_price = floatval($original_price) * 2;
    if ($expected_price == FALSE) {
      throw new \Exception('Price did not get updated after adding the quantity');
    }
  }

  /**
   * @Given /^I am on a simple product page$/
   */
  public function iAmOnASimpleProductPage() {
    $this->visitPath('/click-lock-9oz-insulated-straw-cup-1-pack-assortment');
  }

  /**
   * @Given /^I am on a configurable product$/
   */
  public function iAmOnAConfigurableProduct() {
    $this->visitPath('/bodysuits-2-pack');
    $this->iWaitForThePageToLoad();
    $this->getSession()->getPage()->clickLink('0-3 Months');
    $this->getSession()
      ->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
  }

  /**
   * @Then /^I should see the link for simple product$/
   */
  public function iShouldSeeTheLinkForSimpleProduct() {
    $page = $this->getSession()->getPage();
    $this->simple_product = 'Stronglax';
    $link = $page->findLink($this->simple_product);
    if (!$link) {
      throw new \Exception('Link for simple product not found');
    }
  }

  /**
   * @Given /^I should see the link for configurable product$/
   */
  public function iShouldSeeTheLinkForConfigurableProduct() {
    $page = $this->getSession()->getPage();
    $this->config_product = 'Grey, Navy and Yellow Jersey Shorts - 3 Pack';
    $link = $page->hasLink($this->config_product);
    if (!$link) {
      throw new \Exception('Link for configurable product not found');
    }
  }

  /**
   * @Given /^I should not see the link for simple product$/
   */
  public function iShouldNotSeeTheLinkForSimpleProduct() {
    $element = $this->getSession()->getPage();
    $this->simple_product = 'Stronglax';
    $result = $element->findLink($this->simple_product);

    try {
      if ($result && $result->isVisible()) {
        throw new \Exception(sprintf("The link '%s' was visually visible on the page %s and was not supposed to be", $this->simple_product, $this->getSession()
          ->getCurrentUrl()));
      }
    }
    catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
    }

    if (!$result) {
      throw new \Exception(sprintf("The link '%s' was not loaded on the page %s at all", $this->simple_product, $this->getSession()
        ->getCurrentUrl()));
    }

  }

  /**
   * @Given /^I should not see the link for configurable product$/
   */
  public function iShouldNotSeeTheLinkForConfigurableProduct() {
    $element = $this->getSession()->getPage();
    $this->simple_product = 'Ton-Fax';
    $result = $element->findLink($this->config_product);

    try {
      if ($result && $result->isVisible()) {
        throw new \Exception(sprintf("The link '%s' was visually visible on the page %s and was not supposed to be", $this->config_product, $this->getSession()
          ->getCurrentUrl()));
      }
    }
    catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
    }

    if (!$result) {
      throw new \Exception(sprintf("The link '%s' was not loaded on the page %s at all", $this->config_product, $this->getSession()
        ->getCurrentUrl()));
    }

  }

  /**
   * @When /^I hover over tooltip "([^"]*)"$/
   */
  public function iHoverOverTooltip($arg1) {
    $page = $this->getSession()->getPage();
    $page->find('css', $arg1)->mouseOver();
  }

  /**
   * @Then /^I should see the link for simple product in Arabic$/
   */
  public function iShouldSeeTheLinkForSimpleProductInArabic() {
    $page = $this->getSession()->getPage();
    $this->simple_product = 'انت انتانتانت';
    $link = $page->hasLink($this->simple_product);
    if (!$link) {
      throw new \Exception('Link for simple product not found');
    }
  }

  /**
   * @Given /^I should see the link for configurable product in Arabic$/
   */
  public function iShouldSeeTheLinkForConfigurableProductInArabic() {
    $page = $this->getSession()->getPage();
    $this->config_product = 'لباس عادي - عبوة من قطعتين';
    $link = $page->hasLink($this->config_product);
    if (!$link) {
      throw new \Exception('Link for configurable product not found');
    }
  }

  /**
   * @Then /^I should see the Order Summary block$/
   */
  public function iShouldSeeTheOrderSummaryBlock() {
    $page = $this->getSession()->getPage();
    $block = $page->find('css', '#block-checkoutsummaryblock');
    if ($block == NULL) {
      throw new \Exception('Order Summary block not displayed on Order Summary block');
    }
    $title = $block->find('css', 'div > div.caption > span');
    if ($title == NULL) {
      throw new \Exception('Text Order Summary not displayed on Order Summary');
    }
    $items = $block->find('css', 'div > div.content > div.content-head');
    if ($items == NULL) {
      throw new \Exception('Items in your basket text is missing on Order Summary block');
    }
    $page->find('css', '.content-head')->click();
    $product_name = $block->find('css', 'div > div.content.active--accordion > div.content-items > ul > li > div.right > span.product-name > div > div > div > a');
    if ($product_name == NULL) {
      throw new \Exception('Product name is not displayed in Order Summary block');
    }
    $quantity = $block->find('css', 'div > div.content.active--accordion > div.content-items > ul > li > div.right > span.product-qty > span');
    if ($quantity == NULL) {
      throw new \Exception('Quantity is not displayed on Order Summary block');
    }
    $price = $block->find('css', 'div > div.content.active--accordion > div.content-items > ul > li > div.right > div > div > span > div.price');
    if ($price == NULL) {
      throw new \Exception('Price is not displayed on Order Summary block');
    }
    $sub_total = $block->find('css', 'div > div.totals > div.sub-total > span');
    if ($sub_total == NULL) {
      throw new \Exception('Sub total is not displayed on Order Summary block');
    }
    $order_total = $block->find('css', 'div > div.totals > div.order-total > span');
    if ($order_total == NULL) {
      throw new \Exception('Order total is not displayed on Order Summary block');
    }
  }

  /**
   * @Given /^I should see the Customer Service block$/
   */
  public function iShouldSeeTheCustomerServiceBlock() {
    $page = $this->getSession()->getPage();
    $customer_service = $page->find('css', '#block-customerservice');
    if ($customer_service == NULL) {
      throw new \Exception('Customer service block is not being displayed');
    }
  }

  /**
   * @When /^I fill in an element having class "([^"]*)" with "([^"]*)"$/
   */
  public function iFillInAnElementHavingClassWith($class, $value) {
    $page = $this->getSession()->getPage();
    $page->find('css', $class)->setValue($value);
  }

  /**
   * @When /^I select "([^"]*)" from dropdown "([^"]*)"$/
   */
  public function iSelectFromDropdown($value, $class) {
    $page = $this->getSession()->getPage();
    $page->find('css', $class)->selectOption($value);
  }

  /**
   * @Then /^I should see value "([^"]*)" for element "([^"]*)"$/
   */
  public function iShouldSeeValueForElement($value, $element) {
    $page = $this->getSession()->getPage();
    $actual_text = $page->find('css', $element)->getValue();
    if ($actual_text !== $value) {
      throw new \Exception($value . ' was not found');
    }
  }

  /**
   * @Given /^I check the "([^"]*)" radio button with "([^"]*)" value$/
   */
  public function iCheckTheRadioButtonWithValue($element, $value) {
    foreach ($this->getSession()
      ->getPage()
      ->findAll('css', 'input[type="radio"][name="' . $element . '"]') as $radio) {
      if ($radio->getAttribute('value') == $value) {
        $radio->click();
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * @When /^I select (\d+) from dropdown$/
   */
  public function iSelectFromDropdown1($arg1) {
    $page = $this->getSession()->getPage();
    $page->find('css', '.select2-selection__arrow')->click();
    $page->find('css', 'ul.select2-results__options li:nth-child(2)')->click();
  }

  /**
   * @Then /^the breadcrumb "([^"]*)" should be displayed$/
   */
  public function theBreadcrumbShouldBeDisplayed($breadcrumb) {
    $page = $this->getSession()->getPage();
    $breadcrumb_elements = $page->findAll('css', '#block-breadcrumbs > nav > ol > li');
    foreach ($breadcrumb_elements as $element) {
      $actual_breadcrumb[] = $element->find('css', 'a')->getText();
    }
    $actual_breadcrumb_result = implode(' > ', $actual_breadcrumb);
    if ($breadcrumb !== $actual_breadcrumb_result) {
      throw new \Exception('Incorrect breadcrumb displayed');
    }
  }

  /**
   * @Then /^it should display title, price and item code$/
   */
  public function itShouldDisplayTitlePriceAndItemCode() {
    $page = $this->getSession()->getPage();
    $parent = $page->find('css', '.content__title_wrapper');
    $title = $parent->find('css', 'h1 > span');
    if (NULL == $title) {
      throw new \Exception('Title not displayed on PDP');
    }
    $price = $parent->find('css', '.price-block');
    if (NULL == $price) {
      throw new \Exception('Price not displayed on PDP');
    }
    $english = $parent->find('css', '.content--item-code > span.field__label')
      ->find('named', ['content', 'Item Code:']);
    $arabic = $parent->find('css', '.content--item-code > span.field__label')
      ->find('named', ['content', 'رمز القطعة:']);
    if (!($english or $arabic)) {
      throw new \Exception('Item code not displayed on PDP');
    }

  }

  /**
   * @Then /^it should display quantity$/
   */
  public function itShouldDisplayQuantity() {
    $page = $this->getSession()->getPage();
    $eng_quantity = $page->find('css', '.form-item-quantity > .js-form-required.form-required ')
      ->find('named', ['content', 'Quantity']);
    $arb_quantity = $page->find('css', '.form-item-quantity > .js-form-required.form-required ')
      ->find('named', ['content', 'الكمية']);
    if (!($eng_quantity or $arb_quantity)) {
      throw new \Exception('Quantity is not displayed on PDP');
    }
  }

  /**
   * @Then /^I should see the link for "([^"]*)"$/
   */
  public function iShouldSeeTheLinkFor($arg1) {
    $link = $this->getSession()->getPage()->find('css', $arg1);
    if (!$link) {
      throw new \Exception($arg1 . ' link not found');
    }
  }

  /**
   * @Then /^I should see buttons for facebook, Twitter and Pinterest$/
   */
  public function iShouldSeeButtonsForFacebookTwitterAndPinterest() {
    $page = $this->getSession()->getPage();
    $facebook = $page->find('css', '.st_facebook_custom');
    if (NULL == $facebook) {
      throw new \Exception('Facebook button not displayed on PDP');
    }
    $twitter = $page->find('css', '.st_twitter_custom');
    if (NULL == $twitter) {
      throw new \Exception('Twitter button not displayed on PDP');
    }
    $pinterest = $page->find('css', '.st_pinterest_custom');
    if (NULL == $pinterest) {
      throw new \Exception('Pinterest button not displayed on PDP');
    }
  }

  /**
   * @Then /^I should see the inline modal for "([^"]*)"$/
   */
  public function iShouldSeeTheInlineModalFor($arg1) {
    $modal = $this->getSession()->getPage()->find('css', $arg1);
    if (!$modal) {
      throw new \Exception('Inline modal did not get displayed');
    }
  }

  /**
   * @Given /^I scroll to x "([^"]*)" y "([^"]*)" coordinates of page$/
   */
  public function iScrollToXYCoordinatesOfPage($arg1, $arg2) {

    try {
      $this->getSession()
        ->executeScript("(function(){window.scrollTo($arg1, $arg2);})();");
    }

    catch (\Exception $e) {
      throw new \Exception("ScrollIntoView failed");
    }
  }

  /**
   * @Then /^I should not see the inline modal for "([^"]*)"$/
   */
  public function iShouldNotSeeTheInlineModalFor($arg1) {
    $modal = $this->getSession()->getPage()->find('css', $arg1);
    if ($modal) {
      throw new \Exception('Inline modal did not get displayed');
    }
  }

  /**
   * @Given /^it should display size$/
   */
  public function itShouldDisplaySizeAndQuantity() {
    $page = $this->getSession()->getPage();
    $eng_size = $page->find('css', '#configurable_ajax > div > div.select2Option > h4.list-title')
      ->find('named', ['content', 'Size : ']);
    $arb_size = $page->find('css', '#configurable_ajax > div > div.select2Option > h4.list-title')
      ->find('named', ['content', 'المقاس :']);
    if (!($eng_size or $arb_size)) {
      throw new \Exception('Size is not displayed on PDP');
    }
  }

  /**
   * @Then /^I should be directed to window having "([^"]*)"$/
   */
  public function iShouldBeDirectedToWindowHaving($text) {
    $windowNames = $this->getSession()->getWindowNames();
    if (count($windowNames) > 1) {
      $this->getSession()->switchToWindow($windowNames[1]);
    }
    else {
      throw new \Exception('Social media did not open in a new window');
    }
    $text = $this->getSession()->getPage()->find('named', ['content', $text]);
    if (!$text) {
      throw new \Exception($text . ' was not found anywhere on the new window');
    }
    $current_window = $this->getSession()->getWindowName();
    $this->getSession()->stop($current_window);
  }

  /**
   * @Then /^I should see results sorted in ascending order$/
   */
  public function iShouldSeeResultsSortedInAscendingOrder() {
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', 'h2.field--name-name');
    if ($elements == NULL) {
      echo 'No search results found';
    }
    foreach ($elements as $element) {
      if ($element !== NULL) {
        $value = $element->find('css', 'a')->getText();
        $actual_values[] = $value;
      }
      else {
        throw new \Exception('Element is returning null');
      }
    }
    if (!$this->is_array_ordered($actual_values, ORDER_ASC)) {
      throw new \Exception('Search results list is not sorted in ascending order');
    }
  }

  /**
   * @Then /^I should see results sorted in descending order$/
   */
  public function iShouldSeeResultsSortedInDescendingOrder() {
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', 'h2.field--name-name');
    if ($elements == NULL) {
      echo 'No search results found';
    }
    foreach ($elements as $element) {
      if ($element !== NULL) {
        $value = $element->find('css', 'a')->getText();
        $actual_values[] = $value;
      }
      else {
        throw new \Exception('Element is returning null');
      }
    }
    if (!$this->is_array_ordered($actual_values, ORDER_DSC)) {
      throw new \Exception('Search results list is not sorted in ascending order');
    }
  }

  /**
   * @Then /^I should see results sorted in descending price order$/
   */
  public function iShouldSeeResultsSortedInDescendingPriceOrder() {
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', 'div.price-block');
    if ($elements == NULL) {
      echo 'No search results found';
    }
    foreach ($elements as $element) {
      if ($element !== NULL) {
        $value = NULL;
        $special_price = $element->find('css', 'div.has--special--price');
        if ($special_price) {
          $value = $element->find('css', 'div.special--price span.price-amount')
            ->getText();
        }
        else {
          $valueExists = $element->find('css', 'div.price span.price-amount');
          if ($valueExists) {
            $value = $valueExists->getText();
          }
        }

        if ($value !== NULL) {
          $actual_values[] = $value;
        }
      }
    }
    if (!$this->is_array_ordered($actual_values, ORDER_DSC)) {
      throw new \Exception('Search results list is not sorted in descending price order');
    }
  }

  /**
   * @Then /^I should see results sorted in ascending price order$/
   */
  public function iShouldSeeResultsSortedInAscendingPriceOrder() {
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', 'div.price-block');
    if ($elements == NULL) {
      echo 'No search results found';
    }
    foreach ($elements as $element) {
      if ($element !== NULL) {
        $value = NULL;
        $special_price = $element->find('css', 'div.has--special--price');
        if ($special_price) {
          $value = $element->find('css', 'div.special--price span.price-amount')
            ->getText();
        }
        else {
          $valueExists = $element->find('css', 'div.price span.price-amount');
          if ($valueExists) {
            $value = $valueExists->getText();
          }
        }

        if ($value !== NULL) {
          $actual_values[] = $value;
        }
      }
    }
    if (!$this->is_array_ordered($actual_values, ORDER_ASC)) {
      throw new \Exception('Search results list is not sorted in ascending price order');
    }
  }

  /**
   * @Given /^I select a product in stock$/
   */
  public function iSelectAProductInStock() {
    $page = $this->getSession()->getPage();
    $all_products = $page->findById('block-content');
    if ($all_products !== NULL) {
      $all_products = $all_products->findAll('css', '.c-products__item');
      $total_products = count($all_products);
    }
    else {
      throw new \Exception('Search passed, but search results were empty');
    }
    foreach ($all_products as $item) {
      $item_status = count($item->find('css', 'div.out-of-stock span'));
      if ($item_status) {
        $total_products--;
        if (!$total_products) {
          throw new \Exception('All products are out of stock');
        }
        continue;
      }
      $this->product = $item->find('css', 'h2.field--name-name')->getText();
      $page->clickLink($this->product);
      break;
    }
  }

  /**
   * @Given /^I should see the title and count of items$/
   */
  public function iShouldSeeTheTitleAndCountOfItems() {
    $page = $this->getSession()->getPage();
    $title = $page->find('css', 'h1.c-page-title > div');
    if ($title == NULL) {
      throw new \Exception('Title is not displayed on category page');
    }
    if (!(($page->hasContent('items')) or ($page->hasContent(' قطعة')))) {
      throw new \Exception('Number of items not displayed on category page');
    }
    $this->item_count = count($page->findAll('css', '.field--name-name'));
  }

  /**
   * @Then /^more items should get loaded$/
   */
  public function moreItemsShouldGetLoaded() {
    $page = $this->getSession()->getPage();
    $loaded_items = count($page->findAll('css', '.field--name-name'));
    if ($loaded_items < $this->item_count) {
      throw new \Exception('Load more is not functioning correctly');
    }
  }

  /**
   * @Given /^I select a product from a product category$/
   */
  public function iSelectAProductFromAProductCategory() {
    $page = $this->getSession()->getPage();
    $all_products = $page->findById('block-views-block-alshaya-product-list-block-1');
    if ($all_products !== NULL) {
      $all_products = $all_products->findAll('css', '.c-products__item');
      $total_products = count($all_products);
    }
    else {
      throw new \Exception('No products are listed on PLP');
    }
    foreach ($all_products as $item) {
      $item_status = count($item->find('css', 'div.out-of-stock span'));
      if ($item_status) {
        $total_products--;
        if (!$total_products) {
          throw new \Exception('All products are out of stock on PLP');
        }
        continue;
      }
      $this->product = $item->find('css', 'h2.field--name-name')->getText();
      $page->clickLink($this->product);
      break;
    }
  }

  /**
   * @Given /^I select a size for the product$/
   */
  public function iSelectASizeForTheProduct() {
    $page = $this->getSession()->getPage();
    $all_sizes = $page->findById('configurable_ajax');
    if ($all_sizes !== NULL) {
      $all_sizes = $all_sizes->findAll('css', 'div > div.select2Option > ul li');
      $total_sizes = count($all_sizes);
      foreach ($all_sizes as $size) {
        $check_li = $size->find('css', 'li')->getText();
        $size_status = count($size->find('css', '.disabled'));
        if ($size_status || !$check_li) {
          $total_sizes--;
          if (!$total_sizes) {
            throw new \Exception('All sizes are disabled');
          }
          continue;
        }
        $size->find('css', 'a')->click();
        break;
      }
    }
    else {
      echo 'No size attribute is available for this product';
    }
  }

}
