<?php

/**
 * @file
 * File to create default content for victoria's secret.
 */

use \Drupal\block_content\Entity\BlockContent;

/**
 * Create default copyright block content.
 */
function alshaya_vs_create_default_content() {
  // Create copyright block.
  alshaya_vs_create_copyright_block_content();
  // Create email sign up cta block.
  alshaya_vs_create_email_signup_cta_block_content();
}

/**
 * Create default copyright block content.
 */
function alshaya_vs_create_copyright_block_content() {
  // Create copyright block content.
  $block_content = BlockContent::create([
    'uuid' => '6126accb-c99e-4c53-b8fe-5e97be281d53',
    'info' => 'Copyright',
    'machine_name' => 'copyright',
    'body' => [
      'value' => "<p>&copy; Victoria's Secret. All Rights Reserved</p>",
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
      'value' => "<p>فيكتوريا سيكريت. كل الحقوق محفوظة. &copy;</p>",
      'format' => 'rich_text',
    ]
  ]);
  $ar_block_content->save();
}

/**
 * Create default copyright block content.
 */
function alshaya_vs_create_email_signup_cta_block_content() {
  // Create email signup cta block content.
  $block_content = BlockContent::create([
    'uuid' => '54b4369d-fb82-4f9f-9034-fc869dda1007',
    'info' => 'Stay in the know',
    'machine_name' => 'stay_in_the_know',
    'body' => [
      'value' => "<p>Get the inside scoop on exclusive offers, new product alrts &amp; Middle Eastern Store events.</p>\r\n<p><a href=\"/signup\">Sign up</a></p>",
      'format' => 'rich_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create email signup cta block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'إبقى دائماً على معرفة',
    'body' => [
      'value' => "<p>للحصول على العروض الحصرية و المنتجات الجديدة و أخر أخبار محلات الشرق الأوسط</p>\r\n<p><a href=\"/signup\">تسجيل مستخدم جديد</a></p>",
      'format' => 'rich_text',
    ]
  ]);
  $ar_block_content->save();
}
