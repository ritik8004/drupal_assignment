<?php

/**
 * @file
 * File to create default content for New Balance.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block_content.
 */
function alshaya_nb_create_default_content() {
  // Create copyright block.
  alshaya_nb_create_copyright_block_content();
}

/**
 * Create default copyright block content.
 */
function alshaya_nb_create_copyright_block_content() {
  // Create copyright block content.
  $block_content = BlockContent::create([
    'uuid' => '340b823b-e16c-457a-bc03-4a096f8ae171',
    'info' => 'Copyright',
    'machine_name' => 'copyright',
    'body' => [
      'value' => "<p>&copy; 2022 New Balance. All Rights Reserved</p>",
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
      'value' => "&copy; 2022 نيو بالانس. كل الحقوق محفوظة",
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}

/**
 * Create Footer logo block content for NB.
 */
function alshaya_nb_create_footer_logo_block() {
  // Footer logo path en.
  $logo_path_en = '/' . drupal_get_path('theme', 'alshaya_new_balance') . '/site-logo.svg';
  // Create footer logo block content.
  $block_content = BlockContent::create([
    'info' => 'NB Footer Logo Block',
    'uuid' => '94546c90-1932-4cf4-b8c6-4539d8b9af17',
    'machine_name' => 'nbfooterlogoblock',
    'body' => [
      'value' => "<div class='footer-brand-home'>
                   <p>&copy; Independent since 1906, we empower people through sport and craftsmanship to create positive change in communities around the world.</p>,
                     <img loading='lazy' src='" . $logo_path_en . "' alt='Footer Logo'>
                 </div>",
      'format' => 'rich_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Footer logo path ar.
  $logo_path_ar = '/' . drupal_get_path('theme', 'alshaya_new_balance') . '/site-logo-ar.svg';
  // Create footer logo block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'NB Footer Logo Block',
    'body' => [
      'value' => "<div class='footer-brand-home'>
                  <p>&copy; شركة مستقلة منذ عام 1906 لتمكين الجميع من خلال الرياضة والتصميم بحرفية، من أجل التغيير الإيجابي في المجتمعات حول العالم.</p>,
                     <img loading='lazy' src='" . $logo_path_ar . "' alt='Footer Logo'>
                 </div>",
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}
