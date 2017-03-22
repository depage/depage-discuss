<div class="discuss">
    <h1>Posts</h1>
    <article class="thread">
        <h1><a href="<?php self::t($this->thread->getLink()); ?>"><?php self::t($this->thread->subject); ?></a></h1>
        <p><?php self::e($this->thread->post); ?></p>

        <?php foreach ($this->posts as $post) { ?>
            <article class="post">
                <?php self::e($post->post); ?>
            </article>
        <?php } ?>
    </article>
</div>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
