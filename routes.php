<?php

use Kirby\Cms\App;
use Kirby\Data\Yaml;

return [
  [
    'pattern' => '(:all)',
    'method'  => 'POST',
    'action'  => function ($path) {

      if (!get('comment_submit')) return false;
      if (!csrf(get('csrf_token'))) return false;
      if (!empty(get('website'))) return false;

      // Rate limit (per IP, 60s)
      $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
         ?? (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]) : null)
         ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

      $cacheKey = 'nomad.kirby.comments.rate.' . md5((string)$ip);
      $cache = kirby()->cache('nomad.kirby.comments');

      if ($cache->get($cacheKey)) return false;
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

      $kirby->impersonate('kirby');
      $page->update(['comments' => Yaml::encode($comments)]);
      $kirby->impersonate(null);

      // Email notification (optional)
      if (option('nomad.kirby.comments.notify') && option('nomad.kirby.comments.email')) {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        kirby()->email([
          'to'      => option('nomad.kirby.comments.email'),
          'from'    => option('email.from', 'no-reply@' . $host),
          'subject' => 'New comment pending approval',
          'body'    => "Article: {$page->title()}\n\nFrom: {$comment['author']} ({$comment['email']})\n\n{$comment['text']}"
        ]);
      }

      go($page->url() . '#comments');
    }
  ]
];
