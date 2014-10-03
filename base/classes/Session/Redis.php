<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Database-based session class.
 *
 * Sample schema:
 *
 *     CREATE TABLE  `sessions` (
 *         `session_id` VARCHAR( 24 ) NOT NULL,
 *         `last_active` INT UNSIGNED NOT NULL,
 *         `contents` TEXT NOT NULL,
 *         PRIMARY KEY ( `session_id` ),
 *         INDEX ( `last_active` )
 *     ) ENGINE = MYISAM ;
 *
 * @package    Kohana/Database
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Session_Redis extends Session
{

    // Database instance
    protected $_redis;

    // Database column names
    protected $_columns
        = array(
            'session_id' => 'session_id',
            'last_active' => 'last_active',
            'contents' => 'contents'
        );

    // The current session id
    protected $_session_id;

    public function __construct(array $config = NULL, $id = NULL)
    {
        // Check for the redis extention
        if (!extension_loaded('redis')) {
            throw new Cache_Exception('Redis PHP extention not loaded');
        }
        // Setup Redis
        $this->_redis = new Redis;

        // Load servers from configuration
        $server = Arr::get($config, 'server', NULL);

        if (!$server) {
            // Throw an exception if no server found
            throw new Session_Exception('No Redis server defined in configuration');
        }
        // Merge the defined config with defaults
        $server += array(
            'host' => 'localhost',
            'port' => 11211,
            'persistent' => FALSE,
            'password' => NULL,
            'timeout' => 1,
            'prefix' => 'session'
        );
        $method = $server['persistent'] ? 'pconnect' : 'connect';
        if (!$this->_redis->$method($server['host'], $server['port'], $server['timeout'])) {
            throw new Cache_Exception('Redis could not connect to host \':host\' using port \':port\'', array(
                ':host' => $server['host'],
                ':port' => $server['port']
            ));
        }
        if (!empty($server['password'])) {
            $this->_redis->auth($server['password']);
        }
        if (!empty($server['prefix'])) {
            $this->_redis->setOption(Redis::OPT_PREFIX, $server['prefix'] . ':'); // use custom prefix on all keys
        }
        parent::__construct($config, $id);
    }

    public function id()
    {
        return $this->_session_id;
    }

    protected function _read($id = NULL)
    {
        if ($id OR $id = Cookie::get($this->_name)) {
            $result = $this->_redis->hGetAll($id);
            if (!empty($result)) {
                // Set the current session id
                $this->_session_id = $id;

                // Return the contents
                return $result['contents'];
            }
        }

        // Create a new session id
        $this->_regenerate();

        return NULL;
    }

    protected function _regenerate()
    {
        if ($this->_session_id) {
            $this->_redis->del($this->_session_id);
        }
        do {
            // Create a new session id
            $id = md5(uniqid(NULL, TRUE));
        } while (!$this->_redis->hSetNx($id, 'created', time()));

        $this->_session_id = $id;

        $this->write();

        return $this->_session_id;
    }

    protected function _write()
    {
        $this->_redis->hMSet(
            $this->_session_id,
            array(
                'last_active' => time(),
                'contents' => $this->__toString()
            )
        );
        $this->_redis->expire($this->_session_id, $this->_lifetime + 7200);

        // Update the cookie with the new session id
        Cookie::set($this->_name, $this->_session_id, $this->_lifetime);

        return TRUE;
    }

    /**
     * @return  bool
     */
    protected function _restart()
    {
        $this->_regenerate();

        return TRUE;
    }

    protected function _destroy()
    {
        // Delete the current session
        $this->_redis->del($this->_session_id);
        // Delete the cookie
        Cookie::delete($this->_name);

        return TRUE;
    }

} // End Session_Database
