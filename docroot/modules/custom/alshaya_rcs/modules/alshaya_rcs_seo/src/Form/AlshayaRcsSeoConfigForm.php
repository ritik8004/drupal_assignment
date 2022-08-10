<?php

namespace Drupal\alshaya_rcs_seo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Alshaya RCS SEO settings.
 */
class AlshayaRcsSeoConfigForm extends ConfigFormBase {

  /**
   * Simple sitemap generator.
   *
   * @var \Drupal\simple_sitemap\Simplesitemap
   */
  protected $generator;

  /**
   * AlshayaRcsSeoConfigForm constructor.
   *
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   *   Simple sitemap generator.
   */
  public function __construct(Simplesitemap $generator) {
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('simple_sitemap.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_rcs_seo_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_rcs_seo.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_rcs_seo.settings');
    $form['rcs_seo_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['rcs_seo_configuration']['sitemap_domain_to_use'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#options' => [
        'drupal' => $this->t(\Drupal::class),
        'magento' => $this->t('Magento'),
      ],
      '#default_value' => $config->get('sitemap_domain_to_use') ?? 'drupal',
      '#title' => $this->t('Sitemap domain to use'),
      '#description' => $this->t('Select the domain that you want to use in the sitemap.')
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $rcs_seo_config = $this->config('alshaya_rcs_seo.settings');
    // Regenete the sitemap if the value is changed.
    if ($rcs_seo_config->get('sitemap_domain_to_use') != $form_state->getValue('sitemap_domain_to_use')) {
      $rcs_seo_config->set('sitemap_domain_to_use', $form_state->getValue('sitemap_domain_to_use'))
      ->save();
      // Regeneate the sitemap.
      $this->generator->generateSitemap();
    }

    parent::submitForm($form, $form_state);
  }

}
