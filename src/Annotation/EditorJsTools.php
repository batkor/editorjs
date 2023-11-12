<?php

namespace Drupal\editorjs\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines editorjs_tools annotation object.
 *
 * @Annotation
 */
class EditorJsTools extends Plugin {

  /**
   * The plugin ID.
   */
  public string $id;

  /**
   * The human-readable name of the plugin.
   */
  public Translation $label;

  /**
   * The description of the plugin.
   */
  public Translation $description;

  /**
   * The name javascript class/object implementing this a tool.
   */
  public string $implementer;

  /**
   * The (optional) permission needed to use a tool.
   */
  public string $permission;

  /**
   * The tune-tools indicator.
   */
  public bool $tune;

}
