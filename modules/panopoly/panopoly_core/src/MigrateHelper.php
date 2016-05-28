<?php

namespace Drupal\panopoly_core;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;

/**
 * Service with some helper functions for working with migrate.
 */
class MigrateHelper {

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $migrationManager;

  /**
   * The migrate message object.
   *
   * @var \Drupal\migrate\MigrateMessageInterface
   */
  protected $migrateMessage;

  /**
   * Constructs the migration helper server.
   *
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_manager
   *   The migration manager.
   */
  public function __construct(MigrationPluginManagerInterface $migration_manager) {
    $this->migrationManager = $migration_manager;
  }

  /**
   * Gets the migrate message object.
   *
   * @return \Drupal\migrate\MigrateMessageInterface
   */
  protected function getMessageObject() {
    if (!$this->migrateMessage) {
      $this->migrateMessage = new MigrateMessage();
    }
    return $this->migrateMessage;
  }

  /**
   * Create a new migrate executable.
   *
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration.
   *
   * @return \Drupal\migrate\MigrateExecutableInterface
   *   The migrate executable.
   */
  protected function createExecutable(MigrationInterface $migration) {
    return new MigrateExecutable($migration, $this->getMessageObject());
  }

  /**
   * Imports the given migrations in order.
   *
   * @param string[] $migration_ids
   *   List of migration ids to import.
   */
  public function import(array $migration_ids) {
    $migrations = $this->migrationManager->createInstances($migration_ids);
    foreach ($migrations as $migration) {
      $executable = $this->createExecutable($migration);
      $executable->import();
    }
  }

  /**
   * Rolls back the given migrations in reverse order.
   *
   * @param array $migration_ids
   *   List of migration ids to rollback.
   */
  public function rollback(array $migration_ids) {
    // Do the migrations in reverse (half-ass dependency checking).
    $migration_ids = array_reverse($migration_ids);

    $migrations = $this->migrationManager->createInstances($migration_ids);
    foreach ($migrations as $migration) {
      $executable = $this->createExecutable($migration);
      $executable->rollback();
    }
  }

}
