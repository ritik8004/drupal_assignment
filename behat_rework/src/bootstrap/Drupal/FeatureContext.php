<?php

namespace Alshaya\BehatContexts;

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\ElementNotFoundException;
use Exception;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\BehatContext;
use Behat\Mink;
use Behat\MinkExtension\Context;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Element\Element;
use Behat\Mink\WebAssert;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Drupal\DrupalExtension\Context\MinkExtension;
use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Drupal\node\Entity\Node;


define("ORDER_ASC", 1);
define("ORDER_DSC", 0);

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends CustomMinkContext
{

  private $parameters;

  public function __construct(array $parameters = [])
  {
    $this->parameters = $parameters;
  }

  /**
   * Wait for page to load for 15 seconds.
   *
   * @Given /^I wait for the page to load$/
   */
  public function iWaitForThePageToLoad()
  {
//    print_r($this->parameters['ajax_waittime']);
    $this->getSession()->wait($this->parameters['ajax_waittime'] * 1000, "document.readyState === 'complete'");
  }

  /**
   * Close the popup.
   *
   * @When /^I close the popup$/
   */
  public function iCloseThePopup()
  {
    $page = $this->getSession()->getPage();
    $popup = $this->getSession()->getPage()->findById("popup");
    if ($popup->isVisible()) {
      $page->findById('close-popup')->click();
    } else {
      echo 'Welcome Popup is currently not displayed';
    }
  }

  /**
   * Validate given breadcrumb exists on current page.
   *
   * @Then the breadcrumb :arg1 should be displayed
   */
  public function theBreadcrumbShouldBeDisplayed($breadcrumb)
  {
    $page = $this->getSession()->getPage();
    $breadcrumb_elements = $page->findAll('css', '#block-breadcrumbs > nav > ol > li');
    $actual_breadcrumb = [];
    foreach ($breadcrumb_elements as $element) {
      $actual_breadcrumb[] = $element->find('css', 'a')->getText();
    }
    $actual_breadcrumb_result = implode(' > ', $actual_breadcrumb);
    if ($breadcrumb !== $actual_breadcrumb_result) {
      throw new \Exception('Incorrect breadcrumb displayed');
    }
  }

  /**
   * Opens homepage.
   *
   * Example: Given I am on "/"
   * Example: When I go to "/"
   * Example: And I go to "/"
   *
   * @Given /^(?:|I )navigate to (?:|the )homepage$/
   * @When /^(?:|I )open web (?:|the )homepage$/
   */
  public function iVisitHomePage()
  {
    $this->visitPath($this->minkParam['base_url']);
  }

  /**
   * @Then /^I should see Search results page for "([^"]*)"$/
   */
  public function iShouldSeeSearchResultsPageFor($arg1)
  {
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
    } else {
      echo 'Search passed. But, Search term did not yield any results';
    }
  }

  /**
   * @When /^I enter a valid Email ID in field "([^"]*)"$/
   */
  public function iEnterAValidEmailID($field)
  {
    $randomString = 'randemail' . rand(2, getrandmax());
    $email_id = $randomString . '@gmail.com';
    $this->getSession()->getPage()->fillField($field, $email_id);
  }

  /**
   * @Given /^I select "([^"]*)" quantity$/
   */
  public function iSelectQuantity($quantity)
  {

    $this->getSession()->getPage()->selectFieldOption('quantity', $quantity);
    $this->quantity = $quantity;
  }

  /**
   * @Given /^I am logged in as an authenticated user "([^"]*)" with password "([^"]*)"$/
   */
  public function iAmLoggedInAsAnAuthenticatedUserWithPassword($arg1, $arg2)
  {
    $this->visitPath('/user/login');
    $this->iWaitSeconds('5');
    $this->getSession()->getPage()->fillField('edit-name', $arg1);
    $this->getSession()->getPage()->fillField('edit-pass', $arg2);
    $this->iWaitSeconds('5');
    $this->getSession()->getPage()->pressButton('sign in');
  }

  /**
   * @Then /^I should see Search results page in Arabic for "([^"]*)"$/
   */
  public function iShouldSeeSearchResultsPageInArabicFor($arg1)
  {

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
    } else {
      echo 'Search passed. But, Search term did not yield any results';
    }
  }

  /**
   * @Given /^I select a product from a product category$/
   */
  public function iSelectAProductFromAProductCategory()
  {
    $page = $this->getSession()->getPage();
    $all_products = $page->findById('block-views-block-alshaya-product-list-block-1');
    if ($all_products !== NULL) {
      $all_products = $all_products->findAll('css', '.c-products__item');
      $total_products = count($all_products);
    } else {
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
   * @Given /^I select a product in stock on "([^"]*)"$/
   */
  public function iSelectAProductInStockOn($css)
  {
    $page = $this->getSession()->getPage();
    $all_products = $page->find('css', $css);
    if ($all_products !== NULL) {
      $all_products = $all_products->findAll('css', '.c-products__item');
      $total_products = count($all_products);
    } else {
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
   * @When /^I select address$/
   */
  public function iSelectAddress()
  {
    $page = $this->getSession()->getPage();
    $address_button = $page->findLink('deliver to this address')->isVisible();
    if ($address_button == true) {
      $page->findLink('deliver to this address')->click();
    } else {
      echo 'Address is auto selected';
    }
  }

  /**
   * @Given /^I select a size for the product$/
   */
  public function iSelectASizeForTheProduct()
  {
    $page = $this->getSession()->getPage();
    $all_sizes = $page->findById('configurable_ajax');
    if ($all_sizes !== NULL) {
      $all_sizes = $all_sizes->findAll('css', 'div.form-item-configurables-size > div.select2Option > ul li');
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
    } else {
      echo 'No size attribute is available for this product';
    }
  }


  /**
   * @Given /^I check the "([^"]*)" radio button with "([^"]*)" value$/
   */
  public function iCheckTheRadioButtonWithValue($element, $value)
  {
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
   * @When /^I select a store$/
   */
  public function iSelectAStore()
  {
    $page = $this->getSession()->getPage();
    $address_button = $page->findLink('change store');
    if ($address_button !== null && $address_button->isVisible()) {
      $this->iSelectAnElementHavingClass('.cc-action');
    } else {
      $this->iSelectFirstAutocomplete('Shuwaikh', 'edit-store-location');
      $this->getSession()->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      $this->iWaitSeconds('5');
      $select_store = $page->findLink('select this store');
      if ($select_store->isVisible()) {
        $select_store->click();
      }
      $this->getSession()->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      $this->iSelectAnElementHavingClass('.cc-action');
      $this->iWaitForThePageToLoad();
    }
  }

  /**
   * @Given I select a store for Saudi arabia
   */
  public function iSelectAStoreForSaudiArabia()
  {
    $page = $this->getSession()->getPage();
    $address_button = $page->findLink('change store');
    if ($address_button !== null && $address_button->isVisible()) {
      $this->iSelectAnElementHavingClass('.cc-action');
    } else {
      $this->iSelectFirstAutocomplete('King Fahd Road, Jeddah Saudi Arabia', 'edit-store-location');
      $this->getSession()->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      $this->iWaitSeconds('5');
      $select_store = $page->findLink('select this store');
      if ($select_store->isVisible()) {
        $select_store->click();
      }
      $this->getSession()->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      $this->iSelectAnElementHavingClass('.cc-action');
      $this->iWaitForThePageToLoad();
    }
  }

  /**
   * @Given /^I select a payment option "([^"]*)"$/
   */
  public function iSelectAPaymentOption($payment)
  {
    $parent = $this->getSession()->getPage()->findById($payment);
    if ($parent !== NULL) {
      $parent->click();
    }
  }

  /**
   * @When I accept terms and conditions
   */
  public function iAcceptTermsAndConditions()
  {
    $checkbox = $this->getSession()
      ->getPage()
      ->find('css', '#edit-checkout-terms > div > div > label.option');

    if ($checkbox !== null) {
      $this->getSession()->executeScript("jQuery('#edit-checkout-terms > div > div > label.option').trigger('click');");
    }
  }

  /**
   * @When /^I select address for Arabic$/
   */
  public function iSelectAddressForArabic()
  {
    $page = $this->getSession()->getPage();
    $address_button = $page->findLink('توصيل إلى هذا العنوان')->isVisible();
    if ($address_button == true) {
      $page->findLink('توصيل إلى هذا العنوان')->click();
    } else {
      echo 'Address is auto selected';
    }
  }

  /**
   * @Given /^I scroll to x "([^"]*)" y "([^"]*)" coordinates of page$/
   */
  public function iScrollToXYCoordinatesOfPage($arg1, $arg2)
  {
    try {
      $this->getSession()
        ->executeScript("(function(){window.scrollTo($arg1, $arg2);})();");
    } catch (\Exception $e) {
      throw new \Exception("ScrollIntoView failed");
    }
  }

  /**
   * @Given /^I wait (\d+) seconds$/
   */
  public function iWaitSeconds($seconds)
  {
    sleep($seconds);
  }

  /**
   * @Given I select the first autocomplete option for :prefix on the :field field
   */
  public function iSelectFirstAutocomplete($prefix, $field)
  {
    $field = str_replace('\\"', '"', $field);
    $session = $this->getSession();
    $page = $session->getPage();
    $element = $page->findField($field);
    if (!$element) {
      throw new ElementNotFoundException($session, NULL, 'named', $field);
    }
    $page->fillField($field, $prefix);
    $this->iWaitSeconds(2);
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
      ->wait(10000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
    // Press the down arrow to select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);
    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);
    // Wait for AJAX to finish.
    $this->getSession()
      ->wait(10000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
  }

  /**
   * @When /^I click the label for "([^"]*)"$/
   */
  public function iClickTheLabelFor($arg1)
  {
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
  public function iShouldBeRedirectedToGoogleMapsWindow()
  {

    $windowNames = $this->getSession()->getWindowNames();
    if (count($windowNames) > 1) {
      $this->getSession()->switchToWindow($windowNames[1]);
    } else {
      throw new \Exception('Get directions did not open a new window');
    }
  }

  /**
   * @Given /^the "([^"]*)" tab should be selected$/
   */
  public function theTabShouldBeSelected($tab_name)
  {
    $tab = $this->getSession()
      ->getPage()
      ->findLink($tab_name)
      ->has('css', '.active');
    if (!$tab) {
      throw new \Exception($tab_name . 'is not selected by default');
    }
  }

  public function is_array_ordered($array, $sort_order)
  {
    $i = 0;
    $total_elements = count($array);

    if ($sort_order == ORDER_ASC) {
      //Check for ascending order
      while ($total_elements > 1) {
        if (strtolower($array[$i]) <= strtolower($array[$i + 1])) {
          $i++;
          $total_elements--;
        } else {
          var_dump($array[$i]);
          var_dump($array[$i + 1]);
          return FALSE;
        }
      }
    } elseif ($sort_order == ORDER_DSC) {
      //Check for descending order
      while ($total_elements > 1) {
        if (strtolower($array[$i]) >= strtolower($array[$i + 1])) {
          $i++;
          $total_elements--;
        } else {
          var_dump($array[$i]);
          var_dump($array[$i + 1]);
          return FALSE;
        }
      }
    }

    return TRUE;
  }

  /**
   * @Given /^the list should be sorted in alphabetical order$/
   */
  public function theListShouldBeSortedInAlphabeticalOrder()
  {
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
      } else {
        throw new \Exception('Element is returning null');
      }
    }
    if (!$this->is_array_ordered($actual_values, ORDER_ASC)) {
      throw new \Exception('Store list is not sorted on Store finder page');
    }
  }

  /**
   * @Given /^I select an element having class "([^"]*)"$/
   */
  public function iSelectAnElementHavingClass($arg1)
  {
    $label = $this->getSession()->getPage()->find('css', $arg1)->click();
  }

  /**
   * @Then /^I should see nearby stores listed$/
   */
  public function iShouldSeeNearbyStoresListed()
  {
    $page = $this->getSession()->getPage();

  }

  /**
   * @Then /^I should see the number of stores displayed$/
   */
  public function iShouldSeeTheNumberOfStoresDisplayed()
  {
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
   * @Given /^I should be able to see the header for checkout$/
   */
  public function iShouldBeAbleToSeeTheHeaderForCheckout()
  {
    $page = $this->getSession()->getPage();
//        $logo = $page->has('css', '.logo') and $page->hasLink('Home');
    $logo = $page->has('css', '.logo');
    if (!$logo) {
      throw new \Exception('Logo is not displayed on secure checkout page');
    }
    $text = $page->find('css', '.c-page-title')->getText();
//        print_r($text);
    if (strtolower($text) !== 'secure checkout') {
      throw new \Exception('Text Secure Checkout is not displayed');
    }

  }

  /**
   * @Then /^I should see store name and location for all the listed stores$/
   */
  public function iShouldSeeStoreNameAndLocationForAllTheListedStores()
  {
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
  public function iShouldSeeOpeningHoursForAllTheListedStores()
  {
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
  public function iShouldSeeCollectInStoreInfoForAllTheListedStores()
  {
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
  public function iShouldSeeSelectThisStoreForAllTheListedStores()
  {
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
  public function iShouldSeeViewOnMapButtonForAllTheListedStores()
  {
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
   * @Then /^I should see at most "([^"]*)" recent orders listed$/
   */
  public function iShouldSeeAtMostThreeRecentOrdersListed($count)
  {
    $page = $this->getSession()->getPage();
    $all_rows = count($page->findAll('css', '.order-summary-row'));
    if ($all_rows > $count) {
      throw new \Exception('More than three orders displayed on my account page');
    }
//    $all_orders = $page->findAll('css', '.order-transaction');
//    foreach ($all_orders as $order) {
//      $date_text = $order->find('css', '.light.order--date--time')->getText();
//      $strnew = substr_replace($date_text, ' ', '-3', '1');
//      $date = DateTime::createFromFormat('j M. Y @ H i', $strnew);
//      $time_array[] = $date->format('U');
//    }
//    if (!$this->is_array_ordered($time_array, ORDER_DSC)) {
//      throw new \Exception('Orders are not displayed in descending order');
//    }
  }

  /**
   * @When /^I click "([^"]*)" in my account section$/
   */
  public function iClickInMyAccountSection($arg1)
  {
    $text = $this->getSession()
      ->getPage()
      ->find('css', '#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a')
      ->click();
  }

  /**
   * @Then /^the number of stores displayed should match the count displayed on the page$/
   */
  public function theNumberOfStoresDisplayedShouldMatchTheCountDisplayedOnThePage()
  {
    $page = $this->getSession()->getPage();
    $results = $page->findAll('css', '.list-view-locator');
    if ($results !== NULL) {
      $actual_count = count($results);
      $count = (string)$actual_count;
      $actual_text = $page->find('css', '.view-header')->getText();
      if (strpos($actual_text, $count) === FALSE) {
        throw new \Exception('Count is incorrect');
      }
    } else {
      $message = $page->find('css', '#views-form-stores-finder-page-1 > p')
        ->has('content', 'Sorry, there are no stores or locations available to display.');
      if (!$message) {
        throw new \Exception('No stores message is not displayed when no stores are found');
      }
    }
  }

  /**
   * @When /^I click a pointer on the map$/
   */
  public function iClickAPointerOnTheMap()
  {
    $element = $this->getSession()
      ->getPage()
      ->find('css', 'div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(3) > div:nth-child(4) > img');
    if ($element !== null) {
      $element->click();
    } else {
      throw new Exception('Pointer is not clickable');
    }

  }

  /**
   * Scrolls an element into the viewport.
   *
   * @param string $selector
   *   The element's CSS selector.
   *
   * @When I scroll to the :selector element
   */
  public function scrollToElement($selector)
  {
    $this->getSession()
      ->executeScript('document.querySelector("' . addslashes($selector) . '").scrollIntoView()');
  }

  /**
   * @Then /^the "([^"]*)" tab should be highlighted$/
   */
  public function theTabShouldBeHighlighted($arg1)
  {
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
   * @Given /^the order status should be visible for all products$/
   */
  public function theOrderStatusShouldBeVisibleForAllProducts()
  {
    $page = $this->getSession()->getPage();
    $status_codes = array("Processing", "Cancelled", "Confirmed", "Dispatched", "المعالجة", "تم الإلغاء", "قيد التوصيل");
    $status_codes_length = count($status_codes);

    $all_rows = $page->findAll('css', '.order-summary-row');
    foreach ($all_rows as $row) {
      $status_button_text = $row->find('css', 'td.desktop-only > div')
        ->getText();
      $success = false;
      for ($status = 0; $status < $status_codes_length; $status++) {
        if (strcasecmp($status_button_text, $status_codes[$status]) == 0) {
          $success = true;
          break;
        }
      }
      if (!$success) {
        throw new \Exception('Status for order is not displayed on My account page');
      }
    }
  }

  /**
   * @When /^I click Get directions$/
   */
  public function iClickGetDirections()
  {
    $element = $this->getSession()
      ->getPage()
      ->find('css', 'div.get--directions > div > a');
    if ($element !== null) {
      $element->click();
    } else {
      throw new Exception('Element not clickable');
    }
  }

  /**
   * @Then /^I should see title, address, Opening hours and Get directions link on the popup$/
   */
  public function iShouldSeeTitleAddressOpeningHoursAndGetDirectionsLinkOnThePopup()
  {
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
  public function theNumberOfStoresDisplayedShouldMatchThePointerDisplayedOnMap()
  {
    $page = $this->getSession()->getPage();
    $all_pointers = $page->findAll('css', 'div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(3) div');
    $actual_count = count($all_pointers);
    $count = (string)$actual_count;
    $actual_text = $page->find('css', '.view-header')->getText();
    if (strpos($actual_text, $count) === FALSE) {
      throw new \Exception('Count displayed for number of stores is incorrect on Map view');
    }
  }

  /**
   * @Then /^I should see results sorted in ascending order$/
   */
  public function iShouldSeeResultsSortedInAscendingOrder()
  {
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', 'h2.field--name-name');
    if ($elements == NULL) {
      echo 'No search results found';
    }
    foreach ($elements as $element) {
      if ($element !== NULL) {
        $value = $element->find('css', 'a')->getText();
        $actual_values[] = $value;
      } else {
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
  public function iShouldSeeResultsSortedInDescendingOrder()
  {
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', 'h2.field--name-name');
    if ($elements == NULL) {
      echo 'No search results found';
    }
    foreach ($elements as $element) {
      if ($element !== NULL) {
        $value = $element->find('css', 'a')->getText();
        $actual_values[] = $value;
      } else {
        throw new \Exception('Element is returning null');
      }
    }
    if (!$this->is_array_ordered($actual_values, ORDER_DSC)) {
      throw new \Exception('Search results list is not sorted in ascending order');
    }
  }

  /**
   * @Then /^I should see all "([^"]*)" orders$/
   */
  public function iShouldSeeAllOrders($arg1)
  {
    $page = $this->getSession()->getPage();
    $all_orders = $page->findAll('css', '.order-item');
    foreach ($all_orders as $order) {
      $title = $order->find('css', 'a div.second-third.wrapper > div.second > div.dark.order-name')
        ->getText();
      if (stripos($title, $arg1) === false) {
        throw new Exception('Filter by name is not working on Orders tab in my account section');
      }
    }
  }

  /**
   * @Then /^I should see at most "([^"]*)" recent orders listed on orders tab$/
   */
  public function iShouldSeeAtMostRecentOrdersListedOnOrdersTab($arg1)
  {
    $page = $this->getSession()->getPage();
    $actual_count = count($page->findAll('css', '.order-item'));
    if ($actual_count > $arg1) {
      throw new \Exception('More than 10 orders are listed on Orders tab');
    }
//    $all_orders = $page->findAll('css', '.first-second.wrapper > div.first');
//    $number = [];
//    foreach ($all_orders as $order) {
//      $date_text = $order->find('css', '.light.date')->getText();
//      $strnew = substr_replace($date_text, ' ', '-3', '1');
//      $date = DateTime::createFromFormat('j M. Y @ H i', $strnew);
//      $time_array[] = $date->format('U');
//        if (!$this->is_array_ordered($time_array, ORDER_DSC)) {
//            throw new \Exception('Orders are not displayed in descending order');
//        }
  }


  /**
   * @Given /^I should see all orders for "([^"]*)"$/
   */
  public function iShouldSeeAllOrdersFor($arg1)
  {
    $page = $this->getSession()->getPage();
    $all_orders = $page->findAll('css', '.order-item');
    foreach ($all_orders as $order) {
      $order_id = $order->find('css', '.dark.order-id')->getText();
      $actual_order_id = substr($order_id, 0, 7);
      if (stripos($actual_order_id, $arg1) === false) {
        throw new \Exception('Filter for Order ID is not working');
      }
    }
  }

  /**
   * @Then /^I should see all "([^"]*)" orders listed on orders tab$/
   */
  public function iShouldSeeAllOrdersListedOnOrdersTab($arg1)
  {
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
   * @When /^I click Edit Address$/
   */
  public function iClickEditAddress()
  {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', '#block-content > div.views-element-container > div > div > div > div.views-row.clearfix.row-1 > div:nth-child(1) > div > span > div > div.address--options > div.address--edit.address--controls > a');
    if ($element !== NULL) {
      $element->click();
    }
  }

  /**
   * @Then /^I get the total count of address blocks$/
   */
  public function iGetTheTotalCountOfAddressBlocks()
  {
    $page = $this->getSession()->getPage();
    $this->address_count = count($page->findAll('css', '.address'));
  }

  /**
   * @Given /^the new address block should be displayed on address book$/
   */
  public function theNewAddressBlockShouldBeDisplayedOnAddressBook()
  {
    $page = $this->getSession()->getPage();
    $new_address_count = count($page->findAll('css', '.address'));
    if ($new_address_count < $this->address_count) {
      throw new \Exception('Newly added address is not being displayed on address book');
    }
  }

  /**
   * @Then /^I should not see the delete button for primary address$/
   */
  public function iShouldNotSeeTheDeleteButtonForPrimaryAddress()
  {
    $page = $this->getSession()->getPage();
    $delete_button = $page->find('css', '.address.default .address--options')
      ->hasLink('Delete');
    if ($delete_button) {
      throw new \Exception('Primary address is displaying Delete button');
    }
  }

  /**
   * @Given /^the address block should be deleted from address book$/
   */
  public function theAddressBlockShouldBeDeletedFromAddressBook()
  {
    $page = $this->getSession()->getPage();
    $new_address_count = count($page->findAll('css', '.address'));
    if ($new_address_count > $this->address_count) {
      throw new \Exception('Address did not get deleted from the address book');
    }
  }

  /**
   * @When /^I confirm deletion of address$/
   */
  public function iConfirmDeletionOfAddress()
  {
    $page = $this->getSession()->getPage();
    $button = $page->find('css', '.ui-dialog-buttonset.form-actions > button.button--primary.js-form-submit.form-submit.ui-button.ui-corner-all.ui-widget');
    if ($button !== null) {
      $button->click();
    } else {
      throw new Exception('Element not clickable');
    }

  }

  /**
   * @Then /^I should see the link "([^"]*)" in "([^"]*)" section$/
   */
  public function iShouldSeeTheLinkInSection($arg1, $arg2)
  {
    $link = $this->getSession()
      ->getPage()
      ->find('css', $arg2)
      ->hasLink($arg1);
    if (!$link) {
      throw new \Exception($arg1 . 'link is not visible');
    }
  }

  /**
   * @Then /^I should see the price doubled for the product$/
   */
  public function iShouldSeeThePriceDoubledForTheProduct()
  {
    $page = $this->getSession()->getPage();
    $original_price = $page->find('css', '.subtotal.blend.dark .price-amount')->getText();
    $original_price = floatval($original_price);
    $expected_price = floatval($original_price) * 2;
    if ($expected_price == FALSE) {
      throw new \Exception('Price did not get updated after adding the quantity');
    }
  }

  /**
   * @Then /^I should see the link for "([^"]*)"$/
   */
  public function iShouldSeeTheLinkFor($arg1)
  {
    $link = $this->getSession()->getPage()->find('css', $arg1);
    if (!$link) {
      throw new \Exception($arg1 . ' link not found');
    }
  }

  /**
   * @When /^I hover over tooltip "([^"]*)"$/
   */
  public function iHoverOverTooltip($arg1)
  {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', $arg1);
    if ($element !== null) {
      $element->mouseOver();
    } else {
      throw new Exception('Element not visible');
    }
  }

  /**
   * @Then /^it should display title, price and item code$/
   */
  public function itShouldDisplayTitlePriceAndItemCode()
  {
    $page = $this->getSession()->getPage();
    $parent = $page->find('css', '.content__title_wrapper');
    $title = $parent->find('css', 'h1 > span');
    if (null == $title) {
      throw new \Exception('Title not displayed on PDP');
    }
    $price = $parent->find('css', '.price-block');
    if (null == $price) {
      throw new \Exception('Price not displayed on PDP');
    }
    $english = $parent->find('css', '.content--item-code > span.field__label')
      ->find('named', array('content', 'Item Code:'));
    $arabic = $parent->find('css', '.content--item-code > span.field__label')
      ->find('named', array('content', 'رمز القطعة:'));
    if (!($english or $arabic)) {
      throw new \Exception('Item code not displayed on PDP');
    }
  }

  /**
   * @Then /^I should see buttons for facebook, Twitter and Pinterest$/
   */
  public function iShouldSeeButtonsForFacebookTwitterAndPinterest()
  {
    $page = $this->getSession()->getPage();
    $facebook = $page->find('css', '.st_facebook_custom');
    if (null == $facebook) {
      throw new \Exception('Facebook button not displayed on PDP');
    }
    $twitter = $page->find('css', '.st_twitter_custom');
    if (null == $twitter) {
      throw new \Exception('Twitter button not displayed on PDP');
    }
    $pinterest = $page->find('css', '.st_pinterest_custom');
    if (null == $pinterest) {
      throw new \Exception('Pinterest button not displayed on PDP');
    }
  }

  /**
   * @Then /^I should see results sorted in descending price order$/
   */
  public function iShouldSeeResultsSortedInDescendingPriceOrder()
  {
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
          $value = $element->find('css', 'div.special--price span.price-amount')->getText();
        } else {
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
  public function iShouldSeeResultsSortedInAscendingPriceOrder()
  {
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
          $value = $element->find('css', 'div.special--price span.price-amount')->getText();
        } else {
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
   * @Given /^I should see the title and count of items$/
   */
  public function iShouldSeeTheTitleAndCountOfItems()
  {
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
  public function moreItemsShouldGetLoaded()
  {
    $page = $this->getSession()->getPage();
    $loaded_items = count($page->findAll('css', '.field--name-name'));
    if ($loaded_items < $this->item_count) {
      throw new \Exception('Load more is not functioning correctly');
    }
  }

  /**
   * @Then /^I should see the inline modal for "([^"]*)"$/
   */
  public function iShouldSeeTheInlineModalFor($arg1)
  {
    $modal = $this->getSession()->getPage()->find('css', $arg1);
    if (!$modal) {
      throw new \Exception('Inline modal did not get displayed');
    }
  }

  /**
   * @Then /^I should not see the inline modal for "([^"]*)"$/
   */
  public function iShouldNotSeeTheInlineModalFor($arg1)
  {
    $modal = $this->getSession()->getPage()->find('css', $arg1);
    if ($modal) {
      throw new \Exception('Inline modal did not get displayed');
    }
  }

  /**
   * @Then /^I should be directed to window having "([^"]*)"$/
   */
  public function iShouldBeDirectedToWindowHaving($text)
  {
    $windowNames = $this->getSession()->getWindowNames();
    if (count($windowNames) > 1) {
      $this->getSession()->switchToWindow($windowNames[1]);
    } else {
      throw new \Exception('Social media did not open in a new window');
    }
    $text = $this->getSession()->getPage()->find('named', array('content', $text));
    if (!$text) {
      throw new \Exception($text . ' was not found anywhere on the new window');
    }
    $current_window = $this->getSession()->getWindowName();
    $this->getSession()->stop($current_window);
  }

  /**
   * @Then /^I should see the Order Summary block$/
   */
  public function iShouldSeeTheOrderSummaryBlock()
  {
    $page = $this->getSession()->getPage();
    $block = $page->find('css', '#block-checkoutsummaryblock');
    if ($block == null) {
      throw new \Exception('Order Summary block not displayed on Order Summary block');
    }
    $title = $block->find('css', 'div > div.caption > span');
    if ($title == null) {
      throw new \Exception('Text Order Summary not displayed on Order Summary');
    }
    $items = $block->find('css', 'div > div.content > div.content-head');
    if ($items == null) {
      throw new \Exception('Items in your basket text is missing on Order Summary block');
    }
    $page->find('css', '.content-head')->click();
    $product_name = $block->find('css', 'div > div.content.active--accordion > div.content-items > ul > li > div.right > span.product-name > div > div > div > a');
    if ($product_name == null) {
      throw new \Exception('Product name is not displayed in Order Summary block');
    }
    $quantity = $block->find('css', 'div > div.content.active--accordion > div.content-items > ul > li > div.right > span.product-qty > span');
    if ($quantity == null) {
      throw new \Exception('Quantity is not displayed on Order Summary block');
    }
    $price = $block->find('css', 'div > div.content.active--accordion > div.content-items > ul > li > div.right > div > div > span > div.price');
    if ($price == null) {
      throw new \Exception('Price is not displayed on Order Summary block');
    }
    $sub_total = $block->find('css', 'div > div.totals > div.sub-total > span');
    if ($sub_total == null) {
      throw new \Exception('Sub total is not displayed on Order Summary block');
    }
    $order_total = $block->find('css', 'div > div.totals > div.order-total > span');
    if ($order_total == null) {
      throw new \Exception('Order total is not displayed on Order Summary block');
    }
  }

  /**
   * @Given /^I should see the Customer Service block$/
   */
  public function iShouldSeeTheCustomerServiceBlock()
  {
    $page = $this->getSession()->getPage();
    $customer_service = $page->find('css', '#block-customerservice');
    if ($customer_service == null) {
      throw new \Exception('Customer service block is not being displayed');
    }
  }

  /**
   * @When /^I fill in an element having class "([^"]*)" with "([^"]*)"$/
   */
  public function iFillInAnElementHavingClassWith($class, $value)
  {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', $class);
    if ($element !== null) {
      $element->setValue($value);
    } else {
      echo 'Element not found';
    }
  }

  /**
   * @When /^I select "([^"]*)" from dropdown "([^"]*)"$/
   */
  public function iSelectFromDropdown($value, $class)
  {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', $class);
    if ($element !== null) {
      $element->selectOption($value);
    } else {
      echo 'Element not found';
    }
  }

  /**
   * @When /^I select (\d+) from dropdown$/
   */
  public function iSelectFromDropdown1($arg1)
  {
    $page = $this->getSession()->getPage();
    $page->find('css', '.select2-selection__arrow')->click();
    $page->find('css', 'ul.select2-results__options li:nth-child(2)')->click();
  }

  /**
   * @Then /^I should see value "([^"]*)" for element "([^"]*)"$/
   */
  public function iShouldSeeValueForElement($value, $element)
  {
    $page = $this->getSession()->getPage();
    $actual_text = $page->find('css', $element)->getValue();
    if ($actual_text !== $value) {
      throw new \Exception($value . ' was not found');
    }
  }

  /**
   * @Given /^the order total price should be reflected as per the coupon discount of "([^"]*)" KWD$/
   */
  public function theOrderTotalPriceShouldBeReflectedAsPerTheCoupon($discount)
  {
    $page = $this->getSession()->getPage();
    $sub_total = (float)$page->find('css', '#edit-totals > tbody > tr:nth-child(1) > td:nth-child(2) > div > div > span > div > span.price-amount')->getText();
    $expected_order_total = $sub_total - $discount;
    $actual_order_total = (float)$page->find('css', '#edit-totals > tbody > tr:nth-child(3) > td:nth-child(2) > div > div > span > div > span.price-amount')->getText();
    if ($expected_order_total !== $actual_order_total) {
      throw new \Exception('Promotions for coupon FIXED did not apply correctly');
    }
  }

  /**
   * @Given /^I should get "([^"]*)" products free on buying "([^"]*)"$/
   */
  public function iShouldGetProductsFreeOnBuying($free, $buy)
  {
    $page = $this->getSession()->getPage();
    $sub_total = (float)$page->find('css', '#edit-totals > tbody > tr:nth-child(1) > td:nth-child(2) > div > div > span > div > span.price-amount')->getText();
    $per_item_price = $sub_total / $buy;
    $expected_discount = $sub_total - ($per_item_price * $free);
    $discount = (float)$page->find('css', '#edit-totals > tbody > tr:nth-child(2) > td:nth-child(2) > div > div.price > span > div > span.price-amount')->getText();
    $actual_discount = $sub_total + $discount;
    if ($expected_discount !== $actual_discount) {
      throw new \Exception('Discount did not work for buy ' . $buy . 'and get ' . $free);
    }
  }

  /**
   * @Then /^I should get a discount of "([^"]*)" KWD when the cart subtotal is greater than or equal to "([^"]*)" KWD$/
   */
  public function iShouldGetADiscountOfWhenTheCartSubtotalIsGreaterThanOrEqualTo($discount, $subtotal)
  {
    $page = $this->getSession()->getPage();
    $actual_subtotal = (float)$page->find('css', '#edit-totals > tbody > tr:nth-child(1) > td:nth-child(2) > div > div > span > div > span.price-amount')->getText();
    $expected_discount = $actual_subtotal - $discount;
    $actual_discount = $actual_subtotal + (float)$page->find('css', '#edit-totals > tbody > tr:nth-child(2) > td:nth-child(2) > div > div.price > span > div > span.price-amount')->getText();
    if ($actual_subtotal >= $subtotal) {
      if ($actual_discount !== $expected_discount) {
        throw new \Exception('Discount ' . $discount . 'KWD did not get applied when cart total was greater than or equal to ' . $subtotal . 'KWD');
      }
    }
  }

  /**
   * @Then /^I should be able to subscribe to the newsletter displayed on the popup$/
   */
  public function iShouldBeAbleToSubscribeToTheNewsletterDisplayedOnThePopup()
  {
    $page = $this->getSession()->getPage();
    $popup = $this->getSession()->getPage()->findById("popup");
    if ($popup->isVisible()) {
      $field = $page->findById('edit-email');
      $this->iEnterAValidEmailID($field);
      $popup->findButton('sign up')->click();
    } else {
      echo 'Welcome popup is not displayed on the site';
    }
  }

  /**
   * @When /^I select "([^"]*)" from the dropdown$/
   */
  public function iSelectFromTheDropdown($arg1)
  {
    $page = $this->getSession()->getPage();
    $page->find('css', '.select2-selection__arrow')->click();
    $status = $page->find('named', array('content', $arg1));
    if ($status !== null) {
      $status->click();
    } else {
      echo $arg1 . ' is not displayed in the dropdown';
    }
  }

  /**
   * @When /^I click "([^"]*)" "([^"]*)" in the region "([^"]*)"$/
   */
  public function iClickInTheRegion($element, $type, $region)
  {
    $page = $this->getSession()->getPage();
    $region = $page->find('css', $region);
    if ($region !== null) {
      $region->find('named', array($type, $element))->click();
    } else {
      throw new Exception('Breadcrumbs not displayed');
    }
  }

  /**
   * @When /^I enter a location in "([^"]*)"$/
   */
  public function iEnterALocationIn($css_location)
  {
    $page = $this->getSession()->getPage();
    $change_link = $page->find('css', '.change-location-link');
    if ($change_link !== null && $change_link->isVisible()) {
      $change_link->click();

    }
    $this->iSelectFirstAutocomplete('shuwaikh', $css_location);
  }

  /**
   * @When /^I enter a location in saudi arabia "([^"]*)"$/
   */
  public function iEnterALocationInSaudiArabia($css_location)
  {
    $page = $this->getSession()->getPage();
    $change_link = $page->find('css', '.change-location-link');
    if ($change_link !== null && $change_link->isVisible()) {
      $change_link->click();

    }
    $this->iSelectFirstAutocomplete('King Fahd Road, Jeddah Saudi Arabia', $css_location);
  }

  /**
   * @Given /^I select a product in stock$/
   */
  public function iSelectAProductInStock()
  {
    $page = $this->getSession()->getPage();
    $all_products = $page->findById('block-content');
    if ($all_products !== NULL) {
      $all_products = $all_products->findAll('css', '.c-products__item');
      $total_products = count($all_products);
    } else {
      throw new Exception('Search passed, but search results were empty');
    }
    foreach ($all_products as $item) {
      $item_status = count($item->find('css', 'div.out-of-stock span'));
      if ($item_status) {
        $total_products--;
        if (!$total_products) {
          throw new Exception('All products are out of stock');
        }
        continue;
      }
      $this->product = $item->find('css', 'h2.field--name-name')->getText();
      $page->clickLink($this->product);
      break;
    }
  }


  /**
   * @When /^I select a store on arabic$/
   */
  public function iSelectAStoreOnArabic()
  {
    $page = $this->getSession()->getPage();
    $address_button = $page->findLink('تغيير المحل');
    if ($address_button !== null && $address_button->isVisible()) {
      $this->iSelectAnElementHavingClass('.cc-action');
    } else {
      $this->iSelectFirstAutocomplete('Shuwaikh', 'edit-store-location');
      $this->getSession()->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      $this->iWaitSeconds('5');
      $select_store = $page->findLink('اختر هذا المحل');
      if ($select_store->isVisible()) {
        $select_store->click();
      }
      $this->getSession()->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      $this->iSelectAnElementHavingClass('.cc-action');
      $this->iWaitForThePageToLoad();
    }
  }


  /**
   * @When /^I select a store on arabic for SA$/
   */
  public function iSelectAStoreOnArabicForSA()
  {
    $page = $this->getSession()->getPage();
    $address_button = $page->findLink('تغيير المحل');
    if ($address_button !== null && $address_button->isVisible()) {
      $this->iSelectAnElementHavingClass('.cc-action');
    } else {
      $this->iSelectFirstAutocomplete('King Fahd Road, Jeddah Saudi Arabia', 'edit-store-location');
      $this->getSession()->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      $this->iWaitSeconds('5');
      $select_store = $page->findLink('اختر هذا المحل');
      if ($select_store->isVisible()) {
        $select_store->click();
      }
      $this->getSession()->wait(45000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      $this->iSelectAnElementHavingClass('.cc-action');
      $this->iWaitForThePageToLoad();
    }
  }

  /**
   * @When /^I click on "([^"]*)" element$/
   */
  public function iClickOnElement($css_selector)
  {
    $element = $this->getSession()->getPage()->find("css", $css_selector);
    if (count($element) > 0) {
      $element->click();
    } else {
      throw new Exception("Element " . $css_selector . " not found on " . $this->getSession()->getCurrentUrl());
    }
  }

  /**
   * @When I save card for future use
   */
  public function iSaveCardForFutureUse()
  {
    $checkbox = $this->getSession()
      ->getPage()
      ->find('css', '#payment_card_new > div.form-item.js-form-type-checkbox.form-type-checkbox.form-item-save-card > label.option');

    if ($checkbox !== null) {
      $checkbox->click();
    }
  }

  /**
   * @When /^I confirm deletion of card/
   */
  public function iConfirmDeletionOfCard()
  {
    $page = $this->getSession()->getPage();
    $button = $page->find('css', '.ui-dialog-buttonset.form-actions > button.button--primary.js-form-submit.form-submit.ui-button.ui-corner-all.ui-widget');
    if ($button !== null) {
      $button->click();
    } else {
      throw new Exception('Element not clickable');
    }

  }

  /**
   * @Given /^the card block should be deleted from payment cards/
   */
  public function theCardBlockShouldBeDeletedFromPaymentCards()
  {
    $page = $this->getSession()->getPage();
    $new_address_count = count($page->findAll('css', '.payment-card'));
    if ($new_address_count > $this->address_count) {
      throw new \Exception('Card did not get deleted from the Payment Cards page');
    }


  }

  /**
   * @Given /^I should be able to click my Account/
   */
  public function iShouldBeAbleToClickMyAccount()
  {
    $my_account = $this->getSession()
      ->getPage()
      ->find('css', '#block-account-menu > ul > li:nth-child(1) > a');
    $my_account->click();

  }

  /**
   * @When /^I select a color for the product$/
   */
  public function iSelectAColorForTheProduct()
  {
    $page = $this->getSession()->getPage();
    $all_colors = $page->findById('configurable_ajax');
    if ($all_colors !== NULL) {
      $all_colors = $all_colors->findAll('css', 'div.form-item-configurables-article-castor-id > div.select2Option > ul li');
      foreach ($all_colors as $color) {
        $check_empty_li = $color->find('css','li')->getHtml();
        if($check_empty_li == null) {
          $color++;
          continue;
        }
        $color->find('css', 'a')->click();
        break;
      }
    } else {
      echo 'No color attribute is available for this product';
    }
  }

  /**
   * Clicks link with specified id|class
   * Example: When I click the element "Log In" on page
   *
   * @When /^(?:|I )click the element "(?P<link>(?:[^"]|\\")*)" on page$/
   */
  public function iClickElementOnPage($locator)
  {
    $element = $this->getSession()->getPage()->find('css', $locator);
    if (!empty($element)) {
      $this->getSession()->executeScript("jQuery('$locator').trigger('click');");
    }
    throw new \Exception(sprintf('Element %s is not found on page.', $element));
  }

  /**
   * Clicks link with specified id|class
   * Example: When I click the element with id "Log In" on page
   *
   * @When /^(?:|I )click the element with id "(?P<link>(?:[^"]|\\")*)" on page$/
   */
  public function iClickElementWithIDOnPage($locator)
  {
    $element = $this->getSession()->getPage()->find('css', "#$locator");
    if (empty($element)) {
      throw new \Exception(sprintf('Element %s is not found on page.', $locator));
    }
    $this->getSession()->executeScript('document.getElementById("'. $locator .'").click();');
  }

  /**
   * @When /^I select "([^"]*)" from the filter "([^"]*)"$/
   */
  public function iSelectFromTheFilters($option, $filter)
  {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', "$filter .fieldset-legend");
    if (!empty($element)) {
      $element->click();
      $this->getSession()->executeScript('var id = jQuery("label:contains(\''. $option . '\')").attr(\'for\'); document.getElementById(id).click();');
      return;
    }
    throw new \Exception(sprintf('Element %s is not found on page.', $filter));
  }

  /**
   * @When I click the anchor link :arg1 on page
   */
  public function iClickTheAnchorLinkOnPage($locator)
  {
    $element = $this->getSession()->getPage()->find('css', "$locator");
    if (empty($element)) {
      throw new \Exception(sprintf('Element %s is not found on page.', $locator));
    }
    $this->getSession()->executeScript("jQuery('$locator').get(0).click();");
  }

  /**
   * @Then I scroll :pixels to element
   * @Then I scroll to top
   */
  public function iScrollToElement($pixels = 0)
  {
    $this->getSession()->executeScript("jQuery(window).scrollTop($pixels);");
  }

  /**
   * @Given I select :arg attribute for the product
   */
  public function iSelectAttributeForTheProduct($attribute)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $attribute_wrapper = $page->findById('configurable_ajax');
    if ($attribute_wrapper) {
      $element = $attribute_wrapper->find('css', "select[name='configurables[$attribute]']")->getParent();
      if ($element) {
        $links = $element->findAll('css', '.select2Option li');
        if($links)
        {
          foreach($links as $link){
            if ($link->isVisible()) {
              $link->find('css', 'a')->click();
              break;
            }
          }
        }
      }
    }
  }

  /**
   * @Then the price for product should be doubled
   */
  public function iShouldSeeThePriceDoubled()
  {
    $page = $this->getSession()->getPage();
    $original_price = $page->find('css', '#spc-cart .spc-main .spc-content .spc-cart-item .spc-product-tile .spc-product-container .spc-product-price .price-amount')->getHtml();
    $original_price = floatval($original_price);
    $double_price = floatval($original_price) * 2;
    $expected_price = $page->find('css', '.spc-sidebar .spc-order-summary-block .hero-total .value .price .price-amount')->getText();
    $expected_price = floatval($expected_price);

    if ($expected_price !== $double_price) {
      throw new \Exception('Price did not get updated after adding the quantity');
    }
  }

  /**
   * @Then I should see a :element element on page
   */
  public function iShouldSeeAElementOnPage($element) {
    $this->assertSession()->elementExists('css', $element);
  }
}
