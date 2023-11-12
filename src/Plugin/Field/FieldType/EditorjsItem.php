<?php

declare(strict_types=1);

namespace Drupal\editorjs\Plugin\Field\FieldType;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'editorjs' field type.
 *
 * @FieldType(
 *   id = "editorjs",
 *   label = @Translation("Editor JS"),
 *   category = @Translation("Text"),
 *   default_widget = "editorjs",
 *   default_formatter = "editorjs",
 *   cardinality = 1
 * )
 */
class EditorjsItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties[self::mainPropertyName()] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Raw value'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (!isset($values['value'])) {
      $values = ['value' => $values];
    }

    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    return Json::decode($this->get(self::mainPropertyName())->getValue());
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $formState) {
    $element = parent::fieldSettingsForm($form, $formState);
    $settings = $this->getFieldDefinition()->getSetting('tools') ?? [];
    /** @var \Drupal\Core\Plugin\DefaultPluginManager $toolsManager */
    $toolsManager = \Drupal::service('plugin.manager.editorjs_tools');

    $element['tools'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Tools Settings'),
    ];

    foreach (array_keys($toolsManager->getDefinitions()) as $pluginId) {
      /** @var \Drupal\editorjs\Plugin\EditorJsTools\EditorJsToolsInterface $tool */
      $tool = $toolsManager->createInstance($pluginId);
      $element['tools'][$pluginId]['status'] = [
        '#type' => 'checkbox',
        '#title' => $tool->label(),
        '#description' => $tool->description(),
        '#default_value' => $pluginId === 'paragraph' ? TRUE : $settings[$pluginId]['status'] ?? FALSE,
      ];
      $element['tools'][$pluginId]['settings'] = [
        '#type' => 'details',
        '#title' => $this->t('Settings for <b>@tool</b> tool', ['@tool' => $tool->label()]),
        '#tree' => TRUE,
        '#states' => [
          'visible' => [
            ":input[name='settings[tools][{$pluginId}][status]']" => ['checked' => TRUE],
          ],
        ],
      ] + $tool->settingsForm($settings[$pluginId]['settings'] ?? []);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings(): array {
    return [
      'tools' => [],
    ] + parent::defaultFieldSettings();
  }

}
