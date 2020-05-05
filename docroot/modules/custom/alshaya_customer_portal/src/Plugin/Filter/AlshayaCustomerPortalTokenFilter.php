<?php

namespace Drupal\alshaya_customer_portal\Plugin\Filter;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Utility\Token;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter that replaces tokens for Customer Portal.
 *
 * @Filter(
 *   id = "alshaya_customer_portal_token_filter",
 *   title = @Translation("Replace Customer portal tokens with their values"),
 *   description = @Translation("This filter is used to replace customer portal tokens with their values."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE
 * )
 */
class AlshayaCustomerPortalTokenFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entitytype manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The class constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Token $token,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->token = $token;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('token'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $replacements = [];

    // Scan and get only the customer portal tokens.
    $text_tokens = $this->token->scan($text);
    $customer_portal_tokens = array_filter($text_tokens, function ($key) {
      if ($key === 'customer_portal') {
        return TRUE;
      }
    }, ARRAY_FILTER_USE_KEY);

    if (empty($customer_portal_tokens)) {
      return new FilterProcessResult($text);
    }

    // Generate replacement texts for the customer portal tokens.
    $bubbleable_metadata = new BubbleableMetadata();
    if (!empty($customer_portal_tokens)) {
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
      foreach ($customer_portal_tokens as $type => $tokens) {
        $replacements = $this->token->generate($type, $tokens, ['user' => $user], ['clear' => TRUE], $bubbleable_metadata);
      }

    }

    // Escape the tokens, unless they are explicitly markup.
    foreach ($replacements as $token => $value) {
      $replacements[$token] = $value instanceof MarkupInterface ? $value : new HtmlEscapedText($value);
    }

    // Generate the final string with all tokens of customer_portal replaced by
    // the values.
    $tokens = array_keys($replacements);
    $values = array_values($replacements);
    $result = str_replace($tokens, $values, $text);

    $result = new FilterProcessResult($result);
    $result->addCacheContexts($bubbleable_metadata->getCacheContexts());
    $result->addCacheTags($bubbleable_metadata->getCacheTags());

    return $result;
  }

}
