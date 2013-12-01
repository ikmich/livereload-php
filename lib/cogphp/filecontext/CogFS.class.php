<?php

/**
 * Provides utility methods for working with the filesystem.
 */
class CogFS
{

	/**
	 * Checks if a file or directory exists.
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function exists($path)
	{
		$path = self::normalize($path);
		if (file_exists($path))
		{
			return true;
		}
		return false;
	}

	/**
	 * "Normalizes" a path by reducing multiple slashes to one, and converting
	 * back slashes to forward slashes.
	 *
	 * @param string $path
	 * @return string <p>The normalized path.</p>
	 */
	public static function normalize($path)
	{
		//change back slashes to forward slashes
		$path = self::backToForwardSlash($path);

		//remove trailing and leading slashes
		$path = preg_replace('</+$|^/+>', '', $path);

		//remove duplicate slashes
		$path = preg_replace('</+>', '/', $path);
		return $path;
	}

	/**
	 * Takes a path and converts the back slashes to forward slashes.
	 *
	 * @param string $path <p>The path to work on.</p>
	 * @return string <p>The resulting path string.</p>
	 */
	private static function backToForwardSlash($path)
	{
		$array = explode("\\", $path);
		return implode("/", $array);
	}

	/**
	 * Gets the free disk space on the drive.
	 *
	 * @param string $path <p>The directory path for the disk.</p>
	 * @return float
	 */
	public static function getFreeDiskSpace($path)
	{
		$free = @disk_free_space($path);
		return $free;
	}

	/**
	 * Gets the total disk space for the drive.
	 *
	 * @param string $path <p>The directory path to the drive.</p>
	 * @return float <p>The total disk space</p>
	 */
	public static function getTotalDiskSpace($path)
	{
		$total = @disk_total_space($path);
		return $total;
	}
}

?>