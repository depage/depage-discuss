<?php
    $breadcrumps = $this->discuss->renderBreadcrumpsTo($this->thread);
?>
<section class="discuss">
    <h1 class="breadcrumps"><?php self::e($breadcrumps); ?></h1>
    <article <?php self::attr([
        'class' => "thread",
    ]); ?>>
        <div <?php self::attr([
            'class' => "body",
            'data-discuss-id' => "thread-{$this->thread->id}",
        ]); ?>>
            <?php
                $upvotes = $this->thread->getUpvotes();
                $downvotes = $this->thread->getDownvotes();
                $sum = $upvotes - $downvotes;
            ?>
            <header>
                <?php self::e($this->discuss->htmlUserInfo($this->thread->uid)); ?>
                <time><?php self::t(self::formatDateNatural($this->thread->postDate, true)); ?></time>
            </header>
            <div class="content">
                <?php self::e($this->thread->post); ?>
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
        </div>

        <?php foreach ($this->posts as $post) {
            if (!$post->visible) continue;

            $upvotes = $post->getUpvotes();
            $downvotes = $post->getDownvotes();
            $sum = $upvotes - $downvotes;
        ?>
            <article <?php self::attr([
                'class' => "post",
                'id' => "post-{$post->id}",
                'data-discuss-id' => "post-{$post->id}",
            ]) ?>>
                <header>
                    <?php self::e($this->discuss->htmlUserInfo($post->uid)); ?>
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
    <div class="breadcrumps"><?php self::e($breadcrumps); ?></div>
</section>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
