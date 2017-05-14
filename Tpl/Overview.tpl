<div class="discuss">
    <h1>Topics</h1>
    <nav class="list topics">
        <ul>
            <?php foreach ($this->topics as $topic) {
            ?>
                <li class="teaser">
                    <h1><a href="<?php self::t($this->discuss->getLinkTo($topic)); ?>"><?php self::t($topic->subject); ?></a></h1>
                    <p><?php self::t($topic->description); ?></p>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>
<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
