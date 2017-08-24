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
    );

    /**
     * @brief primary
     **/
    static protected $primary = ["id"];

    /**
     * @brief voteTable
     **/
    static protected $voteTable = "_discuss_post_votes";

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
            ON post.id = vote.postId
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
