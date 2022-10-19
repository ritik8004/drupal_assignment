<?php
/**
 * @file
 * File to create default content for New Balance.
 */
use Drupal\block_content\Entity\BlockContent;
/**
 * Create default block_content.
 */
function alshaya_nb_transac_create_default_content() {
  // Create copyright block.
  alshaya_nb_transac_create_footer_logo_block();
}

/**
 * Create Footer logo block content for NB.
 */
function alshaya_nb_transac_create_footer_logo_block() {
    $logo_path_en = '/' . drupal_get_path('theme', 'alshaya_new_balance') . '/site-logo.svg';
  // Create footer logo block content.
  $block_content = BlockContent::create([
    'info' => 'NB Footer Logo Block',
    'uuid' => '03c1357c-dd96-4854-8cff-f298a9332bbc',
    'machine_name' => 'nbfooterlogoblock',
    'body' => [
      'value' => "<div class='footer-brand-home'>
                    <img loading='lazy' src='" . $logo_path_en . "' alt='Footer Logo'>
                   <p>&copy; Independent since 1906, we empower people through sport and craftsmanship to create positive change in communities around the world.</p>
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
                     <img loading='lazy' src='" . $logo_path_ar . "' alt='Footer Logo'>
                  <p>&copy; شركة مستقلة منذ عام 1906 لتمكين الجميع من خلال الرياضة والتصميم بحرفية، من أجل التغيير الإيجابي في المجتمعات حول العالم.</p></div>",
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}