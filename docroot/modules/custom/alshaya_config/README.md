**Language specific config**
If we need to install the language specific or translated config for a simple
config, we can simply add a file with name like
language.config.LANGCODE.my_config_name.yml where LANGCODE is language code for
which config needs to be installed. This file pattern name is Drupal 8 core
standard and not something custom that we are doing. Then just pass that
language specific simple config to the alshaya_config_install_configs().

**Example -**
See **language.config.ar.alshaya_main_menu.settings.yml** in alshaya_main_menu
module which contains arabic translation of config
**alshaya_main_menu.settings.yml**. Then we can simply pass this to function
like -

`alshaya_config_install_configs(['language.config.ar.alshaya_main_menu.settings'], 'alshaya_main_menu')`
