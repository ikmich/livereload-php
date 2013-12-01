<?php

class CogDir
{

	/**
	 * Creates a folder at the specified path.
	 * 
	 * @param string $dirPath
	 * @return boolean TRUE if successful, FALSE otherwise.
	 * <p>If the folder exists, it is not affected. To create a new folder
	 * regardless of whether it already exists, use CogDir::createNew().</p>
	 */
	public static function create($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		$flag = false;
		if (!is_dir($dirPath))
		{
			/*
			 * Recursively check and create the parent directory if
			 * it does not exist.
			 */
			$parent = dirname($dirPath);
			if (!self::exists($parent))
			{
				self::create($parent);
			}
			$flag = mkdir($dirPath);
		}
		else
		{
			$flag = true;
		}

		return $flag;
	}

	/**
	 * Creates a folder. Deletes one of the same name that already exists 
	 * in the path specified.
	 * 
	 * @param string $dirPath The path of the new folder.
	 * @return boolean
	 */
	public static function createNew($dirPath)
	{
		$dirPath = normalize($dirPath);
		if (is_dir($dirPath))
		{
			self::delete($dirPath);
		}

		return self::create($dirPath);
	}

	/**
	 * Deletes a folder and its contents if not empty.
	 * 
	 * @param string $dirPath The path of the folder to delete.
	 * @return boolean true or false
	 */
	public static function delete($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		if (file_exists($dirPath) && is_dir($dirPath))
		{
			if (!self::isEmpty($dirPath))
			{
				self::purge($dirPath);
			}
			return rmdir($dirPath);
		}

		return false;
	}

	/**
	 * Deletes the contents of a folder.
	 * 
	 * @param string $dirPath The path of the folder to purge.
	 */
	public static function purge($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		if (self::hasFiles($dirPath))
		{
			self::deleteFiles($dirPath);
		}

		if (self::hasFolders($dirPath))
		{
			$subfolders = self::getFolders($dirPath);
			foreach ($subfolders as $subfolder)
			{
				$subfolderPath = "{$dirPath}/{$subfolder}";
				if (!self::isEmpty($subfolderPath))
				{
					//not empty. make recursive call to this function.
					self::purge($subfolderPath);
				}
				else
				{
					self::delete($subfolderPath);
				}
			}
		}
	}

	/**
	 * Deletes only the files inside a folder.
	 * 
	 * @param string $dirpath The path to the folder.
	 */
	public static function deleteFiles($dirpath)
	{
		$dirpath = CogFS::normalize($dirpath);
		if (is_dir($dirpath))
		{
			$files = self::getFiles($dirpath);
			foreach ($files as $file)
			{
				$filepath = "{$dirpath}/{$file}";
				CogFile::delete($filepath);
			}
		}
	}

	/**
	 * Deletes only the folders inside a folder.
	 * 
	 * @param string $dirpath The path to the folder.
	 */
	public static function deleteFolders($dirpath)
	{
		$dirpath = CogFS::normalize($dirpath);
		if (is_dir($dirpath))
		{
			$folders = self::getFolders($dirpath);
			foreach ($folders as $dir)
			{
				$subjPath = "{$dirpath}/{$dir}";
				self::delete($subjPath);
			}
		}
	}

	/**
	 * Gets the contents (files and folders) of a folder.
	 * 
	 * @param string $dirPath The path to the folder.
	 * @return array An array of file names and folder names.
	 */
	public static function getContents($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		if (file_exists($dirPath) && is_dir($dirPath))
		{
			$contents = array();
			foreach (scandir($dirPath) as $item)
			{
				if ($item != "." && $item != "..")
				{
					array_push($contents, $item);
				}
			}
			return $contents;
		}
	}

	private static $temp_files_getFiles = array();
	private static $recursive_depth_getFiles = 0;

	/**
	 * Gets the files in a folder.
	 * 
	 * @param string $dirPath The path to the folder.
	 * @param boolean $deep Whether to search the child directories.
	 * @return array  An array of file names.
	 */
	public static function getFiles($dirPath, $deep = false)
	{
		if (self::$recursive_depth_getFiles < 1)
		{
			self::$temp_files_getFiles = array();
		}

		$dirPath = CogFS::normalize($dirPath);
		if (is_dir($dirPath))
		{
			$contents = self::getContents($dirPath);
			foreach ($contents as $item)
			{
				$itemPath = CogFS::normalize("{$dirPath}/{$item}");
				if (is_file($itemPath))
				{
					array_push(self::$temp_files_getFiles, $item);
				}
				else if ($deep && is_dir($itemPath))
				{
					self::$recursive_depth_getFiles++;
					self::getFiles($itemPath, $deep);
				}
			}

			self::$recursive_depth_getFiles = 0;
			$files = self::$temp_files_getFiles;
			return $files;
		}
		else
		{
			throw new Exception("Directory {$dirPath} does not exist or is not a valid directory.");
		}
	}

	public static function getFileCount($dirPath, $deep)
	{
		self::$recursive_depth_getFiles = 0;
		self::$temp_files_getFiles = 0;

		return count(self::getFiles($dirPath, $deep));
	}

	private static $temp_dirs_getFolders = array();
	private static $recursive_depth_getFolders = 0;

	/**
	 * Gets the folders in a folder.
	 * 
	 * @param string $dirPath The path to the folder.
	 * @return array An array of folder names.
	 */
	public static function getFolders($dirPath, $deep = false)
	{
		if (self::$recursive_depth_getFolders < 1)
		{
			self::$temp_dirs_getFolders = array();
		}

		$dirPath = CogFS::normalize($dirPath);
		if (is_dir($dirPath))
		{
			//$folders = array();
			$contents = self::getContents($dirPath);
			foreach ($contents as $item)
			{
				$itemPath = CogFS::normalize("{$dirPath}/{$item}");
				if (is_dir($itemPath))
				{
					if ($deep)
					{
						self::$recursive_depth_getFolders++;
						self::getFolders($itemPath, $deep);
					}
					array_push(self::$temp_dirs_getFolders, $item);
				}
			}

			self::$recursive_depth_getFolders = 0;
			return self::$temp_dirs_getFolders;
		}
		else
		{
			throw new Exception($dirPath . ' does not exist or is not a valid director.');
		}
	}

	public static function getFolderCount($dirPath, $deep)
	{
		self::$temp_dirs_getFolders = array();
		self::$recursive_depth_getFolders = 0;

		return count(self::getFolders($dirPath, $deep));
	}

	/**
	 * Returns the number of folders in a folder.
	 * 
	 * @param string $dirPath
	 * @return int
	 */
	public static function numFolders($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		if (file_exists($dirPath) && is_dir($dirPath))
		{
			return count(self::getFolders($dirPath));
		}
	}

	/**
	 * Returns the number of files in a folder.
	 * 
	 * @param string $dirPath
	 * @param boolean $deep Whether to search child folders.
	 * @return int
	 */
	public static function numFiles($dirPath, $deep)
	{
		return self::getFileCount($dirPath, $deep);
//		$dirPath = CogFS::normalize($dirPath);
//		if (file_exists($dirPath) && is_dir($dirPath))
//		{
//			return count(self::getFiles($dirPath));
//		}
	}

	/**
	 * Checks if a folder is empty.
	 * 
	 * @param string $dirPath The path to the folder.
	 * @return boolean
	 */
	public static function isEmpty($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		if (count(self::getContents($dirPath)) > 0)
		{
			return false;
		}
		return true;
	}

	/**
	 * Checks if a folder has files in it.
	 * 
	 * @param string $dirPath
	 * @return boolean
	 */
	public static function hasFiles($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		if (count(self::getFiles($dirPath)) > 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Checks if a folder has folders in it.
	 * 
	 * @param string $dirPath
	 * @return boolean
	 */
	public static function hasFolders($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		if (count(self::getFolders($dirPath)) > 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Gets the image files in a folder.
	 * 
	 * @param string $dirPath
	 * @return array Array of image file names.
	 */
	public static function getImageFiles($dirPath)
	{
		//Returns an array containing the image-file filenames in the $folderPath folder
		$dirPath = CogFS::normalize($dirPath);
		if (is_dir($dirPath))
		{
			$imageFiles = array();
			$contents = self::getContents($dirPath);
			foreach ($contents as $item)
			{
				$itemPath = $dirPath . '/' . $item;
				if (is_file($itemPath))
				{
					$filePath = $itemPath;

					//get the file extension and check for image-specific
					//extensions.
					$ext = strtolower(CogFile::getExtension($filePath));
					switch ($ext)
					{
						case "jpg":
						case "jpeg":
						case "png":
						case "gif":
						case "bmp":
						case "tif":
						case "tiff":
						case "ai":
						case "ps":
						case "svg":
							//an image
							array_push($imageFiles, $item);
							break;
						default:
							break;
					}//end switch
				}
			}
			return $imageFiles;
		}
	}

	/**
	 * Gets the files that are of a particular extension, in a folder.
	 * 
	 * @param string $dirPath The path to the folder.
	 * @param string $ext The extension to search by.
	 * @return array An array of file names.
	 */
	public static function getFilesByExt($dirPath, $ext)
	{
		$dirPath = CogFS::normalize($dirPath);
		if (file_exists($dirPath) && is_dir($dirPath))
		{
			$files = self::getFiles($dirPath);
			$filesWithExt = array();
			foreach ($files as $file)
			{
				$filePath = "{$dirPath}/{$file}";
				$fileExt = CogFile::getExtension($filePath);
				if ($fileExt == $ext)
				{
					//a required extension. add to file extensions array.
					array_push($filesWithExt, $file);
				}
			}
			return $filesWithExt;
		}
	}

	/**
	 * Finds a file in a folder. Returns the file path if found, or FALSE otherwise.
	 * It is not recursive.
	 * 
	 * @param string $fileName The name of the file to find.
	 * @param string $dirPath The path to the folder to search.
	 * @return mixed The file path if found; FALSE if not found.
	 */
	public static function findFile($fileName, $dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		$files = self::getFiles($dirPath);
		foreach ($files as $file)
		{
			if ($file === $fileName)
			{
				return $dirPath . "/" . $file;
			}
		}
		return false;
	}

	/**
	 * Finds a folder in a folder. Returns the folder path if found, or FALSE otherwise.
	 * It is not recursive.
	 * 
	 * @param string $dirName The name of the folder to find.
	 * @param string $dirPath The path to the folder to search.
	 * @return mixed The folder path if found; FALSE if not found.
	 */
	public static function findFolder($dirName, $dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		$folders = self::getFolders($dirPath);
		foreach ($folders as $folder)
		{
			if ($folder == $dirName)
			{
				return $dirPath . "/" . $folder;
			}
		}
		return false;
	}

	/**
	 * Gets the name of a folder from its path.
	 * 
	 * @param string $dirPath
	 * @return string The name of the folder.
	 */
	public static function getName($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		return basename($dirPath);
	}

	/**
	 * Gets the path of a folder's containing parent folder.
	 * 
	 * @param string $dirPath
	 * @return mixed The parent path if found; FALSE otherwise.
	 */
	public static function getParentPath($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		return dirname($dirPath);
	}

	/**
	 * Gets the name of a folder's containing folder.
	 * @param string $dirPath
	 * @return string
	 */
	public static function getParentName($dirPath)
	{
		return basename(CogFS::normalize($dirPath));
	}

	/**
	 * Tests if a folder exists.
	 * @param string $dirPath
	 * @return boolean
	 */
	public static function exists($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		return file_exists($dirPath);
	}

	/**
	 * Checks if a folder is valid.
	 * 
	 * @param string $dirPath
	 * @return boolean
	 */
	public static function isValid($dirPath)
	{
		$dirPath = CogFS::normalize($dirPath);
		return is_dir($dirPath);
	}

	/**
	 * Copies a folder from one location to another.
	 * 
	 * @param string $dirPath The path to copy.
	 * @param string $destination The path to copy to.
	 */
	public static function copy($dirPath, $destination)
	{
		$dirPath = CogFS::normalize($dirPath);
		$destination = CogFS::normalize($destination);
		if (self::isValid($dirPath) && self::isValid($destination))
		{
			//create mirror folder in destination
			$mirrorPath = $destination . "/" . self::getName($dirPath);
			self::create($mirrorPath);
			self::fnMirror($dirPath, $mirrorPath);
		}
	}

	/**
	 * Recursive helper method used by CogDir::copy() to mirror a directory.
	 * 
	 * @param string $dirPath
	 * @param string $mirrorPath
	 */
	private static function fnMirror($dirPath, $mirrorPath)
	{
		$fromFiles = self::getFiles($dirPath);
		$fromFolders = self::getFolders($dirPath);

		//copy files
		foreach ($fromFiles as $fileName)
		{
			$fromFilePath = $dirPath . "/" . $fileName;
			$toFilePath = $mirrorPath . "/" . $fileName;
			CogFile::copy($fromFilePath, $toFilePath);
		}

		//copy folders
		foreach ($fromFolders as $folderName)
		{
			$_dirPath = $dirPath . "/" . $folderName;
			$_mirrorPath = $mirrorPath . "/" . $folderName;
			self::create($_mirrorPath);
			//recur
			self::fnMirror($_dirPath, $_mirrorPath);
		}
	}

	/**
	 * Moves a folder from one location to another.
	 * 
	 * @param string $fromDirPath The path to move from.
	 * @param string $toDirPath The path to move to.
	 * @return boolean TRUE if moved successfully; FALSE otherwise.
	 */
	public static function move($fromDirPath, $toDirPath)
	{
		if (is_dir($fromDirPath) && is_dir($fromDirPath))
		{
			return rename($fromDirPath, $toDirPath);
		}
	}
}

?>