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

    // {{{ loadThreads()
    /**
     * @brief loadThreads
     *
     * @param mixed $pdo
     * @return void
     **/
    public static function loadThreads($pdo)
    {

    }
    // }}}
    // {{{ loadThreadById()
    /**
     * @brief loadThreadById
     *
     * @param mixed $pdo
     * @return void
     **/
    public static function loadThreadById($pdo)
    {

    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
