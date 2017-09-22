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

namespace Depage\Discuss\Forms;

/**
 * @brief Post
 * Class Post
 */
class Post extends \Depage\HtmlForm\HtmlForm
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
        $params['class'] = "new-post labels-on-top";

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
        $this->addRichtext("post", [
            'required' => true,
            'autogrow' => true,
            'allowedTags' => [
                "p",
                "br",
                "ul",
                "ol",
                "li",
                "blockquote",
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
