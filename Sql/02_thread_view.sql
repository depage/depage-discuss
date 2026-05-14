/*
    Threads views Table
    -----------------------------------

    @tablename _discuss_thread_views
    @connection _discuss_posts
    @connection _discuss_threads
    @connection _auth_user
    @version 1.5.6
*/
CREATE TABLE `_discuss_thread_views` (
    `uid` int(11) unsigned NOT NULL,
    `threadId` int(11) unsigned DEFAULT NULL,
    `postId` int(11) unsigned NOT NULL,
    PRIMARY KEY (`threadId`, `uid`),
    CONSTRAINT `_discuss_thread_views_ibfk_1` FOREIGN KEY (`threadId`) REFERENCES `_discuss_threads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `_discuss_thread_views_ibfk_2` FOREIGN KEY (`postId`) REFERENCES `_discuss_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `_discuss_thread_views_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `_auth_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

/*
    @version 1.5.6.7
*/
ALTER TABLE `_discuss_thread_views` ADD COLUMN `viewDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER postId;

/*
    @version 1.5.6.8
*/
ALTER TABLE `_discuss_thread_views` CHANGE COLUMN `postId` `postId` int(11) unsigned;
