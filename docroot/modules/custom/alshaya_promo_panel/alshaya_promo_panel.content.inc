<?php

/**
 * @file
 * File to create default block content for promo panel.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block content for site.
 */
function alshaya_promo_panel_create_default_content() {
  // Create default promo panel block content.
  alshaya_promo_panel_create_promo_panel_block_content();
}

/**
 * Create default promo panel block content.
 */
function alshaya_promo_panel_create_promo_panel_block_content() {
  // Create promo panel block content.
  $block_content = BlockContent::create([
    'uuid' => '95bb6c44-62eb-4bdc-870d-cd0d8d1a7460',
    'info' => 'Promo panel',
    'machine_name' => 'block_content_promo_panel',
    'type' => 'paragraph_block',
    'langcode' => 'en',
  ]);
  $block_content->save();

  // Create promo panel block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'عروض اليوم',
  ]);
  $ar_block_content->save();
}
