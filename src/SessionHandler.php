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
     * @param SessionDataInterface $sessionDataClass
     */
    public function __construct(EntityManagerInterface $entityManager, SessionDataInterface $sessionDataClass)
    {
        $this->entityManager = $entityManager;
        $this->sessionDataClass = $sessionDataClass;
    }

    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $sessionId The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
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
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxLifetime <p>
     * Sessions that have not updated for
     * the last maxLifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
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
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $savePath The path where to store/retrieve the session.
     * @param string $sessionId The session id.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($savePath, $sessionId)
    {
        $this->getOrCreateSessionData($sessionId);
        return true;
    }

    /**
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $sessionId The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($sessionId)
    {
        $sessionData = $this->getOrCreateSessionData($sessionId);
        return $sessionData->getSessionData();
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $sessionId The session id.
     * @param string $encodedSessionData <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION super global to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function write($sessionId, $encodedSessionData)
    {
        $this->getOrCreateSessionData($sessionId, $encodedSessionData);
        return true;
    }

    /**
     * Fetches the current session data
     * Creates new object if needed, optionally sets initial encoded session data
     * Updates last hit
     *
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

        // as soon as EntityManagerInterface or ObjectManager support entity as parameter, we can move this change in
        // will flush only the given session data which will improve db persisting since it has no side effects on the
        // application
        $this->entityManager->flush(/*$sessionData*/);

        return $sessionData;
    }
}
