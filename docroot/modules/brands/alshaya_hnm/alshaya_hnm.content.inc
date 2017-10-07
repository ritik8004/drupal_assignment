<?php

/**
 * @file
 * File to create default content for victoria's secret.
 */

use \Drupal\block_content\Entity\BlockContent;

/**
 * Create default copyright block content.
 */
function alshaya_hnm_create_default_content() {
  // Create copyright block.
  alshaya_hnm_create_copyright_block_content();
}

/**
 * Create default copyright block content.
 */
function alshaya_hnm_create_copyright_block_content() {
  // Create copyright block content.
  $block_content = BlockContent::create([
    'uuid' => '340b823b-e16c-457a-bc03-4a096f8ae171',
    'info' => 'Copyright',
    'machine_name' => 'copyright',
    'body' => [
      'value' => "<p>&copy; H&M's business concept is to offer fashion and quality at the best price.
H&M has since it was founded in 1947 grown into one of the world's leading fashion companies. The content of this site is copyright-protected and is the property of H&M Hennes & Mauritz AB.</p>",
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
      'value' => "<p>مفهوم الأعمال لدى H&M هو تقديم أحدث صيحات الموضة والجودة العالية بأفضل الأسعار. ومنذ تأسيس H&M في عام 1947 نمت لتصبح واحدة من شركات الأزياء الرائدة في العالم. حقوق النشر محفوظة لموقع شركة H&M و هو مُلك Hennes H&M & Mauritz AB &copy;</p>",
      'format' => 'rich_text',
    ]
  ]);
  $ar_block_content->save();
}