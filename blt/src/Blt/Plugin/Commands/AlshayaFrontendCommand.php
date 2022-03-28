<?php

namespace Alshaya\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Finder\Finder;

/**
 * Defines commands in the "alshayafe" namespace.
 */
class AlshayaFrontendCommand extends BltTasks {

  /**
   * Support theme types.
   *
   * @var string[]
   */
  protected static $themeTypes = [
    'transac',
    'non_transac',
    'transac_lite',
  ];

  /**
   * Directories to ignore for Theme.
   *
   * @var string[]
   */
  protected static $themeIgnoredDirs = [
    'alshaya_example_subtheme',
    'node_modules',
  ];

  /**
   * Directories to ignore for React.
   *
   * @var string[]
   */
  protected static $reactIgnoredDirs = [
    'alshaya_react',
    'js',
    'dist',
    'node_modules',
    '__tests__',
  ];

  /**
   * Setup Themes.
   *
   * @command alshayafe:setup-themes
   * @aliases setup-themes
   */
  public function setupThemes() {
    $dir = $this->getConfigValue('docroot') . '/themes/custom';
    $task = $this->taskExec("cd $dir; npm install --unsafe-perm=true");

    return $task->run();
  }

  /**
   * Setup React.
   *
   * @command alshayafe:setup-react
   * @aliases setup-react
   */
  public function setupReact() {
    $dir = $this->getConfigValue('docroot') . '/modules/react';
    $task = $this->taskExec('npm install');
    $task->dir($dir);
    $result = $task->run();
    $this->say($result->getOutputData());
    $this->say($result->getMessage());
    return $result;
  }

  /**
   * Setup JS Uglification.
   *
   * @command alshayafe:setup-uglify
   * @aliases setup-uglify
   */
  public function setupUglification() {
    $dir = $this->getConfigValue('docroot');
    $task = $this->taskExec('npm install');
    $task->dir($dir);
    $result = $task->run();
    $this->say($result->getOutputData());
    $this->say($result->getMessage());
    return $result;
  }

  /**
   * Setup all for Alshaya.
   *
   * @command alshayafe:setup
   * @aliases alshaya-setup
   */
  public function setup() {
    $result = $this->setupReact();
    if ($result->getExitCode() !== 0) {
      return 1;
    }

    $result = $this->setupThemes();
    if ($result->getExitCode() !== 0) {
      return 1;
    }

    return 0;
  }

  /**
   * Build styles for all the themes.
   *
   * @command alshayafe:build-all-themes
   * @aliases build-all-themes
   * @description Build themes for all the themes.
   */
  public function buildStyles() {
    foreach (self::$themeTypes as $type) {
      $result = $this->buildThemesOfType($type);
      if ($result->getExitCode() !== 0) {
        return 1;
      }
    }

    return 0;
  }

  /**
   * Build styles for all the themes of a particular type.
   *
   * @param string $type
   *   Theme type.
   *
   * @command alshayafe:build-themes
   * @aliases build-themes
   * @description Build styles for all the themes of a particular type.
   */
  public function buildThemesOfType(string $type) {
    $ignoredDirs = ['alshaya_example_subtheme', 'node_modules', 'gulp-tasks'];

    if (!in_array($type, self::$themeTypes)) {
      throw new \InvalidArgumentException('Type should be one of ' . implode(' / ', self::$themeTypes));
    }

    $dir = $this->getConfigValue('docroot') . '/themes/custom/' . $type;

    /** @var \DirectoryIterator $subDir */
    foreach (new \DirectoryIterator($dir) as $subDir) {
      if ($subDir->isDir()
        && !$subDir->isDot()
        && !(strpos($subDir->getBasename(), '.') === 0)
        && !in_array($subDir->getBasename(), $ignoredDirs)
        && file_exists($subDir->getRealPath() . '/gulpfile.js')) {
        $themes[$subDir->getBasename()] = $subDir->getRealPath();
      }
    }

    // Execute in sequence to see errors if any.
    $tasks = $this->taskExecStack();

    foreach ($themes ?? [] as $themeName => $themePath) {
      $build = FALSE;
      // Copy cloud code only if
      // - we are inside github actions
      // - github push event has been triggered
      // - we have some file changes at least
      // - 'BUILD REQUEST' comment is not present in merge commit message.
      if (getenv('GITHUB_ACTIONS') == 'true'
        && getenv('GITHUB_EVENT_NAME') == 'push'
        && !empty(getenv('CHANGED_ALL_FILES'))
        && strpos(getenv('COMMIT_MESSAGE'), 'BUILD REQUEST') === FALSE
      ) {
        $themeChanges = getenv('CHANGED_THEME_FILES');
        // Build if theme is changed and tracked in CHANGED_THEME_FILES
        // env variable.
        if (strpos($themeChanges, $type . '/' . $themeName) > -1) {
          $build = TRUE;
        }
        // Build all transac themes if alshaya_white_label themes changed.
        elseif ($type == 'transac' && strpos($themeChanges, 'transac/alshaya_white_label') > -1) {
          $build = TRUE;
        }
        // Build all non-transac themes if white_label themes changed.
        elseif ($type == 'non_transac' && strpos($themeChanges, 'transac/whitelabel') > -1) {
          $build = TRUE;
        }

        // Else copy from acquia repo if build is not needed.
        if ($build === FALSE) {
          $cssFromDir = str_replace('docroot', 'docroot/../deploy/docroot', $themePath);
          $cssToDir = $themePath;
          // Building folder paths for copying.
          // In non_transac themes css is inside /dist folder.
          if ($type === 'non_transac') {
            $cssFromDir .= '/dist';
            // Only in whitelabel theme css is inside /components/dist folder.
            if (strpos($themePath, 'whitelabel') > -1) {
              $cssFromDir = str_replace('/dist', '/components/dist', $themePath);
              $cssToDir .= '/components';
            }
          }
          // In transac and transac_lite theme css is inside /css folder.
          else {
            $cssFromDir .= '/css';
          }

          // Copy step.
          $this->say('Copying unchanged ' . $themeName . ' theme from ' . $cssFromDir . ' to ' . $cssToDir);
          $result = $this->taskCopyDir([$cssFromDir => $cssToDir])
            ->overwrite(TRUE)
            ->run();
          // If copying failed preparing for build.
          if (!$result->wasSuccessful()) {
            $this->say('Unable to copy css files from cloud. Building theme ' . $themeName);
            $build = TRUE;
          }
        }
      }
      // Build everything if
      // - we are outside github actions
      // - github create event has been triggered with tag push
      // - it is an empty commit
      // - reviewer requested a force build by commenting 'BUILD REQUEST'
      //   in merge commit message.
      else {
        $build = TRUE;
      }

      // Build theme css.
      if ($build) {
        $fullBuildCommand = sprintf('cd %s; npm run build', $themePath);
        $tasks->exec($fullBuildCommand);
      }
    }

    $tasks->stopOnFail();
    return $tasks->getCommand() ? $tasks->run() : 0;
  }

  /**
   * Build styles for specific themes of a particular type.
   *
   * @param string $type
   *   Theme type.
   * @param string $theme
   *   Theme name.
   *
   * @command alshayafe:build-theme
   * @aliases build-theme
   * @description Build styles for all the themes of a particular type.
   */
  public function buildTheme(string $type, string $theme) {
    $dir = $this->getConfigValue('docroot') . "/themes/custom/$type/";

    if (!is_dir($dir . $theme)) {
      throw new \InvalidArgumentException('Theme not available.');
    }

    $themes = [];
    if ($type === 'transac') {
      $themes[] = $dir . 'alshaya_white_label';
    }
    $themes[] = $dir . $theme;

    foreach ($themes ?? [] as $theme) {
      $this->taskExec('npm run build')
        ->dir($theme)
        ->run();
    }
  }

  /**
   * Build react files.
   *
   * @param string $mode
   *   Mode for building.
   *
   * @command alshayafe:build-react
   * @aliases build-react
   */
  public function buildReact($mode = 'production') {
    $command = $mode === 'dev'
      ? 'npm run build:dev'
      : 'npm run build';

    $tasks = $this->taskExecStack();
    $docroot = $this->getConfigValue('docroot');

    $ignore_dirs = ['node_modules', 'alshaya_react_test'];

    $finder = new Finder();
    $finder->name('webpack.config.js');
    foreach ($finder->in($docroot . '/modules/react') as $file) {
      $dir = str_replace('webpack.config.js', '', $file->getRealPath());

      foreach ($ignore_dirs as $ignore_dir) {
        if (strpos($dir, $ignore_dir) > -1) {
          continue 2;
        }
      }

      $tasks->exec("cd $dir; $command");
    }

    $tasks->stopOnFail();
    return $tasks->run();
  }

  /**
   * Build JS files for uglification.
   *
   * @param string $path
   *   Specific file path for JS uglification.
   *
   * @command alshayafe:build-uglify
   * @aliases build-uglify
   */
  public function buildJsUglification(string $path = '') {
    $dir = $this->getConfigValue('docroot');
    $cmd = 'npm run build';

    // Build specific paths instead of whole docroot.
    if (!empty($path)) {
      // Set path relative to docroot.
      $relative = explode('docroot/', $path, 2);
      if (count($relative) === 2) {
        $path = $relative[1];
      }
      $cmd = $cmd . ' -- --path=' . $path;
    }

    $task = $this->taskExec($cmd);
    $task->dir($dir);
    $result = $task->run();
    $this->say($result->getOutputData());
    $this->say($result->getMessage());
    return $result;
  }

  /**
   * Test Theme files.
   *
   * @command alshayafe:test-themes
   * @aliases test-themes
   */
  public function testThemes() {
    $tasks = $this->taskExecStack();
    $tasks->stopOnFail();

    // @todo increase scope of testing to all theme types.
    $dir = $this->getConfigValue('docroot') . '/themes/custom/transac';

    foreach (new \DirectoryIterator($dir) as $theme) {
      if ($theme->isDot()
        || (strpos($theme->getBasename(), '.') === 0)
        || in_array($theme->getBasename(), self::$themeIgnoredDirs)
        || !file_exists($theme->getRealPath() . '/gulpfile.js')) {
        continue;
      }

      $theme_dir = $theme->getRealPath();
      $tasks->exec("cd $theme_dir; npm run lint");
    }

    $tasks->stopOnFail();
    return $tasks->run();
  }

  /**
   * Test Theme files.
   *
   * @param string $name
   *   Theme Name.
   *
   * @command alshayafe:test-theme
   * @aliases test-theme
   */
  public function testTheme(string $name) {
    // @todo increase scope of testing to all theme types.
    $dir = $this->getConfigValue('docroot') . '/themes/custom/transac/' . $name;

    if (!is_dir($dir)) {
      throw new \InvalidArgumentException($dir . ' does not exist.');
    }

    $tasks = $this->taskExecStack();
    $tasks->stopOnFail();
    $tasks->exec("cd $dir; npm run lint");
    $tasks->stopOnFail();
    return $tasks->run();
  }

  /**
   * Test / Lint React files.
   *
   * @command alshayafe:test-react
   * @aliases test-react
   */
  public function testReact() {
    $reactDir = $this->getConfigValue('docroot') . '/modules/react';

    $tasks = $this->taskExecStack();
    $tasks->stopOnFail();

    // Validate utility files.
    $tasks->exec("cd $reactDir; npm run lint $reactDir/js/");

    foreach (new \DirectoryIterator($reactDir) as $subDir) {
      if ($subDir->isDir()
        && strpos($subDir->getBasename(), '.') === FALSE
        && !in_array($subDir->getBasename(), self::$reactIgnoredDirs)) {
        $pattern = $reactDir . '/' . $subDir->getBasename() . '/js';

        // For module like alshaya_algolia_react we have react files in src.
        if (is_dir($subDir->getRealPath() . '/js/src')) {
          $pattern .= '/src';
        }

        $tasks->exec("cd $reactDir; npm run lint $pattern");
      }
    }

    $tasks->stopOnFail();
    $result = $tasks->run();
    return $result;
  }

}
