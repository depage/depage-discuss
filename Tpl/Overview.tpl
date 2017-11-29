<?php
    $breadcrumps = $this->discuss->renderBreadcrumpsTo($this->discuss);
?>
<header class="fixed">
    <div class="title"><?php self::t($this->discuss->subject . " / " . _("Topics")); ?></div>
</header>

<section class="discuss discuss-overview">
    <h1><?php self::t($this->discuss->subject . " / " . _("Topics")); ?></h1>
    <nav class="list topics">
        <ul>
            <?php foreach ($this->topics as $topic) {
                if (!$topic->visible) continue;
            ?>
                <li class="teaser">
                    <div class="desc">
                        <h2><a href="<?php self::t($this->discuss->getLinkTo($topic)); ?>"><?php self::t(_($topic->subject)); ?></a></h2>
                        <p><?php self::t(_($topic->description)); ?></p>
                    </div>
                    <div class="num num-threads"><?php self::t($topic->numThreads); ?></div>
                    <div class="num num-posts"><?php self::t($topic->numPosts); ?></div>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <div class="breadcrumps"><?php self::e($breadcrumps); ?></div>
</section>
<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
