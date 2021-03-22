<?php

/**
 * @file
 * Token related functions for the alshaya_seo_transac.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Implements hook_token_info().
 */
function alshaya_seo_transac_token_info() {
  $info['types']['alshaya_seo'] = [
    'name' => 'Alshaya SEO Tokens',
    'description' => t('Custom tokens for meta tags.'),
  ];

  // Name and machine_name/key do not match. It is not changed intentionally
  // to avoid hook_update. Functionality was to show all categories first but
  // later it was changed to just the leaf level name.
  $info['tokens']['node']['category_names'] = [
    'name' => t('Category Name'),
    'description' => t('Leaf level category used in breadcrumb.'),
  ];

  $info['tokens']['alshaya_seo']['brand_name'] = [
    'name' => t('Brand name'),
    'description' => t('The brand name for the website.'),
  ];

  $info['tokens']['alshaya_seo']['city1'] = [
    'name' => t('City 1'),
    'description' => t('City for meta data.'),
  ];

  $info['tokens']['alshaya_seo']['city1_keyword'] = [
    'name' => t('City 1 for Keywords'),
    'description' => t('City 1 with "in @title,".'),
  ];

  $info['tokens']['alshaya_seo']['city2'] = [
    'name' => t('City 2'),
    'description' => t('City for meta data.'),
  ];

  $info['tokens']['alshaya_seo']['city2_keyword'] = [
    'name' => t('City 2 for Keywords'),
    'description' => t('City 2 with "in @title,".'),
  ];

  $info['tokens']['alshaya_seo']['cities'] = [
    'name' => t('Cities'),
    'description' => t('Cities for node meta data.'),
  ];

  $info['tokens']['alshaya_seo']['term_cities'] = [
    'name' => t('Term Cities'),
    'description' => t('Cities for term meta data.'),
  ];

  $info['tokens']['alshaya_seo']['country'] = [
    'name' => t('Country'),
    'description' => t('The website country.'),
  ];

  $info['tokens']['alshaya_seo']['usp1'] = [
    'name' => t('USP1'),
  ];

  $info['tokens']['alshaya_seo']['usp2'] = [
    'name' => t('USP2'),
  ];

  $info['tokens']['alshaya_seo']['usp3'] = [
    'name' => t('USP3'),
  ];

  $info['tokens']['alshaya_seo']['text_before_cod'] = [
    'name' => t('Text before COD.'),
  ];

  $info['tokens']['alshaya_seo']['sno_suffix'] = [
    'name' => t('Sale and offers suffix.'),
  ];

  $info['tokens']['alshaya_seo']['desc_start'] = [
    'name' => t('Start text in description.'),
  ];

  $info['tokens']['alshaya_seo']['title_sub_suffix'] = [
    'name' => t('Text after sale in title.'),
  ];

  $info['tokens']['alshaya_seo']['gender'] = [
    'name' => t('Gender category.'),
  ];

  $info['tokens']['alshaya_seo']['term_name'] = [
    'name' => t('Term Name with prefix and suffix.'),
  ];

  $info['tokens']['alshaya_seo']['sub_categories'] = [
    'name' => t('Subcategories.'),
  ];

  $info['tokens']['alshaya_seo']['desc_free_delivery_n_cod'] = [
    'name' => t('Description free delivery and COD.'),
  ];

  $info['tokens']['alshaya_seo']['top_category'] = [
    'name' => t('Holder for top category.'),
  ];

  $info['tokens']['alshaya_seo']['keywords'] = [
    'name' => t('Keywords.'),
  ];

  $info['tokens']['alshaya_seo']['super_category'] = [
    'name' => t('Super Category.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_seo_transac_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if (isset($tokens['category_names']) && !empty($data['node']) && $data['node'] instanceof NodeInterface) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];

    // Get all categories for the node.
    if ($field_category = $node->get('field_category')) {
      $term_list = $field_category->getValue();
      if ($parents = \Drupal::service('alshaya_acm_product.category_helper')->getBreadcrumbTermList($term_list)) {
        foreach ($parents as $term) {
          if ($term instanceof TermInterface) {
            $term = \Drupal::service('entity.repository')->getTranslationFromContext($term);
            $replacements[$tokens['category_names']] = $term->getName();
            break;
          }
        }
      }
    }
  }
  elseif ($type == 'alshaya_seo') {
    $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $seo_config = \Drupal::config('alshaya_seo_transac.meta_variables');
    $token_service = \Drupal::token();

    /** @var \Drupal\taxonomy\Entity\Term $category_term */
    $category_term = NULL;

    if (!empty($data['taxonomy_term'])) {
      $category_term = $data['taxonomy_term'];
    }
    elseif (!empty($data['term'])) {
      $category_term = $data['term'];
    }

    if (isset($category_term)) {
      $term_id = !empty($category_term) ? $category_term->id() : '';
      $sale_ids = \Drupal::service('alshaya_acm_product_category.sales_category_manager')->getCategorizationIds();

      if (isset($sale_ids['sale']) && in_array($term_id, $sale_ids['sale'])) {
        $sale = TRUE;
      }
      elseif (isset($sale_ids) && in_array($term_id, $sale_ids)) {
        $sale = TRUE;
      }
    }

    /** @var \Drupal\alshaya_acm_product_category\ProductCategoryTree $categoryManager */
    $categoryManager = \Drupal::service('alshaya_acm_product_category.product_category_tree');

    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
        case 'brand_name':
          $brand_name = (string) t('seo_brand_name');

          // Invoke the alter hook to allow all modules to set brand.
          \Drupal::moduleHandler()->alter('alshaya_seo_brandname', $brand_name);

          $replacements[$original] = $brand_name;
          break;

        case 'usp1':
          $currency_code = \Drupal::configFactory()->get('acq_commerce.currency')->get('currency_code');
          $min_amount = $seo_config->get('min_shipping_amount');
          $replacements[$original] = (string) t('Free Delivery on orders over @currency_code @min_amount', [
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

        case 'country':
          $replacements[$original] = (string) t('seo_country');
          break;

        case 'city1':
          // Show empty string if translation not available.
          $value = (string) t('seo_city1');
          $replacements[$original] = $value == 'seo_city1' ? '' : $value;
          break;

        case 'city1_keyword':
          $value = (string) t('seo_city1');
          $replacement = '';

          if ($value && $value !== 'seo_city1') {
            $replacement = (string) t('@title in @city', [
              '@title' => !empty($data['node']) ? $data['node']->label() : '',
              '@city' => $value,
            ]);

            $replacement = $replacement . ',';
          }

          $replacements[$original] = $replacement;
          break;

        case 'city2':
          // Show empty string if translation not available.
          $value = (string) t('seo_city2');
          $replacements[$original] = $value == 'seo_city2' ? '' : $value;
          break;

        case 'city2_keyword':
          $value = (string) t('seo_city2');
          $replacement = '';

          if ($value && $value !== 'seo_city2') {
            $replacement = (string) t('@title in @city', [
              '@title' => !empty($data['node']) ? $data['node']->label() : '',
              '@city' => $value,
            ]);

            $replacement = $replacement . ',';
          }

          $replacements[$original] = $replacement;
          break;

        case 'cities':
          $cities = [
            $token_service->replace('[alshaya_seo:city1]'),
            $token_service->replace('[alshaya_seo:city2]'),
          ];

          $replacements[$original] = implode(', ', array_filter($cities));
          break;

        case 'term_cities':
          $cities = array_filter([
            $token_service->replace('[alshaya_seo:city1]'),
            $token_service->replace('[alshaya_seo:city2]'),
          ]);

          foreach ($cities as &$city) {
            if (isset($sale) && $sale === TRUE) {
              $city = (string) t('[term:name] sale in @city', ['@city' => $city]);
            }
            else {
              $city = (string) t('Shop [term:name] in @city', ['@city' => $city]);
            }
          }

          $cities = implode(', ', array_filter($cities));
          $replacements[$original] = $token_service->replace($cities, ['term' => $category_term]);
          break;

        case 'term_name':
          $is_l1 = $categoryManager->isCategoryL1($category_term);

          if (isset($sale) && $sale === TRUE) {
            $prefix_token = (string) t('alshaya_seo_sale_title_prefix');
            $prefix = ($prefix_token == 'alshaya_seo_sale_title_prefix')
              ? ''
              : $token_service->replace($prefix_token, ['term' => $category_term]);

            $suffix_token = (string) t('alshaya_seo_sale_title_suffix');
            $suffix = ($suffix_token == 'alshaya_seo_sale_title_suffix')
              ? ''
              : $token_service->replace($suffix_token, ['term' => $category_term]);
          }
          else {
            $prefix = t('Shop');

            $suffix_token = $is_l1
              ? t('Collection Online')
              : t('Collection for @parent Online', [
                '@parent' => $categoryManager->getL1Category($category_term)->label(),
              ]);

            $suffix = $token_service->replace((string) $suffix_token, ['term' => $category_term]);
          }

          $replacements[$original] = implode(' ', array_filter([
            $prefix,
            $category_term->label(),
            $suffix,
          ]));

          break;

        case 'text_before_cod':
          if (isset($sale) && $sale === TRUE) {
            $value = (string) t('alshaya_seo_sale_text_before_cod');
            $replacements[$original] = $value == 'alshaya_seo_sale_text_before_cod' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          else {
            $value = (string) t('alshaya_seo_text_before_cod');
            $replacements[$original] = $value == 'alshaya_seo_text_before_cod' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          break;

        case 'sno_suffix':
          if (isset($sale) && $sale === TRUE) {
            $value = (string) t('alshaya_seo_sale_sno_suffix');
            $replacements[$original] = $value == 'alshaya_seo_sale_sno_suffix' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          else {
            $value = (string) t('alshaya_seo_sno_suffix');
            $replacements[$original] = $value == 'alshaya_seo_sno_suffix' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          break;

        case 'desc_start':
          if (isset($sale) && $sale === TRUE) {
            $value = (string) t('alshaya_seo_sale_desc_start');
            $replacements[$original] = $value == 'alshaya_seo_sale_desc_start' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          else {
            $value = (string) t('alshaya_seo_desc_start');
            $replacements[$original] = $value == 'alshaya_seo_desc_start' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          break;

        case 'title_sub_suffix':
          if (isset($sale) && $sale === TRUE) {
            $value = (string) t('alshaya_seo_sale_title_sub_suffix');
            $replacements[$original] = $value == 'alshaya_seo_sale_title_sub_suffix' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          else {
            $value = (string) t('alshaya_seo_title_sub_suffix');
            $replacements[$original] = $value == 'alshaya_seo_title_sub_suffix' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          break;

        case 'sub_categories':
          $parent_id = $category_term->get('parent')->getString();
          $parent_id = empty($parent_id) ? $category_term->id() : $parent_id;
          $sub_categories = $categoryManager->getSubCategories($current_langcode, $parent_id);

          if (!empty($sub_categories)) {
            // Show only limited sub categories to stay within limit of 420
            // characters.
            $sub_category_to_show = (int) \Drupal::configFactory()
              ->get('alshaya_seo.settings')
              ->get('sub_category_to_show');

            $sub_categories = ($sub_category_to_show > 0)
              ? array_slice($sub_categories, 0, $sub_category_to_show)
              : $sub_categories;
          }

          $replacements[$original] = implode(', ', $sub_categories);
          break;

        case 'desc_free_delivery_n_cod':
          $value = (string) t('alshaya_seo_desc_free_delivery_n_cod');
          $replacements[$original] = $value == 'alshaya_seo_desc_free_delivery_n_cod' ? '' : $value;
          break;

        case 'top_category':
          $value = (string) t('alshaya_seo_top_category');
          $replacements[$original] = $value == 'alshaya_seo_top_category' ? '' : $token_service->replace($value, ['term' => $category_term]);
          break;

        case 'keywords':
          if (isset($sale) && $sale === TRUE) {
            $value = (string) t('alshaya_seo_sale_keywords');
            $replacements[$original] = $value == 'alshaya_seo_sale_keywords' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          else {
            $value = (string) t('alshaya_seo_keywords');
            $replacements[$original] = $value == 'alshaya_seo_keywords' ? '' : $token_service->replace($value, ['term' => $category_term]);
          }
          break;

        case 'super_category':
          $super_category = _alshaya_super_category_get_super_category_for_term();
          $replacements[$original] = isset($super_category) ? $super_category->label() : '';
          break;
      }
    }
  }

  return $replacements;
}
