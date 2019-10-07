<?php

/**
 * @file
 * File to create default content for justice.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block_content.
 */
function alshaya_jus_non_transac_create_default_content() {
  // Create latest_fashion_update block.
  alshaya_jus_non_transac_create_latest_fashion_update_block_content();
}

/**
 * Create default latest fashion update block content.
 */
function alshaya_jus_non_transac_create_latest_fashion_update_block_content() {
  // Create latest_fashion_update block content.
  $block_content = BlockContent::create([
    'uuid' => '8ff46bb2-3402-4d06-a9c4-0183e9dc4f67',
    'info' => 'Latest Fashion Update',
    'machine_name' => 'latest_fashion_update',
    'body' => [
      'value' => "<div class='latest-update'>
                  <div class='fashion-news'>
                  <h2>Don’t miss the news</h2>
                  <p>Get the latest updates from Justice</p>
                  </div>
                  <div class='latest-update-link'>
                  <a href='/email-sign-up' class='use-ajax button--primary' data-dialog-type='modal'>sign up</a>
                  </div>
                  </div>",
      'format' => 'full_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create latest_fashion_update block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'Latest Fashion Update',
    'body' => [
      'value' => "<div class='latest-update'>
                  <div class='fashion-news'>
                  <h2>لا تفوت الأخبار</h2>
                  <p>احصل على أحدث العروض عبر موقع جستس</p>
                  </div>
                  <div class='latest-update-link'>
                  <a href='/email-sign-up' class='use-ajax button--primary' data-dialog-type='modal'>سجلي الآن</a>
                  </div>
                  </div>",
      'format' => 'full_text',
    ],
  ]);
  $ar_block_content->save();
}
