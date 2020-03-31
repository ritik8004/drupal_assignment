<?php

namespace App\Session;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Drupal\Component\Utility\Crypt;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionUtils;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

/**
 * Default session handler.
 */
class SessionHandler extends SessionHandlerProxy implements \SessionHandlerInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The database connection.
   *
   * @var \Doctrine\DBAL\Driver\Connection
   */
  protected $connection;

  /**
   * SessionHandler constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Doctrine\DBAL\Driver\Connection $connection
   *   The database connection.
   */
  public function __construct(RequestStack $request_stack, Connection $connection) {
    $this->requestStack = $request_stack;
    $this->connection = $connection;

    // Here we try to set the legacy cookies we set in close()
    // in original expected keys if for some reason they are not available.
    // We do this here as this is invoked before starting the session.
    $request = $this->requestStack->getCurrentRequest();
    $name = session_name();
    if (empty($_COOKIE[$name]) && !empty($_COOKIE[$name . '-legacy'])) {
      $_COOKIE[$name] = $_COOKIE[$name . '-legacy'];
      $request->cookies->set($name, $_COOKIE[$name . '-legacy']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function open($save_path, $name) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function read($sid) {
    $data = '';
    if (!empty($sid)) {
      // Read the session data from the database.
      $query = $this->connection
        ->executeQuery('SELECT session FROM sessions WHERE sid = ? limit 0, 1', [Crypt::hashBase64($sid)], [ParameterType::STRING]);
      $data = (string) $query->fetch(FetchMode::COLUMN);
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function write($sid, $value) {
    // The exception handler is not active at this point, so we need to do it
    // manually.
    if (empty($value)) {
      return TRUE;
    }

    try {
      $request = $this->requestStack->getCurrentRequest();
      if (empty($request)) {
        return FALSE;
      }

      $fields = [
        'sid' => Crypt::hashBase64($sid),
        'uid' => $request->getSession()->get('uid', 0),
        'hostname' => $request->getClientIP(),
        'session' => $value,
        'timestamp' => (int) $_SERVER['REQUEST_TIME'],
      ];

      $query = "INSERT INTO sessions (sid, uid, hostname, session, timestamp) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE session = ?, timestamp = ?";
      $values = array_merge(array_values($fields), [$fields['session'], $fields['timestamp']]);
      $stmt = $this->connection->executeQuery($query, $values);
      $stmt->execute();
      return TRUE;
    }
    catch (\Exception $exception) {
      // If we are displaying errors, then do so with no possibility of a
      // further uncaught exception being thrown.
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    // Changes around how the browsers handle cookies when redirecting back
    // from another sites (like cybersource or k-net for us) has forced us to
    // add hacks like below. We set the cookie without SameSite=None for to
    // support both new and old browsers.
    //
    // @see web.dev/samesite-cookie-recipes/#handling-incompatible-clients
    $originalCookie = SessionUtils::popSessionCookie(session_name(), session_id());
    if ($originalCookie) {
      header($originalCookie, FALSE);

      $legacy = str_replace(session_name(), session_name() . '-legacy', $originalCookie);
      $legacy = str_replace('; SameSite=none', '', $legacy);
      header($legacy, FALSE);
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function destroy($sid) {
    // Delete session data.
    $this->connection->delete('sessions', ['sid' => Crypt::hashBase64($sid)]);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function gc($lifetime) {
    // Be sure to adjust 'php_value session.gc_maxlifetime' to a large enough
    // value. For example, if you want user sessions to stay in your database
    // for three weeks before deleting them, you need to set gc_maxlifetime
    // to '1814400'. At that value, only after a user doesn't log in after
    // three weeks (1814400 seconds) will their session be removed.
    $qb = $this->connection->createQueryBuilder();
    $qb->delete('sessions');
    $qb->where('timestamp < :timestamp');
    $qb->setParameter('project', (int) $_SERVER['REQUEST_TIME'] - $lifetime);
    $qb->execute();
    return TRUE;
  }

}
