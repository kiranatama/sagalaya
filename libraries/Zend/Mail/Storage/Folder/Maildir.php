<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Mail
 */

namespace Zend\Mail\Storage\Folder;

use Zend\Mail\Storage;
use Zend\Mail\Storage\Exception;
use Zend\Stdlib\ErrorHandler;

/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Storage
 */
class Maildir extends Storage\Maildir implements FolderInterface
{
    /**
     * root folder for folder structure
     * @var \Zend\Mail\Storage\Folder
     */
    protected $_rootFolder;

    /**
     * rootdir of folder structure
     * @var string
     */
    protected $_rootdir;

    /**
     * name of current folder
     * @var string
     */
    protected $_currentFolder;

    /**
     * delim char for subfolders
     * @var string
     */
    protected $_delim;

    /**
     * Create instance with parameters
     * Supported parameters are:
     *   - dirname rootdir of maildir structure
     *   - delim   delim char for folder structure, default is '.'
     *   - folder initial selected folder, default is 'INBOX'
     *
     * @param  $params array mail reader specific parameters
     * @throws \Zend\Mail\Storage\Exception\InvalidArgumentException
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object)$params;
        }

        if (!isset($params->dirname) || !is_dir($params->dirname)) {
            throw new Exception\InvalidArgumentException('no valid dirname given in params');
        }

        $this->_rootdir = rtrim($params->dirname, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $this->_delim = isset($params->delim) ? $params->delim : '.';

        $this->_buildFolderTree();
        $this->selectFolder(!empty($params->folder) ? $params->folder : 'INBOX');
        $this->_has['top'] = true;
        $this->_has['flags'] = true;
    }

    /**
     * find all subfolders and mbox files for folder structure
     *
     * Result is save in \Zend\Mail\Storage\Folder instances with the root in $this->_rootFolder.
     * $parentFolder and $parentGlobalName are only used internally for recursion.
     *
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    protected function _buildFolderTree()
    {
        $this->_rootFolder = new Storage\Folder('/', '/', false);
        $this->_rootFolder->INBOX = new Storage\Folder('INBOX', 'INBOX', true);

        ErrorHandler::start(E_WARNING);
        $dh = opendir($this->_rootdir);
        ErrorHandler::stop();
        if (!$dh) {
            throw new Exception\RuntimeException("can't read folders in maildir");
        }
        $dirs = array();

        while (($entry = readdir($dh)) !== false) {

            // maildir++ defines folders must start with .
            if ($entry[0] != '.' || $entry == '.' || $entry == '..') {
                continue;
            }

            if ($this->_isMaildir($this->_rootdir . $entry)) {
                $dirs[] = $entry;
            }
        }
        closedir($dh);

        sort($dirs);
        $stack = array(null);
        $folderStack = array(null);
        $parentFolder = $this->_rootFolder;
        $parent = '.';

        foreach ($dirs as $dir) {
            do {
                if (strpos($dir, $parent) === 0) {
                    $local = substr($dir, strlen($parent));
                    if (strpos($local, $this->_delim) !== false) {
                        throw new Exception\RuntimeException('error while reading maildir');
                    }
                    array_push($stack, $parent);
                    $parent = $dir . $this->_delim;
                    $folder = new Storage\Folder($local, substr($dir, 1), true);
                    $parentFolder->$local = $folder;
                    array_push($folderStack, $parentFolder);
                    $parentFolder = $folder;
                    break;
                } elseif ($stack) {
                    $parent = array_pop($stack);
                    $parentFolder = array_pop($folderStack);
                }
            } while ($stack);
            if (!$stack) {
                throw new Exception\RuntimeException('error while reading maildir');
            }
        }
    }

    /**
     * get root folder or given folder
     *
     * @param string $rootFolder get folder structure for given folder, else root
     * @throws \Zend\Mail\Storage\Exception\InvalidArgumentException
     * @return \Zend\Mail\Storage\Folder root or wanted folder
     */
    public function getFolders($rootFolder = null)
    {
        if (!$rootFolder || $rootFolder == 'INBOX') {
            return $this->_rootFolder;
        }

        // rootdir is same as INBOX in maildir
        if (strpos($rootFolder, 'INBOX' . $this->_delim) === 0) {
            $rootFolder = substr($rootFolder, 6);
        }
        $currentFolder = $this->_rootFolder;
        $subname = trim($rootFolder, $this->_delim);

        while ($currentFolder) {
            ErrorHandler::start(E_NOTICE);
            list($entry, $subname) = explode($this->_delim, $subname, 2);
            ErrorHandler::stop();
            $currentFolder = $currentFolder->$entry;
            if (!$subname) {
                break;
            }
        }

        if ($currentFolder->getGlobalName() != rtrim($rootFolder, $this->_delim)) {
            throw new Exception\InvalidArgumentException("folder $rootFolder not found");
        }
        return $currentFolder;
    }

    /**
     * select given folder
     *
     * folder must be selectable!
     *
     * @param \Zend\Mail\Storage\Folder|string $globalName global name of folder or instance for subfolder
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function selectFolder($globalName)
    {
        $this->_currentFolder = (string)$globalName;

        // getting folder from folder tree for validation
        $folder = $this->getFolders($this->_currentFolder);

        try {
            $this->_openMaildir($this->_rootdir . '.' . $folder->getGlobalName());
        } catch(Exception\ExceptionInterface $e) {
            // check what went wrong
            if (!$folder->isSelectable()) {
                throw new Exception\RuntimeException("{$this->_currentFolder} is not selectable", 0, $e);
            }
            // seems like file has vanished; rebuilding folder tree - but it's still an exception
            $this->_buildFolderTree($this->_rootdir);
            throw new Exception\RuntimeException('seems like the maildir has vanished, I\'ve rebuild the ' .
                                                         'folder tree, search for an other folder and try again', 0, $e);
        }
    }

    /**
     * get \Zend\Mail\Storage\Folder instance for current folder
     *
     * @return \Zend\Mail\Storage\Folder instance of current folder
     */
    public function getCurrentFolder()
    {
        return $this->_currentFolder;
    }
}
