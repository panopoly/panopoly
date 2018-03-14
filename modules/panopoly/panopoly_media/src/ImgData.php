<?php

namespace Drupal\panopoly_media;

/**
 * Class ImgData.
 */
class ImgData {

  /**
   * Form element mapping.
   *
   * @var array
   */
  protected $elementMap = [];

  /**
   * Mappings for IPTC attributes.
   *
   * @var array
   *
   * @TODO use plugin system to manage these properties.
   */
  protected $iptcMapping = [
    'title' => '2#005',
    'keywords' => '2#025',
    'copyright' => '2#116',
    'caption' => '2#120',
    'headline' => '2#105',
    'credit' => '2#110',
    'source' => '2#115',
    'jobtitle' => '2#085',
  ];

  /**
   * Gets an image's IPTC data.
   *
   * @param string $uri
   *   The uri of the image.
   *
   * @return array
   *   The IPTC data, keyed by attribute.
   *
   * @see \PHPExif\Adapter\Native
   */
  public function getData($uri) {
    $data = [];

    /** @var \Drupal\Core\File\FileSystemInterface $f */
    $f = \Drupal::service('file_system');
    $size = @getimagesize($f->realpath($uri), $info);

    // Ensure file and IPTC info is present.
    if (!$size || !$info || !isset($info['APP13'])) {
      return $data;
    }

    // Ensure IPTC could be parsed.
    if (!$iptc = iptcparse($info["APP13"])) {
      return $data;
    }

    // Map attributes into sensible structure.
    foreach ($this->iptcMapping as $name => $field) {
      if (!isset($iptc[$field])) {
        continue;
      }

      if (count($iptc[$field]) === 1) {
        $val = trim(reset($iptc[$field]));
        if (!empty($val)) {
          $data[$name] = reset($iptc[$field]);
        }
      }
      else {
        $data[$name] = $iptc[$field];
      }
    }

    return $data;
  }

  /**
   * Provides a mapping of form elements to IPTC data.
   *
   * @param string $entityType
   *   The entity type that the widget is placed on.
   *
   * @return array
   *   The form element map.
   *
   * @TODO: add persistent caching.
   */
  public function getElementMap($entityType) {
    if (!isset($this->elementMap[$entityType])) {
      $map = [
        'name' => [
          'iptc' => 'name',
          'formElement' => ['name', 0, 'value'],
        ],
        'alt' => [
          'iptc' => 'headline',
          'element' => ['alt'],
        ],
        'description' => [
          'iptc' => 'caption',
          'formElement' => ['field_panopoly_media_description', 0, 'value'],
        ],
      ];
      \Drupal::moduleHandler()->alter('panopoly_media_iptc_mapping', $map, $entityType);
      $this->elementMap[$entityType] = $map;
    }

    return $this->elementMap[$entityType];
  }

}
