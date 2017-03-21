<?php
/**
 * @file    Discuss.php
 *
 * Base class for discussions
 *
 * copyright (c) 2017 Frank Hellenkamp [jonas@depage.net]
 *
 * @author    Frank Hellenkamp [jonas@depage.net]
 */

namespace Depage\Discuss;

/**
 * @brief Discuss
 * Class Discuss
 */
class Discuss
{
    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed $pdo
     * @return void
     **/
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    // }}}
    // {{{ updateSchema()
    /**
     * @brief updateSchema
     *
     * @param mixed $pdo
     * @return void
     **/
    public static function updateSchema($pdo)
    {
        $schema = new \Depage\Db\Schema($pdo);

        $schema
            ->setReplace(function ($tableName) use ($pdo) {
                return $pdo->prefix . $tableName;
            })
            ->loadGlob(__DIR__ . "/Sql/*.sql")
            ->update();
    }
    // }}}

    // {{{ loadAllTopics()
    /**
     * @brief loadAllTopics
     *
     * @param mixed $
     * @return void
     **/
    public function loadAllTopics()
    {
        $topics = Topic::loadAll($this->pdo);

        return $topics;
    }
    // }}}
    // {{{ loadTopicById()
    /**
     * @brief loadTopicById
     *
     * @param mixed
     * @return void
     **/
    public function loadTopicById($id)
    {
        $topic = Topic::loadById($this->pdo, $id);

        return $topic;
    }
    // }}}

    // {{{ addTopic()
    /**
     * @brief addTopic
     *
     * @param mixed
     * @return void
     **/
    public function addTopic($title, $description)
    {
        $topic = new Topic($this->pdo);
        $topic->setData([
            "title" => $title,
            "description" => $description,
        ])->save();
    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
