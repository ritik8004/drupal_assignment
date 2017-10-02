<?php

/**
 * @file
 * Hooks specific to the alshaya profile.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow all modules to do something post profile installation is finished.
 *
 * @param string $profile
 *   Profile name.
 * @param array $modules
 *   Modules explicitly installed in profile.
 */
function hook_alshaya_profile_installed($profile, array $modules) {

}

/**
 * Allow all modules to do something post child profile installation finished.
 *
 * @param string $profile
 *   Profile name.
 * @param array $modules
 *   Modules explicitly installed in profile.
 */
function hook_alshaya_profile_installed_final_task($profile, array $modules) {

}

/**
 * @} End of "addtogroup hooks".
 */
