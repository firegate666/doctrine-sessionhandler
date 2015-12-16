<?php

namespace firegate666\Doctrine\Session;

use Doctrine\ORM\EntityManagerInterface;
use SessionHandlerInterface;

/**
 * use as session save handler for PHP
 *
 * @example
 *  $entityManager = EntityManager::create($connConfig, $config);
 *  session_set_save_handler(new SessionHandler($entityManager));
 */
class SessionHandler implements SessionHandlerInterface
{

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var SessionDataInterface */
    protected $sessionDataClass;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, SessionDataInterface $sessionDataClass)
    {
        $this->entityManager = $entityManager;
        $this->sessionDataClass = $sessionDataClass;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy a session
     *
     * @param string $sessionId The session ID being destroyed.
     * @return bool
     */
    public function destroy($sessionId)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(get_class($this->sessionDataClass), 's');
        $qb->where($qb->expr()->eq('s.sessionId', ':sessionId'));
        $qb->setParameter('sessionId', $sessionId);

        $qb->getQuery()->execute();

        return true;
    }

    /**
     * Cleanup old sessions
     *
     * @param int $maxLifetime
     * @return bool
     */
    public function gc($maxLifetime)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(get_class($this->sessionDataClass), 's');
        $qb->where($qb->expr()->lt('s.lastHit', time() - $maxLifetime));

        $qb->getQuery()->execute();

        return true;
    }

    /**
     * Initialize session
     *
     * @param string $savePath The path where to store/retrieve the session.
     * @param string $sessionId The session id.
     * @return bool
     */
    public function open($savePath, $sessionId)
    {
        $this->getOrCreateSessionData($sessionId);
        return true;
    }

    /**
     * Read session data
     *
     * @param string $sessionId The session id to read data for.
     * @return string
     */
    public function read($sessionId)
    {
        $sessionData = $this->getOrCreateSessionData($sessionId);
        return $sessionData->getSessionData();
    }

    /**
     * Write session data
     *
     * @param string $sessionId The session id.
     * @param string $encodedSessionData
     * @return bool
     */
    public function write($sessionId, $encodedSessionData)
    {
        $this->getOrCreateSessionData($sessionId, $encodedSessionData);
        return true;
    }

    /**
     * @param string $sessionId
     * @param string|null $encodedSessionData
     * @return SessionDataInterface
     */
    protected function getOrCreateSessionData($sessionId, $encodedSessionData = null)
    {
        $sessionData = $this->entityManager->getRepository(get_class($this->sessionDataClass))->find($sessionId);

        if (empty($sessionData)) { // no session found, create one
            $sessionData = clone $this->sessionDataClass;
            $sessionData->setSessionId($sessionId);
        }

        if ($encodedSessionData !== null) { // updated session data
            $sessionData->setSessionData($encodedSessionData);
        }

        // access to session data updates last hit
        $sessionData->setLastHit(time());

        $this->entityManager->persist($sessionData);
        $this->entityManager->flush();

        return $sessionData;
    }
}
