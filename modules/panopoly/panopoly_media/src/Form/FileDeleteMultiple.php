<?php

namespace Drupal\panopoly_media\Form;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a file deletion confirmation form.
 */
class FileDeleteMultiple extends ConfirmFormBase {

  /**
   * The array of files to delete.
   *
   * @var array
   */
  protected $fileInfo;

  /**
   * File usage service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityTypeManager $manager
   *   The entity manager.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   The file usage service.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityTypeManager $manager, FileUsageInterface $file_usage) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->storage = $manager->getStorage('file');
    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('entity_type.manager'),
      $container->get('file.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'panopoly_media_file_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->fileInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromUri('internal://admin/content/files');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->fileInfo = $this->tempStoreFactory
      ->get('panopoly_media_file_multiple_delete_confirm')
      ->get($this->getCurrentUser()->id());
    if (empty($this->fileInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }

    /** @var \Drupal\node\NodeInterface[] $nodes */
    $files = $this->storage->loadMultiple($this->fileInfo);

    if ($this->filesHaveUsage($files)) {
      $form['warning'] = [
        '#theme' => 'status_messages',
        // @todo Improve when https://www.drupal.org/node/2278383 lands.
        '#message_list' => ['warning' => [$this->t('One or more of these files have usages recorded. Deleting may affect content that attempts to reference these files.')]],
        '#status_headings' => [
          'status' => $this->t('Status message'),
          'error' => $this->t('Error message'),
          'warning' => $this->t('Warning message'),
        ],
      ];
    }

    $items = [];
    foreach ($this->fileInfo as $fid) {
      if (!empty($files[$fid])) {
        $items[$fid] = $files[$fid]->label();
      }
    }

    $form['files'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->fileInfo)) {
      $count = 0;
      $files = $this->storage->loadMultiple($this->fileInfo);

      foreach ($this->fileInfo as $fid) {
        if (empty($files[$fid])) {
          break;
        }

        $files[$fid]->delete();
        $count++;
      }

      $this->logger('file')->notice('Deleted @count files.', ['@count' => $count]);
      if ($count) {
        drupal_set_message($this->formatPlural($count, 'Deleted 1 file.', 'Deleted @count files.'));
      }
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Determines if files have usage records.
   *
   * @param \Drupal\file\FileInterface[] $files
   *   The files to check for usage.
   *
   * @return bool
   *   Indicates if files have usage records.
   */
  protected function filesHaveUsage(array $files) {
    foreach ($files as $file) {
      /** @var \Drupal\file\FileInterface $file */
      if (!empty($this->fileUsage->listUsage($file))) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Gets the current user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The current user.
   */
  protected function getCurrentUser() {
    return \Drupal::currentUser();
  }

}
