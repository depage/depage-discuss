<?php
    $link = $this->discuss->getLinkTo($this->user);
    $handle = "@" . $this->user->name;
?>
<a class="profile-image" href="<?php self::t($link); ?>">
    <img src="<?php self::t($this->discuss->getProfileImage($this->user)); ?>" alt="<?php self::t($handle); ?>">
</a>

<a class="username" href="<?php self::t($link); ?>"><?php self::t($handle); ?></a>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et :
