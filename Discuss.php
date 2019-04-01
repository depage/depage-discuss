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

    /**
     * @brief breadcrumps
     **/
    public $breadcrumps = "";

    /**
     * @brief subject
     **/
    public $subject = "";

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
        $this->subject = _("Forum");
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
    // {{{ loadThreadsByCurrentUser()
    /**
     * @brief loadThreadsByCurrentUser
     *
     * @param mixed $
     * @return void
     **/
    public function loadThreadsByCurrentUser()
    {
        $topics = Thread::loadByUser($this->pdo, $this->user);

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

    // {{{ getLinkTo()
    /**
     * @brief getLinkTo
     *
     * @param mixed
     * @return void
     **/
    public function getLinkTo($object, $user = false)
    {
        $action = "";
        $hash = "";
        if ($object instanceof \Depage\Discuss\Discuss) {
            return $this->baseUrl;
        } else if ($object instanceof Topic) {
            $action = "topic";
            $id = $object->id;
        } else if ($object instanceof Thread) {
            $action = "thread";
            $id = $object->id;

            if ($user && $lastViewedPost = $object->getLastViewedPost($user)) {
                $hash = "#post-{$lastViewedPost->id}";
            }
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
    public function getLoginMessage($srcUrl)
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

        $this->breadcrumps = $this->renderBreadcrumpsTo($this);

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
        $this->breadcrumps = $this->renderBreadcrumpsTo($topic);

        if (!$topic) {
            return $this->notFound();
        }


        if (!empty($this->user)) {
            $form = new Forms\Thread("new-thread-$topicId", [
            ]);
            $form->process();

            if ($form->validate()) {
                $values = $form->getValues();

                $thread = $topic->addThread($values['subject'], (string) $values['post'], $this->user->id);
                $this->onThreadAdded($thread);

                $form->clearSession();

                self::redirect($this->getLinkTo($thread));
            }
        } else {
            $form = $this->getLoginMessage($this->getLinkTo($topic));
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
        $this->breadcrumps = $this->renderBreadcrumpsTo($thread);

        if (!$thread) {
            return $this->notFound();
        }

        if (!empty($this->user)) {
            $thread->processVote($this->user);

            $form = new Forms\Post("new-post-$threadId");
            $form->process();

            if ($form->validate()) {
                $values = $form->getValues();

                $post = $thread->addPost($values['post'], $this->user->id);
                $this->onPostAdded($post);

                $form->clearSession();

                self::redirect($this->getLinkTo($post));
            }
        } else {
            $form = $this->getLoginMessage($this->getLinkTo($thread));
        }

        $posts = $thread->loadPosts(0, 10000);
        $thread->setLastViewedPost($this->user, end($posts));

        $html = new Html("Thread.tpl", [
            'discuss' => $this,
            'thread' => $thread,
            'posts' => $posts,
            'user' => $this->user,
            'postForm' => $form,
        ], $this->htmlOptions);

        return $html;
    }
    // }}}
    // {{{ renderBreadcrumpsTo()
    /**
     * @brief renderBreadcrumpsTo
     *
     * @param mixed
     * @return void
     **/
    public function renderBreadcrumpsTo($object)
    {
        if (!$object) return "";

        $html = "";

        if ($object instanceof Topic) {
            $html .= $this->renderBreadcrumpsTo($this);
        } else if ($object instanceof Thread) {
            $topic = Topic::loadById($this->pdo, $object->topicId);
            $html .= $this->renderBreadcrumpsTo($topic);
        }

        $link = $this->getLinkTo($object);
        if ($object instanceof Topic) {
            $subject = _($object->subject);
        } else {
            $subject = $object->subject;
        }
        $html .= "<a href=\"$link\">" . htmlspecialchars($subject) . "</a> ";

        return $html;
    }
    // }}}
    // {{{ htmlUserInfo()
    /**
     * @brief htmlUserInfo
     *
     * @param mixed $uid
     * @return void
     **/
    public function htmlUserInfo($uid)
    {
        static $snippets = [];

        if (!isset($snippets[$uid])) {
            $user = \Depage\Auth\CachedUser::loadById($this->pdo, $uid);

            $snippets[$uid] = (string) new Html('UserInfo.tpl', [
                'discuss' => $this,
                'user' => $user,
            ], $this->htmlOptions);
        }

        return $snippets[$uid];
    }
    // }}}
    // {{{ renderThreadsByCurrentUser()
    /**
     * @brief renderThreadsByCurrentUser
     *
     * @param mixed
     * @return void
     **/
    public function renderThreadsByCurrentUser($max = null)
    {
        $threads = $this->loadThreadsByCurrentUser();
        $user = $this->getCurrentUser();

        if ($max !== null) {
            $threads = array_slice($threads, 0, $max, true);
        }

        if (count($threads) > 0) {
            $html = new Html("Topic.tpl", [
                'discuss' => $this,
                'threads' => $threads,
                'user' => $user,
            ], $this->htmlOptions);
        } else {
            $html = "<p>" . _("You are not involved in any discussions yet.") . "</p>";
        }

        return $html;
    }
    // }}}
    // {{{ replaceUserHandles()
    /**
     * @brief replaceUserHandles
     *
     * @param mixed $post
     * @return void
     **/
    public function replaceUserHandles($post)
    {
        // removed linked handles
        $post = preg_replace("/<a[^>]*>(@[_a-zA-Z0-9]+)<\/a>/i", '${1}', $post);

        // replace handles with linked handles
        $post = preg_replace_callback("/@([_a-zA-Z0-9]+)/i", function($matches) {
            $username = $matches[1];

            try {
                $user = \Depage\Auth\CachedUser::loadByUsername($this->pdo, $username);
                $link = $this->getLinkTo($user);

                return "<a href=\"$link\">@$username</a>";
            } catch (\Exception $e) {
            }

            return $username;
        }, $post);

        return $post;
    }
    // }}}
    // {{{ notFound()
    /**
     * @brief notFound
     *
     * @param mixed
     * @return void
     **/
    protected function notFound()
    {
        return "<p>" . _("Not found") . "</p>";
    }
    // }}}

    // {{{ onThreadAdded()
    /**
     * @brief onThreadAdded
     *
     * @param mixed
     * @return void
     **/
    protected function onThreadAdded($thread)
    {

    }
    // }}}
    // {{{ onPostAdded()
    /**
     * @brief onPostAdded
     *
     * @param mixed
     * @return void
     **/
    protected function onPostAdded($post)
    {

    }
    // }}}

    // {{{ parseMentions()
    /**
     * @brief parseMentions
     *
     * @param mixed $post
     * @return void
     **/
    public static function parseMentions($post)
    {
        $users = [];
        $text = strip_tags(str_replace("<", " <", $post));

        $count = preg_match_all("/@([_a-zA-Z0-9]+)/", $text, $matches);

        if ($count > 0) {
            foreach($matches[1] as $username) {
                $users[$username] = true;
            }
        }

        return array_keys($users);
    }
    // }}}
    // {{{ loadMentionsUsers()
    /**
     * @brief loadMentionedUsers
     *
     * @param mixed $pdo, $post
     * @return void
     **/
    public static function loadMentionedUsers($pdo, $post)
    {
        $users = [];
        $mentions = Discuss::parseMentions($post);

        foreach($mentions as $username) {
            try {
                $u = \Depage\Auth\CachedUser::loadByUsername($pdo, $username);
                $users[$u->id] = $u;
            } catch (\Exception $e) {
            }
        }

        return $users;
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
