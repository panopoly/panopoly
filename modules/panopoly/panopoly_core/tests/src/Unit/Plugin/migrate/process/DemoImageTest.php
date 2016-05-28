<?php

namespace Drupal\Tests\panopoly_core\Unit\Plugin\migrate\process;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Image\ImageInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileStorageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\panopoly_core\Plugin\migrate\process\DemoImage;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the 'panopoly_demo_image' migrate process plugin.
 *
 * @coversDefaultClass \Drupal\panopoly_core\Plugin\migrate\process\DemoImage
 *
 * @group PanopolyCore
 */
class DemoImageTest extends UnitTestCase {

  /**
   * Mock of the migration class.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $migration;

  /**
   * Mock of the file entity storage.
   *
   * @var \Drupal\file\FileStorageInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $fileStorage;

  /**
   * Mock of the module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $moduleHandler;

  /**
   * Mock of the filesystem service.
   *
   * @var \Drupal\Core\File\FileSystemInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $filesystem;

  /**
   * Mock of the image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $imageFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // @todo: This seems wrong. We just need the FILE_STATUS_PERMANENT constant.
    //        Should this be a kernel test instead?
    require_once './includes/file.inc';

    $this->migration = $this->prophesize(MigrationInterface::class);
    $this->fileStorage = $this->prophesize(FileStorageInterface::class);
    $this->moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $this->filesystem = $this->prophesize(FileSystemInterface::class);
    $this->imageFactory = $this->prophesize(ImageFactory::class);
  }

  /**
   * Creates a new plugin to test.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param array|NULL $methods
   *   The list of methods to mock.
   *
   * @return \Drupal\panopoly_core\Plugin\migrate\process\DemoImage|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function createPlugin(array $configuration, array $methods = NULL) {
    return $this->getMockBuilder(DemoImage::class)
      ->setConstructorArgs([
        $configuration,
        'demo_image',
        [],
        $this->migration->reveal(),
        $this->fileStorage->reveal(),
        $this->moduleHandler->reveal(),
        $this->filesystem->reveal(),
        $this->imageFactory->reveal(),
      ])
      ->setMethods($methods)
      ->getMock();
  }

  /**
   * Invokes a normally inaccessible method.
   *
   * @param object $object
   *   Object to invoke the method on.
   * @param string $method
   *   The name of the method.
   * @param array $arguments
   *   The arguments to pass to the method.
   *
   * @return mixed
   *   The result of the invoked method.
   */
  protected function invokeMethod($object, $method, array $arguments = []) {
    $method = new \ReflectionMethod(get_class($object), $method);
    $method->setAccessible(TRUE);
    return $method->invokeArgs($object, $arguments);
  }

  /**
   * Tests a successful call to transform().
   *
   * @covers ::transform
   */
  public function testTransformSuccess() {
    $image = $this->prophesize(ImageInterface::class);
    $image->getWidth()
      ->willReturn(300);
    $image->getHeight()
      ->willReturn(200);
    $this->imageFactory->get('public://demo_images/image.jpg')
      ->willReturn($image);

    $executable = $this->prophesize(MigrateExecutableInterface::class);
    $row = $this->prophesize(Row::class);
    $row->getSourceProperty('image_filename')
      ->willReturn('image.jpg');

    $plugin = $this->createPlugin([
      'filename_property' => 'image_filename'
    ], [
      'getSourcePath',
      'getDestinationPath',
      'checkFile',
      'findOrCreateFile',
      'getOptionalProperty'
    ]);

    $plugin->method('getSourcePath')
      ->willReturn('modules/demo_module/images');

    $plugin->method('getDestinationPath')
      ->willReturn('public://demo_images');

    $plugin->method('checkFile')
      ->willReturn(TRUE);

    $file = $this->prophesize(File::class);
    $file->id()->willReturn(32);
    $plugin->method('findOrCreateFile')
      ->with('modules/demo_module/images/image.jpg', 'public://demo_images/image.jpg')
      ->willReturn($file->reveal());

    $plugin->expects($this->exactly(3))->method('getOptionalProperty')
      ->willReturnMap([
        ['description_property', $row->reveal(), NULL],
        ['alt_property', $row->reveal(), 'This is the alternative text'],
        ['title', $row->reveal(), ''],
      ]);

    $result = $plugin->transform(NULL, $executable->reveal(), $row->reveal(), 'field_image');

    $this->assertEquals([
      'target_id' => 32,
      'display' => 1,
      'description' => '',
      'alt' => 'This is the alternative text',
      'title' => '',
      'width' => 300,
      'height' => 200,
    ], $result);
  }

  /**
   * Tests calling transform() with bad configuration.
   *
   * @covers ::transform
   */
  public function testTransformBadConfiguration() {
    $plugin = $this->createPlugin([]);

    $executable = $this->prophesize(MigrateExecutableInterface::class);
    $row = $this->prophesize(Row::class);

    $this->assertEquals([], $plugin->transform(NULL, $executable->reveal(), $row->reveal(), 'field_image'));
  }

  /**
   * Tests that transform() throws an exception when a file is inaccessible.
   *
   * @covers ::transform
   *
   * @expectedException \Drupal\migrate\MigrateException
   * @expectedExceptionMessage Cannot find source image: modules/demo_module/images/image.jpg
   */
  public function testTransformBadSourceFile() {
    $executable = $this->prophesize(MigrateExecutableInterface::class);
    $row = $this->prophesize(Row::class);
    $row->getSourceProperty('image_filename')
      ->willReturn('image.jpg');

    $plugin = $this->createPlugin([
      'filename_property' => 'image_filename'
    ], [
      'getSourcePath',
      'getDestinationPath',
      'checkFile',
    ]);

    $plugin->method('getSourcePath')
      ->willReturn('modules/demo_module/images');

    $plugin->method('getDestinationPath')
      ->willReturn('public://demo_images');

    $plugin->method('checkFile')
      ->willReturn(FALSE);

    $plugin->transform(NULL, $executable->reveal(), $row->reveal(), 'field_image');
  }

  /**
   * Tests the getOptionalProperty() method.
   *
   * @covers ::getOptionalProperty
   */
  public function testGetOptionalProperty() {
    $plugin = $this->createPlugin([
      'test_property' => 'source_property_name',
    ]);

    $row = $this->prophesize(Row::class);
    $row->getSourceProperty('source_property_name')
      ->willReturn('test_value');

    $this->assertEquals('test_value', $this->invokeMethod($plugin, 'getOptionalProperty', ['test_property', $row->reveal()]));
  }
}
