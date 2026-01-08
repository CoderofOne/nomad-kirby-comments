<?php

Kirby::plugin('nomad/kirby-comments', [

  // Convenience methods for Panel blueprints/snippets
  'pageMethods' => [
    'approvedCommentsCount' => function () {
      return $this->comments()
        ->toStructure()
        ->filterBy('status', 'approved')
        ->count();
    },

    'pendingCommentsCount' => function () {
      return $this->comments()
        ->toStructure()
        ->filterBy('status', 'pending')
        ->count();
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
