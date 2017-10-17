<section class="discuss">
    <h1>Topics</h1>
    <nav class="list topics">
        <ul>
            <?php foreach ($this->topics as $topic) {
                if (!$topic->visible) continue;
            ?>
                <li class="teaser">
                    <div class="desc">
                        <h2><a href="<?php self::t($this->discuss->getLinkTo($topic)); ?>"><?php self::t($topic->subject); ?></a></h2>
                        <p><?php self::t($topic->description); ?></p>
                    </div>
                    <div class="num num-threads"><?php self::t($topic->numThreads); ?></div>
                    <div class="num num-posts"><?php self::t($topic->numPosts); ?></div>
                </li>
            <?php } ?>
        </ul>
    </nav>
</section>
<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
