<div class="discuss">
    <h1>Threads</h1>
    <nav class="list threads">
        <ul>
            <?php foreach ($this->threads as $thread) {
            ?>
                <li class="teaser">
                    <h1><a href="<?php self::t($thread->getLink()); ?>"><?php self::t($thread->subject); ?></a></h1>
                    <p><?php self::t($thread->post); ?></p>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :

