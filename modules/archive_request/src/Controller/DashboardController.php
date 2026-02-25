<?php

namespace Drupal\archive_request\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Views;

/**
 * Provides the Archive Requests dashboard page.
 */
class DashboardController extends ControllerBase {

  public function dashboard() {
    $view = Views::getView('archive_request');
    if ($view) {
      $view->setDisplay('page_1');
      $view->execute();
      return $view->render();
    }

    return [
      '#markup' => $this->t('Please create a View named "archive_request" that lists nodes where field_archive_request is checked.'),
    ];
  }

}