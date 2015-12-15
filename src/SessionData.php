<?php

namespace firegate666\Doctrine\Session;

/**
 * @Entity @Table(name="SessionData")
 */
class SessionData
{
    /**
     * @Id
     * @Column(type="string")
     * @var string
     **/
    protected $sessionId;

    /**
     * @Column(type="text", nullable=TRUE)
     * @var string
     **/
    protected $sessionData;

    /**
     * @Column(type="integer")
     * @var int
     **/
    protected $lastHit;

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param mixed $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return mixed
     */
    public function getSessionData()
    {
        return $this->sessionData;
    }

    /**
     * @param mixed $sessionData
     */
    public function setSessionData($sessionData)
    {
        $this->sessionData = $sessionData;
    }

    /**
     * @return mixed
     */
    public function getLastHit()
    {
        return $this->lastHit;
    }

    /**
     * @param mixed $lastHit
     */
    public function setLastHit($lastHit)
    {
        $this->lastHit = $lastHit;
    }
}
