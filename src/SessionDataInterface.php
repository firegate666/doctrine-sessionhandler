<?php

namespace firegate666\Doctrine\Session;

interface SessionDataInterface
{
    /**
     * @return mixed
     */
    public function getSessionId();

    /**
     * @param mixed $sessionId
     */
    public function setSessionId($sessionId);

    /**
     * @return mixed
     */
    public function getSessionData();

    /**
     * @param mixed $sessionData
     */
    public function setSessionData($sessionData);

    /**
     * @return mixed
     */
    public function getLastHit();

    /**
     * @param mixed $lastHit
     */
    public function setLastHit($lastHit);
}
