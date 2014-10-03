<?php defined('SYSPATH') or die('No direct script access.');

/**
 * [Kohana Cache](api/Kohana_Cache) Redis driver,
 *
 * ### Supported cache engines
 *
 * *  [Redis](https://github.com/nicolasff/phpredis)
 *
 * ### Configuration example
 *
 * Below is an example of a _redis_ server configuration.
 *
 *     return array(
 *          'default'   => array(                          // Default group
 *                  'driver'         => 'redis',        // using Redis driver
 *                  'server'        => array(             // Available server definitions
 *                              'host'             => 'localhost',
 *                              'port'             => 11211,
 *                              'persistent'       => FALSE
 *                              'password'           => 1,
 *                              'timeout'          => 1,
 *                  ),
 *           ),
 *     )
 *
 * In cases where only one cache group is required, if the group is named `default` there is
 * no need to pass the group name when instantiating a cache instance.
 *
 * #### General cache group configuration settings
 *
 * Below are the settings available to all types of cache driver.
 *
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 * server         | __YES__  | (_array_) Associative array of server details, must include a __host__ key. (see _Redis server configuration_ below)
 *
 * #### Redis server configuration
 *
 * The following settings should be used when defining redis server
 *
 * Name             | Required | Description
 * ---------------- | -------- | ---------------------------------------------------------------
 * host             | __YES__  | (_string_) The host of the memcache server, i.e. __localhost__; or __127.0.0.1__; or __memcache.domain.tld__
 * port             | __NO__   | (_integer_) Point to the port where memcached is listening for connections. Set this parameter to 0 when using UNIX domain sockets.  Default __11211__
 * persistent       | __NO__   | (_boolean_) Controls the use of a persistent connection. Default __TRUE__
 * #weight           | __NO__   | (_integer_) Number of buckets to create for this server which in turn control its probability of it being selected. The probability is relative to the total weight of all servers. Default __1__
 * timeout          | __NO__   | (_integer_) Value in seconds which will be used for connecting to the daemon. Think twice before changing the default value of 1 second - you can lose all the advantages of caching if your connection is too slow. Default __1__
 * #retry_interval   | __NO__   | (_integer_) Controls how often a failed server will be retried, the default value is 15 seconds. Setting this parameter to -1 disables automatic retry. Default __15__
 * #status           | __NO__   | (_boolean_) Controls if the server should be flagged as online. Default __TRUE__
 * #failure_callback | __NO__   | (_[callback](http://www.php.net/manual/en/language.pseudo-types.php#language.types.callback)_) Allows the user to specify a callback function to run upon encountering an error. The callback is run before failover is attempted. The function takes two parameters, the hostname and port of the failed server. Default __NULL__
 *
 * ### System requirements
 *
 * *  Kohana 3.0.x
 * *  PHP 5.2.4 or greater
 * *  phpredis
 *
 * @package    Kohana/Cache
 * @category   Base
 * @version    3.3
 * @author     GC Kiev
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Cache_Redis extends Cache implements Cache_Tagging
{

    // Redis has a maximum cache lifetime of 30 days
    const CACHE_CEILING = 86400;

    /**
     * Redis resource
     *
     * @var Redis
     */
    protected $_redis;

    /**
     * Cache tags
     *
     * @var array
     */
    protected $_tags = array();

    protected $_default_lifetime = 3600;

    /**
     * Constructs the redis Kohana_Cache object
     *
     * @param   array $config configuration
     *
     * @throws  Cache_Exception
     */
    protected function __construct(array $config)
    {
        // Check for the redis extention
        if (!extension_loaded('redis')) {
            throw new Cache_Exception('Redis PHP extention not loaded');
        }

        parent::__construct($config);

        // Setup Redis
        $this->_redis = new Redis;

        // Load servers from configuration
        $server = Arr::get($this->_config, 'server', NULL);

        $this->_default_lifetime = Arr::get($this->_config, 'default_expire', $this->_default_lifetime);

        if (!$server) {
            // Throw an exception if no server found
            throw new Cache_Exception('No Redis server defined in configuration');
        }

        // Merge the defined config with defaults
        $server += array(
            'host' => 'localhost',
            'port' => 11211,
            'persistent' => FALSE,
            'password' => NULL,
            'timeout' => 1,
            'prefix' => 'cache',
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

        $this->_tags = $this->_redis->hGetAll('tags');
    }

    /**
     * @return Redis
     */
    public function redis()
    {
        return $this->_redis;
    }

    /**
     * Retrieve a cached value entry by id.
     *
     *     // Retrieve cache entry from memcache group
     *     $data = Cache::instance('memcache')->get('foo');
     *
     *     // Retrieve cache entry from memcache group and return 'bar' if miss
     *     $data = Cache::instance('memcache')->get('foo', 'bar');
     *
     * @param   string $id id of cache to entry
     * @param   string $default default value to return if cache miss
     *
     * @return  mixed
     * @throws  Cache_Exception
     */
    public function get($id, $default = NULL)
    {
        if (!Kohana::$caching) {
            return $default;
        }
        try {
            $id = $this->_sanitize_id($id);

            // Get the value from Redis
            $value = $this->_redis->hGetAll($id);

            if ($value) {
                extract($value);
                /**
                 * @var string $tags
                 * @var int $time
                 * @var int $lifetime
                 * @var string $type
                 * @var string $data
                 */
                if ($tags) {
                    $tags = explode('|', $tags);
                } else {
                    $tags = FALSE;
                }
                if ((($time + $lifetime) >= time()) && (!$tags || $this->_checkTags($tags, $time))) {
                    switch ($type) {
                        case 'json':
                            return json_decode($data, TRUE);
                        case 'raw':
                            return $data;
                        case 'igbinary':
                            if (function_exists('igbinary_unserialize')) {
                                return igbinary_unserialize($data);
                            }
                            return $default;
                        default:
                            return unserialize($data);
                    }
                } else {
                    $this->_redis->del($id);
                }
            }
        } catch (Exception $e) {
            $this->_redis->del($id);
        }
        return $default;
    }

    /**
     * Set a value to cache with id and lifetime
     *
     *     $data = 'bar';
     *
     *     // Set 'bar' to 'foo' in memcache group for 10 minutes
     *     if (Cache::instance('memcache')->set('foo', $data, 600))
     *     {
     *          // Cache was set successfully
     *          return
     *     }
     *
     * @param   string $id id of cache entry
     * @param   mixed $data data to set to cache
     * @param   integer $lifetime lifetime in seconds, maximum value 2592000
     *
     * @return  boolean
     */
    public function set($id, $data, $lifetime = NULL)
    {
        return Kohana::$caching && $this->set_with_tags($id, $data, $lifetime);
    }

    /**
     * Delete a cache entry based on id
     *
     *     // Delete the 'foo' cache entry immediately
     *     Cache::instance('redis')->delete('foo');
     *
     * @param   string $id id of entry to delete
     *
     * @return  boolean
     */
    public function delete($id)
    {
        // Delete the id
        return $this->_redis->del($this->_sanitize_id($id));
    }

    /**
     * Delete all cache entries.
     *
     * Beware of using this method when
     * using shared memory cache systems, as it will wipe every
     * entry within the system for all clients.
     *
     *     // Delete all cache entries in the default group
     *     Cache::instance('redis')->delete_all();
     *
     * @return  boolean
     */
    public function delete_all()
    {
        array_map(array($this, 'delete_tag'), $this->_tags);
        return FALSE;
    }

    /**
     * Set a value based on an id with tags
     *
     * @param   string $id id
     * @param   mixed $data data
     * @param   integer $lifetime lifetime [Optional]
     * @param   array $tags tags [Optional]
     *
     * @return  boolean
     */
    public function set_with_tags($id, $data, $lifetime = NULL, array $tags = array())
    {
        if ($lifetime < 0) {
            return $this->delete($id);
        }
        if (!$lifetime) {
            $lifetime = $this->_default_lifetime;
        }
        // If the lifetime is greater than the ceiling
        if ($lifetime > Cache_Redis::CACHE_CEILING) {
            // Set the lifetime to maximum cache time
            $lifetime = Cache_Redis::CACHE_CEILING;
        }

        $id = $this->_sanitize_id($id);

        foreach ($tags as $tag) {
            $tag = (string)$tag;
            if (empty($this->_tags[$tag])) {
                $this->delete_tag($tag);
            }
        }

        $value = array(
            'time' => time(),
            'lifetime' => $lifetime,
            'tags' => implode('|', $tags)
        );

        if (is_string($data) || is_scalar($data)) {
            $value['type'] = 'raw';
            $value['data'] = $data;
        } elseif (FALSE && function_exists('igbinary_serialize')) {
            $value['type'] = 'igbinary';
            $value['data'] = igbinary_serialize($data);
        } else {
            $value['type'] = 'serialize';
            $value['data'] = serialize($data);
        }
        if ($this->_redis->hMset($id, $value)) {
            $this->_redis->expire($id, $lifetime);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Delete cache entries based on a tag
     *
     * @param   string $tag tag
     *
     * @return  boolean
     */
    public function delete_tag($tag)
    {
        return $this->_redis->hSet('tags', $tag, $this->_tags[$tag] = time());
    }

    /**
     * Find cache entries based on a tag
     *
     * @param   string $tag tag
     *
     * @return  void
     * @throws  Cache_Exception
     */
    public function find($tag)
    {
        throw new Cache_Exception('Redis does not support finding by tag');
    }


    protected function _checkTags(array $tags = array(), $time = 0)
    {
        foreach ($tags as $tag) {
            if (!isset($this->_tags[$tag]) || ($this->_tags[$tag] >= $time)) {
                return FALSE;
            }
        }
        return TRUE;
    }

}