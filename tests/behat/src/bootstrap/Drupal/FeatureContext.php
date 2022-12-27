<?php

namespace Alshaya\BehatContexts;

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\ElementNotFoundException;
use Exception;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
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
use Behat\Behat\Hook\Scope\AfterScenarioScope;

define("ORDER_ASC", 1);
define("ORDER_DSC", 0);

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends CustomMinkContext
{

  private $parameters;

  /**
   * Removes banners and popups.
   *
   * @BeforeStep
   */
  public function hideBanners()
  {
    $classesToHide = [
      '.block-content--marketing-popup',
      '.exponea-subbox-banner',
      '.exponea-subbox-subscription-dialog',
      '.subbox-banner-backdrop',
      '.subbox-banner',
      '.subbox-wrap',
    ];

    try {
      $session = $this->getSession();
      if ($session instanceof Session) {
        $driver = $session->getDriver();
        if ($driver instanceof Selenium2Driver && $driver->getWebDriverSession()) {
          $classes = implode(',', $classesToHide);
          $script = 'var sheet = window.document.styleSheets[0];';
          $script .= "sheet.insertRule('$classes { display: none!important; }', sheet.cssRules.length);";
          $this->getSession()->executeScript($script);
        }
      }
    } catch (\Exception) {
      // Silently fail when there is an error in this event.
    }
  }

  /**
   * Storing URL of page.
   * @var
   */
  public $pageurl;

  public function __construct(array $parameters = [])
  {
    $this->parameters = $parameters;
  }

  /**
   * @AfterScenario
   */
  public function cleanBrowserSessions(AfterScenarioScope $scope)
  {
    $this->getSession()->stop();
  }

  /**
   * Wait for page to load for 15 seconds.
   *
   * @Given /^I wait for the page to load$/
   */
  public function iWaitForThePageToLoad()
  {
    $this->getSession()->wait($this->parameters['ajax_waittime'] * 3000, "document.readyState === 'complete'");
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
    $randomString = 'randemail' . random_int(2, mt_getrandmax());
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
    $this->iWaitSeconds('10');
    $this->getSession()->getPage()->fillField('edit-name', $arg1);
    $this->getSession()->getPage()->fillField('edit-pass', $arg2);
    $this->iWaitSeconds('10');
    $this->getSession()->executeScript('jQuery("#edit-submit").click()');
    $this->iWaitSeconds('5');
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
      if ($item->find('css', 'div.out-of-stock span')) {
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
        $size_status = $size->find('css', '.disabled') === null ? 0 : count($size->find('css', '.disabled'));
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
    } catch (\Exception) {
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
    $total_elements = is_countable($array) ? count($array) : 0;

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
    $actual_values = [];
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
    if (!str_contains($actual_text, $count)) {
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
      if (!str_contains($actual_text, $count)) {
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
    $status_codes = ["Processing", "Cancelled", "Confirmed", "Dispatched", "المعالجة", "تم الإلغاء", "قيد التوصيل"];
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
    if (!str_contains($actual_text, $count)) {
      throw new \Exception('Count displayed for number of stores is incorrect on Map view');
    }
  }

  /**
   * @Then /^I should see results sorted in ascending order$/
   */
  public function iShouldSeeResultsSortedInAscendingOrder()
  {
    $actual_values = [];
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', '#hits h2.field--name-name');
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
    $actual_values = [];
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', '#hits h2.field--name-name');
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
      if (stripos($title, (string) $arg1) === false) {
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
      if (stripos($actual_order_id, (string) $arg1) === false) {
        throw new \Exception('Filter for Order ID is not working');
      }
    }
  }

  /**
   * @Then /^I should see all "([^"]*)" orders listed on orders tab$/
   */
  public function iShouldSeeAllOrdersListedOnOrdersTab($arg1)
  {
    $status_name = null;
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
    $page = $this->getSession()->getPage();
    $section = $page->find('css', $arg2);
    if (!empty($section)) {
      $link = $section->hasLink($arg1);
      if (!$link) {
        throw new \Exception($arg1 . 'link is not visible.');
      }
    } else {
      throw new \Exception($arg2 . 'section does not exists.');
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
      ->find('named', ['content', 'Item Code:']);
    $arabic = $parent->find('css', '.content--item-code > span.field__label')
      ->find('named', ['content', 'رمز القطعة:']);
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
    $actual_values = [];
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', 'div.product-plp-detail-wrapper');
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

        if (!empty($value)) {
          $actual_values[] = $value;
        }
      }
    }
    if (!$this->is_array_ordered($actual_values, ORDER_DSC)) {
      throw new \Exception('Search results list is not sorted in descending price order');
    }
  }

  /**
   * @When /^I select the filter "([^"]*)"$/
   */
  public function iSelectTheFilter($filter)
  {
    if (!empty($filter)) {
      $page = $this->getSession()->getPage();
      $element = $this->getSession()
        ->getPage()
        ->find('xpath', "//div[@id=\"alshaya-algolia-search\"]//div[@class=\"container-without-product\"]//div[contains(@id, '$filter')]/h3");
      if (!empty($element)) {
        $element->click();
      } else {
        throw new \Exception(sprintf('Filter is not found on page.', $filter));
      }
      $filterValue_element = $this->getSession()
        ->getPage()
        ->find('xpath', "//div[@id=\"alshaya-algolia-search\"]//div[@class=\"container-without-product\"]//div[contains(@id, '$filter')]/ul/li[1]");
      if (!empty($filterValue_element)) {
        $text = $page->find('xpath', "//div[@id=\"alshaya-algolia-search\"]//div[@class=\"container-without-product\"]//div[contains(@id, $filter)]/ul/li[1]/span")->getText();
        $filterValue_element->click();
      } else {
        throw new \Exception(sprintf('Filter has no value to select.', $filter));
      }
//            $text = $page->find('xpath', '//div[@class="container-without-product"]//div[contains(@id, \'price\')]/ul/li/span')->getText();
      $selectedFilters = $page->findAll('css', '#block-filterbar ul li a span.facet-item__value');
      foreach ($selectedFilters as $result) {
        if ($result->getText() == $text) {
          echo 'Selected filter is displaying';
          break;
        }
      }
    }
  }

  /**
   * @Then /^I should see results sorted in ascending price order$/
   */
  public function iShouldSeeResultsSortedInAscendingPriceOrder()
  {
    $actual_values = [];
    $page = $this->getSession()->getPage();
    $elements = $page->findAll('css', 'div.product-plp-detail-wrapper');
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
    $text = $this->getSession()->getPage()->find('named', ['content', $text]);
    if (!$text) {
      throw new \Exception($text . ' was not found anywhere on the new window');
    }
    $current_window = $this->getSession()->getWindowName();
    $this->getSession()->stop();
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
    $status = $page->find('named', ['content', $arg1]);
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
      $region->find('named', [$type, $element])->click();
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
      $item_status = $item->find('css', 'div.out-of-stock span') === null ? 0 : count($item->find('css', 'div.out-of-stock span'));
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
    if ($element) {
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
        $check_empty_li = $color->find('css', 'li')->getHtml();
        if ($check_empty_li == null) {
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
    } else {
      throw new \Exception(sprintf('Element %s is not found on page.', $element));
    }
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
    $this->getSession()->executeScript('document.getElementById("' . $locator . '").click();');
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
      $this->getSession()->executeScript('var id = jQuery("label:contains(\'' . $option . '\')").attr(\'for\'); document.getElementById(id).click();');
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
        if ($links) {
          foreach ($links as $link) {
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

    if ($page->find('css', '#block-content #spc-cart .spc-cart-item .spc-product-price .has--special--price')) {
      $product_price = $page->find('css', '#block-content #spc-cart .spc-cart-item .spc-product-price .special--price .price .price-amount')->getHtml();
      $double_price = floatval(str_replace(',', '', $product_price)) * 2;
    } else {
      $original_price = $page->find('css', '#spc-cart .spc-main .spc-content .spc-cart-item .spc-product-tile .spc-product-container .spc-product-price .price-amount')
        ->getHtml();
      $original_price = floatval(str_replace(',', '', $original_price));
      $double_price = floatval(str_replace(',', '', $original_price)) * 2;
    }

    if ($page->find('css', '.spc-sidebar .spc-order-summary-block .totals .discount-total')) {
      $discount_parent = $page->find('css', '.spc-sidebar .spc-order-summary-block .totals .discount-total')->getParent();
      $discount = abs($discount_parent->find('css', '.value .price .price-amount')->getHtml());
      $double_price = $double_price - floatval($discount);
    }

    $expected_price = $page->find('css', '.spc-sidebar .spc-order-summary-block .hero-total .value .price .price-amount')->getText();
    $expected_price = floatval(str_replace(',', '', $expected_price));

    if ($expected_price !== $double_price) {
      throw new \Exception('Price did not get updated after adding the quantity');
    }
  }

  /**
   * @Then I should see a :element element on page
   */
  public function iShouldSeeAElementOnPage($element)
  {
    $this->assertSession()->elementExists('css', $element);
  }

  /**
   * @Then the price and currency matches the content of product having promotional code set as :cart_promotional
   */
  public function iPriceCurrencyMatches($cart_promotional = NULL)
  {
    $page = $this->getSession()->getPage();
    if ($page->find('css', '#block-content .acq-content-product div.content__title_wrapper .price-block .has--special--price')) {
      $product_price = $page->find('css', '#block-content .acq-content-product div.content__title_wrapper .price-block .special--price .price .price-amount')->getHtml();
      $product_price = floatval($product_price);
      $product_currency = $page->find('css', '#block-content .acq-content-product .content__title_wrapper .special--price .price .price-currency')->getHtml();
    } else {
      $product_price = $page->find('css', '.acq-mini-cart .price .price-amount')->getHtml();
      $product_price = floatval($product_price);
      $product_currency = $page->find('css', '.acq-mini-cart .price .price-currency')->getHtml();
    }
    $cart_price = $page->find('css', '#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount')->getHtml();
    $cart_price = floatval($cart_price);
    $cart_currency = $page->find('css', '#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency')->getHtml();
    if (!empty($cart_promotional)) {
      $message = ($product_price >= $cart_price) ? '' : 'Correct price did not added get in minicart, expected cart price %d';
      if (!empty($message)) {
        throw new \Exception(sprintf($message, $product_price));
      }
    } else {
      if ($product_price !== $cart_price) {
        throw new \Exception(sprintf('Correct price did not added get in minicart, expected cart price %d', $product_price));
      }
    }
    if ($product_currency !== $cart_currency) {
      throw new \Exception(sprintf('Correct currency did not added get in minicart, expected cart price %d', $product_price));
    }
  }

  /**
   * Asserts that an element, specified by CSS selector, exists.
   *
   * @param string $selector
   *   The CSS selector to search for.
   *
   * @Then the element :selector should exist
   */
  public function theElementShouldExist($selector)
  {
    $this->assertSession()->elementExists('css', $selector);
  }

  /**
   * Checks, that current page PATH is equal to specified
   * Example: Then url should contain "/" page
   * Example: And I should be on "/bats" page
   * Example: And I should be on "http://google.com" page
   * @Then /^(?:|I )should be on "(?P<page>[^"]+)" page$/
   */
  public function assertPageLocate($path)
  {
    $parts = parse_url($path);
    $fragment = empty($parts['fragment']) ? '' : '#' . $parts['fragment'];
    $path = empty($parts['path']) ? '/' : $parts['path'];

    $expected = preg_replace('/^\/[^\.\/]+\.php\//', '/', $path) . $fragment;
    $actual = $this->getSession()->getCurrentUrl();

    if (!str_contains($actual, $expected)) {
      throw new \Exception(sprintf('Current page is "%s", but "%s" expected.', $actual, $expected));
    }
  }

  /**
   * @When I select :option from the dropdown filter :css
   */
  public function iSelectOptionFromTheFilters($option, $filter)
  {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', "$filter");
    if (!empty($element)) {
      $element->click();
      $this->getSession()->executeScript('var id = jQuery("label:contains(\'' . $option . '\')").attr(\'for\'); document.getElementById(id).click();');
      return;
    }
    throw new \Exception(sprintf('Element %s is not found on page.', $filter));
  }

  /**
   * @Then I click jQuery :element element on page
   */
  public function iClickJqueryElementOnPage($element)
  {
    $element = addslashes($element);
    $this->getSession()->executeScript("document.querySelector('$element').click();");
  }

  /**
   * Fills in form fields with provided table
   * Example: When fill in billing address with following:
   *              | username | bruceWayne |
   *              | password | iLoveBats123 |
   *
   * @When /^(?:|I )fill in billing address with following:$/
   */
  public function fillBillingFields(TableNode $fields)
  {
    foreach ($fields->getRowsHash() as $field => $value) {
      if ($value) {
        if ($field == "spc-area-select-selected-city" || $field == "spc-area-select-selected") {
          $this->iClickJqueryElementOnPage(".spc-address-form-content .spc-address-add .delivery-address-fields #$field");
          $this->iWaitSeconds(5);
          $this->iClickJqueryElementOnPage(".spc-address-add .filter-list .spc-filter-area-panel-list-wrapper ul li span:contains($value)");
          // Adding a wait time for elements to load properly
          $this->iWaitSeconds(5);
        } else {
          $this->getSession()->getPage()->fillField($field, $value);
        }
      }
    }
  }


  /**
   * @When /^I select "([^"]*)" from the dropdown "([^"]*)"$/
   */
  public function iSelectTheValueFromTheDropdown($value, $dropdown)
  {
    $xpath = null;
    if (!empty($value)) {
      $driver = $this->getSession()->getDriver();
      $page = $this->getSession()->getPage();

      $highlightedClass = '.select2-results__option--highlighted';
      $highlightedSelector = '.select2-results__options ' . $highlightedClass;
      $selectableSelector = '.select2-results__options .select2-results__option';
      $searchSelector = '.select2-container.select2-container--open .select2-search__field';
      $page->find('css', '.select2-selection__arrow')->click();

      $element = $page->find('css', $searchSelector);
      if ($element) {
        $element->setValue($value);
        $this->iWaitSeconds(2);
        $xpath = $element->getXpath();
      } else {
        echo 'Element not found';
      }
      $prefix = str_replace('\\"', '"', $value);
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
  }

  /**
   * Fills in Select2 field with specified and wait for results
   *
   * @When I fill in select2 input :field with the :value and wait :time seconds until results are loaded
   * @When I fill in select2 :field with :value and wait :time seconds until results are loaded
   * @When I fill in select2 :value for :field and wait :time seconds until results are loaded
   */
  public function fillInSelectInputWithAndSelect1($field, $value, $time)
  {
    $page = $this->getSession()->getPage();

    $inputField = $page->findField($field);

    if (!$inputField) {
      throw new \Exception('No field found');
    }

    $choice = $inputField->getParent()->find('css', '.select2-selection');
    if (!$choice) {
      throw new \Exception('No select2 choice found');
    }

    $choice->press();

    $select2Input = $choice->find('css', '.select2-search__field');
    if (!$select2Input) {
      throw new \Exception('No input found');
    }

    $select2Input->setValue($value);

    $this->getSession()->wait($time * 1000);

    $chosenResults = $page->findAll('css', '.select2-results li');
    foreach ($chosenResults as $result) {
      if ($result->getText() == $value) {
        $result->click();
        break;
      }
    }
    $this->getSession()->wait(1000);
  }

  /**
   * Checks, that page doesn't contain specified text
   * Example: Then I should not see "Batman is Bruce Wayne" on page
   * Example: And I should not see "Batman is Bruce Wayne" on page
   *
   * @Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)" on page$/
   */
  public function assertTextNotInPaget($text)
  {
    if (!empty($text)) {
      $actual = $this->getSession()->getPage()->getText();
      $actual = preg_replace('/\s+/u', ' ', $actual);
      $regex = '/' . preg_quote($text, '/') . '/ui';
      $message = sprintf('The text "%s" appears in the text of this page, but it should not.', $text);

      if (!preg_match($regex, $actual)) {
        return;
      }

      throw new \Exception($message);
    }
  }

  /**
   * Asserts that an element, specified by CSS selector, doesn't exists.
   *
   * @param string $selector
   *   The CSS selector to search for.
   *
   * @Then the element :selector should not exist
   */
  public function theElementShouldNotExist($selector)
  {
    $this->assertSession()->elementNotExists('css', $selector);
  }

  /**
   * Selects option in select field with specified id|name|label|value
   * Example: When I select "Bats" from "user_fears" address
   * Example: And I select "Bats" from "user_fears" address
   *
   * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" address$/
   */
  public function selectOptionAddress($select, $option)
  {
    if ($option) {
      $this->getSession()->getPage()->selectFieldOption($select, $option);
    }
  }

  /**
   * Returns the id.
   *
   * @param string $selector
   *   The CSS ID to search for.
   */
  public function theElementSimilar($id)
  {
    $element = $this->getSession()
      ->getPage()
      ->find('xpath', "//*[contains(@id,'" . $id . "')]");
    if ($element) {
      return $element->getAttribute('id');
    } else {
      throw new ElementNotFoundException(sprintf('Could not evaluate CSS selector: "%s"', $id));
    }
  }

  /**
   * @When I fill in area billing address with id similar to :field with :value
   */
  public function fillBillingFieldsSimilarTo($field, $value)
  {
    if (empty($value)) {
      return;
    }
    $field = $this->theElementSimilar($field);
    $this->iClickJqueryElementOnPage(".spc-address-form-content .spc-address-add .delivery-address-fields #$field");
    $this->iWaitSeconds(5);
    $this->iClickJqueryElementOnPage(".spc-address-add .filter-list .spc-filter-area-panel-list-wrapper ul li span:contains($value)");
    $this->iWaitSeconds(5);
  }

  /**
   * Fills in form fields with provided table
   * Example: When I add in the billing address with following:
   *              | username | bruceWayne |
   *              | password | iLoveBats123 |
   *
   * @When I add in the billing address with following:
   */
  public function addBillingFields(TableNode $fields)
  {
    $page = $this->getSession()->getPage();
    // Check if no billing add exists
    $element = $this->getSession()->getPage()->find("css", "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text");
    if ($element) {
      $element->click();
      $this->iWaitSeconds(10);
      $this->iWaitForThePageToLoad();
      if ($this->getSession()->getPage()->find('css', 'div.spc-address-list-member-overlay div.address-list-content div.spc-checkout-address-list')) {
        $address = $this->getSession()->getPage()->find('css', 'div.spc-address-list-member-overlay div.address-list-content div.spc-checkout-address-list div.spc-address-tile .spc-address-tile-actions .spc-address-select-address');
        $address->press();
        $this->iWaitSeconds(20);
      } else {
        foreach ($fields->getRowsHash() as $field => $value) {
          if ($value) {
            if ($field == "spc-area-select-selected-city" || $field == "spc-area-select-selected") {
              $this->iClickJqueryElementOnPage(".spc-address-form-content .spc-address-add .delivery-address-fields #$field");
              $this->iWaitSeconds(5);
              $this->iClickJqueryElementOnPage(".spc-address-add .filter-list .spc-filter-area-panel-list-wrapper ul li span:contains($value)");
              $this->iWaitSeconds(5);
            } else {
              $this->getSession()->getPage()->fillField($field, $value);
            }
          }
        }
        $this->iClickJqueryElementOnPage("#address-form-action #save-address");
      }
    } else {
      $element = $this->getSession()->getPage()->find("css", "#spc-checkout .spc-main .spc-content .delivery-information-preview");
      if (!$element) {
        throw new Exception("Element " . "#spc-checkout .spc-main .spc-content .delivery-information-preview" . " not found on " . $this->getSession()->getCurrentUrl());
      }
    }
  }

  /**
   * Fills in form fields with provided table
   * Example: Then I add the store details with::
   *              | username | bruceWayne |
   *              | password | iLoveBats123 |
   *
   * @Then I add the store details with:
   */
  public function addStoreFields(TableNode $fields)
  {
    $page = $this->getSession()->getPage();
    // Check if no billing add exists
    $element = $this->getSession()->getPage()->find("css", "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text");
    if ($element) {
      $element->click();
      $this->iWaitSeconds(10);
      $this->iWaitForThePageToLoad();
      foreach ($fields->getRowsHash() as $field => $value) {
        if ($value) {
          if ($field == "edit-store-location") {
            $this->iSelectFirstAutocomplete($value, $field);
            $this->iWaitSeconds(10);
            $this->iWaitForThePageToLoad();
            $this->iClickJqueryElementOnPage(".popup-overlay  #click-and-collect-list-view li[data-index=0] .spc-store-name-wrapper");
            $this->iWaitSeconds(10);
            $this->iWaitForThePageToLoad();
            $this->iClickJqueryElementOnPage(".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button");
            $this->iWaitSeconds(10);
          } else {
            $this->getSession()->getPage()->fillField($field, $value);
          }
        }
      }
      $this->iClickJqueryElementOnPage(".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address");
    } else {
      $element = $this->getSession()->getPage()->find("css", "#spc-checkout .spc-main .spc-content .delivery-information-preview");
      if (!$element) {
        throw new Exception("Element " . "#spc-checkout .spc-main .spc-content .delivery-information-preview" . " not found on " . $this->getSession()->getCurrentUrl());
      }
    }
  }

  /**
   * Fills in form fields with provided table
   * Example: When I add in the CnC billing address with following:
   *              | username | bruceWayne |
   *              | password | iLoveBats123 |
   *
   * @When I add CnC billing address with following:
   */
  public function addCnCBillingFields(TableNode $fields)
  {
    // Check if no billing add exists
    $element = $this->getSession()->getPage()->find("css", "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel");
    if ($element) {
      $element->click();
      $this->iWaitSeconds(10);
      $this->iWaitForThePageToLoad();
      foreach ($fields->getRowsHash() as $field => $value) {
        if ($value) {
          if ($field == "spc-area-select-selected-city" || $field == "spc-area-select-selected") {
            $this->iClickJqueryElementOnPage(".spc-address-form-content .spc-address-add .delivery-address-fields #$field");
            $this->iWaitSeconds(5);
            $this->iClickJqueryElementOnPage(".spc-address-add .filter-list .spc-filter-area-panel-list-wrapper ul li span:contains($value)");
            $this->iWaitSeconds(5);
          } else {
            $this->getSession()->getPage()->fillField($field, $value);
          }
        }
      }
      $this->iClickJqueryElementOnPage("#address-form-action #save-address");
    } else {
      $element = $this->getSession()->getPage()->find("css", "#spc-checkout .spc-main .spc-content .spc-billing-bottom-panel .spc-billing-information");
      if (!$element) {
        throw new Exception("Existing Billing address not attached on the page " . $this->getSession()->getCurrentUrl());
      }
    }
  }

  /**
   * Clicks link with specified id|class
   * Example: When I click the element "Log In" on page
   *
   * @When I click the element :locator having text :text on page$/
   */
  public function iClickElementWithTextOnPage($locator, $text)
  {
    $element = $this->getSession()->getPage()->find('css', $locator);
    if (!empty($element)) {
      $this->getSession()->executeScript("jQuery('$locator:contains('$text')').trigger('click');");
    } else {
      throw new \Exception(sprintf('Element %s is not found on page.', $element));
    }
  }

  /**
   * @Given the element :arg1 having attribute :arg2 should contain :arg3
   */
  public function inOfElementShouldContain($selector, $attribute, $contains)
  {
    $element = $this->getSession()->getPage()->find('css', $selector);
    $Selectelement = $element->getAttribute($attribute);
    if (!str_contains($Selectelement, $contains)) {
      throw new \Exception(sprintf('Element %s does not contain attribute %s with %s.', $selector, $attribute, $contains));
    }
  }

  /**
   * @Then I fill in :selector with date :value
   * @param $selector
   * @param $value
   */
  public function iFillInDateWith($selector, $value)
  {
    $field = $this->getSession()->getPage()->findField($selector);
    $field->setValue($value);
  }

  /**
   * @Then /^I check the address-book form$/
   */
  public function iCheckTheAddressBookForm()
  {
    $page = $this->getSession()->getPage();
    $address_form = $page->find('css', 'form#profile-address-book-add-form');
    if ($address_form == NULL) {
      $page->find('css', '#block-content > a')->click();
      $this->getSession()->wait(5000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
    }
  }

  /**
   * @Given /^I double click on "([^"]*)" element$/
   */
  public function iDoubleClickOnElement($selector)
  {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', $selector);
    if (!empty($element)) {
      $element->click();
      $element->click();
    } else {
      throw new \Exception ('Element %s not found');
    }
  }

  /**
   * @Given /^I select a product in stock on "([^"]*)"$/
   */
  public function iSelectAProductInStockOn($css)
  {
    $page = $this->getSession()->getPage();
    $algoliaenabled = $page->find('css', '#alshaya-algolia-plp');
    if (!empty($algoliaenabled)) {
      $all_products = $page->find('css', '#plp-hits');
    }
    else {
      $all_products = $page->find('css', $css);
    }
    if (!empty($all_products)) {
      $all_products = $all_products->findAll('css', '.c-products__item');
      $total_products = count($all_products);
    } else {
      throw new \Exception('Search passed, but search results were empty');
    }
    foreach ($all_products as $item) {
      if ($item->find('css', 'div.out-of-stock span')) {
        $total_products--;
        if (!$total_products) {
          throw new \Exception('All products are out of stock');
        }
        continue;
      }
      $this->getSession()->executeScript("jQuery('h2.field--name-name a').get(0).click();");
      break;
    }
  }

  /**
   * @Then /^the promo code should be applied$/
   */
  public function thePromoCodeShouldBeApplied()
  {
    $js = <<<JS
    return jQuery('input#promo-code').val();
JS;
    $page = $this->getSession()->getPage();
    $val = $this->getSession()->evaluateScript($js);
    if ($val == '') {
      throw new \Exception('Promo-code not applied');
    }
  }

  /**
   * @When I press :addCart button
   * @param $addCart
   */
  public function pressAddCartButton($addCart) {
    $page = $this->getSession()->getPage();
    $algoliaenabled = $page->find('css', '#add-to-cart-main');
    if (!empty($algoliaenabled)) {
      $addCartButton = 'add-to-cart-main';
    }
    else {
      $addCartButton = $addCart;
    }
    $button = $page->find('named', ['button', $addCartButton]);
    if (null === $button) {
      throw new ElementNotFoundException($this->getDriver(), 'button', 'id|name|title|alt|value', $addCartButton);
    }
    $button->press();
  }

  /**
   * @Then I fill in Qpay pin code
   */
  public function iFillinQpayPin()
  {
    $this->getSession()->executeScript("jQuery('#pinField').val('123456');");
  }

  /**
   * @Then I should see special price on newpdp having promotion :promotion
   */
  public function iShouldSeeSpecialPrice($promotion)
  {
    if (!empty($promotion)) {
      $this->theElementShouldExist(".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container .magv2-pdp-final-price-wrapper .magv2-pdp-final-price-currency");
      $this->theElementShouldExist(".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container .magv2-pdp-final-price-wrapper .magv2-pdp-final-price-amount");
    }
  }

  /**
   * @Then I select the Knet payment method
   */
  public function iSelectKnetPaymentMethod()
  {
    $page = $this->getSession()->getPage();
    $newCheckoutKnet = $page->find('css', '#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_knet');
    if (!empty($newCheckoutKnet)) {
      $element = '#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_knet';
    } else {
      $element = '#payment-method-knet';
    }
    $this->getSession()->executeScript("jQuery('$element').trigger('click');");
    $this->iWaitSeconds(10);
    $checkbox = $page->findField('#' . $element);
    if ($checkbox !== null) {
      if (!$checkbox->isChecked()) {
        throw new \Exception(sprintf('Knet Payment method has not be checked on page.'));
      }
    }
  }

  /**
   * @Then I select the Checkout payment method
   */
  public function iSelectCheckoutPaymentMethod()
  {
    $page = $this->getSession()->getPage();
    $newCheckout = $page->find('css', '#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi');
    if (!empty($newCheckout)) {
      $element = '#payment-method-checkout_com_upapi';
    } else {
      $element = '#payment-method-checkout_com';
    }
    $this->getSession()->executeScript("jQuery('$element').siblings('.payment-method-label-wrapper').find('label').trigger('click');");
    $this->iWaitSeconds(10);
    $checkbox = $page->findField($element);

    if ($checkbox !== null) {
      if (!$checkbox->isChecked()) {
        throw new \Exception(sprintf('Checkout Payment method has not be checked on page.'));
      }
    }
  }

  /**
   * @Then I fill checkout card details having class :class with :value
   */
  public function iFillCheckoutCardDetailsHavingClassWith($class, $value) {
    $page = $this->getSession()->getPage();
    $newCheckout = $page->find('css', '#payment-method-checkout_com_upapi');
    if (!empty($newCheckout)) {
      $element = '.payment-method-checkout_com_upapi';
    } else {
      $element = '.payment-method-checkout_com';
    }
    $card = $page->find('css', $element . ' ' . $class);
    if ($card !== null) {
      $card->setValue($value);
    } else {
      echo 'Element not found';
    }
  }

  /**
   * @Then the checkout payment checkbox should be checked
   */
  public function checkoutPaymentMethodSelected () {
    $page = $this->getSession()->getPage();
    $checkoutField = $page->find('css', '#payment-method-checkout_com_upapi');
    if (!empty($checkoutField)) {
      $checkoutField->isChecked();
    } else {
      $checkoutField = $page->find('css', '#payment-method-checkout_com');
      $checkoutField->isChecked();
    }
  }

  /**
   * @Then the Knet payment checkbox should be checked
   */
  public function checkoutKNETPaymentMethodSelected() {
    $page = $this->getSession()->getPage();
    $checkoutField = $page->find('css', '#payment-method-checkout_com_upapi_knet');
    if (!empty($checkoutField)) {
      $checkoutField->isChecked();
    } else {
      $checkoutField = $page->find('css', '#payment-method-knet');
      $checkoutField->isChecked();
    }
  }

  /**
   * @Given /^the product quantity should be "([^"]*)"$/
   */
  public function theQuantityShouldBe($value) {
    $page = $this->getSession()->getPage();
    $qty_before_click = $page->find('css', '.c-products__item:first-child .qty-text-wrapper .qty')->getText();
    if ($value == 'increased') {
      $this->getSession()->executeScript("jQuery('.qty-sel-btn--up').click()");
      $this->iWaitForAjaxToFinish();
      $this->iWaitSeconds('20');
      $qty_after_click = $page->find('css', '.c-products__item:first-child .qty-text-wrapper .qty')->getText();
      if ($qty_after_click != $qty_before_click + 1) {
        $script = <<<JS
            return jQuery('#cart_notification .notification.error-notification').text();
JS;
        $error_msg = $this->getSession()->evaluateScript($script);
        if ($error_msg != 'The product that was requested doesn\'t exist. Verify the product and try again.') {
          throw new \Exception(sprintf('Quantity doesn\'t match'));
        }
      }
    }
    else {
      $this->getSession()->executeScript("jQuery('.qty-sel-btn--down').click()");
      $this->iWaitForAjaxToFinish();
      $this->iWaitSeconds('20');
      $qty_after_click = $page->find('css', '.c-products__item:first-child .qty-text-wrapper .qty')->getText();
      if ($qty_after_click != $qty_before_click - 1) {
        throw new \Exception(sprintf('Quantity doesn\'t match'));
      }
    }
  }

  /**
   * Wait for AJAX to finish.
   */
  public function iWaitForAjaxToFinish() {
    $this->getSession()->wait(80000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
  }

  /**
   * @Given /^I select the collection store$/
   */
  public function iSelectTheCollectionStore()
  {
    $page = $this->getSession()->getPage();
    $empty_delivery_info = $page->find('css', '.spc-empty-delivery-information');
    if ($empty_delivery_info !== null) {
      $empty_delivery_info->click();
      $this->iWaitForAjaxToFinish();
      $this->iWaitForElement('.spc-cnc-stores-list-map');
      $page->find('css', '#click-and-collect-list-view li.select-store:first-child .spc-store-name-wrapper')->click();
      $this->iWaitForAjaxToFinish();
      $page->find('css', 'button.select-store')->click();
      $this->iWaitForElement('#click-and-collect-selected-store');
      $script = <<<JS
        jQuery("input#fullname").val("Test User");
        var maxlength = jQuery("input[name=\"mobile\"]").attr('maxlength');
        var value = "55667788";
        if (maxlength == 9) {
            value = value + "9";
        }
        else if (maxlength == 10) {
            value = 1255557111;
        }
        jQuery("input[name=\"mobile\"]").val(value);

JS;
      $this->getSession()->executeScript($script);
      if ($page->find('css', 'input[name="email"]')) {
        $this->getSession()->executeScript('jQuery("input[name=\"email\"]").val("user@test.com")');
      }
      $page->find('css', 'button#save-address')->click();
      $this->iWaitForAjaxToFinish();
      $this->iWaitForElement('.delivery-information-preview');
    }
    $this->theElementShouldExist('.delivery-information-preview');
  }

  /**
   * @Given /^I select "([^"]*)" option from "([^"]*)"$/
   */
  public function iSelectOptionFrom($select, $field_name) {
    if ($field_name) {
      $val = $this->getSession()->evaluateScript("return document.querySelector('select[name=\"{$field_name}\"] option:nth-child(2)').value");
      $this->selectOptionAddress($field_name, $val);
    }
  }

  /**
   * @Then /^I select the home delivery address$/
   */
  public function iSelectTheHomeDeliveryAddress() {
    $session = $this->getSession();
    $page = $session->getPage();
    $empty_delivery_info = $page->find('css', '.spc-empty-delivery-information');
    if ($empty_delivery_info !== null) {
      $empty_delivery_info->click();
      $this->iWaitForAjaxToFinish();
      $this->iWaitForElement('.spc-address-form-sidebar');
      $this->iWaitSeconds('20');
      if ($page->find('css', 'header.spc-change-address') !== null) {
        if ($page->find('css', 'div.spc-address-tile:first-child button')) {
          $page->find('css', 'div.spc-address-tile:first-child button')->click();
          $this->iWaitForAjaxToFinish();
          $this->iWaitSeconds('20');
        }
      } else {
        $this->fillFormAndSubmit($session, $page);
      }
    }
    $this->theElementShouldExist('.delivery-information-preview');
  }

  /**
   * @Given /^the cart quantity should be "([^"]*)"$/
   */
  public function theCartQuantityShouldBe($value){
    $page = $this->getSession()->getPage();
    $qty_before_click = $page->find('css', '#mini-cart-wrapper .cart-link .quantity')->getText();
    if ($value == 'increased') {
      $this->getSession()->executeScript("jQuery('.qty-sel-btn--up').click()");
      $this->iWaitForAjaxToFinish();
      $this->iWaitSeconds('20');
      $qty_after_click = $page->find('css', '#mini-cart-wrapper .cart-link .quantity')->getText();
      if ($qty_after_click != $qty_before_click + 1) {
        $this->iWaitSeconds('10');
        $script = <<<JS
            return jQuery('#cart_notification .notification.error-notification').text();
JS;
        $error_msg = $this->getSession()->evaluateScript($script);
        if ($error_msg != 'The product that was requested doesn\'t exist. Verify the product and try again.') {
          throw new \Exception(sprintf('Quantity doesn\'t match'));
        }
      }
    }
    else {

      $this->getSession()->executeScript("jQuery('.qty-sel-btn--down').click()");
      $this->iWaitForAjaxToFinish();
      $this->iWaitSeconds('20');
      $qty_after_click = $page->find('css', '#mini-cart-wrapper .cart-link .quantity')->getText();
      if ($qty_after_click != $qty_before_click - 1) {
        throw new \Exception(sprintf('Quantity doesn\'t match'));
      }
    }
  }

  /**
   * @Given /^I click on Add-to-cart button$/
   */
  public function iClickOnAddToCartButton() {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', '#add-to-cart-main');
    $element2 = $page->find('css', "[id^='edit-add-to-cart-']");
    $element3 = $page->find('css', "[id^='addtobag-button-']");
    if ($element !== NULL) {
      $element->click();
    }
    elseif ($element2 !== NULL) {
      $element2->click();
    }
    elseif ($element3 !== NULL) {
      $element3->click();
    }
    else {
      throw new \Exception(sprintf('Add to cart button not found.'));
    }
  }

  /**
   * @Given /^I navigate to the copied URL$/
   */
  public function iNavigateUrl() {
    $this->pageurl = $this->getSession()->getCurrentUrl();
  }

  /**
   * @Then /^ Get element by css$/
   */
  public function getElementByCss($selector)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $element = $page->find('css', $selector);
    return $element;
  }

  /**
   * @Then /^I should see an iframe window$/
   */
  public function iShouldSeeAnIframeWindow()
  {
    $this->switchToIFrame();
    $this->iWaitForAjaxToFinish();
    $this->iWaitSeconds('20');
    $this->getSession()->getPage()->find('css', 'body > div:nth-child(4) > button')->click();
    $this->iWaitSeconds('30');
    $this->getSession()->getDriver()->switchToIFrame(null);
  }


  /**
   * @Then I switch To IFrame$/
   */
  public function switchToIFrame(){
    $function = <<<JS
            (function(){
                 var iframe = document.querySelector("div.postpay-iframe-container .postpay-iframe");
                 iframe.name = "iframeToSwitchTo";
            })()
JS;
    try{
      $this->getSession()->executeScript($function);
    }catch (Exception $e){
      print_r($e->getMessage());
      throw new \Exception("Element was NOT found.".PHP_EOL . $e->getMessage());
    }
    $this->getSession()->getDriver()->switchToIFrame("iframeToSwitchTo");
  }

  /**
   * @Given /^I click on the checkout button$/
   */
  public function iClickOnTheCheckoutButton1()
  {
    $checkoutButton = $this->getElementByCss('#spc-checkout .spc-content .checkout-link');
    $checkoutButton->click();
    $this->iWaitSeconds('30');
  }

  /**
   * @Given /^I select date and month in the form$/
   */
  public function iSelectDateAndMonthInTheForm()
  {
    $page = $this->getSession()->getPage();
    $knet_expiration = $page->find('css', '#cardExpdate .col:nth-child(2) select:first-child');
    if ($knet_expiration != null) {
      $this->getSession()->executeScript("jQuery('#debitMonthSelect').val(9)");
      $this->getSession()->executeScript("jQuery('#debitYearSelect').val(2025)");
      $this->iWaitSeconds('20');
    }
    else {
      throw new \Exception(sprintf('Month-year not found.'));
    }
  }

  /**
   * @Given /^I select date and month in the form for arabic$/
   */
  public function iSelectDateAndMonthInTheFormForArabic()
  {
    $page = $this->getSession()->getPage();
    $knet_expiration = $page->find('css', '#cardExpdate .col:nth-child(1) select:first-child');
    if ($knet_expiration != null) {
      $this->getSession()->executeScript("jQuery('#debitYearSelect').val(2021)");
      $this->getSession()->executeScript("jQuery('#debitMonthSelect').val(9)");
      $this->iWaitSeconds('20');
    }
    else {
      throw new \Exception(sprintf('Month-year not found.'));
    }
  }

  public function getRandomString($length) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randstring = '';
    for ($i = 0; $i < $length; $i++) {
      $randstring .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $randstring;
  }

  /**
   * @Given /^I fill in "([^"]*)"$/
   */
  public function iFillIn($name) {
    $random_string = $this->getRandomString(5);
    $this->getSession()->executeScript('jQuery(\'input[name="' . $name . '"]\').val("' . $random_string . '")');
  }

  /**
   * @Then /^I should save the order details in the file$/
   */
  public function iShouldSaveTheOrderDetailsInTheFile() {
    $session = $this->getSession();
    $email_id = $session->evaluateScript('return jQuery(\'.spc-main\').first().find(\'.spc-order-summary-order-preview .spc-value\').eq(0).text()');
    $order_id = $session->evaluateScript('return jQuery(\'.spc-main\').first().find(\'.spc-order-summary-order-preview .spc-value\').eq(1).text()');
    $payment_method = $session->evaluateScript('return getPaymentMethod(); function getPaymentMethod() {var value=null; window.dataLayer.some(function eachObject(item) {if (item.event === \'purchaseSuccess\') {value=item.paymentOption; return true;} return false;}); return value;}');
    $delivery_option = $session->evaluateScript('return getDeliveryMethod(); function getDeliveryMethod() {var value=null; window.dataLayer.some(function eachObject(item) {if (item.event === \'purchaseSuccess\') {value=item.deliveryOption; return true;} return false;}); return value;}');
    $order_detail = [
      'email' => $email_id,
      'order_id' => $order_id,
      'order_date' => date('Y-m-d'),
      'payment_method' => $payment_method,
      'delivery_option' => $delivery_option,
    ];
    $filename = 'order_details.json';
    $orders = [];
    if (file_exists($filename)) {
      $orders = (array) json_decode(file_get_contents($filename), null);
    }
    array_push($orders, $order_detail);
    file_put_contents($filename, json_encode($orders, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  }

  /**
   * @Given /^I apply the "([^"]*)" delivery filter$/
   */
  public function iApplyTheDeliveryFilter($delivery_type) {
    $page = $this->getSession()->getPage();
    $page->find('css', '.plp-facet-product-filter .show-all-filters-algolia')->click();
    $this->iWaitSeconds('2');
    $delivery_type_wrapper = $page->find('css', '.block-alshaya-algolia-plp-facets-block-all .filter__inner #attr_delivery_ways');
    if (!empty($delivery_type_wrapper)) {
      $delivery_type_wrapper->click();
      if ($delivery_type == 'Express') {
        $delivery_type_wrapper->find('css', 'ul li.express_delivery')->click();
      }
      else {
        $delivery_type_wrapper->find('css', 'ul li.same_day_delivery')->click();
      }
    }
    else {
      $page->find('css', '.block-alshaya-algolia-plp-facets-block-all .all-filters-close')->click();
      $page->find('css', '#block-alshaya-algolia-react-plp .plp-facet-product-filter #attr_delivery_ways h3')->click();
      if ($delivery_type == 'Express') {
        $page->find('css', '#block-alshaya-algolia-react-plp .plp-facet-product-filter #attr_delivery_ways ul li.express_delivery')->click();
      } else {
        $page->find('css', '#block-alshaya-algolia-react-plp .plp-facet-product-filter #attr_delivery_ways ul li.same_day_delivery')->click();
      }
      $page->find('css', '#block-alshaya-algolia-react-plp .plp-facet-product-filter #attr_delivery_ways h3')->click();
    }
  }

  /**
   * @Then /^I should see tabby payment window$/
   */
  public function iShouldSeeTabbyPaymentWindow() {
    $page = $this->getSession()->getPage();
    $iframe_element = $page->find('css', '#tabby-checkout iframe');
    if ($iframe_element == null) {
      throw new \Exception(sprintf('Iframe element not found.'));
    }

  }

  /**
   * @Given /^I add the billing address on checkout page$/
   */
  public function iAddTheBillingAddressOnCheckoutPage() {
    $session = $this->getSession();
    $page = $session->getPage();
    $top_panel = $page->find('css', '.spc-billing-top-panel');
    if ($top_panel != null) {
      $text = $session->evaluateScript('return jQuery(".spc-billing-top-panel").text()');
      if ($text == 'please add your billing address.') {
        $session->executeScript('jQuery(\'.spc-billing-top-panel\').click()');
        $this->iWaitSeconds('5');
        $this->fillFormAndSubmit($session, $page, TRUE);
      }
    }
  }

  /**
   * Fills billing information form and submit.
   * @param $session
   * @param $page
   * @param $billing_address
   */
  public function fillFormAndSubmit($session, $page, $billing_address = FALSE) {
    $script = <<<JS
        jQuery(".spc-address-form-guest-overlay input#fullname").val("Test User");
        jQuery(".spc-address-form-guest-overlay input[name=\"email\"]").val("user@test.com");
        var maxlength = jQuery("input[name=\"mobile\"]").attr('maxlength');
        var country_code = jQuery(".country-code").text();
        var value = "55667788";
        if (maxlength == 9) {
            value = value + "9";
        }
        if (country_code == '+20') {
            value = "1255557111";
        }
        jQuery("input[name=\"mobile\"]").val(value);
        jQuery(".spc-address-form-guest-overlay input#locality").val("Block 1");
        jQuery(".spc-address-form-guest-overlay input#address_line1").val("Street A");
        jQuery(".spc-address-form-guest-overlay input#dependent_locality").val("Building B");
        jQuery(".spc-address-form-guest-overlay input#address_line2").val("Floor C");
JS;
    $session->executeScript($script);
    $city = $page->find('css', '#spc-area-select-selected-city');
    if ($city !== null) {
      $city->click();
      $this->iWaitSeconds('5');
      $page->find('css', '.spc-filter-area-panel-list-wrapper ul li:first-child')->click();
      $area_value = $this->getSession()->evaluateScript('return jQuery(\'#spc-area-select-selected\').text()');
      if ($area_value == 'Select Area' or 'Choose a region') {
        $page->find('css', '#spc-area-select-selected')->click();
        $this->iWaitSeconds('5');
        $page->find('css', '.spc-filter-area-panel-list-wrapper ul li:first-child')->click();
      }
    }
    else {
      $page->find('css', '#spc-area-select-selected')->click();
      $this->iWaitSeconds('5');
      $page->find('css', '.spc-filter-area-panel-list-wrapper ul li:first-child')->click();
    }
    $page->find('css', 'button#save-address')->click();
    $this->iWaitForAjaxToFinish();
    $this->iWaitSeconds('20');
  }

  /**
   * @Given /^I should see the Wishlist icon$/
   */
  public function iShouldSeeTheWishlistIcon() {
    $wishlist_icon = $this->getSession()->evaluateScript('return jQuery(\'.wishlist-button-wrapper .wishlist-icon\').length');
    if ($wishlist_icon == 0) {
      throw new \RuntimeException('Unable to find wishlist icon.');
    }
  }

  /**
   * @When /^I click on the Wishlist icon$/
   */
  public function iClickOnTheWishlistIcon()
  {
    $session = $this->getSession();
    $session->executeScript('jQuery(\'.c-products__item.views-row:first-child .wishlist-button-wrapper .wishlist-icon\').first().click()');
    $this->iWaitSeconds('5');

    // Check if wishlist icon is active by checking the class "in-wishlist".
    $in_wishlist_wrapper = $session->evaluateScript('return jQuery(".in-wishlist.wishlist-button-wrapper").length');
    if ($in_wishlist_wrapper == 0) {
      throw new \RuntimeException('Unable to click on the wishlist icon.');
    }
  }

  /**
   * @Then /^I should see the Wishlist icon active$/
   */
  public function iShouldSeeTheWishlistIconActive() {
    // Check if wishlist icon on header is active by checking the class "wishlist-active".
    $wishlist_active = $this->getSession()->evaluateScript('return jQuery(".wishlist-header .wishlist-active").length');

    if ($wishlist_active == 0) {
      throw new \RuntimeException('Wishlist icon is inactive.');
    }
  }

  /**
   * @Given /^I create an account with "(?P<field>(?:[^"]|\\")*)" using custom password$/
   */
  public function iCreateAnAccountUsingCustomPassword($email) {
    $this->getSession()->getPage()->fillField('edit-mail', $email);
    $this->fillPassword($email);
  }

  /**
   * @Given /^I login with "(?P<field>(?:[^"]|\\")*)" using custom password$/
   * @Given /^I login with "(?P<field>(?:[^"]|\\")*)" using custom password with date "([^"]*)"$/
   */
  public function iLoginUsingCustomPassword($email, $date = '') {
    $this->getSession()->getPage()->fillField('edit-name', $email);
    $this->fillPassword($email, $date);
  }

  public function fillPassword($email, $date = '') {
    $secret_key = 'vGnZFpa8JA';
    $date = empty($date) ? date("Ymd") : $date;
    $password_key = $email . $date . $secret_key;
    $password = base64_encode($password_key);
    $this->getSession()->getPage()->fillField('edit-pass', $password . '%');
  }

  /**
   * @Given /^I uncheck the newsletter subscription checkbox$/
   */
  public function iUncheckTheNewsletterSubscriptionCheckbox() {
    $this->getSession()->executeScript("jQuery('#edit-field-subscribe-newsletter-value').removeAttr('checked');jQuery('#edit-field-subscribe-newsletter-value').val(0)");
  }

  /**
   * @Given /^I select "([^"]*)" from "([^"]*)" select2 field$/
   */
  public function iSelectFromSelect2field($value, $selector)
  {
    $this->iFillInWithUsingJQuery($selector, $value);
  }

  /**
   * @Given /^I verify the wishlist popup block if enabled and remove the cart item$/
   */
  public function iVerifyTheWishlistPopupBlock()
  {
    $page = $this->getSession()->getPage();
    $empty_cart = $page->find('css', '#spc-cart .spc-empty-text');

    if (!empty($empty_cart)) {
      throw new \Exception('Cart is empty!');
    }

    // Click the remove button.
    $page->find('css', '#spc-cart .spc-cart-items .spc-product-tile-actions .spc-remove-btn')->click();

    // If Wishlist is enabled we will get the popup.
    if ($this->getSession()->evaluateScript('return jQuery(".wishlist-enabled").length')) {
      $page = $this->getSession()->getPage();

      // For our tests, we don't want to move to wishlist.
      // Click the no button to just remove the item.
      $page->pressButton('wishlist-no');
    }
    $this->iWaitSeconds('5');
  }

  /**
   * @Given /^I wait for element "([^"]*)"$/
   */
  public function iWaitForElement($arg1)
  {
    $found = $this->waitForElement($arg1);
    if (!$found) {
      throw new \Exception("Element {$arg1} not found.");
    }
  }

  /**
   * Dynamically wait for an element to be available on page.
   */
  private function waitForElement($selector, $time = 60): bool
  {
    while ($time > 0) {
      if (!is_null($this->getSession()->getPage()->find('css', $selector))) {
        return true;
      } else {
        sleep(1);
        $time--;
      }
    }
    return false;
  }

  /**
   * @Given /^I fill in "([^"]*)" with "([^"]*)" using jQuery$/
   */
  public function iFillInWithUsingJQuery($selector, $value){
    $session = $this->getSession();
    $selector = addslashes($selector);
    $value = addslashes($value);
    $session->executeScript("jQuery('$selector').val('$value').trigger('change')");
  }

  /**
   *
   * @Given /^I edit the page$/
   */
  public function iEditPage() {
    $edit = $this->getSession()
      ->getPage()
      ->find('css', "#block-local-tasks ul li a[href$= 'edit']");
    $edit->click();
  }

  /**
   * Helper to enter values on React input fields.
   * It emulates a real user input and will make React update the states.
   *
   * @param string $selector
   *   The CSS selector.
   * @param string $value
   *   The input value.
   */
  private function enterReactInput($value)
  {
    $digits = str_split($value);
    $session = $this->getSession();
    for ($i=1; $i <= sizeof($digits); $i++) {
      $value = $digits[$i - 1];
      $locator = ".cod-mobile-otp__field:nth-child($i) input";
      $session->executeScript("let input = document.querySelector('$locator'); alshayaBehat.userEvent.type(input, '$value')");
    }
  }

  /**
   * @Given /^I enter a valid mobile otp$/
   */
  public function iEnterAValidMobileOtp()
  {
    $this->enterReactInput(1234);
  }

  /**
   * @Given /^I enter an invalid mobile otp$/
   */
  public function iEnterInValidMobileOtp()
  {
    $this->enterReactInput(4321);
  }

  /**
   * @Given /^the mobile OTP is verified$/
   */
  public function theMobileOTPIsVerified()
  {
    // Check if we have OTP fields on the page.
    $page = $this->getSession()->getPage();
    $hasOtpFields = $page->find('css', '.cod-mobile-otp__field');
    if ($hasOtpFields) {
      $this->iEnterAValidMobileOtp();
    }
    // Wait for the verified message.
    $this->iWaitForElement('.cod-mobile-otp__verified_message');
  }

}
