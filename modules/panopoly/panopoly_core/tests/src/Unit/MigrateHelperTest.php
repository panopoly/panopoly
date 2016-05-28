<?php

namespace Drupal\Tests\panopoly_core\Unit;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\panopoly_core\MigrateHelper;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for Panopoly Core's MigrateHelper service.
 *
 * @coversDefaultClass \Drupal\panopoly_core\MigrateHelper
 *
 * @group PanopolyCore
 */
class MigrateHelperTest extends UnitTestCase {

  /**
   * Mocked migration manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $migrationManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->migrationManager = $this->prophesize(MigrationPluginManagerInterface::class);
  }

  /**
   * Tests the getMessageObject() method.
   *
   * @covers ::getMessageObject
   */
  public function testGetMessageObject() {
    $migrate_helper = new MigrateHelper($this->migrationManager->reveal());

    $method = new \ReflectionMethod(MigrateHelper::class, 'getMessageObject');
    $method->setAccessible(TRUE);

    $message = $method->invoke($migrate_helper);
    $this->assertInstanceOf(MigrateMessageInterface::class, $message);
    $message2 = $method->invoke($migrate_helper);
    $this->assertSame($message, $message2);
  }

  /**
   * Tests the import() method
   *
   * @covers ::import
   */
  public function testImport() {
    $migration1 = $this->prophesize(MigrationInterface::class);
    $migration2 = $this->prophesize(MigrationInterface::class);

    $ids = ['migration1', 'migration2'];
    $this->migrationManager->createInstances($ids)
      ->willReturn([$migration1, $migration2]);

    $migrate_helper = $this->getMockBuilder(MigrateHelper::class)
      ->setConstructorArgs([$this->migrationManager->reveal()])
      ->setMethods(['createExecutable'])
      ->getMock();
    $executable = $this->prophesize(MigrateExecutableInterface::class);
    $executable->import()->shouldBeCalled();
    $migrate_helper->method('createExecutable')
      ->willReturn($executable->reveal());

    $migrate_helper->import($ids);
  }

  /**
   * Tests the rollback() method
   *
   * @covers ::rollback
   */
  public function testRollback() {
    $migration1 = $this->prophesize(MigrationInterface::class);
    $migration2 = $this->prophesize(MigrationInterface::class);

    $ids = ['migration1', 'migration2'];
    $this->migrationManager->createInstances(array_reverse($ids))
      ->willReturn([$migration1, $migration2]);

    $migrate_helper = $this->getMockBuilder(MigrateHelper::class)
      ->setConstructorArgs([$this->migrationManager->reveal()])
      ->setMethods(['createExecutable'])
      ->getMock();
    $executable = $this->prophesize(MigrateExecutableInterface::class);
    $executable->rollback()->shouldBeCalled();
    $migrate_helper->method('createExecutable')
      ->willReturn($executable->reveal());

    $migrate_helper->rollback($ids);
  }

}
