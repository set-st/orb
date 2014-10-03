<?php

if (!defined('MEMCACHE_USER1')) {
	define('MEMCACHE_USER1', 65536);
}

class PageCache {
	protected static $memcached = NULL;

	public static function set($key, $body, $expire = Cache::DEFAULT_EXPIRE) {
		if (PageCache::$memcached === NULL) {
			PageCache::$memcached = new Memcache();
			$config = Kohana::$config->load('pagecache');
			PageCache::$memcached->connect($config['host'], $config['port']);
			PageCache::$memcached->setCompressThreshold(0x7FFFFFFF);
		}

		if (PageCache::$memcached) {
			if ($body == NULL || $body == FALSE || $expire == 0) {
				PageCache::$memcached->delete($key);
			}
			else {
				$flag = 0;
//				if (strlen($body) > 1024) {
//					$body = gzencode($body, 9);
//					$flag = MEMCACHE_USER1;
//				}
				PageCache::$memcached->set($key, $body, $flag, min($expire, Cache::DEFAULT_EXPIRE));
			}
		}
	}
}