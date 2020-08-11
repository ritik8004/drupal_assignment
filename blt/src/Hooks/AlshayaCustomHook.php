<?php

namespace Acquia\Blt\Custom\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Consolidation\AnnotatedCommand\CommandData;

/**
 * This class defines hooks.
 */
class AlshayaCustomHook extends BltTasks {

  /**
   * This will be called post `git:pre-commit` command is executed.
   *
   * @hook post-command internal:git-hook:execute:pre-commit
   */
  public function postGitPreCommit($result, CommandData $commandData) {
    $arguments = $commandData->arguments();
    if (!empty($arguments['changed_files'])) {
      $this->invokeCommand('tests:yaml:lint:files:paragraph', ['file_list' => $arguments['changed_files']]);
    }

    $failed = FALSE;
    $files = explode(PHP_EOL, $arguments['changed_files']);
    $patterns = [
      'react/alshaya_spc/js',
      'react/alshaya_pdp_react/js',
    ];
    foreach ($files as $file) {
      foreach ($patterns as $pattern) {
        if (strpos($file, $pattern) !== FALSE) {
          $paths = explode('react/', $file, 2);
          $output = $this->_exec('cd ' . $paths[0] . 'react; npm run lint ' . $paths[1]);
          if ($output->getExitCode() !== 0) {
            $failed = TRUE;
          }
        }
      }
    }

    if ($failed) {
      throw new \Exception('Please fix eslint errors described above.');
    }
  }

}
