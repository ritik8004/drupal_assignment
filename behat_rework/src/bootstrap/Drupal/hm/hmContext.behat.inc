<?php

namespace Alshaya\BehatContexts;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\ElementNotFoundException;
use Exception;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Mink\Element\Element;
use Drupal\DrupalExtension\Context\MinkContext;
use Behat\Mink\WebAssert;
use Behat\Behat\Tester\Exception\PendingException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;
use Behat\Gherkin\Node\PyStringNode;

/**
 * Example subcontext.
 */
class hmContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();

    $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * @Then /^I should be able to see the header$/
   */
  public function iShouldBeAbleToSeeTheHeader() {
    $page = $this->getSession()->getPage();
    $create_account = $page->hasLink('create an account');
    if (!$create_account) {
      throw new Exception('Link for creating account is missing in the header');
    }

    $sign_in = $page->hasLink('Sign in');
    if (!$sign_in) {
      throw new Exception('Link to Sign in is missing in the header');
    }

    $find_store = $page->hasLink('Find Store');
    if (!$find_store) {
      throw new Exception('Link to find a store is missing in the header');
    }

    $language = $page->hasLink('عربية');
    if (!$language) {
      throw new Exception('Link to switch to Arabic language is missing in the header');
    }
  }

  /**
   * @Given /^I should be able to see the footer$/
   */
  public function iShouldBeAbleToSeeTheFooter() {
    $page = $this->getSession()->getPage();
    $footer_region_shop = $page->find('css','#block-shopby');
    if(!$footer_region_shop){
      throw new Exception('No Shop categories are displayed in the footer');
    }

    $footer_region_about = $page->find('css','#block-aboutbrand > ul > li:nth-child(1) > div > a');
    if(!$footer_region_shop){
      throw new Exception('About section is not displayed in the footer');
    }

    $footer_region_about = $page->find('css','#block-alshaya-help > ul > li:nth-child(1) > div > a');
    if(!$footer_region_shop){
      throw new Exception('Help section is not displayed in the footer');
    }

    $text = 'Newsletter' and 'Be the first to know about our newest arrivals, special offers and store events near you.';
    $subscription = $page->find('css', '.alshaya-newsletter-subscribe');
    $subscription->find('named_partial', ['content', $text]);
    if ($subscription == NULL) {
      throw new Exception('Text related to Subscription is missing in the footer');
    }

    $sub_button = $subscription->hasButton('sign up');
    if ($sub_button == NULL) {
      throw new Exception('Sign up button to subscribe to newsletters is missing in the footer');
    }

    $copyright1 = '© H&M\'s business concept is to offer fashion and quality at the best price.
H&M has since it was founded in 1947 grown into one of the world\'s leading fashion companies. The content of this site is copyright-protected and is the property of H&M Hennes & Mauritz AB.';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', ['content', $copyright1]);

    if ($copyright == NULL) {
      throw new Exception('Copyright information is missing in the footer');
    }

    $facebook_link = $page->find('css','#block-sociallinks .c-social-links--facebook');
    if(!$facebook_link){
      throw new Exception('Facebook link is missing');
    }
    $twitter_link = $page->find('css','#block-sociallinks .c-social-links--twitter');
    if(!$twitter_link){
      throw new Exception('Twitter link is missing');
    }
    $instagram_link = $page->find('css','#block-sociallinks .c-social-links--instagram');
    if(!$instagram_link){
      throw new Exception('Instagram link is missing');
    }
//        $youtube_link = $page->find('css','#block-sociallinks .c-social-links--youtube');
//        if(!$youtube_link){
//            throw new Exception('YouTube link is missing');
//        }
//        $googlePlus_link = $page->find('css','#block-sociallinks .c-social-links--google-plus');
//        if(!$googlePlus_link){
//            throw new Exception('Google plus link is missing');
//        }
//        $pinterest_link = $page->find('css','#block-sociallinks .c-social-links--pinterest');
//        if(!$pinterest_link){
//            throw new Exception('Pinterest link is missing');
//        }

    $hnm_logo = $page->find('css','#block-footerhnmlogo > div > div > a > img');
    if(!$hnm_logo){
      throw new Exception('hnm logo is missing in the footer');
    }
  }

  /**
   * @Then /^I should be able to see the header in Arabic$/
   */
  public function iShouldBeAbleToSeeTheHeaderInArabic() {
    $page = $this->getSession()->getPage();
    $create_account = $page->hasLink('تسجيل مستخدم جديد');
    if ($create_account == NULL) {
      throw new Exception('Link for creating account is missing in the header on Arabic site');
    }

    $sign_in = $page->hasLink('تسجيل الدخول');
    if ($sign_in == NULL) {
      throw new Exception('Link to Sign in is missing in the header on Arabic site ');
    }

    $find_store = $page->hasLink('البحث عن محلاتنا');
    if ($find_store == NULL) {
      throw new Exception('Link to find a store is missing in the header on Arabic site');
    }

    $language = $page->hasLink('English');
    if ($language == NULL) {
      throw new Exception('Link to switch to English language is missing in the header');
    }
  }

  /**
   * @Given /^I should be able to see the footer in Arabic$/
   */
  public function iShouldBeAbleToSeeTheFooterInArabic() {
    $page = $this->getSession()->getPage();
    $footer_region_shop = $page->find('css','#block-aboutbrand > ul > li:nth-child(1) > div > a');
    if(!$footer_region_shop){
      throw new Exception('No Shop categories are displayed in the footer');
    }

    $footer_region_about = $page->find('css','#block-aboutbrand > ul > li:nth-child(1) > div > a');
    if(!$footer_region_shop){
      throw new Exception('About section is not displayed in the footer');
    }

    $footer_region_about = $page->find('css','#block-alshaya-help > ul > li:nth-child(1) > div > a');
    if(!$footer_region_shop){
      throw new Exception('Help section is not displayed in the footer');
    }

    $text = 'تواصل معنا' and 'كن أول من يعرف عن أحدث الوافدين، والعروض الخاصة والأحداث مخزن بالقرب منك.';
    $subscription = $page->find('css', '.alshaya-newsletter-subscribe');
    $subscription->find('named_partial', ['content', $text]);
    if ($subscription == NULL) {
      throw new Exception('Text related to Subscription is missing in the footer');
    }

    $sub_button = $subscription->hasButton('سجل الآن');
    if ($sub_button == NULL) {
      throw new Exception('Sign up button to subscribe to newsletters is missing in the footer');
    }

    $copyright1 = 'مفهوم الأعمال لدى H&M هو تقديم أحدث صيحات الموضة والجودة العالية بأفضل الأسعار. ومنذ تأسيس H&M في عام 1947 نمت لتصبح واحدة من شركات الأزياء الرائدة في العالم. حقوق النشر محفوظة لموقع شركة H&M و هو مُلك Hennes H&M & Mauritz AB ©';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', ['content', $copyright1]);

    if ($copyright == NULL) {
      throw new Exception('Copyright information is missing in the footer');
    }

    $facebook_link = $page->find('css','#block-sociallinks .c-social-links--facebook');
    if(!$facebook_link){
      throw new Exception('Facebook link is missing');
    }
    $twitter_link = $page->find('css','#block-sociallinks .c-social-links--twitter');
    if(!$twitter_link){
      throw new Exception('Twitter link is missing');
    }
    $instagram_link = $page->find('css','#block-sociallinks .c-social-links--instagram');
    if(!$instagram_link){
      throw new Exception('Instagram link is missing');
    }
//        $youtube_link = $page->find('css','#block-sociallinks .c-social-links--youtube');
//        if(!$youtube_link){
//            throw new Exception('YouTube link is missing');
//        }
//        $googlePlus_link = $page->find('css','#block-sociallinks .c-social-links--google-plus');
//        if(!$googlePlus_link){
//            throw new Exception('Google plus link is missing');
//        }
//        $pinterest_link = $page->find('css','#block-sociallinks .c-social-links--pinterest');
//        if(!$pinterest_link){
//            throw new Exception('Pinterest link is missing');
//        }

    $hnm_logo = $page->find('css','#block-footerhnmlogo > div > div > a > img');
    if(!$hnm_logo){
      throw new Exception('hnm logo is missing in the footer');
    }
  }

}