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

namespace Depage\Discuss\Forms;

/**
 * @brief Thread
 * Class Thread
 */
class Thread extends \Depage\HtmlForm\HtmlForm
{
    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed $name, $params = []
     * @return void
     **/
    public function __construct($name, $params = [])
    {
        $params['class'] = $params['class'] ?? "";
        $params['class'] .= " new-post labels-on-top";
        $params['label'] = _("Post new thread");

        parent::__construct($name, $params);

    }
    // }}}
    // {{{ addChildElements()
    /**
     * @brief addChildElements
     *
     * @param mixed
     * @return void
     **/
    public function addChildElements()
    {
        $this->addText("subject", [
            'label' => _("Title"),
            'class' => "title",
            'required' => true,
            'maxlength' => 100,
        ]);
        $this->addRichtext("post", [
            'label' => _("Post"),
            'class' => "post",
            'required' => true,
            'autogrow' => true,
            'allowedTags' => [
                "p",
                "br",
                "ul",
                "ol",
                "li",
                "a",
                "b",
                "strong",
                "i",
                "em",
            ],
        ]);
    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
