<?php

namespace Drupal\content_403\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\NodeInterface;

class ContentAccessDeniedSubscriber implements EventSubscriberInterface {

  protected RendererInterface $renderer;
  protected CurrentRouteMatch $routeMatch;

  public function __construct(RendererInterface $renderer, CurrentRouteMatch $route_match) {
    $this->renderer = $renderer;
    $this->routeMatch = $route_match;
  }

  public static function getSubscribedEvents(): array {
    return [KernelEvents::EXCEPTION => ['onException', 100]];
  }

  public function onException(ExceptionEvent $event): void {
    $exception = $event->getThrowable();
    if (!($exception instanceof AccessDeniedHttpException)) {
      return;
    }

    if ($this->routeMatch->getRouteName() !== 'entity.node.canonical') {
      return;
    }

    $node = $this->routeMatch->getParameter('node');
    if (!($node instanceof NodeInterface)) {
      return;
    }

    $is_unpublished = !$node->isPublished();
    $is_archived = $node->hasField('moderation_state')
      && $node->get('moderation_state')->value === 'archived';

    if (!($is_unpublished || $is_archived)) {
      return;
    }

    $build = [
      '#theme'        => 'content_403_node',
      '#node_title'   => $node->label(),
      '#is_archived'  => $is_archived,
      '#is_unpub'     => $is_unpublished,
    ];

    $html = $this->renderer->renderRoot($build);
    $response = new Response($html, 403);
    $event->setResponse($response);
  }
}
