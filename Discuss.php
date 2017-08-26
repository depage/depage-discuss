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

    /**
     * @brief user
     **/
    protected $user = null;

    /**
     * @brief baseUrl
     **/
    protected $baseUrl = "";

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
        $this->baseUrl = parse_url($_SERVER['REQUEST_URI'], \PHP_URL_PATH);
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

    // {{{ redirect
    public static function redirect($url)
    {
        header('Location: ' . $url);
        die( "Tried to redirect you to <a href=\"$url\">$url</a>");
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
        $action = !empty($_GET['action']) ? $_GET['action'] : "";

        if ($action == "" || $action == "topics") {
            $this->html = $this->renderAllTopics();
        } else if ($action == "topic") {
            $this->html = $this->renderTopic($_GET['id']);
        } else if ($action == "thread") {
            $this->html = $this->renderThread($_GET['id']);
        }
    }
    // }}}
    // {{{ processVote()
    /**
     * @brief processVote
     *
     * @return void
     **/
    public function processVote()
    {
        if (!empty($this->user) && !empty($_POST['id']) && !empty($_POST['vote'])) {
            list($type, $id) = explode("-", $_POST['id']);

            if ($type == "post") {
                $el = Post::loadById($this->pdo, $id);
            } else if ($type == "thread") {
                $el = Thread::loadById($this->pdo, $id);
            } else {
                die();
            }

            $el->vote($this->user->id, $_POST['vote']);

            $result = [
                'uid' => $this->user->id,
                'id' => "{$type}-{$el->id}",
                'upvotes' => $el->upvotes,
                'downvotes' => $el->downvotes,
            ];
            echo(json_encode($result, \JSON_NUMERIC_CHECK));
            die();
        }
    }
    // }}}

    // {{{ getLinkTo()
    /**
     * @brief getLinkTo
     *
     * @param mixed
     * @return void
     **/
    public function getLinkTo($object)
    {
        $action = "";
        $hash = "";
        if ($object instanceof Topic) {
            $action = "topic";
            $id = $object->id;
        } else if ($object instanceof Thread) {
            $action = "thread";
            $id = $object->id;
        } else if ($object instanceof Post) {
            $action = "thread";
            $id = $object->threadId;
            $hash = "#post-{$object->id}";
        } else {
            return "";
        }

        $link =  "{$this->baseUrl}?" . http_build_query([
            'action' => $action,
            'id' => $id,
        ]) . $hash;

        return $link;
    }
    // }}}
    // {{{ getProfileImage()
    /**
     * @brief getProfileImage
     *
     * @param mixed $
     * @return void
     **/
    public function getProfileImage($user)
    {
        return $user->getProfileImage();
    }
    // }}}
    // {{{ getLoginMessage()
    /**
     * @brief getLoginMessage
     *
     * @param mixed
     * @return void
     **/
    public function getLoginMessage()
    {
        return "";
    }
    // }}}
    // {{{ getCurrentUser()
    /**
     * @brief getCurrentUser
     *
     * @param mixed
     * @return void
     **/
    public function getCurrentUser()
    {
        return $this->user;
    }
    // }}}
    // {{{ setCurrentUser()
    /**
     * @brief setCurrentUser
     *
     * @param mixed $user
     * @return void
     **/
    public function setCurrentUser($user)
    {
        $this->user = $user;

        return $this;
    }
    // }}}
    // {{{ setBaseUrl()
    /**
     * @brief setBaseUrl
     *
     * @param mixed $url
     * @return void
     **/
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;

        return $this;
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
            'discuss' => $this,
            'topics' => $topics,
            'user' => $this->getCurrentUser(),
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

        if (!empty($this->user)) {
            $form = new Forms\Thread("new-thread-$topicId", [
            ]);
            $form->process();

            if ($form->validate()) {
                $values = $form->getValues();

                $thread = $topic->addThread($values['subject'], (string) $values['post'], $this->user->id);

                $form->clearSession();

                self::redirect($this->getLinkTo($thread));
            }
        } else {
            $form = $this->getLoginMessage();
        }
        $threads = $topic->loadAllThreads();

        $html = new Html("Topic.tpl", [
            'discuss' => $this,
            'topic' => $topic,
            'threads' => $threads,
            'user' => $this->getCurrentUser(),
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

        if (!empty($this->user)) {
            $this->processVote();

            $form = new Forms\Post("new-post-$threadId");
            $form->process();

            if ($form->validate()) {
                $values = $form->getValues();

                $post = $thread->addPost((string) $values['post'], $this->user->id);

                $form->clearSession();

                self::redirect($this->getLinkTo($post));
            }
        } else {
            $form = $this->getLoginMessage();
        }

        $posts = $thread->loadPosts(0, 400);

        $html = new Html("Thread.tpl", [
            'discuss' => $this,
            'thread' => $thread,
            'posts' => $posts,
            'user' => $this->getCurrentUser(),
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
        $user = \Depage\Auth\User::loadById($this->pdo, $uid);

        $html = new Html('UserInfo.tpl', [
            'discuss' => $this,
            'user' => $user,
        ], $this->htmlOptions);

        echo($html);
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
