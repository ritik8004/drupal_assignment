<?php

/**
 * @file
 * Drush commands for alshaya_acm_customer.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_image_sitemap_drush_command() {
  $items = [];
  $items['create-img-sitemap'] = [
    'description' => 'Create Image Sitemap',
    'aliases' => ['cis'],
    'examples' => [
      'drush create-img-sitemap' => 'Create image sitemap',
    ],
  ];
  return $items;
}

/**
 * Implements drush_command().
 */
function drush_alshaya_image_sitemap_create_img_sitemap() {
  drush_print('Creating image sitemap.');

  \Drupal::service('alshaya_image_sitemap.generator')->getSitemapReady();

  $batch = [
    'operations' => [],
    'finished' => 'batch_end_callback',
    'title' => t('Create Image Sitemap'),
    'init_message' => t('Starting sitemap creation.....'),
    'progress_message' => t('Completed @current step of @total.'),
    'error_message' => t('Sitemap creation has encountered an error.'),
  ];

  $nids = \Drupal::service('alshaya_image_sitemap.generator')->getNodes();
  $batch_size = \Drupal::config('alshaya_image_sitemap.settings')->get('image_sitemap_batch_chunk_size');
  $nid_chunks = array_chunk($nids, $batch_size);
  foreach ($nid_chunks as $nid_chunk) {
    $batch['operations'][] = [
      'batch_start_callback',
      [$nid_chunk],
    ];
  }

  // Initialize the batch.
  batch_set($batch);

  // Start the batch process.
  drush_backend_batch_process();
}

/**
 * Implements batch start function.
 *
 * @param array $nids
 *   Array of nids.
 */
function batch_start_callback(array $nids) {
  \Drupal::service('alshaya_image_sitemap.generator')->process($nids);
}

/**
 * Batch finished callback.
 *
 * @param bool $success
 *   Success or fail import.
 * @param array $results
 *   Result array.
 * @param array $operations
 *   Operation array.
 */
function batch_end_callback($success, array $results = [], array $operations = []) {
  if ($success) {
    \Drupal::service('alshaya_image_sitemap.generator')
      ->sitemapGenerateFinished();
    \Drupal::state()->set('alshaya_image_sitemap.last_generated', REQUEST_TIME);
    drush_print(dt('Image Sitemap Generated Successfully.'));
  }
  else {
    drush_print(dt('There was some error while importing redirects.'));
  }
}
