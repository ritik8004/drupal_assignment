<?php

namespace Drupal\alshaya_main_menu\Twig;

use Drupal\Core\Render\RendererInterface;

/**
 * Class Alshaya Main Menu Twig Extension.
 */
class AlshayaMainMenuTwigExtension extends \Twig_Extension {

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * AlshayaMainMenuTwigExtension constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

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

    return $this->renderer->render($themable_data);
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('alshaya_main_menu', $this->alshayaMainMenu(...)),
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
