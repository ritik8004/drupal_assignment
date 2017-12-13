<?php

/**
 * @file
 * Drush commands for alshaya_seo.
 */

use Drupal\Core\Url;
use Drupal\redirect\Entity\Redirect;

/**
 * Implements hook_drush_command().
 */
function alshaya_seo_drush_command() {
  $items = [];

  $items['redirect-arabic-categories'] = [
    'description' => 'Creates redirect rules for categories arabic URLs',
    'aliases' => ['rac'],
  ];

  $items['bulk-redirect-import'] = [
    'description' => 'Creates bulk redirects',
    'aliases' => ['brc'],
    'arguments' => [
      'file' => 'Path to the csv file',
    ],
  ];
  return $items;
}

/**
 * Creates a new user account.
 */
function drush_alshaya_seo_redirect_arabic_categories() {

  drush_print('Creating redirect rules for arabic Product categories URLs...');

  $vid = 'acq_product_category';
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
  foreach ($terms as $term) {
    $path = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid])->toString();

    // Redirect storage.
    $redirect_repository = \Drupal::service('redirect.repository');
    $source_url = str_replace('/en/', '', $path);
    $destination_url = 'entity:taxonomy_term/' . $term->tid;
    try {

      // Check if redirect already exists.
      $redirect_exists = $redirect_repository->findMatchingRedirect($source_url, [], 'ar');
      if ($redirect_exists) {
        drush_print(dt('Redirect rule already exists for : /ar/@url', [
          '@url' => $source_url,
        ]));
        continue;
      }
      // Create redirect for the path.
      $redirect_entity = [
        'redirect_source' => $source_url,
        'redirect_redirect' => $destination_url,
        'status_code' => '301',
        'language' => 'ar',
      ];

      $new_redirect = Redirect::create($redirect_entity);
      $new_redirect->save();
      drush_print(dt('Created redirect rule for : /ar/@url', [
        '@url' => $source_url,
      ]));
    }
    catch (\Exception $e) {
      // If any exception.
      drupal_set_message(t('There was some problem in adding redirect for the url @url. Please check if redirect already exists or not.', ['@url' => $source_url]));
    }

  }

  drush_print('Done creating redirect rules');
}

/**
 * Creates bulk redirects.
 */
function drush_alshaya_seo_bulk_redirect_import($file) {
  if (empty($file)) {
    drush_print('Please provide a valid file path');
    return drush_user_abort();
  }
  elseif (!is_file($file)) {
    drush_print("File not found. Make sure you specified the correct path.");
    return drush_user_abort();
  }
  elseif (!drush_confirm(dt("Are you sure you want to import the URL Redirects? Please make sure the csv doesn't have a header."))) {
    return drush_user_abort();
  }
  global $base_url;
  $redirect_repository = \Drupal::service('redirect.repository');
  $redirects_created_count = 0;
  drush_print('Importing Redirects...');
  if ($handle = fopen($file, 'r')) {
    while ($data = fgetcsv($handle, NULL, "\r")) {
      foreach ($data as $d) {
        $value = explode(',', $d);
        if (empty($value[2])) {
          $value[2] = 'und';
        }
        $redirect_exists = $redirect_repository->findMatchingRedirect($value[0], [], $value[2]);

        // Check if the redirect already exists.
        if ($redirect_exists) {
          drush_print(dt('Redirect rule already exists for language :@lang and source path @url', [
            '@lang' => $value[2],
            '@url' => $value[0],
          ]));
          continue;
        }
        else {
          // Create redirect for the path.
          $destination = Url::fromUri($base_url . '/' . $value[1])->getUri();
          $redirect_entity = [
            'redirect_source' => $value[0],
            'redirect_redirect' => $destination,
            'status_code' => '301',
            'language' => $value[2],
          ];
          $new_redirect = Redirect::create($redirect_entity);
          $new_redirect->save();
          $redirects_created_count++;
        }
      }
    }
    drush_print(dt('@count redirects created.', [
      '@count' => $redirects_created_count,
    ]));
  }
}
