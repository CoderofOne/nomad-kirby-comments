<section id="comments">

  <h2>Comments</h2>

  <?php
  $comments = $page->comments()
    ->toStructure()
    ->filterBy('status', 'approved')
    ->sortBy('date', 'asc');
  ?>

  <?php if ($comments->count()): ?>
    <ul>
      <?php foreach ($comments as $comment): ?>
        <li>
          <strong><?= esc($comment->author()) ?></strong>
          <small><?= $comment->date()->toDate('M d, Y') ?></small>
          <p><?= esc($comment->text()) ?></p>
        </li>
      <?php endforeach ?>
    </ul>
  <?php else: ?>
    <p>No comments yet.</p>
  <?php endif ?>

  <h3>Leave a comment</h3>

  <form method="post" action="<?= $page->url() ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
    <input type="hidden" name="comment_submit" value="1">

    <!-- Honeypot (spam bots fill this; humans never see it) -->
    <input type="text" name="website" tabindex="-1" autocomplete="off" style="display:none">

    <input name="author" required placeholder="Name">
    <input name="email" type="email" required placeholder="Email">
    <textarea name="text" required placeholder="Comment"></textarea>

    <button type="submit">Post Comment</button>
  </form>

</section>
