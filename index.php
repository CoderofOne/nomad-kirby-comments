<?php

Kirby::plugin('nomad/kirby-comments', [

  // Convenience methods for Panel blueprints/snippets
  'pageMethods' => [
    'approvedCommentsCount' => function () {
      return $this->comments()->toStructure()->filterBy('status', 'approved')->count();
    },
    'pendingCommentsCount' => function () {
      return $this->comments()->toStructure()->filterBy('status', 'pending')->count();
    },
  ],

  // Keep a queryable field in sync so Panel blueprints can filter "pending only"
  'hooks' => [
    'page.update:after' => function ($newPage, $oldPage) {
      if ($newPage->intendedTemplate()->name() !== 'article') return;
      if (!$newPage->content()->has('comments')) return;

      $pending = $newPage->comments()->toStructure()->filterBy('status', 'pending')->count();
      $flag = $pending > 0 ? 'true' : 'false';

      $current = $newPage->content()->get('hasPendingComments')->value();
      if ($current === $flag) return;

      kirby()->impersonate('kirby');
      $newPage->update(['hasPendingComments' => $flag]);
      kirby()->impersonate(null);
    },
  ],

  'routes' => require __DIR__ . '/routes.php',

  'blueprints' => [
    'fields/comments' => __DIR__ . '/blueprints/fields/comments.yml',
  ],

  'snippets' => [
    'comments'     => __DIR__ . '/snippets/comments.php',
    'comment-meta' => __DIR__ . '/snippets/comment-meta.php',
  ],
]);
