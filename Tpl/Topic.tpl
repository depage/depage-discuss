<?php
    $maxlength = 100;
?>
<section class="discuss">
    <h1>Threads</h1>
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
                    <h1><a href="<?php self::t($this->discuss->getLinkTo($thread)); ?>"><?php self::t($thread->subject); ?></a></h1>
                    <p><?php self::e($post); ?></p>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php self::e($this->threadForm); ?>
</section>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :

