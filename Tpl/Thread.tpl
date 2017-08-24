<section class="discuss">
    <h1><a href="<?php self::t($this->discuss->getLinkTo($thread)); ?>"><?php self::t($this->thread->subject); ?></a></h1>
    <article class="thread">
        <div class="content">
            <header>
                <?php $this->discuss->renderUserInfo($this->thread->uid); ?>
                <time><?php self::t(self::formatDateNatural($this->thread->postDate, true)); ?></time>
            </header>
            <?php self::e($this->thread->post); ?>
        </div>

        <?php foreach ($this->posts as $post) {
            $upvotes = $post->getUpvotes();
            $downvotes = $post->getDownvotes();
            $sum = $upvotes - $downvotes;
        ?>
            <article <?php self::attr([
                'class' => "post",
                'id' => "post-{$post->id}",
                'data-discuss-post-id' => $post->id,
            ]) ?>>
                <header>
                    <?php $this->discuss->renderUserInfo($post->uid); ?>
                    <time><?php self::t(self::formatDateNatural($post->postDate, true)); ?></time>
                </header>
                <div class="content">
                    <?php self::e($post->post); ?>
                </div>
                <footer>
                    <?php if(!empty($this->user)) { ?>
                        <div class="votes">
                            <span class="sum"><?php self::t($sum); ?></span>
                            <span class="up"><?php self::t($upvotes); ?></span>
                            <span class="down"><?php self::t($downvotes); ?></span>
                        </div>
                    <?php } ?>
                </footer>
            </article>
        <?php } ?>
    </article>
    <?php self::e($this->postForm); ?>
</section>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
