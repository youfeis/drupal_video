<?php

namespace Drupal\video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\video\ProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "video_embed_thumbnail",
 *   label = @Translation("Embedded Video Thumbnail"),
 *   field_types = {
 *     "video"
 *   }
 * )
 */
class VideoEmbedThumbnailFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The embed provider plugin manager.
   *
   * @var \Drupal\video_embed_field\ProviderManagerInterface
   */
  protected $providerManager;

  /**
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * Class constant for linking to content.
   */
  const LINK_CONTENT = 'content';

  /**
   * Class constant for linking to the provider URL.
   */
  const LINK_PROVIDER = 'provider';

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   * @param $langcode
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // load widget settings
    $field_definition = $this->fieldDefinition;
    $form_mode = 'default';
    $entity_form_display = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load($field_definition->getTargetEntityTypeId() . '.' . $field_definition->getTargetBundle() . '.' . $form_mode);
    if (!$entity_form_display) {
      $entity_form_display = \Drupal::entityTypeManager()
        ->getStorage('entity_form_display')
        ->create([
          'targetEntityType' => $field_definition->getTargetEntityTypeId(),
          'bundle' => $field_definition->getTargetBundle(),
          'mode' => $form_mode,
          'status' => TRUE,
        ]);
    }
    $widget = $entity_form_display->getRenderer($field_definition->getName());
    $widget_settings = $widget->getSettings();
    $element = [];
    foreach ($items as $delta => $item) {
      $file = File::load($item->target_id);
      $metadata = isset($item->data) ? unserialize($item->data) : [];
      $scheme = $this->streamWrapperManager->getScheme($file->getFileUri());
      $provider = $this->providerManager->loadProviderFromStream($scheme, $file, $metadata, $widget_settings);
      $url = FALSE;
      if ($this->getSetting('link_image_to') == static::LINK_CONTENT) {
        $url = $items->getEntity()->toUrl();
      }
      elseif ($this->getSetting('link_image_to') == static::LINK_PROVIDER) {
        $url = Url::fromUri(file_create_url($file->getFileUri()));
      }
      $element[$delta] = $provider->renderThumbnail($this->getSetting('image_style'), $url);
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'link_image_to' => ''
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = [];
    $form['image_style'] = [
      '#title' => $this->t('Image Style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#required' => TRUE,
      '#options' => image_style_options(),
    ];
    $form['link_image_to'] = [
      '#title' => $this->t('Link image to'),
      '#type' => 'select',
      '#empty_option' => $this->t('- None -'),
      '#default_value' => $this->getSetting('link_image_to'),
      '#options' => [
        static::LINK_CONTENT => $this->t('Content'),
        static::LINK_PROVIDER => $this->t('Provider URL'),
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Video thumbnail (@quality).', ['@quality' => $this->getSetting('image_style')]);
    return $summary;
  }

  /**
   * Constructs a new instance of the plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\video\ProviderManagerInterface $provider_manager
   *   The video embed provider manager.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *`  The stream wrapper manager.
   */
  public function __construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, ProviderManagerInterface $provider_manager, StreamWrapperManagerInterface $stream_wrapper_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->providerManager = $provider_manager;
    $this->streamWrapperManager = $stream_wrapper_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('video.provider_manager'),
      $container->get('stream_wrapper_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if(empty($field_definition->getTargetBundle())){
      return TRUE;
    }
    else{
      $form_mode = 'default';
      $entity_form_display = \Drupal::entityTypeManager()
        ->getStorage('entity_form_display')
        ->load($field_definition->getTargetEntityTypeId() . '.' . $field_definition->getTargetBundle() . '.' . $form_mode);
      if (!$entity_form_display) {
        $entity_form_display = \Drupal::entityTypeManager()
          ->getStorage('entity_form_display')
          ->create([
            'targetEntityType' => $field_definition->getTargetEntityTypeId(),
            'bundle' => $field_definition->getTargetBundle(),
            'mode' => $form_mode,
            'status' => TRUE,
          ]);
      }
      $widget = $entity_form_display->getRenderer($field_definition->getName());
      $widget_id = $widget->getBaseId();
      if($widget_id == 'video_embed'){
        return TRUE;
      }
    }
    return FALSE;
  }
}
