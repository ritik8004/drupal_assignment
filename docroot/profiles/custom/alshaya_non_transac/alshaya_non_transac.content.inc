<?php

/**
 * @file
 * File to create default content for bouchon bakery.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default copyright block content.
 */
function alshaya_non_transac_create_default_content() {
  // Create email sign up cta block.
  alshaya_non_transac_create_email_signup_cta_block_content();
}

/**
 * Create email sign up cta block..
 */
function alshaya_non_transac_create_email_signup_cta_block_content() {
  // Create email signup cta block content.
  $block_content = BlockContent::create([
    'uuid' => '54b4369d-fb82-4f9f-9034-fc869dda1007',
    'info' => 'Stay in the know',
    'machine_name' => 'stay_in_the_know',
    'body' => [
      'value' => "<p>Get the inside scoop on exclusive offers, new product alerts &amp; Middle Eastern Store events.</p>\r\n<p><a href=\"/email-sign-up\" class=\"use-ajax\" data-dialog-type=\"modal\">Sign up</a></p>",
      'format' => 'full_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create email signup cta block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'إبقى دائماً على معرفة',
    'body' => [
      'value' => "<p>للحصول على العروض الحصرية و المنتجات الجديدة و أخر أخبار محلات الشرق الأوسط</p>\r\n<p><a href=\"/ar/email-sign-up\" class=\"use-ajax\" data-dialog-type=\"modal\">تسجيل مستخدم جديد</a></p>",
      'format' => 'full_text',
    ],
  ]);
  $ar_block_content->save();
}
