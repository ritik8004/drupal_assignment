<?php
// @codingStandardsIgnoreFile

/**
 * @file.
 *
 * This file contains some settings which are environment and/or site
 * dependent. The function will identify the appropriate settings to apply
 * which will be then merged with global settings.
 */

/**
 * Get settings which are environment and/or site dependent.
 */
function alshaya_get_additional_settings($site_code, $country_code, $env) {
  $mapping = [
    'mc' => [
      'default' => [
        'default' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-PP5PK4C',
          ],
          'social_auth_google.settings' => [
            'client_id' => '333631634865-b8neo4gqdr65nld1rgo9ffngq4fh4go3.apps.googleusercontent.com',
            'client_secret' => 'nrDGHEmKXzwcZU4LtzxLOhpF',
          ],
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@mothercareuk',
            'tags.twitter_cards_site' => '@mothercareuk',
          ],
          'live' => [
            'exponea.settings' => [
              "name" => "Mothercare",
              "short_name" => "MC",
            ],
          ],
        ],
      ],
      'kw' => [
        'uat' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-PP5PK4C',
          ],
        ],
      ],
      'ae' => [
        'uat' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-TTQFBZ',
          ],
        ],
      ],
      'sa' => [
        'uat' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-NMZXSP',
          ],
        ],
      ],
    ],
    'hm' => [
      'default' => [
        'default' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-NQ4JXJP',
          ],
          'social_auth_google.settings' => [
            'client_id' => '162325944786-f1go0fiukfja1rs44ajk5341r2omgocr.apps.googleusercontent.com',
            'client_secret' => '_C_obvqFmy2YHs6n2o-9hMgg',
          ],
          'dynamic_yield.settings' => [
            'section_id' => '9876643',
          ],
          'live' => [
            'exponea.settings' => [
              "name" => "H&M",
              "short_name" => "H&M",
            ],
            'dynamic_yield.settings' => [
              'section_id' => '9876644',
            ],
          ],
        ],
      ],
      'kw' => [
        'default' => [
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@hmkuwait',
            'tags.twitter_cards_site' => '@hmkuwait',
          ],
        ],
        'live' => [
          'alshaya_knet.settings' => [
            'alias' => 'hm',
          ],
        ],
        'uat' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-NQ4JXJP',
          ],
        ],
      ],
      'ae' => [
        'default' => [
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@hmuae',
            'tags.twitter_cards_site' => '@hmuae',
          ],
        ],
        'uat' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-5ZNGJRP',
          ],
        ],
      ],
      'sa' => [
        'default' => [
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@hmsaudiarabia',
            'tags.twitter_cards_site' => '@hmsaudiarabia',
          ],
        ],
        'uat' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-PXGWK9J',
          ],
        ],
      ],
      'eg' => [
        'default' => [
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@hmegypt',
            'tags.twitter_cards_site' => '@hmegypt',
          ],
        ],
        'uat' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-NSGRLVC',
          ],
        ],
      ],
    ],
    'bbw' => [
      'default' => [
        'default' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-KXQ8P3S',
          ],
          'social_auth_google.settings' => [
            'client_id' => '979778569503-3a6dfbjcfvm5er41fd4s46d27oqe8efl.apps.googleusercontent.com',
            'client_secret' => 'kSiVHuw6W4AGXnqW_aXWRFS9',
          ],
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@BBWMENA',
            'tags.twitter_cards_site' => '@BBWMENA',
          ],
          'dynamic_yield.settings' => [
            'section_id' => '9876649',
          ],
          'live' => [
            'exponea.settings' => [
              "name" => "Bath and Body Works",
              "short_name" => "BBW",
            ],
            'dynamic_yield.settings' => [
              'section_id' => '9876648',
            ],
          ],
        ],
      ],
    ],
    'fl' => [
      'default' => [
        'default' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-T8L97TK',
          ],
          'social_auth_google.settings' => [
            'client_id' => '489743492796-4euishl08asap26893fooomkqgi8ketl.apps.googleusercontent.com',
            'client_secret' => 'mT47obTKfcKzXIJSRcXyjdkx',
          ],
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@footlocker',
            'tags.twitter_cards_site' => '@footlocker',
          ],
          'live' => [
            'exponea.settings' => [
              "name" => "Footlocker",
              "short_name" => "FL",
            ],
          ],
          'algolia_sandbox.settings' => [
            'app_id' => 'VSW25YM48U',
            'write_api_key' => '1033c9bef964ede8cfd5ea308660d710',
            'search_api_key' => 'ad97d63e6036c6d66a4032f90780234f',
          ],
        ],
      ],
    ],
    'pb' => [
      'default' => [
        'default' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-M793JHJ',
          ],
          'social_auth_google.settings' => [
            'client_id' => '290156815523-4pqhofs87lrj62gucij8rko1dj61od0q.apps.googleusercontent.com',
            'client_secret' => 'YE8Y9NX3mli9p-ivnstUzBpG',
          ],
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@potterybarn',
            'tags.twitter_cards_site' => '@potterybarn',
          ],
          'live' => [
            'exponea.settings' => [
              "name" => "Pottery Barn",
              "short_name" => "PB",
            ],
          ],
        ],
      ],
    ],
    'vs' => [
      'default' => [
        'default' => [
          'social_auth_google.settings' => [
            'client_id' => '764146281003-4qh0d2dgj3cjfotf5fr8307bp0l0248g.apps.googleusercontent.com',
            'client_secret' => 'ZEZJZTxz9mmY0H0sAB03iJdo',
          ],
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_creator' => '@VictoriasSecret',
            'tags.twitter_cards_site' => '@VictoriasSecret',
          ],
          'dynamic_yield.settings' => [
            'section_id' => '9876647',
          ],
          'google_tag.container.primary' => [
            'container_id' => 'GTM-KLZ3ZQR',
          ],
          'live' => [
            'exponea.settings' => [
              "name" => "Victoria's Secret",
              "short_name" => "VS",
            ],
            'dynamic_yield.settings' => [
              'section_id' => '9876645',
            ],
          ],
        ],
      ],
    ],
    'pbk' => [
      'default' => [
        'default' => [
          'social_auth_google.settings' => [
            'client_id' => '702222333032-0htvmbqgnfjh8a7l0mashbnpmbnua446.apps.googleusercontent.com',
            'client_secret' => 'aJhYUR-qa9yRWUUj8L_wNK8k',
          ],
          'google_tag.container.primary' => [
            'container_id' => 'GTM-NSNS9H2',
          ],
          'algolia_sandbox.settings' => [
            'app_id' => 'SH0QY6KHZU',
            'write_api_key' => '0a0ab0dd399c62e21c66deaadf017943',
            'search_api_key' => 'fb1e411b7506a03ac9b7748b23e48e6e',
          ],
        ],
      ],
    ],
    'mu' => [
      'default' => [
        'default' => [
          'social_auth_google.settings' => [
            'client_id' => '470888570837-s24e1ldao2bhmkhjtbdcmh65aavqer9c.apps.googleusercontent.com',
            'client_secret' => '0KcWSq0fcdaKilaujMPB8a13',
          ],
          'google_tag.container.primary' => [
            'container_id' => 'GTM-NHZ6KSS',
          ],
          'algolia_sandbox.settings' => [
            'app_id' => '0VZKNNY9PK',
            'write_api_key' => '7e61a686834a4dd288e590008212a123',
            'search_api_key' => '9bfc987b54a4200a28ff91ddeb5fb27d',
          ],
        ],
      ],
    ],
    'we' => [
      'default' => [
        'default' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-M6BXN4X',
          ],
          'algolia_sandbox.settings' => [
            'app_id' => 'W4M368LMN4',
            'write_api_key' => '488027fdb499b2e711f58d169b5c2200',
            'search_api_key' => 'e8d102a1ed17ed7529cfa005db6e8b4a',
          ],
        ],
      ],
    ],
    'aeo' => [
      'default' => [
        'default' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-PJM7JG2',
          ],
          'algolia_sandbox.settings' => [
            'app_id' => 'JUO1UFS42Z',
            'write_api_key' => '49c687275158f614123556ff0afaaaa6',
            'search_api_key' => '6f5db46bc6cfa0a1e0768acf62e7a74d',
          ],
        ],
      ],
    ],
    'bp' => [
      'default' => [
        'default' => [
          'google_tag.container.primary' => [
            'container_id' => 'GTM-5HXC6Q2',
          ],
          'dynamic_yield.settings' => [
            'section_id' => '9877008',
          ],
          'live' => [
            'exponea.settings' => [
              "name" => "Boots",
              "short_name" => "BP",
            ],
            'dynamic_yield.settings' => [
              'section_id' => '9877013',
            ],
          ],
          'algolia_sandbox.settings' => [
            'app_id' => 'HAXEDKQC1A',
            'write_api_key' => '777c5fe45cecfb1e899bfe19f7d50fae',
            'search_api_key' => 'fe50afcd32a68ed75e7ab7c4aa04c6fd',
          ],
        ],
      ],
    ],
    'tbs' => [
      'default' => [
        'default' => [
          'dynamic_yield.settings' => [
            'section_id' => '9877876',
          ],
        ],
        'live' => [
          'dynamic_yield.settings' => [
            'section_id' => '9877879',
          ],
        ],
      ],
    ],
    'default' => [
      'default' => [
        'default' => [
          'alshaya_knet.settings' => [
            'alias' => 'alshaya',
          ],
          'google_tag.container.primary' => [
            'container_id' => '',
          ],
          'metatag.metatag_defaults.global' => [
            'tags.twitter_cards_type' => 'summary',
          ],
          'exponea.settings' => [
            "name" => "Alshaya",
            "short_name" => "AlshayaTest",
            "start_url" => "/",
            "display" => "standalone",
            "gcm_sender_id" => "130945280786",
          ],
          'algolia_sandbox.settings' => [
            'app_id' => 'testing24192T8KHZ',
            'write_api_key' => '1a3473b08a7e58f0b808fe4266e08187',
            'search_api_key' => '950ad607b0d79914702c82849af9a63f',
          ],
        ],
      ],
    ],
  ];
  // Get the settings following this fallback (from the more generic to the
  // more specific one): default+default+default > site+country+env.
  $settings = [];

  if (isset($mapping['default']['default']['default'])) {
    $settings = array_replace_recursive($settings, $mapping['default']['default']['default']);
  }
  if (isset($mapping['default']['default'][$env])) {
    $settings = array_replace_recursive($settings, $mapping['default']['default'][$env]);
  }
  if (isset($mapping['default'][$country_code]['default'])) {
    $settings = array_replace_recursive($settings, $mapping['default'][$country_code]['default']);
  }
  if (isset($mapping[$site_code]['default']['default'])) {
    $settings = array_replace_recursive($settings, $mapping[$site_code]['default']['default']);
  }
  if (isset($mapping[$site_code][$country_code]['default'])) {
    $settings = array_replace_recursive($settings, $mapping[$site_code][$country_code]['default']);
  }
  if (isset($mapping[$site_code]['default'][$env])) {
    $settings = array_replace_recursive($settings, $mapping[$site_code]['default'][$env]);
  }
  if (isset($mapping[$site_code][$country_code][$env])) {
    $settings = array_replace_recursive($settings, $mapping[$site_code][$country_code][$env]);
  }

  return $settings;
}
