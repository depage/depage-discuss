<div class="discuss">
    <h1><a href="<?php self::t($this->thread->getLink()); ?>"><?php self::t($this->thread->subject); ?></a></h1>
    <article class="thread">
        <div class="content">
            <header>
                <a class="profile-image" href="#">
                    <img src="" alt="@username">
                </a>

                <a class="username" href="#"><?php self::t("@username"); ?></a>
                <time><?php self::t(self::formatDateNatural($this->thread->postDate, true)); ?></time>
            </header>
            <?php self::e($this->thread->post); ?>
        </div>

        <?php foreach ($this->posts as $post) { ?>
            <article class="post">
                <header>
                    <a class="profile-image" href="#">
                        <img src="" alt="@username">
                    </a>

                    <a class="username" href="#"><?php self::t("@username"); ?></a>
                    <time><?php self::t(self::formatDateNatural($post->postDate, true)); ?></time>
                </header>
                <?php self::e($post->post); ?>
            </article>
        <?php } ?>
    </article>
    <?php self::e($this->postForm); ?>
</div>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
