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
use Drupal\alshaya_seo\Event\MetaImageRenderEvent;

/**
 * Implements hook_token_info().
 */
function alshaya_seo_token_info() {
  $info = [];

  $info['types']['alshaya_seo'] = [
    'name' => 'Alshaya SEO Tokens',
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

  $info['tokens']['site']['country_name'] = [
    'name' => 'site:country_name',
    'description' => t('Country name in the format required for og tags.'),
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

  $info['tokens']['alshaya_seo']['meta_image'] = [
    'name' => 'site:meta_image',
    'description' => t('Meta image.'),
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
    if (isset($tokens['country_name'])) {
      $locale = explode('_', AlshayaI18nLanguages::getLocale($langcode));
      $country_code = strtolower(end($locale)) . ' (https://countrycode.org/ )';
      $replacements[$tokens['country_name']] = $country_code;
    }
  }
  elseif ($type == 'node' && !empty($data['node']) && $data['node'] instanceof NodeInterface) {

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
    if ($arg_tokens = \Drupal::token()->findWithPrefix($tokens, 'summary_custom')) {
      // Get the dynamic value.
      $size = key($arg_tokens);
      // Get the original token.
      $original = $arg_tokens[$size];
      // Get the translation for the current node.
      $translation = \Drupal::entityManager()->getTranslationFromContext($node, $langcode, ['operation' => 'node_tokens']);
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
  elseif ($type == 'alshaya_seo') {
    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
        case 'meta_image':
          // Dispatch the MetaImageRender event to set the meta image.
          $event = new MetaImageRenderEvent(\Drupal::routeMatch());
          // Get the event_dispatcher service and dispatch the event.
          \Drupal::service('event_dispatcher')->dispatch(MetaImageRenderEvent::EVENT_NAME, $event);
          if ($event && $event instanceof MetaImageRenderEvent) {
            if (!empty($event->getMetaImage())) {
              $replacements[$tokens['meta_image']] = $event->getMetaImage();
            }
          }
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
  if ($context['type'] == 'term') {

    // This fix is done to put empty strings whenever parent tokens are called
    // for categories which dont have parents.
    $token_service = \Drupal::token();
    $value_tokens = '';
    $prefix_list = [
      'parent',
      'parents',
    ];
    foreach ($prefix_list as $prefix) {
      if (empty($value_tokens)) {
        $value_tokens = $token_service->findWithPrefix($context['tokens'], $prefix);
      }
    }
    if (!empty($value_tokens)) {
      $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($context['data']['term']->get('tid')->getValue()[0]['value']);
      if (empty($parents)) {
        foreach ($value_tokens as $value_token) {
          $replacements[$value_token] = '';
        }
      }
    }
  }
}
