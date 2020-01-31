<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_addressbook\AddressBookAreasTermsHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSpcAddressController.
 */
class AlshayaSpcAddressController extends ControllerBase {

  /**
   * Address terms helper.
   *
   * @var \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper
   */
  protected $areaTermsHelper;

  /**
   * AlshayaSpcAddressController constructor.
   *
   * @param \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper $area_term_helper
   *   Areas term helper.
   */
  public function __construct(AddressBookAreasTermsHelper $area_term_helper) {
    $this->areaTermsHelper = $area_term_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_addressbook.area_terms_helper')
    );
  }

  /**
   * Get area list for a given parent area.
   *
   * @param mixed $area
   *   Parent area id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getAreaListByParent($area) {
    $data = $this->areaTermsHelper->getAllAreasWithParent($area, TRUE);
    return new JsonResponse($data);
  }

  /**
   * Get areas list.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getAreaList() {
    $area_list = [];
    $field_list = _alshaya_spc_get_address_fields();
    // If `area_parent` is not available in the fields.
    if (!isset($field_list['area_parent'])) {
      $area_list = $this->areaTermsHelper->getAllAreas(TRUE);
    }

    return new JsonResponse($area_list);
  }

  /**
   * Get parent areas list.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getParentAreaList() {
    $list = [];
    $field_list = _alshaya_spc_get_address_fields();
    // If `area_parent` is available in the fields.
    if (isset($field_list['area_parent'])) {
      $list = $this->areaTermsHelper->getAllGovernates(TRUE);
    }

    return new JsonResponse($list);
  }

}
