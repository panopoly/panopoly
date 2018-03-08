<?php

namespace Drupal\panopoly_media\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget as CoreImageWidget;

/**
 * Class ImageWidget.
 *
 * Used in place of the core image widget.
 */
class ImageWidget extends CoreImageWidget {

  /**
   * {@inheritdoc}
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $element = parent::process($element, $form_state, $form);
    $entityType = $element['#entity_type'];

    // Only deal with media entities for now.
    if ($entityType != 'media') {
      return $element;
    }

    // If we've got files, use the first one.
    if (!empty($element['#files'])) {
      $files = $element['#files'];
      /** @var \Drupal\file\Entity\File $file */
      $file = reset($files);

      // Add IPTC handling to form.
      $iptc = self::getDataService()->getData($file->getFileUri());
      $iptc['name'] = empty($iptc['title']) ? $file->getFilename() : $iptc['title'];

      $parents = array_slice($element['#parents'], 0, -2);

      // Allow JS processing.
      $element['#attached']['library'][] = 'panopoly_media/img_data';
      $element['panopoly_media_img_data'] = [
        '#type' => 'container',
        '#attributes' => [
          'data-type' => ['panopolyMediaImgData'],
          'data-entity-type' => $entityType,
          'data-form-parents' => $parents,
        ],
      ];
      foreach (array_filter($iptc) as $property => $value) {
        $element['panopoly_media_img_data']['#attributes']['data-iptc-' . $property] = $value;
      }

      $map = static::getDataService()->getElementMap($entityType);
      $element['#attached']['drupalSettings']['panopolyMediaImgDataMap'][$entityType] = $map;

      // Allow the IPTC values to be altered.
      static::getModuleHandler()->alter('panopoly_media_iptc_values', $iptc, $entityType);

      // Set IPTC data onto the backend form handling.
      foreach ($map as $mapData) {
        if (isset($mapData['element'])) {
          // Set directly into the widget's element.
          $name = array_merge($mapData['element'], ['#default_value']);
          $val = NestedArray::getValue($element, $name);
          if (empty($val)) {
            NestedArray::setValue($element, $name, $iptc[$mapData['iptc']]);
          }
        }
      }
    }

    return $element;
  }

  /**
   * Gets the module handler service.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler service.
   */
  protected static function getModuleHandler() {
    return \Drupal::moduleHandler();
  }

  /**
   * Gets the image data service.
   *
   * @return \Drupal\panopoly_media\ImgData
   *   The image data service.
   */
  protected static function getDataService() {
    return \Drupal::service('panopoly_media.img_data');
  }

}
