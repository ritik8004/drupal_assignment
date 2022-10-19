<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a path processor to detect the super category page type.
 *
 */
class AlshayaRcsSuperCategoryPathProcessor extends RcsPhPathProcessor {

  /**
   * Department page helper.
   *
   * @var \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper
   */
  protected $departmentPageHelper;

  /**
   * Constructs a new AlshayaRcsSuperCategoryPathProcessor instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper $alshaya_department_page_helper
   *   Department page helper.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory,
    AlshayaDepartmentPageHelper $alshaya_department_page_helper
  ) {
    parent::__construct(
      $entity_type_manager,
      $language_manager,
      $module_handler,
      $config_factory
    );
    $this->departmentPageHelper = $alshaya_department_page_helper;
  }

  /**
   * {@inheritDoc}
   */
  public function processInbound($path, Request $request) {
    // Use static cache to improve performance.
    if (isset(self::$processedPaths[$path])) {
      return self::$processedPaths[$path];
    }

    $this->processFullPagePath($request);
    $department_node = $this->departmentPageHelper->getDepartmentPageNode();
    // Return from the parent function in case the current page is not a
    // department page.
    if (!$department_node) {
      return parent::processInbound($path, $request);
    }

    $this->processEntity('category');
    return self::$processedPaths[self::$pageFullPath];
  }

}
