<?php
    $breadcrumps = $this->discuss->renderBreadcrumpsTo($this->thread);
?>
<header class="fixed">
    <div class="title"><?php self::t($this->discuss->subject . " / " . $this->thread->subject); ?></div>
</header>

<section class="discuss discuss-thread">
    <hgroup class="title">
        <h1><?php self::t($this->thread->subject); ?></h1>
    </hgroup>
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
                $canVote = !empty($this->user) && $this->thread->uid != $this->user->id;
            ?>
            <header>
                <?php self::e($this->discuss->htmlUserInfo($this->thread->uid)); ?>
                <time><?php self::t(self::formatDateNatural($this->thread->postDate, true)); ?></time>
            </header>
            <div class="content">
                <?php self::e($this->discuss->replaceUserHandles($this->thread->post)); ?>
            </div>
            <footer>
                <?php if(!empty($this->user)) { ?>
                    <div class="votes <?php if ($canVote) self::t("can-vote"); ?>">
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
                    <?php self::e($this->discuss->replaceUserHandles($post->post)); ?>
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
