/*
    Post votes Table
    -----------------------------------

    @tablename _discuss_votes
    @connection _discuss_posts
    @connection _auth_user
    @version 1.5.6
*/
CREATE TABLE `_discuss_votes` (
    `postId` int(11) unsigned NOT NULL,
    `uid` int(11) unsigned NOT NULL,
    `upvote` smallint(2) NOT NULL DEFAULT '0',
    `downvote` smallint(2) NOT NULL DEFAULT '0',
    UNIQUE (postId, uid),
    CONSTRAINT `_discuss_votes_ibfk_1` FOREIGN KEY (`postId`) REFERENCES `_discuss_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `_discuss_votes_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `_auth_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
