<?php
    $maxlength = 100;
    $breadcrumps = $this->discuss->renderBreadcrumpsTo($this->topic);
    $subject = $this->topic->subject ?? "";
?>
<?php if (!empty($subject)) { ?>
    <header class="fixed">
        <div class="title"><?php self::t($this->discuss->subject . ": " . _($subject)); ?></div>
    </header>
<?php } ?>

<section class="discuss discuss-topic">
    <h1><?php self::t(_("Topic") . ": " . _($subject)); ?></h1>
    <?php if (count($this->threads) == 0) { ?>
        <p><?php self::t(_("There are no threads in this topic yet.")); ?></p>
    <?php } ?>
    <nav class="list threads">
        <ul>
            <?php foreach ($this->threads as $thread) {
                if (!$thread->visible) continue;

                $post = strip_tags($thread->post);
                if (strlen($post) > $maxlength) {
                    $post = substr($post, 0, $maxlength) . "…";
                }
            ?>
                <li class="teaser">
                    <div class="desc">
                        <h2><a href="<?php self::t($this->discuss->getLinkTo($thread, $this->user)); ?>"><?php self::t($thread->subject); ?></a></h2>
                        <p><?php self::e($post); ?></p>
                    </div>
                    <div class="num num-posts"><?php self::t($thread->numPosts); ?></div>
                </li>
            <?php } ?>
        </ul>
    </nav>

    <?php self::e($this->threadForm); ?>
    <div class="breadcrumps"><?php self::e($breadcrumps); ?></div>
</section>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :

