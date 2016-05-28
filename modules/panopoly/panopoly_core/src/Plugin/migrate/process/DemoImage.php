<?php

namespace Drupal\panopoly_core\Plugin\migrate\process;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileStorageInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process plugin for creating demo images from files in the module.
 *
 * @MigrateProcessPlugin(
 *   id = "panopoly_demo_image"
 * )
 */
class DemoImage extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The migration plugin.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * The file entity storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The filesystem.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $filesystem;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, FileStorageInterface $file_storage, ModuleHandlerInterface $module_handler, FileSystemInterface $filesystem, ImageFactory $image_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
    $this->fileStorage = $file_storage;
    $this->moduleHandler = $module_handler;
    $this->filesystem = $filesystem;
    $this->imageFactory = $image_factory;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity_type.manager')->getStorage('file'),
      $container->get('module_handler'),
      $container->get('file_system'),
      $container->get('image.factory')
    );
  }

  /**
   * Removes trailing characters from given path.
   *
   * @param string $path
   *   The path to process.
   *
   * @return string
   *   The path without any trailing slashes.
   */
  protected function removeTrailing($path) {
    return rtrim($path, '/ ');
  }

  /**
   * Gets the source path (within the module providing the migration).
   *
   * @return string
   *   The path relative to the Drupal root.
   */
  protected function getSourcePath() {
    $module_path = $this->moduleHandler->getModule($this->migration->getPluginDefinition()['provider'])
      ->getPath();
    $relative_path = !empty($this->configuration['source_path']) ? $this->configuration['source_path'] : 'import/images';
    return $this->removeTrailing($module_path . '/' . $relative_path);
  }

  /**
   * Gets the destination path.
   *
   * @return string
   *   The destination path (using a stream wrapper).
   */
  protected function getDestinationPath() {
    if (!empty($this->configuration['destination_path'])) {
      return $this->removeTrailing($this->configuration['destination_path']);
    }
    return 'public://demo';
  }

  /**
   * Gets an existing file entity if there is one.
   *
   * @param string $uri
   *   The file URI (path).
   *
   * @return \Drupal\file\FileInterface|NULL
   */
  protected function findExistingFile($uri) {
    $files = $this->fileStorage->loadByProperties([
      'uri' => $uri,
    ]);
    if (!empty($files)) {
      return reset($files);
    }
    return NULL;
  }

  /**
   * Copy the file.
   *
   * @param $source_path
   *   The source path.
   * @param $destination_path
   *   The destination path.
   *
   * @return bool
   *   TRUE if successful; otherwise FALSE.
   */
  protected function copyFile($source_path, $destination_path) {
    $dir = $this->filesystem->dirname($destination_path);
    // Apparently, file_prepare_directory() can't deal with a protocol but no
    // path, so we convert it to the real path.
    if (substr($dir, -3) == '://') {
      $dir = $this->fileSystem->realpath($dir);
    }
    if (file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      return @copy($source_path, $destination_path);
    }
    return FALSE;
  }

  /**
   * Creates a new file from the source path.
   *
   * @param $source_path
   *   The source path.
   * @param $destination_path
   *   The destination path.
   *
   * @return \Drupal\file\FileInterface
   *   The file that was created
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function createFile($source_path, $destination_path) {
    if (!$this->copyFile($source_path, $destination_path)) {
      throw new MigrateException("Unable to copy {$source_path} to {$destination_path}");
    }

    $file = $this->fileStorage->create([
      'uri' => $destination_path,
      'status' => TRUE,
    ]);
    $file->save();

    return $file;
  }

  /**
   * Finds existing or creates a new file.
   *
   * @param $source_path
   *   The source path.
   * @param $destination_path
   *   The destination path.
   *
   * @return \Drupal\file\FileInterface
   *   The file that was found or created.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function findOrCreateFile($source_path, $destination_path) {
    $file = $this->findExistingFile($destination_path);
    if (!$file) {
      $file = $this->createFile($source_path, $destination_path);
    }
    return $file;
  }

  /**
   * Checks that a file exists and is readable.
   *
   * We're wrapping the filesystem so it's easier to test ::transform().
   *
   * @param string $path
   *   The path.
   *
   * @return bool
   *   TRUE if the file is good; FALSE otherwise.
   */
  protected function checkFile($path) {
    return file_exists($path);
  }

  /**
   * Gets the value of an optional property.
   *
   * @param $config_name
   *   The configuration key that holds the name of the source property.
   * @param \Drupal\migrate\Row $row
   *   The row.
   *
   * @return mixed|NULL
   *   Returns the value of the property or NULL if not found.
   */
  protected function getOptionalProperty($config_name, Row $row) {
    if (!empty($this->configuration[$config_name])) {
      return $row->getSourceProperty($this->configuration[$config_name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // The only required property is the filename property.
    if (empty($this->configuration['filename_property'])) {
      return [];
    }

    $filename = $row->getSourceProperty($this->configuration['filename_property']);
    $destination_path = $this->getDestinationPath() . '/' . $filename;
    $source_path = $this->getSourcePath() . '/' . $filename;

    if (!$this->checkFile($source_path)) {
      throw new MigrateException('Cannot find source image: ' . $source_path);
    }

    $file = $this->findOrCreateFile($source_path, $destination_path);

    $image = $this->imageFactory->get($destination_path);
    return [
      'target_id' => $file->id(),
      'display' => 1,
      'description' => $this->getOptionalProperty('description_property', $row) ?: '',
      'alt' => $this->getOptionalProperty('alt_property', $row) ?: '',
      'title' => $this->getOptionalProperty('title_property', $row) ?: '',
      'width' => $image->getWidth(),
      'height' => $image->getHeight(),
    ];
  }

}
