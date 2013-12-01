<?php

/**
 * Utility class that encapsulates session handling.
 */
class CogSession
{

	/**
	 * Key used to represent an object or data structure that holds user 
	 * records to keep in session. It could be a number or a string or an 
	 * array or other data structure. It is advisable that confidential data 
	 * such as passwords or banking details are not stored in session.
	 * 
	 * @var String 
	 */
	//public static $USER_REF = 'user';
	public static $DATA = 'data';
	public static $START_TIME = 'start_time';
	public static $LIFETIME = 'life_time';

	/**
	 * Starts a session.
	 */
	public static function start()
	{
		$sessionStatus = session_status();
		switch ($sessionStatus)
		{
			case PHP_SESSION_NONE:
				//Session enabled, not started.
				session_start();
				if (self::isExpired())
				{
					self::clear();
				}
				break;
			case PHP_SESSION_DISABLED:
				//Session disabled.
				die('EXECUTION ENDED>> Session disabled.');
		}
	}

	/**
	 * Ends the session.
	 */
	public static function end()
	{
		self::stop();
	}

	/**
	 * Ends the session.
	 */
	public static function stop()
	{
		$_SESSION = array();
		session_destroy();
		session_regenerate_id();
	}

	public static function clear()
	{
		//foreach ($_SESSION as $key => $value)
		foreach (array_keys($_SESSION) as $key)
		{
			unset($_SESSION[$key]);
		}
	}

	/**
	 * Returns the session id as provided by the session_id() PHP function.
	 * 
	 * @return int The session id.
	 */
	public static function getID()
	{
		return session_id();
	}

	/**
	 * Alias for self::set($key, $value);
	 */
	public static function save($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Sets a session variable.
	 * 
	 * @param string $key The name of the session variable.
	 * @param string $value The value of the session variable.
	 */
	public static function set($key, $value)
	{
		self::save($key, $value);
	}

	/**
	 * Gets a session variable by name.
	 * 
	 * @param string $key The name of the session variable to get.
	 * @return mixed The session variable represented by $key or null.
	 */
	public static function get($key)
	{
		if (isset($_SESSION[$key]))
		{
			return $_SESSION[$key];
		}
		return null;
	}

	/**
	 * Checks if a session variable exists.
	 * 
	 * @param string $key The name of the session variable to check.
	 * @return boolean
	 */
	public static function check($key)
	{
		if (isset($_SESSION[$key]))
		{
			return true;
		}
		return false;
	}

	public static function exists($key)
	{
		return self::check($key);
	}

	public static function notExists($key)
	{
		return !self::check($key);
	}

	/**
	 * Removes a session variable.
	 * @param {string} $key The variable name/key.
	 */
	public static function delete($key)
	{
		if (isset($_SESSION[$key]))
		{
			unset($_SESSION[$key]);
		}
	}

	/**
	 * Sets the number of seconds before the user login session expires.
	 * @param int $sec
	 */
	public static function setExpiry($sec)
	{
		if (is_integer($sec))
		{
			self::set(self::$LIFETIME, $sec);
			self::set(self::$START_TIME, time());
		}
	}

	/**
	 * Sets the number of minutes before the user login session expires.
	 * 
	 * @param int $mins
	 */
	public static function setExpiryMinutes($mins)
	{
		if (is_integer($mins))
		{
			$sec = $mins * 60;
			self::setExpiry($sec);
		}
	}

	/**
	 * Sets the number of hours before the user login session expires.
	 * 
	 * @param int $hours
	 */
	public static function setExpiryHours($hours)
	{
		if (is_integer($hours))
		{
			$sec = $hours * 60 * 60;
			self::setExpiry($sec);
		}
	}

	/**
	 * Sets the number of days before the user login session expires.
	 * 
	 * @param int $days
	 */
	public static function setExpiryDays($days)
	{
		if (is_integer($days))
		{
			$sec = $days * 24 * 60 * 60;
			self::setExpiry($sec);
		}
	}

	/**
	 * Sets the number of weeks before the user login session expires.
	 * 
	 * @param int $weeks
	 */
	public static function setExpiryWeeks($weeks)
	{
		if (is_integer($weeks))
		{
			$sec = $weeks * 7 * 24 * 60 * 60;
			self::setExpiry($sec);
		}
	}

	/**
	 * Sets the number of months before the user login session expires.
	 * 
	 * @param int $months
	 */
	public static function setExpiryMonths($months)
	{
		if (is_integer($months))
		{
			$sec = $months * 30 * 24 * 60 * 60;
			self::setExpiry($sec);
		}
	}

	/**
	 * Checks if the user's login session has expired.
	 * The setExpiry(seconds) function is called at the point of login.
	 * 
	 * @return boolean
	 */
	public static function isExpired()
	{
		if (self::check(self::$LIFETIME))
		{
			$startTime = self::get(self::$START_TIME);
			$lifeTime = self::get(self::$LIFETIME);
			$elapsedTime = time() - $startTime;

			if ($elapsedTime > $lifeTime)
			{
				return true;
			}
		}

		/*
		 * No lifetime set. No expiry set. Assume unlimited time. 
		 */
		return false;
	}
}

?>
