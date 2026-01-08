<?php

use Kirby\Data\Yaml;

Kirby::plugin('nomad/kirby-comments', [

  'pageMethods' => [
    'approvedCommentsCount' => function () {
      return $this->comments()->toStructure()->filterBy('status', 'approved')->count();
    },
    'pendingCommentsCount' => function () {
      return $this->comments()->toStructure()->filterBy('status', 'pending')->count();
    },
  ],

  'hooks' => [
    // ✅ compute flag *before* save so it's part of the same update transaction
    'page.update:before' => function ($page, $values, $strings) {

      // Only for article pages
      if ($page->intendedTemplate()->name() !== 'article') {
        return $values;
      }

      // Only if comments field exists (or is being written)
      $commentsRaw = $values['comments'] ?? $page->comments()->value();
      if ($commentsRaw === null) {
        return $values;
      }

      // Parse YAML comments into array
      $decoded = is_array($commentsRaw) ? $commentsRaw : (Yaml::decode($commentsRaw) ?? []);
      $pending = 0;

      foreach ($decoded as $c) {
        if (($c['status'] ?? '') === 'pending') $pending++;
      }

      $values['hasPendingComments'] = $pending > 0 ? 'true' : 'false';

      return $values;
    }
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
