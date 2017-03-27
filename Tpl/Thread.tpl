<div class="discuss">
    <?php if (!empty($this->topic)) { ?>
        <header>
            <a href="<?php self::t($this->topic->getLink()); ?>"><?php self::t($this->topic->subject); ?></a>
        </header>
    <?php } ?>
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
            <article <?php self::attr([
                "class" => "post",
                "data-discuss-post" => $post->id,
            ]) ?>>
                <header>
                    <a class="profile-image" href="#">
                        <img src="" alt="@username">
                    </a>

                    <a class="username" href="#"><?php self::t("@username"); ?></a>
                    <time><?php self::t(self::formatDateNatural($post->postDate, true)); ?></time>
                </header>
                <?php self::e($post->post); ?>
                <footer>
                    <div class="votes">
                        <span class="sum"><?php self::t($post->getVotes()); ?></span>
                        (<span class="up"><?php self::t($post->getUpvotes()); ?></span> /
                        <span class="down"><?php self::t($post->getDownvotes()); ?></span>)
                    </div>
                </footer>
            </article>
        <?php } ?>
    </article>
    <?php self::e($this->postForm); ?>
</div>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
