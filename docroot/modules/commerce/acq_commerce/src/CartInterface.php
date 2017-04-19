<?php

namespace Drupal\acq_commerce;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Cart entity.
 *
 * @ingroup acq_commerce
 */
interface CartInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
