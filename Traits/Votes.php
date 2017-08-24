<?php
/**
 * @file    Votes.php
 *
 * description
 *
 * copyright (c) 2017 Frank Hellenkamp [jonas@depage.net]
 *
 * @author    Frank Hellenkamp [jonas@depage.net]
 */

namespace Depage\Discuss\Traits;

/**
 * @brief Votes
 * Class Votes
 */
trait Votes
{
    /**
     * @brief upvotes
     **/
    protected $upvotes = 0;

    /**
     * @brief downvotes
     **/
    protected $downvotes = 0;

    // {{{ getUpvotes()
    /**
     * @brief getUpvotes
     *
     * @return void
     **/
    public function getUpvotes()
    {
        return $this->upvotes;

    }
    // }}}
    // {{{ getDownvotes()
    /**
     * @brief getDownvotes
     *
     * @return void
     **/
    public function getDownvotes()
    {
        return $this->downvotes;
    }
    // }}}

    // {{{ voteUp()
    /**
     * @brief voteUp
     *
     * @param mixed $uid
     * @return void
     **/
    public function voteUp($uid)
    {
        return $this->vote($uid, 1);
    }
    // }}}
    // {{{ voteDown()
    /**
     * @brief voteDown
     *
     * @param mixed $uid
     * @return void
     **/
    public function voteDown($uid)
    {
        return $this->vote($uid, -1);
    }
    // }}}
    // {{{ voteReset()
    /**
     * @brief voteReset
     *
     * @param mixed $uid
     * @return void
     **/
    public function voteReset($uid)
    {
        return $this->vote($uid, 0);
    }
    // }}}
    // {{{ vote()
    /**
     * @brief vote
     *
     * @param mixed $uid, $direction
     * @return void
     **/
    public function vote($uid, $direction)
    {
        if ($this->uid == $uid) {
            // users themselves cannot vote on their posts
            return $this;
        }
        $direction = (int) $direction;
        if ($direction > 0) {
            $upvote = 1;
            $downvote = 0;
        } else if ($direction < 0) {
            $upvote = 0;
            $downvote = 1;
        } else {
            $upvote = 0;
            $downvote = 0;
        }

        $query = $this->pdo->prepare("
            REPLACE INTO " . self::$voteTable . "
                (postId, uid, upvote, downvote) VALUES (:postId, :uid, :upvote, :downvote);
        ");
        $query->execute([
            "postId" => $this->id,
            "uid" => $uid,
            "upvote" => $upvote,
            "downvote" => $downvote,
        ]);

        // updated current votes in object
        $query = $this->pdo->prepare(
            "SELECT
                IFNULL(SUM(vote.upvote), 0) AS upvotes,
                IFNULL(SUM(vote.downvote), 0) AS downvotes
            FROM
                " . self::voteTable . " AS vote
            WHERE vote.postId = :postId
            "
        );
        $query->execute([
            "postId" => $this->id,
        ]);

        $vote = $query->fetchObject();
        $this->upvotes = $vote->upvotes;
        $this->downvotes = $vote->downvotes;

        return $this;
    }
    // }}}

}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
