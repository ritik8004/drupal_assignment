<?php

/**
 * @file
 * File to create default content for vision express.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block_content.
 */
function alshaya_ve_non_transac_create_default_content() {
  // Create customer_subscribe block.
  alshaya_ve_non_transac_create_customer_subscribe_block_content();

  // Create customer_support block.
  alshaya_ve_non_transac_create_customer_support_block_content();
}

/**
 * Create default Customer Subscribe block content.
 */
function alshaya_ve_non_transac_create_customer_subscribe_block_content() {
  // Create customer_subscribe block content.
  $block_content = BlockContent::create([
    'uuid' => '5c3f6d2c-6191-462d-91a4-2d9b0bb80afc',
    'info' => 'Customer Subscribe',
    'machine_name' => 'customer_subscribe',
    'body' => [
      'value' => "<div class='block-subscribe'>
                  <h3 class='title icon-email'>Don’t miss the news</h3>
                  <div class='button'><a class='btn' href='/en/email-sign-up'>SUBSCRIBE</a></div>
                  </div>",
      'format' => 'full_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create customer_subscribe block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'Customer Subscribe',
    'body' => [
      'value' => "<div class='block-subscribe'>
                  <h3 class='title icon-email'>لا تفوت الأخبار</h3>
                  <div class='button'><a class='btn' href='/ar/email-sign-up'>الإشتراك</a></div>
                  </div>",
      'format' => 'full_text',
    ],
  ]);
  $ar_block_content->save();
}

/**
 * Create default Customer Support block content.
 */
function alshaya_ve_non_transac_create_customer_support_block_content() {
  // Create customer_support block content.
  $block_content = BlockContent::create([
    'uuid' => '37282103-1a2e-4856-aab8-2c380e10acdc',
    'info' => 'Customer Support',
    'machine_name' => 'customer_support',
    'body' => [
      'value' => "<div class='cust-support icon-contact'>
                  <a href='/en/contact'>
                  <h3>Customer Support</h3>
                  <p>+965 182 12 12</p>
                  </a>
                  </div>",
      'format' => 'full_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create customer_support block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'Customer Support',
    'body' => [
      'value' => "<div class='cust-support icon-contact'>
                  <a href='/ar/contact'>
                  <h3>دعم العملاء</h3>
                  <p>+965 182 12 12</p>
                  </a>
                  </div>",
      'format' => 'full_text',
    ],
  ]);
  $ar_block_content->save();
}
