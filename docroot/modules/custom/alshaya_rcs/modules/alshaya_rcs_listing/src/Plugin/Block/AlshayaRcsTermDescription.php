<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dynamic term description for commerce pages.
 *
 * @Block(
 *   id = "rcs_term_description",
 *   admin_label = @Translation("Alshaya RCS Term Description"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class AlshayaRcsTermDescription extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Department page helper.
   *
   * @var \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper
   */
  protected $departmentPageHelper;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    $department_page_helper
  ) {
    $this->configuration = $configuration;
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->departmentPageHelper = $department_page_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_advanced_page.department_page_helper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div class="field c-page-title__description"><span>#rcs.category.description#</span></div>',
      '#attached' => [
        'library' => 'alshaya_white_label/rcs-ph-term-description',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    // Do not render this block on Department pages.
    $is_department_page = $this->departmentPageHelper->getDepartmentPageNid();
    return AccessResult::allowedIf(empty($is_department_page));
  }

}
