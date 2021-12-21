<?php

namespace Acquia\GithubActions\Blt\Plugin\EnvironmentDetector;

use Acquia\Blt\Robo\Common\EnvironmentDetector;

class GithubActionsDetector extends EnvironmentDetector {
    public static function getCiEnv() {
        return isset($_ENV['GITHUB_ACTIONS']) ? true : false;
    }

    public static function getCiSettingsFile() {
        return sprintf('%s/blt/settings/github.settings.php', dirname(DRUPAL_ROOT));
    }
}
