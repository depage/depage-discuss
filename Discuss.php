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
        $this->htmlOptions = [
            'template_path' => __DIR__ . "/Tpl/",
            'clean' => "space",
        ];
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
    // {{{ loadThreadById()
    /**
     * @brief loadThreadById
     *
     * @param mixed
     * @return void
     **/
    public function loadThreadById($id)
    {
        $thread = Thread::loadById($this->pdo, $id);

        return $thread;
    }
    // }}}

    // {{{ addTopic()
    /**
     * @brief addTopic
     *
     * @param mixed
     * @return void
     **/
    public function addTopic($subject, $description)
    {
        $topic = new Topic($this->pdo);
        $topic->setData([
            "subject" => $subject,
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
        } else if ($action == "posts") {
            $this->html = $this->renderThread($_GET['thread']);
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

        $html = new Html("Overview.tpl", [
            'topics' => $topics,
            'user' => null,
        ], $this->htmlOptions);

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
        $form = new Forms\Thread("new-thread-$topicId", [
        ]);
        $form->process();

        if ($form->validate()) {
            $values = $form->getValues();

            $uid = 1;
            $topic->addThread($values['subject'], (string) $values['post'], $uid);

            $form->clearSession();
        }

        $threads = $topic->loadAllThreads();

        $html = new Html("Topic.tpl", [
            'topic' => $topic,
            'threads' => $threads,
            'user' => null,
            'threadForm' => $form,
        ], $this->htmlOptions);

        return $html;
    }
    // }}}
    // {{{ renderThread()
    /**
     * @brief renderThread
     *
     * @param mixed $threatId
     * @return void
     **/
    public function renderThread($threadId)
    {
        $thread = $this->loadThreadById($threadId);

        // @todo just temporary
        if (!empty($_POST['post'])) {
            $_POST['post'] = "<p>" . str_replace("\n", "</p><p>", $_POST['post']) . "</p>";
        }

        $form = new Forms\Post("new-post-$threadId", [
        ]);
        $form->process();

        if ($form->validate()) {
            $values = $form->getValues();

            $uid = 1;
            $thread->addPost((string) $values['post'], $uid);

            $form->clearSession();
        }

        $posts = $thread->loadPosts(0, 100);

        $html = new Html("Thread.tpl", [
            'thread' => $thread,
            'posts' => $posts,
            'user' => null,
            'renderUserInfo' => [$this, 'renderUserInfo'],
            'postForm' => $form,
        ], $this->htmlOptions);

        return $html;
    }
    // }}}
    // {{{ renderUserInfo()
    /**
     * @brief renderUserInfo
     *
     * @param mixed $uid
     * @return void
     **/
    public function renderUserInfo($uid)
    {
        $user = \Depage\Auth\User::loadById($uid);

        $html = new Html([
            'topic' => $topic,
            'threads' => $threads,
            'user' => null,
        ], $this->htmlOptions);

        return $html;

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
