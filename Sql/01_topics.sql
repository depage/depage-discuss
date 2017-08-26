/*
    Topics Table
    -----------------------------------

    @tablename _discuss_topics
    @version 1.5.6
*/
CREATE TABLE `_discuss_topics` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `subject` text NOT NULL,
    `description` text NOT NULL,
    `pos` int(11) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

/*
    @version 1.5.6.3
*/
ALTER TABLE `_discuss_topics` ADD COLUMN `visible` tinyint(1) NOT NULL DEFAULT 1 AFTER pos;
