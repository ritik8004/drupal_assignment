<?php

namespace Drupal\alshaya_aura_react;

use Drupal\Core\Render\Element\RenderCallbackInterface;
use Drupal\Core\Url;

/**
 * Provides a trusted callback to render aura blocks.
 */
class AuraBlockViewBuilder implements RenderCallbackInterface {

  /**
   * Pre render callback for building My Account Links block.
   *
   * @param array $build
   *   The block build array.
   *
   * @return array
   *   The altered block build array.
   */
  public static function myAccountBlockPreRender(array $build): array {
    $build['content']['my_account_my_aura_block_root'] = [
      '#markup' => '<div id="my-accounts-aura-mobile"></div>',
      '#weight' => -1,
    ];

    $build['#attributes']['class'][] = 'aura-enabled';

    return $build;
  }

  /**
   * Pre render callback for system_menu_block:account block.
   *
   * @param array $build
   *   The block build array.
   *
   * @return array
   *   The altered block build array.
   */
  public static function systemAccountBlockPreRender(array $build): array {
    $build['content']['#prefix'] = '<div id="aura-mobile-header-signin-register"></div>';
    return $build;
  }

  /**
   * Pre render callback for page_title_block block.
   *
   * @param array $build
   *   The block build array.
   *
   * @return array
   *   The altered block build array.
   */
  public static function auraPageTitleBlockPreRender(array $build): array {
    $edit_account = [
      '#type' => 'link',
      '#title' => t('edit account details'),
      '#url' => Url::fromRoute('entity.user.edit_form', ['user' => \Drupal::currentUser()->id()]),
      '#attributes' => [
        'class' => ['button', 'button-wide', 'edit-account'],
      ],
    ];

    $build['content']['#suffix'] = \Drupal::service('renderer')->render($edit_account);

    return $build;
  }

}
