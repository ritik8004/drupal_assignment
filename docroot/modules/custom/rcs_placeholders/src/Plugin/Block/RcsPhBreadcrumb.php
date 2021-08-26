<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a dynamic breadcrumb for commerce pages.
 *
 * @Block(
 *   id = "rcs_ph_breadcrumb",
 *   admin_label = @Translation("RCS Placeholders breadcrumb"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhBreadcrumb extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'rcs-ph-breadcrumb',
        'data-param-get-data' => 'false',
      ],
    ];

    $build['wrapper']['content'] = [
      '#theme' => 'breadcrumb',
      '#links' => [
        Link::fromTextAndUrl(
          $this->t('Home'),
          Url::fromUserInput('/')
        ),
        Link::fromTextAndUrl(
          '#rcs.categories.breadcrumbs.category_name#',
          Url::fromUserInput('#rcs.categories.breadcrumbs.category_url_path#')
        ),
      ],
    ];

    return $build;
  }

}
