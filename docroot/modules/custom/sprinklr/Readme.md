# INTRODUCTION
Purpose of this module is to integrate sprinklr chatbot on the site.

# REQUIREMENTS
It requires only Drupal CORE.

# INSTALLATION
Install as any other contrib module, no specific configuration required for
installation.

# CONFIGURATION
* Visit /admin/config/system/sprinklr to configure.
* Uncheck the "Enable sprinklr chatbot" checkbox to disable the feature.
* Enter "App Id" to connect with sprinklr chatbot.
  For Multilingual Site - Add translations for "App Id" admin/config/system/sprinklr/translate 
  if different App Ids are required.
* Configure URLs list to enable sprinklr chatbot on specific pages.
* Click "Save configuration" to apply your changes.

# OVERRIDE CHATBOT FEATURES
It is possible to override chatbot feature by implementing an event listener
(will be added soon)
