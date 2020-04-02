<?php

namespace Drupal\alshaya_algolia_react\Controller;

use Drupal\block\BlockViewBuilder;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Customer controller to add front page.
 */
class AlgoliaController extends ControllerBase {

  /**
   * Returns the build for home page.
   *
   * @return array
   *   Build array.
   */
  public function search() {
    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-search-page"></div>',
      '#attached' => [
        'drupalSettings' => [
          'algoliaSearch' => [
            'showSearchResults' => TRUE,
          ],
        ],
      ],
    ];
  }

  /**
   * Callback to get Algolia settings.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Settings as JSON.
   */
  public function getSettings() {
    /** @var \Drupal\block\BlockInterface $block */
    $block = $this->entityTypeManager()->getStorage('block')->load('alshayaalgoliareactautocomplete');
    if (empty($block)) {
      throw new NotFoundHttpException();
    }

    /** @var \Drupal\block\BlockViewBuilder $builder */
    $builder = $this->entityTypeManager()->getViewBuilder($block->getEntityTypeId());
    $lazy_build = $builder->view($block);
    $lazy_build['#block'] = $block;
    $build = BlockViewBuilder::preRender($lazy_build);
    $dependency = CacheableMetadata::createFromRenderArray($lazy_build);

    $drupalSettings = $build['content']['#attached']['drupalSettings'];

    $settings = [];
    $settings['application_id'] = $drupalSettings['algoliaSearch']['application_id'];
    $settings['api_key'] = $drupalSettings['algoliaSearch']['api_key'];
    $settings['indexName'] = $drupalSettings['algoliaSearch']['indexName'];
    $settings['filters'] = $drupalSettings['algoliaSearch']['filters'];
    $settings['gallery']['showHoverImage'] = $drupalSettings['reactTeaserView']['gallery']['showHoverImage'];
    $settings['gallery']['showThumbnails'] = $drupalSettings['reactTeaserView']['gallery']['showThumbnails'];
    $settings['swatches'] = $drupalSettings['reactTeaserView']['swatches'];

    $response = new CacheableJsonResponse($settings);
    $response->addCacheableDependency($dependency);
    return $response;
  }

}
