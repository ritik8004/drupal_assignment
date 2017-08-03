<?php

namespace Drupal;

use Drupal\Driver\Exception\Exception;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Context\Context;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements Context, SnippetAcceptingContext {

  private $product;
  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /** @BeforeScenario @javascript */
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
        if($create_account==null){
            throw new \Exception('Link to create an account is missing in the header');
        }

        $sign_in = $page->hasLink('Sign in');
        if($sign_in==null){
            throw new \Exception('Link to Sign in is missing in the header');
        }

        $find_store = $page->hasLink('Find Store');
        if($find_store==null){
            throw new \Exception('Link to find a store is missing in the header');
        }

        $language = $page->hasLink('عربية');
        if($language==null){
            throw new \Exception('Link to switch to Arabic language is missing in the header');
        }
    }

    /**
     * @Given /^I should be able to see the footer$/
     */
    public function iShouldBeAbleToSeeTheFooter() {
        $page = $this->getSession()->getPage();
        $footer_region_categories = $page->find('css','.footer--menu .footer--categories');
        $footer_region_categories->hasLink('Baby Clothing' and 'Toddler Clothing' and 'Maternity' and 'Bathing & Care' and 'Car Seats');
        $footer_region_categories = $footer_region_categories and $footer_region_categories->hasLink('Feeding' and 'Nursery & Bedroom' and 'Pushchairs');

        if($footer_region_categories==null){
            throw new \Exception ('Main menu is not being displayed in the footer');
        }

        $about_brand = $page->find('css','.footer--menu .footer--abouthelp');
        $about_brand->hasLink('Corporate information' and 'Delivery information' and 'Exchange & refund' and 'Terms and Conditions');
        if($about_brand==null){
            throw new \Exception('About brand section missing in the footer');
        }

        $help = $page->find('css','.footer--abouthelp');
        $help->hasLink('contact' and 'faq' and 'sitemap' and 'store');
        if($help==null){
            throw new \Exception('Help section missing in the footer');
        }

        $text = 'connect with us' and 'get email offers and the latest news from Mothercare Kuwait';
        $subscription = $page->find('css','.alshaya-newsletter-subscribe');
        $subscription->find('named_partial',array('content',$text));
        if($subscription==null){
            throw new \Exception('Text related to Subscription is missing in the footer');
        }

        $sub_button = $subscription->hasButton('sign up');
        if($sub_button==null){
            throw new \Exception('Sign up button to subscribe to newsletters is missing in the footer');
        }

        $copyright1 = '© Copyright Mothercare UK Limited 2016 Registered in England no. 533087, VAT Reg no 440 6445 66';
        $copyright1 = $copyright1 and 'Registered ofﬁce: Cherry Tree Road, Watford, Hertfordshire, WD24 6SH';
        $copyright = $page->find('css','.region__footer-secondary');
        $copyright->has('named',array('content',$copyright1));

        if($copyright==null){
            throw new \Exception('Copyright information is missing in the footer');
        }

        $payment = $page->find('css','.c-footer-secondary');
        $payment->hasLink('Mastercard' and 'Verision' and 'Visa');
        if($payment==null){
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
        $email_id = $randomString.'@gmail.com';
        $this->getSession()->getPage()->fillField("edit-email",$email_id);
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
        if($current_url == null){
            throw new \Exception('URL not found');
        } else {
            $expected_url = $base_url."/search?keywords=$arg1";
        }

        if($expected_url !== $current_url){
            throw new \Exception("Incorrect URL! Expected:$expected_url | Actual:$current_url ");
        }

    }

    /**
     * @Then /^I should be able to see the header in Arabic$/
     */
    public function iShouldBeAbleToSeeTheHeaderInArabic() {
        $page = $this->getSession()->getPage();
        $create_account = $page->hasLink('تسجيل مستخدم جديد');
        if($create_account==null){
            throw new \Exception('Link for creating account is missing in the header on Arabic site');
        }

        $sign_in = $page->hasLink('تسجيل الدخول');
        if($sign_in==null){
            throw new \Exception('Link to Sign in is missing in the header on Arabic site ');
        }

        $find_store = $page->hasLink('البحث عن محلاتنا');
        if($find_store==null){
            throw new \Exception('Link to find a store is missing in the header on Arabic site');
        }

        $language = $page->hasLink('English');
        if($language==null){
            throw new \Exception('Link to switch to English language is missing in the header');
        }
    }

    /**
     * @Given /^I should be able to see the footer in Arabic$/
     */
    public function iShouldBeAbleToSeeTheFooterInArabic() {
        $page = $this->getSession()->getPage();
        $footer_region_categories = $page->find('css','.footer--menu .footer--categories');
        $footer_region_categories->hasLink('ملابس الرضع' and 'الإستبدال و الر' and 'اتصل بنا');

        if($footer_region_categories==null){
            throw new \Exception ('Main menu is not being displayed in the footer');
        }

        $text = 'تواصل معنا' and 'مذركير الكويت احصل على أحدث العروض الحصرية عبر عنوان البريد الإكتروني';
        $subscription = $page->find('css','.alshaya-newsletter-subscribe');
        $subscription->find('named_partial',array('content',$text));
        if($subscription==null){
            throw new \Exception('Text related to Subscription is missing in the footer');
        }

        $sub_button = $subscription->hasButton('سجل الآن');
        if($sub_button==null){
            throw new \Exception('Sign up button to subscribe to newsletters is missing in the footer');
        }

        $copyright1 = '© حقوق النشر محفوظة لشركة مذركير المحدودة المملكة المتحدة 2015 | مذركير المحدودة المملكة المتحدة (شركة خاصة محدودة)';
        $copyright1 = $copyright1 and 'مسجلة في إنجلترا برقم 533087 . رقم تسجيل ضريبة القيمة المضافة 66 6445 440 ';
        $copyright1 = $copyright1 and 'مكتب التسجيل: شيري تري رود، واتفورد، هيرتفوردشاير، WD24 6SH';
        $copyright = $page->find('css','.region__footer-secondary');
        $copyright->has('named',array('content',$copyright1));

        if($copyright==null){
            throw new \Exception('Copyright information is missing in the footer');
        }

        $payment = $page->find('css','.c-footer-secondary');
        $payment->hasLink('Mastercard' and 'Verision' and 'Visa');
        if($payment==null){
            throw new \Exception('Payment links are missing in the footer');
        }
    }

  /**
   * @Given /^I see the header for checkout$/
   */
  public function iSeeTheHeaderForCheckout() {
    $page = $this->getSession()->getPage();
    $logo = $page->has('css','.logo') and $page->hasLink('Home');
    if(!$logo){
      throw new \Exception('Logo is not displayed on secure checkout page');
    }
    $text = $page->find('css','.secure__checkout--label')->getText();
    if($text !== 'Secure Checkout' ){
      throw new \Exception('Text Secure Checkout is not displayed');
    }
    $lock = $page->has('css','.icon-ic_login');
    if(!$lock){
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
      ->find('css','.option')
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
    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);
    // Wait for AJAX to finish.
    $this->getSession()->wait(500, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
    $all_results = $page->findById('click-and-collect-list-view');
    if( $all_results == null ){
      $message = $page->hasContent('Sorry, No store found for your location.');
      if(!$message){
        throw new \Exception('No stores message is not displayed');
      }
    }
  }

  /**
   * @Given /^I select an element having class "([^"]*)"$/
   */
  public function iSelectAnElementHavingClass($arg1) {
    $this->getSession()->getPage()->find('css',$arg1)->click();
  }

  /**
   * @Given /^I should see "([^"]*)" in the cart area$/
   */
  public function iShouldSeeInTheCartArea($arg1) {
    $price = $this->getSession()->getPage()->findById('block-cartminiblock')->find('css','.price')->hasLink($arg1);

    if(!$price){
      throw new \Exception('Product of the price is not displayed on the mini cart');
    }

  }

  /**
   * @Then /^I should see the number of stores displayed$/
   */
  public function iShouldSeeTheNumberOfStoresDisplayed() {
    $page = $this->getSession()->getPage();
    $all_stores = $page->findAll('css','#click-and-collect-list-view ul li');
    $count = count($all_stores);
    $value = $page->findById('edit-store-location')->getValue();
    $text = $page->hasContent("'Available at' .$count 'stores near' .$value");
    if(!$text){
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
    $username = $this->getSession()->getPage()->find('css','h3.my-account-title')->getText();
    if( $username == null ){
      throw new \Exception('Authenticated user could not login. Please check the credentials entered.');
    }
  }

}
