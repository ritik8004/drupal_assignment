<?php

/**
 * @file
 * Example implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

use Symfony\Component\Yaml\Yaml;

// This variable is declared and filled in post-sites-php/includes.php
global $site_name;

// If we are on local environment, the site name has not been detected yet.
if (empty($site_name) && $settings['env'] == 'local' && $_SERVER['HTTP_HOST'] == 'local.alshaya.com') {
  $data = Yaml::parse(file_get_contents(DRUPAL_ROOT . '/../blt/project.local.yml'));
  $site_name = $data['brands']['transac'];
}

// We merge the entire settings with the specific ones.
include_once DRUPAL_ROOT . '/../factory-hooks/environments/includes.php';
$settings = array_merge($settings, alshaya_get_specific_settings($site_name, $settings['env']));
