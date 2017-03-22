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
        "sticky" => 0,
    );

    /**
     * @brief primary
     **/
    static protected $primary = ["id"];

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
            "SELECT $fields
            FROM
                {$pdo->prefix}_discuss_threads AS thread
            WHERE thread.topicId = :topicId
            ORDER BY thread.sticky"
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array($pdo));
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
            "SELECT $fields
            FROM
                {$pdo->prefix}_discuss_threads AS thread
            WHERE thread.id = :threadId
            ORDER BY thread.sticky"
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array($pdo));
        $thread = $query->fetch();

        return $thread;

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
    // {{{ addPost()
    /**
     * @brief addPost
     *
     * @param mixed
     * @return void
     **/
    public function addPost($post, $uid)
    {
        $thread = new Post($this->pdo);
        $thread->setData([
            'threadId' => $this->id,
            'post' => $post,
            'uid' => $uid,
        ])
        ->save();

        return $post;
    }
    // }}}

    // {{{ getLink()
    /**
     * @brief getLink
     *
     * @param mixed
     * @return void
     **/
    public function getLink()
    {
        $link = "?" . http_build_query([
            'action' => "posts",
            'thread' => $this->id,
        ]);

        return $link;
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
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
