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

use \Depage\Html\Html;
use \Depage\Html\Cleaner;

/**
 * @brief Discuss
 * Class Discuss
 */
class Discuss
{
    /**
     * @brief html
     **/
    protected $html = "";

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
        $this->htmlOptions = array(
            'template_path' => __DIR__ . "/Tpl/",
            'clean' => "space",
        );

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

    // {{{ process()
    /**
     * @brief process
     *
     * @param mixed
     * @return void
     **/
    public function process()
    {
        // @todo add better router
        $action = !empty($_GET['action']) ? $_GET['action'] : "";

        if ($action == "" || $action == "topics") {
            $this->html = $this->renderAllTopics();
        } else if ($action == "threads") {
            $this->html = $this->renderTopic($_GET['topic']);
        }
    }
    // }}}

    // {{{ renderAllTopics()
    /**
     * @brief renderAllTopics
     *
     * @param mixed
     * @return void
     **/
    public function renderAllTopics()
    {
        $topics = $this->loadAllTopics();

        var_dump($topics);
        $html = new Html("Topics.tpl", array(
            'topics' => $topics,
            'user' => null,
        ), $this->htmlOptions);

        return $html;
    }
    // }}}
    // {{{ renderTopic()
    /**
     * @brief renderTopic
     *
     * @param mixed $topicId
     * @return void
     **/
    public function renderTopic($topicId)
    {
        $topic = $this->loadTopicById($topicId);

        var_dump($topic);

        return "";
    }
    // }}}

    // {{{ __toString()
    /**
     * @brief __toString
     *
     * @return void
     **/
    public function __toString()
    {
        return (string) $this->html;
    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
