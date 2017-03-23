<?php

namespace Drupal\alshaya_main_menu\Twig;

/**
 * Class AlshayaMainMenuTwigExtension.
 */
class AlshayaMainMenuTwigExtension extends \Twig_Extension {

  /**
   * Alshaya 'alshaya_main_menu()' for twig.
   *
   * @param string $theme_name
   *   Theme name.
   * @param array $data
   *   Data array.
   *
   * @return mixed
   *   Rendered output.
   */
  public function alshayaMainMenu($theme_name, array $data) {
    $themable_data = [
      '#theme' => $theme_name,
      '#data' => $data,
    ];
    return \Drupal::service('renderer')->render($themable_data);
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('alshaya_main_menu', [$this, 'alshayaMainMenu']),
    ];
  }

  /**
   * Returns the name of the extension.
   *
   * @return string
   *   The extension name
   */
  public function getName() {
    return 'AlshayaMainMenuTwigExtension';
  }

}
