<?php
/**
 * @file    Topic.php
 *
 * description
 *
 * copyright (c) 2017 Frank Hellenkamp [jonas@depage.net]
 *
 * @author    Frank Hellenkamp [jonas@depage.net]
 */

namespace Depage\Discuss;

/**
 * @brief Topic
 * Class Topics
 */
class Topic extends \Depage\Entity\Entity
{
    // {{{ variables
    /**
     * @brief fields
     **/
    static protected $fields = array(
        "id" => null,
        "title" => "",
        "description" => "",
        "pos" => 0,
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

    // {{{ loadAll()
    /**
     * @brief loadAll
     *
     * @param mixed $
     * @return void
     **/
    public static function loadAll($pdo)
    {
        $fields = "t." . implode(", t.", self::getFields());
        $params = [];

        $query = $pdo->prepare(
            "SELECT $fields
            FROM
                {$pdo->prefix}_discuss_topics AS t
            ORDER BY t.pos"
        );
        $query->execute($params);

        // pass pdo-instance to constructor
        $query->setFetchMode(\PDO::FETCH_CLASS, get_called_class(), array($pdo));
        $n = $query->fetchAll();

        return $n;
    }
    // }}}
    // {{{ loadById()
    /**
     * @brief loadById
     *
     * @param mixed $
     * @return void
     **/
    public static function loadById($pdo, $id)
    {

    }
    // }}}

    // {{{ loadAllThreads()
    /**
     * @brief loadAllThreads
     *
     * @param mixed
     * @return void
     **/
    public function loadAllThreads()
    {
        $threads = Thread::loadByTopic($this->pdo, $this->id);

        return $threads;
    }
    // }}}
    // {{{ loadThreadById()
    /**
     * @brief loadThreadById
     *
     * @param mixed $param
     * @return void
     **/
    public function loadThreadById($id)
    {
        $thread = Thread::loadById($this->pdo, $id);

        return $thread;
    }
    // }}}

    // {{{ addThread()
    /**
     * @brief addThread
     *
     * @param mixed
     * @return void
     **/
    public function addThread($subject, $post, $uid)
    {
        $thread = new Thread($this->pdo);
        $thread->setData([
            'subject' => $subject,
            'topicId' => $this->id,
            'post' => $post,
            'uid' => $uid,
        ])
        ->save();

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
                $query = "INSERT INTO {$this->pdo->prefix}_discuss_topics";
            } else {
                $query = "UPDATE {$this->pdo->prefix}_discuss_topics";
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
