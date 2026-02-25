<?php

namespace Drupal\custom_toolbar\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides dashboard pages with tabs.
 */
class DashboardController extends ControllerBase {

  /**
   * Default dashboard landing page.
   */
  public function overview() {
    return [
      '#markup' => '<h2>Welcome to your Dashboard</h2><p>Select a tab above.</p>',
    ];
  }

  /**
   * Drafts tab.
   */
  public function drafts() {
    return [
      '#markup' => '<h2>My Drafts</h2><p>Here you could embed a View of current user drafts.</p>',
    ];
  }

  /**
   * Review tab.
   */
  public function review() {
    return [
      '#markup' => '<h2>For Review</h2><p>Here you could embed a View of pending review content.</p>',
    ];
  }

  /**
   * Archive tab.
   */
  public function archive() {
    return [
      '#markup' => '<h2>Archive Requests</h2><p>Here you could embed a View of archive requests.</p>',
    ];
  }

}
