<?php

namespace Drupal\alshaya_rcs_olapic\Plugin\Block;

use Drupal\alshaya_olapic\Plugin\Block\OlapicBlock;

/**
 * Provides a 'RCS Olapic Block' Block.
 *
 * @Block(
 *   id = "rcsolapicblock",
 *   admin_label = @Translation("Alshaya RCS Olapic Widget"),
 *   category = @Translation("Alshaya RCS Olapic Widget"),
 * )
 */
class RcsOlapicBlock extends OlapicBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $country_code = strtoupper(_alshaya_custom_get_site_level_country_code());
    $data_apikey_field_name = 'olapic_' . $lang . '_data_apikey';
    $data_instance_field_name = 'instance_id_' . $lang;
    $data_apikey = $this->configFactory->get('alshaya_olapic.settings')->get($data_apikey_field_name) ?? '';
    $data_instance = $this->configuration[$data_instance_field_name] ?? '';
    $development_mode = $this->configFactory->get('alshaya_olapic.settings')->get('development_mode') ?? '';
    $data_lang = $lang . '_' . $country_code;
    $olapic_keys = [
      'data_apikey' => $data_apikey,
      'development_mode' => $development_mode,
      'lang' => $data_lang,
    ];

    $olapic_external_script_url = $this->configFactory->get('alshaya_olapic.settings')->get('olapic_external_script_url');

    return [
      '#type' => 'container',
      '#attributes' => [
        'rcs_instance_id' => $data_instance,
        'rcs_div_id' => $this->configuration['div_id'] ?? '',
        'rcs_data_olapic' => 'block-rcsalshayaolapicwidget-2',
        'rcs_data_apikey' => $olapic_keys['data_apikey'],
      ],
      '#attached' => [
        'library' => [
          'alshaya_rcs_olapic/product',
          'alshaya_olapic/alshaya_olapic_widget',
        ],
        'drupalSettings' => [
          'olapic_keys' => $olapic_keys,
          'olapic_external_script_url' => $olapic_external_script_url,
        ],
      ],
    ];
  }

}
