<?php

namespace Drupal\panopoly_search\Controller;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Panopoly Search controller.
 */
class PanopolySearchController {

  /**
   * Controller to redirect to the search page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   The response.
   */
  public function redirectToSearch(Request $request) {
    $search_page_route = panopoly_search_page_route();
    if (empty($search_page_route)) {
      $build = [
        '#markup' => t('Search is currently disabled'),
      ];
      return $build;
    }

    $parameters = [];
    if ($keys = $request->get('keys')) {
      $parameters['keys'] = $keys;
    }

    return new RedirectResponse(Url::fromRoute($search_page_route, $parameters, ['absolute' => TRUE])->toString());
  }

}
