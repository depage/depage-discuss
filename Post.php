<?php
/**
 * @file    Post.php
 *
 * description
 *
 * copyright (c) 2017 Frank Hellenkamp [jonas@depage.net]
 *
 * @author    Frank Hellenkamp [jonas@depage.net]
 */

namespace Depage\Discuss;

/**
 * @brief Post
 * Class Post
 */
class Post extends \Depage\Entity\Entity
{
    // {{{ variables
    /**
     * @brief fields
     **/
    static protected $fields = array(
        "id" => null,
        "threadId" => null,
        "uid" => null,
        "post" => "",
        "postDate" => null,
        "editDate" => null,
    );

    /**
     * @brief primary
     **/
    static protected $primary = ["id"];

    /**
     * @brief upvotes
     **/
    protected $upvotes = 0;

    /**
     * @brief downvotes
     **/
    protected $downvotes = 0;

    /**
     * @brief pdo object for database access
     **/
    protected $pdo = null;
    // }}}

    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed $
     * @return void
     **/
    public function __construct($pdo)
    {
        parent::__construct($pdo);

        $this->pdo = $pdo;
    }
    // }}}

    // {{{ loadByThread()
    /**
     * @brief loadByThread
     *
     * @param mixed $
     * @return void
     **/
    public static function loadByThread($pdo, $threadId, $from = null, $to = null)
    {
        $fields = "post." . implode(", post.", self::getFields());
        $params = [
            "threadId" => $threadId,
        ];

        $query = $pdo->prepare(
            "SELECT
                $fields,
                IFNULL(SUM(vote.upvote), 0) AS upvotes,
                IFNULL(SUM(vote.downvote), 0) AS downvotes
            FROM
                {$pdo->prefix}_discuss_posts AS post
                LEFT JOIN {$pdo->prefix}_discuss_votes AS vote
            ON post.id = vote.postId
            WHERE post.threadId = :threadId
            GROUP BY post.id
            ORDER BY post.postDate ASC
            "
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array($pdo));
        $posts = $query->fetchAll();

        return $posts;

    }
    // }}}

    // {{{ setThread()
    /**
     * @brief setThread
     *
     * @param mixed $thread
     * @return void
     **/
    public function setThread($thread)
    {
        if ($this->data['threadId'] == null) {
            $this->threadId = $thread->id;
            $this->thread = $thread;
        } else if ($this->data['threadId'] == $thread->id) {
            $this->thread = $thread;
        }

        return $this;
    }
    // }}}
    // {{{ getTopic()
    /**
     * @brief getTopic
     *
     * @param mixed
     * @return void
     **/
    public function getTopic()
    {
        if (empty($this->topic) && $this->data['topicId'] !== null) {
            $this->topic = Topic::loadById($this->pdo, $this->data['topicId']);
        }
        return $this->topic;
    }
    // }}}

    // {{{ voteUp()
    /**
     * @brief voteUp
     *
     * @param mixed $uid
     * @return void
     **/
    public function voteUp($uid)
    {
        return $this->vote($uid, 1);
    }
    // }}}
    // {{{ voteDown()
    /**
     * @brief voteDown
     *
     * @param mixed $uid
     * @return void
     **/
    public function voteDown($uid)
    {
        return $this->vote($uid, -1);
    }
    // }}}
    // {{{ voteReset()
    /**
     * @brief voteReset
     *
     * @param mixed $uid
     * @return void
     **/
    public function voteReset($uid)
    {
        return $this->vote($uid, 0);
    }
    // }}}
    // {{{ vote()
    /**
     * @brief vote
     *
     * @param mixed $uid, $direction
     * @return void
     **/
    protected function vote($uid, $direction)
    {
        if ($this->uid == $uid) {
            // users themselves cannot vote on their posts
            return $this;
        }
        if ($direction > 0) {
            $upvote = 1;
            $downvote = 0;
        } else if ($direction < 0) {
            $upvote = 0;
            $downvote = 1;
        } else {
            $upvote = 0;
            $downvote = 0;
        }

        $query = $this->pdo->prepare("
            REPLACE INTO {$this->pdo->prefix}_discuss_votes
                (postId, uid, upvote, downvote) VALUES (:postId, :uid, :upvote, :downvote);
        ");
        $query->execute([
            "postId" => $this->id,
            "uid" => $uid,
            "upvote" => $upvote,
            "downvote" => $downvote,
        ]);

        // updated current votes in object
        $query = $this->pdo->prepare(
            "SELECT
                IFNULL(SUM(vote.upvote), 0) AS upvotes,
                IFNULL(SUM(vote.downvote), 0) AS downvotes
            FROM
                {$this->pdo->prefix}_discuss_votes AS vote
            WHERE vote.postId = :postId
            "
        );
        $query->execute([
            "postId" => $this->id,
        ]);

        $vote = $query->fetchObject();
        $this->upvotes = $vote->upvotes;
        $this->downvotes = $vote->downvotes;

        return $this;
    }
    // }}}

    // {{{ notify()
    /**
     * @brief notify
     *
     * @param mixed $isNew
     * @return void
     **/
    public function notify($isNew)
    {
        if ($isNew) {
            $mentions = $this->getMentions();
            foreach($mentions as $username) {
                try {
                    $user = \Depage\Auth\User::loadByUsername($this->pdo, $username);
                    // @todo notify user
                } catch (\Exception $e) {
                }
            }
        }
    }
    // }}}
    // {{{ getMentions()
    /**
     * @brief getMentions
     *
     * @param mixed
     * @return void
     **/
    public function getMentions()
    {
        $users = [];
        $text = strip_tags($this->post);

        $count = preg_match_all("/@([a-z0-9]+)/i", $text, $matches);

        if ($count > 0) {
            foreach($matches[1] as $username) {
                $users[$username] = true;
            }
        }

        return array_keys($users);
    }
    // }}}

    // {{{ save()
    /**
     * save a notification object
     *
     * @public
     */
    public function save() {
        $fields = array();
        $primary = self::$primary[0];
        $isNew = $this->data[$primary] === null;

        $dirty = array_keys($this->dirty, true);

        if (count($dirty) > 0) {
            if ($isNew) {
                $query = "INSERT INTO {$this->pdo->prefix}_discuss_posts";
            } else {
                $query = "UPDATE {$this->pdo->prefix}_discuss_posts";
            }
            foreach ($dirty as $key) {
                $fields[] = "$key=:$key";
            }
            $query .= " SET " . implode(",", $fields);

            if (!$isNew) {
                $query .= " WHERE $primary=:$primary";
                $dirty[] = $primary;
            }

            $params = array_intersect_key($this->data,  array_flip($dirty));

            $cmd = $this->pdo->prepare($query);
            $success = $cmd->execute($params);

            if ($isNew) {
                $this->data[$primary] = $this->pdo->lastInsertId();
            }

            if ($success) {
                $this->dirty = array_fill_keys(array_keys(static::$fields), false);
            }

            $this->notify($isNew);
        }
    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
