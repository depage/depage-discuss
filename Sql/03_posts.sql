/*
    Posts Table
    -----------------------------------

    @tablename _discuss_posts
    @connection _discuss_threads
    @connection _auth_user
    @version 1.5.6
*/
CREATE TABLE `_discuss_posts` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `threadId` int(11) unsigned NOT NULL,
    `uid` int(11) unsigned NOT NULL,
    `post` text NOT NULL,
    `postDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `editDate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FULLTEXT KEY `content` (`post`),
    CONSTRAINT `_discuss_posts_ibfk_1` FOREIGN KEY (`threadId`) REFERENCES `_discuss_threads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `_discuss_posts_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `_auth_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

/*
    @version 1.5.6.3
*/
ALTER TABLE `_discuss_posts` ADD COLUMN `visible` tinyint(1) NOT NULL DEFAULT 1 AFTER editDate;

/*
    @version 1.5.6.4
*/
ALTER TABLE `_discuss_posts` ADD KEY publishing (postDate);
