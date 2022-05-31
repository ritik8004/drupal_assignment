<?php

/**
 * @file
 * Module file.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default content for footlocker.
 */
function alshaya_fl_create_default_content() {
  // Create copyright block content.
  alshaya_fl_create_copyright_block();
}

/**
 * Create copyright block content for footlocker.
 */
function alshaya_fl_create_copyright_block() {
  // Create copyright block content.
  $block_content = BlockContent::create([
    'info' => 'Copyright',
    'uuid' => '340b823b-e16c-457a-bc03-4a096f8ae171',
    'body' => [
      'value' => "<p>&copy; 2018 Foot Locker. All rights reserved.</p>",
      'format' => 'rich_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
    'id' => 'copyright',
    'machine_name' => 'copyright',
  ]);
  $block_content->save();

  // Create copyright block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'Copyright',
    'body' => [
      'value' => "<p>&copy; 2018 فوت لوكر. جميع الحقوق محفوظة</p>",
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}
