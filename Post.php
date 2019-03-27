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
    use Traits\Votes;

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
        "visible" => 1,
    );

    /**
     * @brief primary
     **/
    static protected $primary = ["id"];

    /**
     * @brief voteTable
     **/
    static public $voteTable = "_discuss_post_votes";

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
                LEFT JOIN {$pdo->prefix}" . self::$voteTable . " AS vote
            ON post.id = vote.id
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
    // {{{ loadById()
    /**
     * @brief loadById
     *
     * @param mixed $
     * @return void
     **/
    public static function loadById($pdo, $postId)
    {
        $fields = "post." . implode(", post.", self::getFields());
        $params = [
            "postId" => $postId,
        ];

        $query = $pdo->prepare(
            "SELECT
                $fields,
                IFNULL(SUM(vote.upvote), 0) AS upvotes,
                IFNULL(SUM(vote.downvote), 0) AS downvotes
            FROM
                {$pdo->prefix}_discuss_posts AS post
                LEFT JOIN {$pdo->prefix}" . self::$voteTable . " AS vote
            ON post.id = vote.id
            WHERE post.id = :postId
            GROUP BY post.id
            "
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array($pdo));
        $post = $query->fetch();

        return $post;

    }
    // }}}
    // {{{ loadByUserLastViewed()
    /**
     * @brief loadByUserLastViewed
     *
     * @param mixed $
     * @return void
     **/
    public static function loadByUserLastViewed($pdo, $thread, $user)
    {
        $fields = "post." . implode(", post.", self::getFields());
        $params = [
            "threadId" => $thread->id,
            "uid" => $user->id,
        ];

        $query = $pdo->prepare(
            "SELECT
                $fields,
                IFNULL(SUM(vote.upvote), 0) AS upvotes,
                IFNULL(SUM(vote.downvote), 0) AS downvotes
            FROM
                {$pdo->prefix}_discuss_thread_views AS view,
                {$pdo->prefix}_discuss_posts AS post
                LEFT JOIN {$pdo->prefix}" . self::$voteTable . " AS vote
                    ON post.id = vote.id
            WHERE post.id = view.postId
                AND view.threadId = :threadId
                AND view.uid = :uid
            GROUP BY post.id
            "
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array($pdo));
        $post = $query->fetch();

        return $post;
    }
    // }}}
    // {{{ loadThread()
    /**
     * @brief loadThread
     *
     * @param mixed
     * @return void
     **/
    public function loadThread()
    {
        return Thread::loadById($this->pdo, $this->threadId);
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
                COUNT(post.id)
            FROM
                {$pdo->prefix}_discuss_posts AS post,
                {$pdo->prefix}_discuss_threads AS thread
            WHERE post.threadId = thread.id AND thread.topicId = :topicId
            "
        );
        $query->execute($params);

        $count = $query->fetchColumn();

        return $count;

    }
    // }}}
    // {{{ countByThread()
    /**
     * @brief countByThread
     *
     * @param mixed $
     * @return void
     **/
    public static function countByThread($pdo, $threadId)
    {
        $params = [
            "threadId" => $threadId,
        ];

        $query = $pdo->prepare(
            "SELECT
                COUNT(*)
            FROM
                {$pdo->prefix}_discuss_posts AS post
            WHERE post.threadId = :threadId
            "
        );
        $query->execute($params);

        $count = $query->fetchColumn();

        return $count;

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
        $text = strip_tags(str_replace("<", " <", $this->post));

        $count = preg_match_all("/@([_a-zA-Z0-9]+)/", $text, $matches);

        if ($count > 0) {
            foreach($matches[1] as $username) {
                $users[$username] = true;
            }
        }

        return array_keys($users);
    }
    // }}}
    // {{{ loadMentionedUsers()
    /**
     * @brief loadMentionedUsers
     *
     * @param mixed
     * @return void
     **/
    public function loadMentionedUsers()
    {
        $mentions = $this->getMentions();

        foreach($mentions as $username) {
            try {
                $u = \Depage\Auth\User::loadByUsername($this->pdo, $username);
                $users[$u->id] = $u;
            } catch (\Exception $e) {
            }
        }

        return $users;
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

                $cmd = $this->pdo->prepare("UPDATE {$this->pdo->prefix}_discuss_threads SET lastPostDate=NOW(), editDate=editDate WHERE id = :threadId");
                $cmd->execute([
                    'threadId' => $this->threadId,
                ]);
            }

            if ($success) {
                $this->dirty = array_fill_keys(array_keys(static::$fields), false);
            }
        }
    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
