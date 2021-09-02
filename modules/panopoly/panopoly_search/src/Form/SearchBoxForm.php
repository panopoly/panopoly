<?php

namespace Drupal\panopoly_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Search box form.
 */
class SearchBoxForm extends FormBase {

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * SearchBoxForm constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')->get('panopoly_search')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'panopoly_search_box_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $search_page_route = panopoly_search_page_route();
    if (empty($search_page_route)) {
      $form['message'] = [
        '#markup' => $this->t('Search is currently disabled'),
      ];
      return $form;
    }

    $form['#action'] = Url::fromRoute($search_page_route)->toString();
    $form['#method'] = 'get';

    $form['keys'] = [
      '#type' => 'search',
      '#title' => $this->t('Enter your keywords'),
      '#size' => 15,
      '#default_value' => '',
      '#attributes' => [
        'title' => $this->t('Enter the terms you wish to search for.'),
      ],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      // Prevent op from showing up in the query string.
      '#name' => '',
      // Get the default style from Bartik.
      '#attributes' => [
        'class' => ['search-form__submit'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form submits directly to the search pages, so this is never reached.
  }

}
