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
     * @brief seconds before a user is notified again for the same thread
     **/
    const NOTIFICATION_INTERVAL = 21600;

    /**
     * @brief pdo object for database access
     **/
    protected $pdo = null;

    /**
     * @brief number of posts, when loaded with the thread
     **/
    protected $numPosts = null;
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
        $fields .= ", (
            SELECT COUNT(*) FROM {$pdo->prefix}_discuss_posts AS p
            WHERE p.threadId = thread.id AND p.visible = 1
        ) AS numPosts";
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
                AND thread.visible = 1
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
        $fields .= ", (
            SELECT COUNT(*) FROM {$pdo->prefix}_discuss_posts AS p
            WHERE p.threadId = thread.id AND p.visible = 1
        ) AS numPosts";
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
    public static function loadByUser($pdo, $user, $range = null)
    {
        $fields = "thread." . implode(", thread.", self::getFields());
        $fields .= ", (
            SELECT COUNT(*) FROM {$pdo->prefix}_discuss_posts AS p
            WHERE p.threadId = thread.id AND p.visible = 1
        ) AS numPosts";
        $params = [
            "uid1" => $user->id,
            "uid2" => $user->id,
        ];
        $where = "";

        if (!is_null($range)) {
            $params["from"] = $range->getStartDate()->format("Y-m-d");
            $params["to"] = $range->getEndDate()->format("Y-m-d");
            $where = "AND thread.postDate >= :from AND thread.postDate < :to";
        }

        $query = $pdo->prepare(
            "SELECT $fields
            FROM
                {$pdo->prefix}_discuss_threads AS thread
                LEFT JOIN {$pdo->prefix}_discuss_posts AS post
                    ON thread.id = post.threadId
            WHERE
                (thread.uid = :uid1 OR post.uid = :uid2)
                AND thread.topicId IS NOT NULL
                AND thread.visible = 1
                $where
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
            WHERE thread.topicId = :topicId
                AND thread.visible = 1"
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
    public function loadPosts($from, $to, $order = "ASC")
    {
        $posts = Post::loadByThread($this->pdo, $this->id, $from, $to, $order);

        return $posts;
    }
    // }}}
    // {{{ loadPostsSince()
    /**
     * @brief loadPostsSince
     *
     * @param mixed $
     * @return void
     **/
    public function loadPostsSince($postId)
    {
        $posts = Post::loadByThreadSince($this->pdo, $this->id, $postId);

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
    public function loadUsersToNotify($additionalUsers = [], $throttleSeconds = self::NOTIFICATION_INTERVAL)
    {
        $users = [];

        $author = \Depage\Auth\CachedUser::loadById($this->pdo, $this->uid);
        if ($author) {
            $users[$author->id] = $author;
        }

        $query = $this->pdo->prepare(
            "SELECT DISTINCT uid FROM {$this->pdo->prefix}_discuss_posts WHERE threadId = :threadId"
        );
        $query->execute([
            'threadId' => $this->id,
        ]);

        while ($uid = $query->fetchColumn()) {
            if (!isset($users[$uid])) {
                $users[$uid] = \Depage\Auth\CachedUser::loadById($this->pdo, $uid);
            }
        }

        foreach ($additionalUsers as $user) {
            if ($user && !isset($users[$user->id])) {
                $users[$user->id] = $user;
            }
        }

        return $this->filterUsersToNotify($users, $throttleSeconds);
    }
    // }}}
    // {{{ filterUsersToNotify()
    /**
     * @brief filters the given users down to those who should be notified
     *
     * A user is notified when there is a visible post newer than the last
     * viewed post and the notification throttle has expired. Opening the thread
     * after the last notification resets the throttle.
     *
     * @param mixed $users
     * @param mixed $throttleSeconds
     * @return void
     **/
    public function filterUsersToNotify($users, $throttleSeconds = self::NOTIFICATION_INTERVAL)
    {
        $usersById = [];
        foreach ($users as $user) {
            if ($user && !empty($user->id)) {
                $usersById[$user->id] = $user;
            }
        }

        if (count($usersById) === 0) {
            return [];
        }

        $params = [
            "threadId1" => $this->id,
            "threadId2" => $this->id,
            "notificationCutoff" => date("Y-m-d H:i:s", time() - $throttleSeconds),
        ];

        $selects = [];
        $i = 0;
        foreach ($usersById as $user) {
            $i++;
            $params["uid$i"] = $user->id;
            $selects[] = "SELECT :uid$i AS uid";
        }

        $query = $this->pdo->prepare(
            "SELECT u.uid
            FROM (
                " . implode(" UNION ALL ", $selects) . "
            ) AS u
            LEFT JOIN {$this->pdo->prefix}_discuss_thread_views AS views
                ON u.uid = views.uid AND views.threadId = :threadId1
            LEFT JOIN {$this->pdo->prefix}_discuss_posts AS lastViewed
                ON lastViewed.id = views.postId
            WHERE EXISTS (
                SELECT 1
                FROM {$this->pdo->prefix}_discuss_posts AS unseen
                WHERE unseen.threadId = :threadId2
                    AND unseen.visible = 1
                    AND unseen.postDate > COALESCE(lastViewed.postDate, '1970-01-02 00:00:00')
            )
            AND (
                views.notificationDate IS NULL
                OR views.viewDate >= views.notificationDate
                OR views.notificationDate < :notificationCutoff
            )
            "
        );
        $query->execute($params);

        $result = [];
        while ($uid = $query->fetchColumn()) {
            if (isset($usersById[$uid])) {
                $result[$uid] = $usersById[$uid];
            }
        }

        return $result;
    }
    // }}}
    // {{{ markUsersNotified()
    /**
     * @brief records that the given users have been notified for this thread
     *
     * @param mixed $users
     * @return void
     **/
    public function markUsersNotified($users)
    {
        $query = $this->pdo->prepare("
            INSERT INTO
                {$this->pdo->prefix}_discuss_thread_views
            SET
                uid=:uid,
                threadId=:threadId,
                postId=NULL,
                viewDate='1970-01-02 00:00:00',
                notificationDate=NOW()
            ON DUPLICATE KEY UPDATE
                notificationDate=NOW()
        ");

        foreach ($users as $user) {
            if (!$user || empty($user->id)) {
                continue;
            }

            $query->execute([
                'uid' => $user->id,
                'threadId' => $this->id,
            ]);
        }
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
        if (!is_null($this->numPosts)) {
            return (int) $this->numPosts;
        }

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
    // {{{ setVisible()
    /**
     * @brief setVisible
     *
     * @param mixed $value
     * @return void
     **/
    protected function setVisible($value)
    {
        $this->data['visible'] = (int) $value;
        $this->dirty['visible'] = true;

        return $this;
    }
    // }}}
    // {{{ publish()
    /**
     * @brief publish
     *
     * Makes the thread visible and records the change time.
     *
     * @return self
     **/
    public function publish(): self
    {
        $this->setVisible(true);
        $this->editDate = date('Y-m-d H:i:s');
        $this->save();

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
            INSERT INTO
                {$this->pdo->prefix}_discuss_thread_views
            SET
                uid=:uid,
                threadId=:threadId,
                postId=:postId,
                viewDate=NOW()
            ON DUPLICATE KEY UPDATE
                postId=VALUES(postId),
                viewDate=NOW()
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
