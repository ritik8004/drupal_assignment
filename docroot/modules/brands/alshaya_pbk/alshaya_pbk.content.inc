<?php

/**
 * @file
 * File to create default content for Pottery Bard Kids.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block_content.
 */
function alshaya_pbk_create_default_content() {
  // Create copyright block.
  alshaya_pbk_create_copyright_block_content();
}

/**
 * Create default copyright block content.
 */
function alshaya_pbk_create_copyright_block_content() {
  // Create copyright block content.
  $block_content = BlockContent::create([
    'uuid' => '340b823b-e16c-457a-bc03-4a096f8ae171',
    'info' => 'Copyright',
    'machine_name' => 'copyright',
    'body' => [
      'value' => "<p>&copy; 2017 Williams-Sonoma, Inc. All Rights Reserved</p>",
      'format' => 'rich_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create copyright block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'Copyright',
    'body' => [
      'value' => "<p>ويليامز سونوما، وشركة جميع الحقوق محفوظة 2017 &copy;</p>",
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}
