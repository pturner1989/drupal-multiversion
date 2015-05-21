<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\Storage\Sql\NodeStorage.
 */

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;
use Drupal\node\NodeStorage as CoreNodeStorage;

/**
 * Defines the controller class for nodes.
 *
 * @todo Remove this, as it's not needed anymore.
 */
class NodeStorage extends CoreNodeStorage implements ContentEntityStorageInterface {

  use ContentEntityStorageTrait;

}