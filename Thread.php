<?php
/**
 * @file    Thread.php
 *
 * description
 *
 * copyright (c) 2017 Frank Hellenkamp [jonas@depage.net]
 *
 * @author    Frank Hellenkamp [jonas@depage.net]
 */

namespace Depage\Discuss;

/**
 * @brief Thread
 * Class Thread
 */
class Thread extends \Depage\Entity\Entity
{
    use Traits\Votes;

    // {{{ variables
    /**
     * @brief fields
     **/
    static protected $fields = array(
        "id" => null,
        "topicId" => null,
        "uid" => null,
        "subject" => "",
        "post" => "",
        "postDate" => null,
        "editDate" => null,
        "lastPostDate" => null,
        "sticky" => 0,
        "visible" => 1,
    );

    /**
     * @brief primary
     **/
    static protected $primary = ["id"];

    /**
     * @brief voteTable
     **/
    static public $voteTable = "_discuss_thread_votes";

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

    // {{{ loadByTopic()
    /**
     * @brief loadByTopic
     *
     * @param mixed $
     * @return void
     **/
    public static function loadByTopic($pdo, $topicId)
    {
        $fields = "thread." . implode(", thread.", self::getFields());
        $params = [
            "topicId" => $topicId,
        ];

        $query = $pdo->prepare(
            "SELECT
                $fields,
                IFNULL(SUM(vote.upvote), 0) AS upvotes,
                IFNULL(SUM(vote.downvote), 0) AS downvotes
            FROM
                {$pdo->prefix}_discuss_threads AS thread
                LEFT JOIN {$pdo->prefix}" . self::$voteTable . " AS vote
                ON thread.id = vote.id
            WHERE thread.topicId = :topicId
            GROUP BY thread.id
            ORDER BY thread.sticky DESC, thread.lastPostDate DESC"
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), [$pdo]);
        $thread = $query->fetchAll();

        return $thread;

    }
    // }}}
    // {{{ loadById()
    /**
     * @brief loadById
     *
     * @param mixed $
     * @return void
     **/
    public static function loadById($pdo, $threadId)
    {
        $fields = "thread." . implode(", thread.", self::getFields());
        $params = [
            "threadId" => $threadId,
        ];

        $query = $pdo->prepare(
            "SELECT
                $fields,
                IFNULL(SUM(vote.upvote), 0) AS upvotes,
                IFNULL(SUM(vote.downvote), 0) AS downvotes
            FROM
                {$pdo->prefix}_discuss_threads AS thread
                LEFT JOIN {$pdo->prefix}" . self::$voteTable . " AS vote
                ON thread.id = vote.id
            WHERE thread.id = :threadId
            GROUP BY thread.id
            ORDER BY thread.sticky"
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array($pdo));
        $thread = $query->fetch();

        return $thread;

    }
    // }}}
    // {{{ loadByUser()
    /**
     * @brief loadByUser
     *
     * @param mixed $
     * @return void
     **/
    public static function loadByUser($pdo, $user)
    {
        $fields = "thread." . implode(", thread.", self::getFields());
        $params = [
            "uid1" => $user->id,
            "uid2" => $user->id,
        ];

        $query = $pdo->prepare(
            "SELECT $fields
            FROM
                {$pdo->prefix}_discuss_threads AS thread
                LEFT JOIN {$pdo->prefix}_discuss_posts AS post
                ON thread.id = post.threadId
            WHERE
                (thread.uid = :uid1 OR post.uid = :uid2)
                AND thread.topicId IS NOT NULL
            GROUP BY thread.id
            ORDER BY thread.lastPostDate DESC"
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array($pdo));
        $threads = $query->fetchAll();

        return $threads;

    }
    // }}}

    // {{{ countByTopic()
    /**
     * @brief countByTopic
     *
     * @param mixed $
     * @return void
     **/
    public static function countByTopic($pdo, $topicId)
    {
        $params = [
            "topicId" => $topicId,
        ];

        $query = $pdo->prepare(
            "SELECT
                COUNT(*)
            FROM
                {$pdo->prefix}_discuss_threads AS thread
            WHERE thread.topicId = :topicId"
        );
        $query->execute($params);

        $count = $query->fetchColumn();

        return $count;

    }
    // }}}

    // {{{ loadPosts()
    /**
     * @brief loadPosts
     *
     * @param mixed $
     * @return void
     **/
    public function loadPosts($from, $to)
    {
        $posts = Post::loadByThread($this->pdo, $this->id, $from, $to);

        return $posts;
    }
    // }}}
    // {{{ loadUsersToNotify()
    /**
     * @brief loadUsersToNotify
     *
     * @param mixed
     * @return void
     **/
    public function loadUsersToNotify()
    {
        $users = [];

        $params = [
            "threadId1" => $this->id,
            "threadId2" => $this->id,
            "threadId3" => $this->id,
            "threadId4" => $this->id,
        ];

        $query = $this->pdo->prepare(
            "SELECT threadUserViews.uid FROM
                (SELECT views.* FROM
                    (SELECT
                        thread.uid
                    FROM
                        {$this->pdo->prefix}_discuss_threads AS thread
                    WHERE thread.id = :threadId1
                    UNION DISTINCT
                    SELECT
                        post.uid
                    FROM
                        {$this->pdo->prefix}_discuss_posts AS post
                    WHERE post.threadId = :threadId2) AS uids
                INNER JOIN
                    {$this->pdo->prefix}_discuss_thread_views as views
                    ON uids.uid = views.uid
                WHERE views.threadId = :threadId3
                ) AS threadUserViews
            JOIN
                {$this->pdo->prefix}_discuss_posts AS post2
                ON threadUserViews.threadId = post2.threadId
            WHERE postDate > viewDate
                AND post2.threadId = :threadId4
            GROUP BY threadUserViews.uid
            HAVING COUNT(post2.threadId) = 1
            "
        );
        $query->execute($params);

        while ($uid = $query->fetchColumn()) {
            $users[$uid] = \Depage\Auth\User::loadById($this->pdo, $uid);
        }

        return $users;
    }
    // }}}
    // {{{ addPost()
    /**
     * @brief addPost
     *
     * @param mixed
     * @return void
     **/
    public function addPost($text, $uid)
    {
        $post = new Post($this->pdo);
        $post->setData([
            'threadId' => $this->id,
            'post' => (string) $text,
            'uid' => $uid,
        ])
        ->save();

        return $post;
    }
    // }}}

    // {{{ getNumPosts()
    /**
     * @brief getNumPosts
     *
     * @param mixed
     * @return void
     **/
    public function getNumPosts()
    {
        return Post::countByThread($this->pdo, $this->id);
    }
    // }}}

    // {{{ setPost()
    /**
     * @brief setPost
     *
     * @param mixed $post
     * @return void
     **/
    public function setPost($post)
    {
        if (!empty($post) && substr($post, 0, 1) !== "<") {
            $post = "<p>" . str_replace("\n", "</p><p>", $post) . "</p>";
        }

        $this->data['post'] = $post;
        $this->dirty['post'] = true;

        return $this;
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

        if ($isNew) {
            $this->lastPostDate = date('Y-m-d H:i:s');
        }

        $dirty = array_keys($this->dirty, true);

        if (count($dirty) > 0) {
            if ($isNew) {
                $query = "INSERT INTO {$this->pdo->prefix}_discuss_threads";
            } else {
                $query = "UPDATE {$this->pdo->prefix}_discuss_threads";
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
        }
    }
    // }}}

    // {{{ processVote()
    /**
     * @brief processVote
     *
     * @return void
     **/
    public function processVote(\Depage\Auth\User $user)
    {
        if (!empty($user) && !empty($_POST['action']) && $_POST['action'] == "changeVote" && !empty($_POST['id']) && !empty($_POST['vote'])) {
            list($type, $id) = explode("-", $_POST['id']);

            if ($type == "post") {
                $el = Post::loadById($this->pdo, $id);
            } else if ($type == "thread" && $this->id == $id) {
                $el = $this;
            } else {
                return;
            }

            $el->vote($user->id, $_POST['vote']);

            $result = [
                'uid' => $user->id,
                'id' => "{$type}-{$el->id}",
                'upvotes' => $el->upvotes,
                'downvotes' => $el->downvotes,
            ];
            echo(json_encode($result, \JSON_NUMERIC_CHECK));
            die();
        }
    }
    // }}}
    // {{{ setLastViewedPost()
    /**
     * @brief setLastViewedPost
     *
     * @param mixed $
     * @return void
     **/
    public function setLastViewedPost($user, $post)
    {
        if (!$user) {
            return false;
        }
        if (!$post) {
            $postId = NULL;
        } else {
            $postId = $post->id;
        }

        $query = $this->pdo->prepare("
            REPLACE INTO
                {$this->pdo->prefix}_discuss_thread_views
            SET
                uid=:uid,
                threadId=:threadId,
                postId=:postId
        ");
        return $query->execute([
            'uid' => $user->id,
            'threadId' => $this->id,
            'postId' => $postId,
        ]);
    }
    // }}}
    // {{{ getLastViewedPost()
    /**
     * @brief getLastViewedPost
     *
     * @param mixed $user
     * @return void
     **/
    public function getLastViewedPost($user)
    {
        if (!$user) {
            return false;
        }

        return Post::loadByUserLastViewed($this->pdo, $this, $user);
    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
