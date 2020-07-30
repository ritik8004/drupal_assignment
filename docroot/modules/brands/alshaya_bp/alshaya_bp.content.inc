<?php

/**
 * @file
 * File to create default content for Boots.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block_content.
 */
function alshaya_bp_create_default_content() {
  // Create copyright block.
  alshaya_bp_create_copyright_block_content();
}

/**
 * Create default copyright block content.
 */
function alshaya_bp_create_copyright_block_content() {
  // Create copyright block content.
  $block_content = BlockContent::create([
    'uuid' => '340b823b-e16c-457a-bc03-4a096f8ae171',
    'info' => 'Copyright',
    'machine_name' => 'copyright',
    'body' => [
      'value' => "<p>Copyright &copy; Boots International Management Services Limited. All rights reserved. me.boots.com is a trading name of Boots
International Management Services Limited. Registered office: Nottingham NG2 3AA.Registered in England. Company number7136280.</p>",
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
      'value' => "حقوق الطبع والنشر &copy; Boots International Management Services Limited. جميع الحقوق محفوظة. me.boots.com هو اسم تجاري لشركة Boots International Management Services Limited. المكتب المسجل: نوتينغهام NG2 3AA. مسجل في إنجلترا. رقم الشركة 7136280.",
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}
