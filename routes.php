<?php

use Kirby\Cms\App;
use Kirby\Data\Yaml;

return [
  [
    // Post to the article URL itself
    'pattern' => '(:all)',
    'method'  => 'POST',
    'action'  => function ($path) {

      // Only handle comment submissions
      if (!get('comment_submit')) return false;

      // CSRF protection
      if (!csrf(get('csrf_token'))) return false;

      // Honeypot (spam protection)
      if (!empty(get('website'))) return false;

      // Optional: very lightweight rate limit (per IP, 60s)
      $ip = kirby()->request()->ip();
      $cacheKey = 'nomad.kirby.comments.rate.' . md5((string)$ip);

      $cache = kirby()->cache('nomad.kirby.comments');
      if ($cache->get($cacheKey)) {
        return false;
      }
      $cache->set($cacheKey, true, 60);

      $kirby = App::instance();
      $page  = page($path);
      if (!$page) return false;

      $comment = [
        'author' => esc(get('author')),
        'email'  => esc(get('email')),
        'text'   => esc(get('text')),
        'status' => 'pending',
        'date'   => date('Y-m-d H:i:s'),
      ];

      $comments = $page->comments()->toStructure()->toArray();
      $comments[] = $comment;

      // Kirby 5 requires elevated permissions for frontend updates
      $kirby->impersonate('kirby');
      $page->update(['comments' => Yaml::encode($comments)]);
      $kirby->impersonate(null);

      // Email notification (optional)
      if (option('nomad.kirby.comments.notify') && option('nomad.kirby.comments.email')) {
        kirby()->email([
          'to'      => option('nomad.kirby.comments.email'),
          'from'    => 'no-reply@' . server()->host(),
          'subject' => 'New comment pending approval',
          'body'    => "Article: {$page->title()}

From: {$comment['author']} ({$comment['email']})

{$comment['text']}"
        ]);
      }

      go($page->url() . '#comments');
    }
  ]
];
