/*
    Threads Table
    -----------------------------------

    @tablename _discuss_threads
    @connection _discuss_topics
    @connection _auth_user
    @version 1.5.6
*/
CREATE TABLE `_discuss_threads` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `topicId` int(11) unsigned DEFAULT NULL,
    `uid` int(11) unsigned NOT NULL,
    `subject` text NOT NULL,
    `post` text NOT NULL,
    `postDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `sticky` smallint(2) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    CONSTRAINT `_discuss_threads_ibfk_1` FOREIGN KEY (`topicId`) REFERENCES `_discuss_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `_discuss_threads_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `_auth_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

/*
    @version 1.5.6.1
*/
ALTER TABLE `_discuss_threads` ADD FULLTEXT KEY `content` (`subject`, `post`);
