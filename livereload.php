<?php
/*
 * Use libraries.
 */
require_once "lib/cogphp/session/CogSession.class.php";
require_once "lib/cogphp/filecontext/CogFS.class.php";
require_once "lib/cogphp/filecontext/CogDir.class.php";

/*
 * Start session.
 */
CogSession::start();

class Context
{

	const SESS_MOD_TIMES = 'mod_times';
	const SESS_HAS_INDEX = 'has_index';
	const SUCCESS_RESULT = 1;
	const ERROR_RESULT = 0;

	private static $sessModifiedTimes = array();
	private static $has_modified = false;
	private static $config = array();

	public static function getDocRoot()
	{
		if (is_file("livereload.xml"))
		{
			$config = simplexml_load_file("livereload.xml");
			$root = (string) $config->{"root-path"};
			if (!empty($root))
			{
				return realpath($root);
			}
		}

		return $_SERVER['DOCUMENT_ROOT'];
	}

	private static function fn_collectIndex($dirPath)
	{
		$files = CogDir::getFiles($dirPath);
		foreach ($files as $filename)
		{
			if ($filename == '.' || $filename == '..')
			{
				continue;
			}
			$filepath = $dirPath . '/' . $filename;
			$modifiedTime = filemtime($filepath);
			self::$sessModifiedTimes[$filepath] = $modifiedTime;
		}

		$dirs = CogDir::getFolders($dirPath);
		foreach ($dirs as $dirname)
		{
			if ($dirname == '.' || $dirname == '..')
			{
				continue;
			}
			$path = $dirPath . "/" . $dirname;
			self::fn_collectIndex($path);
		}
	}

	private static function collectIndex($dirPath)
	{
		self::fn_collectIndex($dirPath);
		CogSession::save(self::SESS_MOD_TIMES, self::$sessModifiedTimes);
		CogSession::save(self::SESS_HAS_INDEX, 1);
	}

	private static function fn_doModifiedCheck($dirPath)
	{
		$files = CogDir::getFiles($dirPath);
		foreach ($files as $filename)
		{
			if ($filename == '.' || $filename == '..')
			{
				continue;
			}
			$filepath = $dirPath . '/' . $filename;
			$modifiedTime = filemtime($filepath);

			// Ignore marked files in livereload.xml
			if (in_array($filepath, self::$config["ignore-files"]))
			{
				continue;
			}

			// Check if this filepath and modified time exist in the session index data.
			foreach (self::$sessModifiedTimes as $key => $value)
			{
				if ($key == $filepath)
				{
					if ($value !== $modifiedTime)
					{
						// A file has been modified.
						self::$sessModifiedTimes[$filepath] = $modifiedTime;
						CogSession::save(self::SESS_MOD_TIMES, self::$sessModifiedTimes);
						self::$has_modified = true;

						/*
						 * Return now to exit the loop. We only need to have one file modified.
						 * This would prevent multiple reloads when more than one file is modified.
						 * Upon return, the session data will be reset so that the file index can
						 * be collected again.
						 */
						return;
					}
				}
			}

			if (!array_key_exists($filepath, self::$sessModifiedTimes))
			{
				self::$sessModifiedTimes[$filepath] = $modifiedTime;
			}

			CogSession::save(self::SESS_MOD_TIMES, self::$sessModifiedTimes);
			//if (self::$has_modified){return;}
		}

		$dirs = CogDir::getFolders($dirPath);
		foreach ($dirs as $dirname)
		{
			if ($dirname == '.' || $dirname == '..')
			{
				continue;
			}
			$path = $dirPath . "/" . $dirname;

			//Ignore marked directories in livereoad.xml
			if (in_array($path, self::$config["ignore-dirs"]))
			{
				continue;
			}

			self::fn_doModifiedCheck($path);
		}
	}

	private static function doModifiedCheck($dirPath)
	{
		self::$sessModifiedTimes = CogSession::get(self::SESS_MOD_TIMES);
		self::fn_doModifiedCheck($dirPath);
		if (self::$has_modified)
		{
			/*
			 * Reset the session so that the files index can be collected again.
			 * This would help prevent multiple reloads in cases where more than
			 * one file was modified.
			 */
			self::resetState();
			return self::SUCCESS_RESULT;
		}
		else
		{
			return self::ERROR_RESULT;
		}
	}

	private static function initConfig()
	{
		if (!is_file("livereload.xml"))
			return;

		$configXml = simplexml_load_file("livereload.xml");
		self::$config["base-dir"] = (string) $configXml->{"base-dir"}["value"];
		foreach ($configXml->{"ignore-dirs"}->children() as $dir)
		{
			self::$config["ignore-dirs"][] = CogFS::normalize(realpath((string) $dir));
		}

		foreach ($configXml->{"ignore-files"}->children() as $file)
		{
			self::$config["ignore-files"][] = CogFS::normalize(realpath((string) $file));
		}
	}

	public static function initState()
	{
		if (CogSession::notExists(Context::SESS_HAS_INDEX))
		{
			CogSession::save(Context::SESS_HAS_INDEX, 0);
		}
		if (CogSession::notExists(Context::SESS_MOD_TIMES))
		{
			CogSession::save(Context::SESS_MOD_TIMES, array());
		}
	}

	private static function resetState()
	{
		CogSession::delete(self::SESS_HAS_INDEX);
		CogSession::delete(self::SESS_MOD_TIMES);
	}

	public static function run()
	{
		self::initState();
		self::initConfig();

		if (CogSession::get(self::SESS_HAS_INDEX) !== 1)
		{
			self::collectIndex(self::getDocRoot());
		}

		$result = self::doModifiedCheck(self::getDocRoot());

		header("Content-Type: text/plain");
		print $result;
	}
}

Context::run();
?>