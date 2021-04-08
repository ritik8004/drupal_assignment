<?php

namespace Drupal\alshaya_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\media\Entity\MediaType;
use Drupal\media\IFrameUrlHelper;
use Drupal\media\OEmbed\Resource;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Drupal\media\Plugin\media\Source\OEmbedInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'alshaya oembed' formatter.
 *
 * @FieldFormatter(
 *   id = "alshaya_oembed",
 *   label = @Translation("Alshaya oEmbed"),
 *   field_types = {
 *     "link",
 *     "string",
 *     "string_long",
 *   },
 * )
 */
class AlshayaOembedFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The oEmbed resource fetcher.
   *
   * @var \Drupal\media\OEmbed\ResourceFetcherInterface
   */
  protected $resourceFetcher;

  /**
   * The oEmbed URL resolver service.
   *
   * @var \Drupal\media\OEmbed\UrlResolverInterface
   */
  protected $urlResolver;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The media settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The iFrame URL helper service.
   *
   * @var \Drupal\media\IFrameUrlHelper
   */
  protected $iFrameUrlHelper;

  /**
   * Constructs an OEmbedFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin ID for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\media\OEmbed\ResourceFetcherInterface $resource_fetcher
   *   The oEmbed resource fetcher service.
   * @param \Drupal\media\OEmbed\UrlResolverInterface $url_resolver
   *   The oEmbed URL resolver service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\media\IFrameUrlHelper $iframe_url_helper
   *   The iFrame URL helper service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, MessengerInterface $messenger, ResourceFetcherInterface $resource_fetcher, UrlResolverInterface $url_resolver, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, IFrameUrlHelper $iframe_url_helper) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->messenger = $messenger;
    $this->resourceFetcher = $resource_fetcher;
    $this->urlResolver = $url_resolver;
    $this->logger = $logger_factory->get('media');
    $this->config = $config_factory->get('media.settings');
    $this->iFrameUrlHelper = $iframe_url_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('messenger'),
      $container->get('media.oembed.resource_fetcher'),
      $container->get('media.oembed.url_resolver'),
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('media.oembed.iframe_url_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'responsive' => TRUE,
      'width' => '854',
      'height' => '480',
      'autoplay' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $width = $this->getSetting('width');
    $height = $this->getSetting('height');

    foreach ($items as $delta => $item) {
      $main_property = $item->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
      $value = $item->{$main_property};

      if (empty($value)) {
        continue;
      }
      try {
        $resource_url = $this->urlResolver->getResourceUrl($value, $width, $height);
        $resource = $this->resourceFetcher->fetchResource($resource_url);
      }
      catch (ResourceException $exception) {
        $this->logger->error("Could not retrieve the remote URL (@url).", ['@url' => $value]);
        continue;
      }

      if ($resource->getType() === Resource::TYPE_LINK) {
        $element[$delta] = [
          '#title' => $resource->getTitle(),
          '#type' => 'link',
          '#url' => Url::fromUri($value),
        ];
      }
      elseif ($resource->getType() === Resource::TYPE_PHOTO) {
        $element[$delta] = [
          '#theme' => 'image',
          '#uri' => $resource->getUrl()->toString(),
          '#width' => $width ?: $resource->getWidth(),
          '#height' => $height ?: $resource->getHeight(),
        ];
      }
      else {
        $url = Url::fromRoute('media.oembed_iframe', [], [
          'query' => [
            'url' => $value,
            'max_width' => $width,
            'max_height' => $height,
            'hash' => $this->iFrameUrlHelper->getHash($value, $width, $height),
          ],
        ]);

        $domain = $this->config->get('iframe_domain');
        if ($domain) {
          $url->setOption('base_url', $domain);
        }
        // Render videos and rich content in an iframe for security reasons.
        // @see: https://oembed.com/#section3
        $element[$delta] = [
          '#type' => 'html_tag',
          '#tag' => 'iframe',
          '#attributes' => [
            'src' => $url->toString(),
            'frameborder' => 0,
            'scrolling' => FALSE,
            'allowtransparency' => TRUE,
            'allowfullscreen' => 'allowfullscreen',
            'width' => $width ?: $resource->getWidth(),
            'height' => $height ?: $resource->getHeight(),
            'class' => ['alshaya-media-oembed-content'],
          ],
        ];

        // An empty title attribute will disable title inheritance, so only
        // add it if the resource has a title.
        $title = $resource->getTitle();
        if ($title) {
          $element[$delta]['#attributes']['title'] = $title;
        }
        $element[$delta] = [
          '#type' => 'container',
          '#attributes' => ['class' => [Html::cleanCssIdentifier(sprintf('alshaya-oembed-field-provider'))]],
          'children' => $element[$delta],
        ];
        // For responsive videos, wrap each field item in it's own container.
        if ($this->getSetting('responsive')) {
          $element[$delta]['#attached']['library'][] = 'alshaya_media/responsive-video';
          $element[$delta]['#attributes']['class'][] = 'alshaya-oembed-responsive-video';
        }

        CacheableMetadata::createFromObject($resource)
          ->addCacheTags($this->config->getCacheTags())
          ->applyTo($element[$delta]);
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['autoplay'] = [
      '#title' => $this->t('Autoplay'),
      '#type' => 'checkbox',
      '#description' => $this->t('Autoplay the videos.'),
      '#default_value' => $this->getSetting('autoplay'),
    ];
    $elements['responsive'] = [
      '#title' => $this->t('Responsive Video'),
      '#type' => 'checkbox',
      '#description' => $this->t("Make the video fill the width of it's container, adjusting to the size of the user's screen."),
      '#default_value' => $this->getSetting('responsive'),
    ];
    // Loosely match the name attribute so forms which don't have a field
    // formatter structure (such as the WYSIWYG settings form) are also matched.
    $responsive_checked_state = [
      'visible' => [
        [
          ':input[name*="responsive"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $elements['width'] = [
      '#title' => $this->t('Width'),
      '#type' => 'number',
      '#field_suffix' => 'px',
      '#default_value' => $this->getSetting('width'),
      '#required' => TRUE,
      '#size' => 20,
      '#states' => $responsive_checked_state,
    ];
    $elements['height'] = [
      '#title' => $this->t('Height'),
      '#type' => 'number',
      '#field_suffix' => 'px',
      '#default_value' => $this->getSetting('height'),
      '#required' => TRUE,
      '#size' => 20,
      '#states' => $responsive_checked_state,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $dimensions = $this->getSetting('responsive') ?
    $this->t('Responsive') :
    $this->t('@widthx@height', [
      '@width' => $this->getSetting('width'),
      '@height' => $this->getSetting('height'),
    ]);
    $summary[] = $this->t('Embedded Video (@dimensions@autoplay).', [
      '@dimensions' => $dimensions,
      '@autoplay' => $this->getSetting('autoplay') ? $this->t(', autoplaying') : '',
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if ($field_definition->getTargetEntityTypeId() !== 'media') {
      return FALSE;
    }

    if (parent::isApplicable($field_definition)) {
      $media_type = $field_definition->getTargetBundle();

      if ($media_type) {
        $media_type = MediaType::load($media_type);
        return $media_type && $media_type->getSource() instanceof OEmbedInterface;
      }
    }

    return FALSE;
  }

}
