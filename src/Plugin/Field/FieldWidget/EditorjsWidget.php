<?php

declare(strict_types=1);

namespace Drupal\editorjs\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the 'editorjs' field widget.
 *
 * @FieldWidget(
 *   id = "editorjs",
 *   label = @Translation("EditorJs"),
 *   field_types = {"editorjs"},
 * )
 */
class EditorjsWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The manager of tools.
   */
  protected DefaultPluginManager $toolsManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $static = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $static->toolsManager = $container->get('plugin.manager.editorjs_tools');

    return $static;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $fieldSettings = $this->fieldDefinition->getSetting('tools');
    $tools = [];

    foreach ($fieldSettings as $toolId => $toolConfig) {
      if (empty($toolConfig['status'])) {
        continue;
      }

      /** @var \Drupal\editorjs\Plugin\EditorJsTools\EditorJsToolsInterface $tool */
      $tool = $this->toolsManager->createInstance($toolId);

      if (!empty($tool->implementer())) {
        $toolConfig['settings']['class'] = $tool->implementer();
      }

      if (array_key_exists('tunes', $toolConfig['settings']) && is_array($toolConfig['settings']['tunes'])) {
        $toolConfig['settings']['tunes'] = array_values(array_filter($toolConfig['settings']['tunes']));
      }

      $tools[$toolId] = $toolConfig['settings'];
    }

    $element['value'] = $element + [
      '#type' => 'editorjs',
      '#default_value' => $items->first()->get('value')->getValue(),
      '#tools' => $tools,
    ];

    return $element;
  }

}
