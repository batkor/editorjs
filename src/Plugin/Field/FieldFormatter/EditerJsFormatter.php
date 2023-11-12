<?php

declare(strict_types=1);

namespace Drupal\editorjs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'EditerJs' formatter.
 *
 * @FieldFormatter(
 *   id = "editorjs",
 *   label = @Translation("EditerJs"),
 *   field_types = {
 *     "editorjs"
 *   }
 * )
 */
class EditerJsFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if ($items->isEmpty()) {
      return [];
    }

    $element[] = [
      '#theme' => 'editorjs',
      '#data' => $items->first()->toArray(),
      '#tools' => array_keys($this->fieldDefinition->getSetting('tools')),
    ];

    return $element;
  }

}
