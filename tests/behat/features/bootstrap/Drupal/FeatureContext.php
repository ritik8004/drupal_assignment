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

  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /** @BeforeScenario @javascript */
  public function before(BeforeScenarioScope $scope)
  {
    $this->getSession()->getDriver()->resizeWindow(1440, 900, 'current');
  }

    /**
     * @Given /^I wait for the page to load$/
     */
    public function iWaitForThePageToLoad()
    {
        $this->getSession()->wait(5000, '(0 === jQuery.active)');
    }

    /**
     * @Then /^I should be able to see the header$/
     */
    public function iShouldBeAbleToSeeTheHeader()
    {
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
    public function iShouldBeAbleToSeeTheFooter()
    {
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
     * @Given /^the page title should be "([^"]*)"$/
     */
    public function thePageTitleShouldBe($arg1)
    {
        $titleElement = $this->getSession()->getPage()->find('css', 'head title');
        if ($titleElement === null) {
            throw new \Exception('Page title element was not found!');
        } else {
            $title = $titleElement->getText();
            $expectedTitle = $arg1;
            if ($expectedTitle !== $title) {
                throw new \Exception("Incorrect title! Expected:$expectedTitle | Actual:$title ");
            }
        }
    }

    /**
     * @When /^I subscribe using a valid Email ID$/
     */
    public function iSubscribeUsingAValidEmailID()
    {
        $length = 5;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);

        $randomString = 'tempemail' . rand(0, $charactersLength - 1);
        $email_id = $randomString.'@gmail.com';
        $this->getSession()->getPage()->fillField("edit-email",$email_id);
    }

}
