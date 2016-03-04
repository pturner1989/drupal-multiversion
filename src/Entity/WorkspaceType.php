<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\WorkspaceType.
 */

namespace Drupal\multiversion\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Workspace type entity.
 *
 * @ConfigEntityType(
 *   id = "workspace_type",
 *   label = @Translation("Workspace type"),
 *   handlers = {
 *     "list_builder" = "Drupal\multiversion\WorkspaceTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *     "form" = {
 *       "add" = "Drupal\multiversion\Form\WorkspaceTypeForm",
 *       "edit" = "Drupal\multiversion\Form\WorkspaceTypeForm",
 *       "delete" = "Drupal\multiversion\Form\WorkspaceTypeDeleteForm"
 *     }
 *   },
 *   config_prefix = "type",
 *   bundle_of = "workspace",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/workspaces/types/{workspace_type}/edit",
 *     "delete-form" = "/admin/structure/workspaces/types/{workspace_type}/delete",
 *     "collection" = "/admin/structure/workspaces/types"
 *   },
 *   config_export = {
 *      "id",
 *      "label",
 *    }
 * )
 */
class WorkspaceType extends ConfigEntityBundleBase implements WorkspaceTypeInterface {
  /**
   * The Workspace type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Workspace type label.
   *
   * @var string
   */
  protected $label;

}