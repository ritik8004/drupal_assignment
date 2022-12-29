<?php

namespace Alshaya\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Finder\Finder;
use Robo\Exception\TaskException;

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
      if ($result !== 0) {
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
    $exitCode = 0;
    $ignoredDirs = ['alshaya_example_subtheme', 'node_modules', 'gulp-tasks'];

    if (!in_array($type, self::$themeTypes)) {
      throw new \InvalidArgumentException('Type should be one of ' . implode(' / ', self::$themeTypes));
    }

    $dir = $this->getConfigValue('docroot') . '/themes/custom/' . $type;

    /** @var \DirectoryIterator $subDir */
    foreach (new \DirectoryIterator($dir) as $subDir) {
      if ($subDir->isDir()
        && !$subDir->isDot()
        && !(str_starts_with($subDir->getBasename(), '.'))
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
      // - we have some file changes at least.
      if (getenv('GITHUB_ACTIONS') == 'true'
        && getenv('GITHUB_EVENT_NAME') == 'push'
        && (int) getenv('CHANGED_ALL_FILES') > 0
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
          $cssFromDir = '/tmp/blt-deploy/' . substr($themePath, strpos($themePath, 'docroot'));
          // Building folder paths for copying.
          // In non_transac themes css is inside /dist folder.
          if ($type === 'non_transac') {
            // Only in whitelabel theme css is inside /components/dist folder.
            if (strpos($themePath, 'whitelabel') > -1) {
              $cssFromDir .= '/components/dist';
              $cssToDir = $themePath . '/components/dist';
            }
            else {
              $cssFromDir .= '/dist';
              $cssToDir = $themePath . '/dist';
            }
          }
          // In transac and transac_lite theme css is inside /css folder.
          else {
            $cssFromDir .= '/css';
            $cssToDir = $themePath . '/css';
          }

          // Copy step.
          $this->say('Copying unchanged ' . $themeName . ' theme from ' . $cssFromDir . ' to ' . $cssToDir);
          try {
            $result = $this->taskCopyDir([$cssFromDir => $cssToDir])
              ->overwrite(TRUE)
              ->run();
            // If copying failed preparing for build.
            if (!$result->wasSuccessful()) {
              $this->say('Unable to copy css files from cloud. Building theme ' . $themeName);
              $build = TRUE;
            }
          }
          catch (\Throwable $e) {
            $this->say('Unable to copy css files from cloud. Building theme ' . $themeName);
            $this->say('Error: ' . $e->getMessage());
            $build = TRUE;
          }
        }
      }
      // Build everything if
      // - we are outside github actions
      // - github create event has been triggered with tag push
      // - it is an empty commit
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
    if ($tasks->getCommand()) {
      $runTasks = $tasks->run();
      $exitCode = $runTasks->getExitCode();
    }
    return $exitCode;
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

      $build = FALSE;
      // Copy cloud code only if
      // - we are inside github actions
      // - github push event has been triggered
      // - we have some file changes at least.
      if (getenv('GITHUB_ACTIONS') == 'true'
        && getenv('GITHUB_EVENT_NAME') == 'push'
        && (int) getenv('CHANGED_ALL_FILES') > 0
      ) {
        $reactChanges = getenv('CHANGED_REACT_FILES');
        // Build if change in common (modules/react/js) folder.
        if (strpos($reactChanges, 'modules/react/js') > -1) {
          $build = TRUE;
        }
        // Build if theme is changed and tracked in CHANGED_REACT_FILES
        // env variable.
        elseif (strpos($reactChanges, 'react/' . $file->getRelativePath()) > -1) {
          $build = TRUE;
        }

        // Build if dependent react module has some changes.
        $dependencyFile = $dir . 'react_dependencies.txt';
        if ($build === FALSE && file_exists($dependencyFile)) {
          $dependencies = explode(PHP_EOL, file_get_contents($dependencyFile));
          foreach ($dependencies as $dependency) {
            if ($dependency && strpos($reactChanges, $dependency) > -1) {
              $build = TRUE;
              break;
            }
          }
        }

        // Else copy from acquia repo if build is not needed.
        if ($build === FALSE) {
          $reactFromDir = '/tmp/blt-deploy/' . substr($dir, strpos($dir, 'docroot')) . 'dist';
          $reactToDir = $dir . 'dist';

          // Copy step.
          $this->say('Copying unchanged ' . $file->getRelativePath() . ' react module from ' . $reactFromDir . ' to ' . $reactToDir);

          try {
            $result = $this->taskCopyDir([$reactFromDir => $reactToDir])
              ->overwrite(TRUE)
              ->run();
            if (!$result->wasSuccessful()) {
              $this->say('Unable to copy react files from cloud. Building react module ' . $file->getRelativePath());
              $build = TRUE;
            }
          }
          catch (\Throwable $e) {
            $this->say('Unable to copy react files from cloud. Building react module ' . $file->getRelativePath());
            $this->say('Error: ' . $e->getMessage());
            $build = TRUE;
          }
        }
      }
      // Build everything if
      // - we are outside github actions
      // - github create event has been triggered with tag push
      // - it is an empty commit
      //   in merge commit message.
      else {
        $build = TRUE;
      }

      // Build react files.
      if ($build) {
        $tasks->exec("cd $dir; $command");
      }
    }

    $tasks->stopOnFail();
    return $tasks->getCommand() ? $tasks->run() : 0;
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
   * Minify all SVG images inside modules and themes folder during deployment.
   *
   * To minify svg files manually, please check npm script svg-minify.
   *
   * @command alshayafe:minify-svg
   * @aliases minify-svg
   */
  public function buildMinifiedSvg() {
    $processOutput = 0;
    // Github CI env push event check.
    // Only proceed if we are in Github CI.
    if (getenv('GITHUB_ACTIONS') == 'true') {
      $tasks = $this->taskExecStack();
      $tasks->stopOnFail();

      // Process all svg files inside docroot/modules folder.
      $this->processSvgFiles(
        $tasks,
        'modules',
        [
          'brands',
          'commerce',
          'custom',
          'products',
        ]);
      // Process all svg files inside docroot/themes/custom folder.
      $this->processSvgFiles(
        $tasks,
        'themes/custom',
        self::$themeTypes
      );

      try {
        $tasks->stopOnFail();
        $processOutput = $tasks->run();
      }
      catch (TaskException $e) {
        // Ignore TaskException, we might simply be copying everything.
      }
    }
    else {
      $this->say('No need to minify svg files outside Github CI as we do it during deployments.');
    }

    return $processOutput;
  }

  /**
   * List all files in a directory.
   *
   * @param object $tasks
   *   Executable task object.
   * @param string $containingFolderPath
   *   Folder where its running like modules or themes.
   * @param array $subFolders
   *   Sub-folders inside modules or themes like custom, brands, non-transac.
   */
  private function processSvgFiles(object $tasks, string $containingFolderPath, array $subFolders) {
    $docroot = $this->getConfigValue('docroot');
    // Ignore these directories.
    $ignoredDirs = [
      'js',
      'alshaya_example_subtheme',
      'node_modules',
      'gulp-tasks',
    ];

    // Process svg files inside modules folder.
    $containingFolderPath = $docroot . '/' . $containingFolderPath;
    foreach ($subFolders as $singleFolder) {
      foreach (new \DirectoryIterator($containingFolderPath . '/' . $singleFolder) as $subDir) {
        $baseFolder = $subDir->getBasename();
        // Skip ignored directories and
        // directories that does not contain a svg file.
        if ($subDir->isDir()
          && !$subDir->isDot()
          && !in_array($baseFolder, $ignoredDirs)
          && (new Finder())->name('*.svg')->in($subDir->getRealpath())->hasResults()) {
          // Preparing the SVG files array which are present
          // in the latest commit.
          $svgFiles = !empty(getenv('CHANGED_SVG_FILES')) ? explode(" ", getenv('CHANGED_SVG_FILES')) : [];
          // Finding and looping through all the SVG files present in code-base.
          foreach (Finder::create()->followLinks()->files()->in($subDir->getRealpath())->name('/\.(svg)$/') as $file) {
            $minify = TRUE;
            // Checks to ensure to run the minify command only
            // to the files which are modified and failed during copy.
            $filePath = substr($file, strpos($file, 'docroot'));
            if (empty($svgFiles) || !in_array($filePath, $svgFiles)) {
              // Copy the unchanged files from cloud where
              // those files are already in minify state.
              try {
                $svgCloudFilePath = '/tmp/blt-deploy/' . $filePath;
                // Copy step.
                $this->say('Copying ' . $file . ' from ' . $svgCloudFilePath . ' to ' . $file);
                $result = $this->taskFilesystemStack()->copy($svgCloudFilePath, $file, TRUE)->run();
                // If copy is successful change minify flag as
                // false, as we don't need to execute minify command.
                if ($result->wasSuccessful()) {
                  $minify = FALSE;
                }
                else {
                  $this->say('Unable to copy following svg file from cloud: ' . $file);
                }
              }
              catch (\Throwable $e) {
                $this->say('Error: Unable to copy the following file - ' . $file . ', from ' . $svgCloudFilePath . ' to ' . $file);
                $this->say('Error Message: ' . $e->getMessage());
              }
            }
            // Execute minify command if it is true.
            if ($minify) {
              // Minify command.
              $tasks->exec("cd $docroot; npm run svg-minify-specific --filePath=" . $file->getRealPath());
            }
          }
        }
      }
    }
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
        || (str_starts_with($theme->getBasename(), '.'))
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
   * Test / Lint React changed files.
   *
   * @param string $changed_files
   *   Changed files..
   *
   * @command alshayafe:test-react:changed-files
   * @aliases test-react-changed-files
   */
  public function testOnlyChangedReactFiles(string $changed_files = '') {
    $reactDir = $this->getConfigValue('docroot') . '/modules/react';
    $tasks = $this->taskExecStack();

    // Flag to determine if there are files to lint.
    $files_to_lint = FALSE;

    $changed_react_js_files = explode(' ', $changed_files);

    // If there are files changed.
    if (!empty($changed_react_js_files)) {
      $relative_react_directory_path = 'docroot/modules/react/';
      $react_changed_files = array_filter($changed_react_js_files, function ($js_file) use ($relative_react_directory_path) {
        // Get the directory/module name of the file.
        $pos = strpos($js_file, "docroot/modules/react/");
        $dir_name = explode("/", substr($js_file, $pos))[3];
        // Get files only in react directory
        // and ignore webpack, node modules, react module files.
        return (
          str_contains($js_file, $relative_react_directory_path) &&
          !(str_contains($js_file, 'webpack.config.js') ||
            str_contains($js_file, 'node_modules') ||
            str_contains($js_file, 'alshaya_react') ||
            str_contains($js_file, $dir_name . '/assets/') ||
            str_contains($js_file, $dir_name . '/js/assets/'))
        );
      });

      // If there are files for linting.
      if (!empty($react_changed_files)) {
        $files_to_lint = TRUE;
        $files_for_linting = implode(' ', str_replace($relative_react_directory_path, '', $react_changed_files));

        $tasks->printTaskInfo('Changed JS files for linting - {changed_files}', [
          'changed_files' => $files_for_linting,
        ]);

        $tasks->exec("cd $reactDir; npm run lint $files_for_linting");
      }
    }

    // If there are no files to lint.
    if (!$files_to_lint) {
      $this->say("There are no files for linting.");
      return;
    }

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

    // Run Jest tests.
    $tasks->exec("cd $reactDir; npm test");

    $finder = new Finder();
    // Find all react folders containing the file used to specify entry points.
    $finder->name('webpack.config.js');
    // We need to find inside react code only.
    $files = $finder->in($reactDir);

    foreach ($files as $file) {
      // Ignore webpack.config.js found inside below folders.
      if (
        str_contains($file, 'node_modules')
        || str_contains($file, 'alshaya_react')
        || str_contains($file, 'alshaya_react_test')
      ) {
        continue;
      }
      $dir = str_replace('webpack.config.js', '', $file->getRealPath());
      // For module like alshaya_algolia_react we keep react files in src.
      $dir .= is_dir($dir . '/js/src') ? '/js/src' : '/js';

      $tasks->exec("cd $reactDir; npm run lint $dir");
    }

    $tasks->stopOnFail();
    $result = $tasks->run();
    return $result;
  }

  /**
   * Pre-compile handlebarjs Template.
   *
   * @command alshayafe:handlebars-build
   * @aliases handlebars-build, hbb
   */
  public function preCompileHandlebars() {
    $docroot = $this->getConfigValue('docroot');

    $tasks = $this->taskExecStack();

    $finder = new Finder();
    $finder->name('*.handlebars');
    // Find handlebar templates in custom and brand folder.
    $finder->in($docroot . '/modules/custom');
    $finder->in($docroot . '/modules/brands');

    // Execute pre-compile command in all dir.
    foreach ($finder as $file) {
      $handlebarFilePath = $file->getRealPath();
      // Pre-compiled path.
      $jsFilePath = str_replace(
        ["/handlebars/", '.handlebars'],
        ["/dist/", '.js'],
        $handlebarFilePath
      );
      $path_arr = explode('/', $jsFilePath);
      array_pop($path_arr);
      // Create dist folder if does not exists and
      // Pre-compile HandlebarsJs template.
      $dist_dir = implode('/', $path_arr);
      $tasks->exec("cd $docroot && mkdir -p $dist_dir &&
        ./modules/custom/node_modules/.bin/handlebars $handlebarFilePath -f $jsFilePath -n window.rcsHandlebarsTemplates"
      );
    }
    $tasks->stopOnFail();
    $result = $tasks->run();
    return $result;
  }

}
