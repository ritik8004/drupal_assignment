## Reason for moving contrib module to custom

Module machine_name_widget adds a [feature][feature] to Drupal core which is
not yet merged to core yet. This module takes a [patch][patch] from
[this][feature] issue queue and adds it as a dependency in composer.json. But
this [patch][patch] introduces a new [issue][issue] which can be fixed with the
latest available [patch][newpatch]. Since patching occurs after Composer
resolves dependencies and installs packages, changes to an underlying 
dependency's composer.json file introduced in a patch will have no effect on
installed packages. So patching a composer.json will not work. A forked repo of
the issue would work but its not reliable. So machine_name_widget have been
moved to custom and the [patch][newpatch] for composer.json have been directly
to project's main composer.json file with the label "Issue 2685749: Add a 
Machine Name widget to core."

Module block_content_machine_name also have been moved to custom since it has
its own dependency to machine_name_widget, defined in its composer.json. Since
this will again require machine_name_widget which would install the issuable 
core patch, it has been moved to custom.


## Future Resolution Options

1. Move the modules back to contrib after [issue][issue] gets fixed when the
patch is merged and remove the patch "Issue 2685749: Add a Machine Name widget
to core." from main composer.json file.

2. Remove the dependency of machine_name_widget module and uninstall it
completely if underlying core [patch][newpatch] gets merged to Drupal core
itself. Then remove the patch "Issue 2685749: Add a Machine Name widget
to core." from main composer.json file.



[feature]: https://www.drupal.org/project/drupal/issues/2685749
[patch]: https://www.drupal.org/files/issues/2019-12-16/2685749-73.patch
[issue]: https://www.drupal.org/project/machine_name_widget/issues/3177775
[newpatch]: https://www.drupal.org/files/issues/2020-10-01/2685749-85.patch
