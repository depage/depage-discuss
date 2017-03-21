/*
    Topics Table
    -----------------------------------

    @tablename _discuss_topics
    @version 1.5.6
*/
CREATE TABLE `_discuss_topics` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title` text NOT NULL,
    `description` text NOT NULL,
    `pos` int(11) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
