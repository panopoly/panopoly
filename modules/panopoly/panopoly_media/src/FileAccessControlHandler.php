<?php

namespace Drupal\panopoly_media;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\FileAccessControlHandler as CoreAccessControlHandler;

/**
 * Class FileAccessControlHandler.
 */
class FileAccessControlHandler extends CoreAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer files')) {
      return AccessResult::allowed();
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
