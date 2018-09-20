<?php
namespace Drush\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Load this example by using the --include option - e.g. `drush --include=/path/to/drush/examples`
 */
class SiteAliasAlterCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * A few example alterations to site aliases.
   *
   * @hook pre-init *
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Consolidation\AnnotatedCommand\AnnotationData $annotationData
   */
  public function alter(InputInterface $input, AnnotationData $annotationData) {
    $options = $input->getOptions();

    // Set HTTP_HOST based on the site uri.
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?: $options['uri'];
  }
}