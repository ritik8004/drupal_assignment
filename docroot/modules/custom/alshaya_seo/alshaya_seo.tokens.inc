<?php

/**
 * @file
 * Token related functions for the alshaya_seo.
 */

use Drupal\alshaya_i18n\AlshayaI18nLanguages;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Implements hook_token_info().
 */
function alshaya_seo_token_info() {
  $info = [];

  $info['types']['custom_meta'] = [
    'name' => 'Custom Meta Tokens',
    'description' => t('Custom tokens for meta tags.'),
  ];

  // Token to return locale for the currently visited page.
  $info['tokens']['site']['locale'] = [
    'name' => 'locale',
    'description' => t('Locale (en_US) for the currently visited page.'),
  ];

  $info['tokens']['site']['locale:alternate'] = [
    'name' => 'locale:alternate',
    'description' => t('Alternate locale available for the site.'),
  ];

  $info['tokens']['node']['locale:alternate'] = [
    'name' => 'locale:alternate',
    'description' => t('Alternate locale available for the node.'),
  ];

  $info['tokens']['node']['summary_custom'] = [
    'name' => t('Summary custom'),
    'description' => t("The summary of the node's main body text with dynamic length."),
    'dynamic' => TRUE,
  ];

  $info['tokens']['term']['shopby_subcat'] = [
    'name' => 'All categories in Shop By',
  ];

  $info['tokens']['custom_meta']['brand_name'] = [
    'name' => t('Brand name'),
    'description' => t('The brand name for the website.'),
  ];

  $info['tokens']['custom_meta']['city1'] = [
    'name' => t('City 1'),
    'description' => t('City for meta data.'),
  ];

  $info['tokens']['custom_meta']['city2'] = [
    'name' => t('City 2'),
    'description' => t('City for meta data.'),
  ];

  $info['tokens']['custom_meta']['cities'] = [
    'name' => t('Cities'),
    'description' => t('Cities for meta data.'),
  ];

  $info['tokens']['custom_meta']['country'] = [
    'name' => t('Country'),
    'description' => t('The website country.'),
  ];

  $info['tokens']['custom_meta']['usp1'] = [
    'name' => t('USP1'),
  ];

  $info['tokens']['custom_meta']['usp2'] = [
    'name' => t('USP2'),
  ];

  $info['tokens']['custom_meta']['usp3'] = [
    'name' => t('USP3'),
  ];

  $info['tokens']['custom_meta']['usp4'] = [
    'name' => t('USP4'),
  ];

  $info['tokens']['custom_meta']['usp5'] = [
    'name' => t('USP5'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_seo_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  if (isset($options['langcode'])) {
    $langcode = $options['langcode'];
  }
  else {
    $langcode = LanguageInterface::LANGCODE_DEFAULT;
  }

  $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

  $replacements = [];

  if ($type == 'site') {
    if (isset($tokens['locale'])) {
      $replacements[$tokens['locale']] = AlshayaI18nLanguages::getLocale($langcode);
    }
    if (isset($tokens['locale:alternate'])) {
      // Get all languages available for the site.
      $languages = \Drupal::languageManager()->getLanguages();

      // If there are languages available.
      if (!empty($languages)) {
        $alternates = [];

        // Remove the current language.
        unset($languages[$current_langcode]);

        // If we still have some data in language array.
        if (!empty($languages)) {
          // Prepare the alternate locale data.
          foreach ($languages as $lang => $language) {
            $alternates[] = AlshayaI18nLanguages::getLocale($lang);
          }
        }

        $replacements[$tokens['locale:alternate']] = implode(',', $alternates);
      }
    }
  }

  if ($type == 'node' && !empty($data['node']) && $data['node'] instanceof NodeInterface) {

    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];

    if (isset($tokens['locale:alternate'])) {
      // Get all translations in which the node is available.
      $translations = $node->getTranslationLanguages();

      if (!empty($translations)) {
        $alternates = [];

        foreach ($translations as $lang => $language) {
          if ($lang == $current_langcode) {
            continue;
          }

          $alternates[] = AlshayaI18nLanguages::getLocale($lang);
        }

        $replacements[$tokens['locale:alternate']] = implode(',', $alternates);
      }
    }

    // Replace token for [node:summary_custom:?]
    //
    // Where the last argument accepted is integer, Which defines the length of
    // the string for the summary.
    if ($arg_tokens = \Drupal::token()
      ->findWithPrefix($tokens, 'summary_custom')) {
      // Get the dynamic value.
      $size = key($arg_tokens);
      // Get the original token.
      $original = $arg_tokens[$size];
      // Get the translation for the current node.
      $translation = \Drupal::entityManager()
        ->getTranslationFromContext($node, $langcode, ['operation' => 'node_tokens']);
      if ($translation->hasField('body') && ($items = $translation->get('body')) && !$items->isEmpty()) {
        $item = $items[0];
        // If the summary was requested and is not empty, use it.
        if (!empty($item->summary)) {
          $output = $item->summary_processed;
        }
        // Attempt to provide a suitable version of the 'body' field.
        else {
          $output = $item->processed;
        }
        $replacements[$original] = text_summary($output, 'plain_text', $size);
      }
    }
  }

  if ($type == 'term' && !empty($data['term']) && $data['term'] instanceof TermInterface && isset($tokens['shopby_subcat'])) {
    $term = $data['term'];
    $parents = array_keys(\Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadAllParents($term->id()));
    $parent = reset(array_slice($parents, -1, TRUE));
    $query = \Drupal::database()->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid']);
    $query->innerJoin('taxonomy_term__parent', 'tth', 'tth.entity_id = tfd.tid');
    $query->condition('tth.parent_target_id', $parent);
    $query->condition('tfd.name', '%' . db_like('Shop By Product') . '%', 'LIKE');
    $result = $query->execute()->fetchCol();
    $children = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadChildren(reset($result));
    $shop_by_array = [];
    foreach ($children as $child) {
      $shop_by_array[] = $child->getName();
    }

    $replacements[$tokens['shopby_subcat']] = implode(', ', $shop_by_array);
  }

  if ($type == 'custom_meta') {
    $seo_config = \Drupal::configFactory()
      ->get('alshaya_seo_transac.meta_variables');
    $brand_name = (string) t('seo_brand_name');
    $min_amount = $seo_config->get('min_shipping_amount');
    $currency_code = \Drupal::configFactory()
      ->get('acq_commerce.currency')
      ->get('currency_code');

    // Invoke the alter hook to allow all modules to set brand.
    \Drupal::moduleHandler()->alter('alshaya_seo_brandname', $brand_name);

    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
        case 'brand_name':
          $replacements[$original] = $brand_name;
          break;

        case 'usp1':
          $replacements[$original] = t('Free Delivery on orders over @currency_code @min_amount', [
            '@currency_code' => $currency_code,
            '@min_amount' => $min_amount,
          ]);
          break;

        case 'usp2':
          $replacements[$original] = $seo_config->get('usp2');
          break;

        case 'usp3':
          $replacements[$original] = $seo_config->get('usp3');
          break;

        case 'usp4':
          $replacements[$original] = $seo_config->get('usp4');
          break;

        case 'usp5':
          $replacements[$original] = $seo_config->get('usp5');
          break;

        case 'country':
          $replacements[$original] = t('seo_country');
          break;

        case 'city1':
          // Show empty string if translation not available.
          $value = t('seo_city1');
          $replacements[$original] = $value == 'seo_city1' ? '' : $value;
          break;

        case 'city2':
          // Show empty string if translation not available.
          $value = t('seo_city2');
          $replacements[$original] = $value == 'seo_city2' ? '' : $value;
          break;

        case 'cities':
          $token_service = \Drupal::token();
          $cities = [
            $token_service->replace('[custom_meta:city1]'),
            $token_service->replace('[custom_meta:city2]'),
          ];

          $replacements[$original] = implode(', ', array_filter($cities));
          break;
      }
    }
  }

  return $replacements;
}

/**
 * Implements hook_tokens_alter().
 *
 * [current-page:url:absolute] is the default canonical metatag for nodes.
 * However, for department page, we replace the url "on-the-fly" with the
 * related category's url.
 */
function alshaya_seo_tokens_alter(array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {
  // Alter [current-page:url:absolute] token for canonical meta tag for
  // department page.
  if ($context['type'] == 'current-page') {
    $token_service = \Drupal::token();
    if ($value_tokens = $token_service->findWithPrefix($context['tokens'], 'url')) {
      if (!empty($value_tokens['absolute'])) {
        if ($node = \Drupal::routeMatch()->getParameter('node')) {
          // Department pages are now advanced pages.
          if ($node instanceof NodeInterface && $node->getType() == 'advanced_page') {
            if ($node->get('field_use_as_department_page')->value && $node->get('field_product_category')->getValue() !== NULL) {
              $product_category = $node->get('field_product_category')->getValue();
              $url_options = ['absolute' => TRUE];
              $category_alias = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $product_category['0']['target_id']], $url_options)->toString();
              $replacements[$value_tokens['absolute']] = $category_alias;
            }
          }
        }
      }
    }
  }
}
