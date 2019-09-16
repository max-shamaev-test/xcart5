<?php

namespace XCart\SUS {

    class Config
    {
        // @todo: read from config
        public static $packageURL     = 'http://localhost:8888/mp_emulator/get_bus';
        public static $currentVersion = '1.0.0.0';
        public static $tokenURL       = 'http://localhost:8888/bus_emulator/verifyAccess';
        public static $authCodeFile   = '/../../var/data/.safeModeAccessKey';
        public static $xcartRoot      = '';

        public static function getPackageURL($version = '')
        {
            $version = $version ?: self::$currentVersion;

            return self::$packageURL
                . (parse_url(self::$packageURL, PHP_URL_QUERY) ? '&' : '?')
                . 'version=' . $version;
        }
    }

    class Logger
    {
        /**
         * @var string
         */
        private static $logFile;

        /**
         * @param string $logFile Existing absolute path to log file
         */
        public static function setLogFile($logFile)
        {
            self::$logFile = $logFile;
        }

        /**
         * @param string $message
         * @param mixed  $context Will be rendered via var_export() function, avoid recursion
         *
         * @return bool
         */
        public static function log($message, $context = null)
        {
            if (self::$logFile === null) {

                return false;
            }

            if ($context && !is_scalar($context)) {
                $context = var_export($context, true);
            }

            $message = sprintf('[%s] %s', date('r'), $message);
            if ($context !== null) {
                $message .= \PHP_EOL . $context;
            }

            return self::write($message);
        }

        /**
         * @param string $message
         *
         * @return bool
         */
        private static function write($message)
        {
            $guard     = '<' . '?php die(); ?' . '>';
            $guardSize = strlen($guard);

            if (!file_exists(self::$logFile) || filesize(self::$logFile) < $guardSize) {
                if (!@file_put_contents(self::$logFile, $guard)) {

                    return false;
                }
            }

            $handler = @fopen(self::$logFile, 'r+b');
            if (!$handler) {

                return false;
            }

            if (fread($handler, $guardSize) !== $guard) {
                fseek($handler, 0);
                fwrite($handler, $guard);
                ftruncate($handler, $guardSize);

            } else {
                fseek($handler, 0, \SEEK_END);
            }

            return fwrite($handler, \PHP_EOL . $message);
        }
    }

    class Exception extends \Exception
    {
        /**
         * @var mixed
         */
        private $data;

        /**
         * @param string          $message
         * @param mixed           $data
         * @param int             $code
         * @param \Exception|null $previous
         */
        public function __construct($message = '', $data = [], $code = 0, \Exception $previous = null)
        {
            $this->data = $data;

            parent::__construct($message, $code, $previous);
        }

        /**
         * @return mixed
         */
        public function getData()
        {
            return $this->data;
        }
    }

    class ConnectionProvider
    {
        /**
         * @var string
         */
        private $url;
        private $host;
        private $port;
        private $uri;

        /**
         * @var array
         */
        private $headers;

        /**
         * @var resource
         */
        private $handler;

        /**
         * @param string $url
         * @param array  $headers
         */
        public function __construct($url, array $headers = [])
        {
            $this->url     = $url;
            $this->headers = $headers;

            list($this->host, $this->port, $this->uri) = $this->parseUrl($this->url);
        }

        /**
         * @return resource
         * @throws Exception
         */
        public function getHandler()
        {
            if ($this->handler === null) {
                $this->handler = $this->connect();
            }

            return $this->handler;
        }

        /**
         * @return bool|resource
         * @throws Exception
         */
        public function connect()
        {
            $handler = @fsockopen($this->host, $this->port, $errorCode, $errorText);

            if ($handler === false) {

                throw new Exception('Connection failed', [
                    'url'       => $this->url,
                    'errorCode' => $errorCode,
                    'error'     => $errorText,
                ]);
            }

            $message = 'GET ' . $this->uri . ' HTTP/1.1' . "\r\n";

            $headers = ['Host' => $this->host] + $this->headers;
            foreach ($headers as $name => $value) {
                $message .= $name . ': ' . $value . "\r\n";
            }

            $message .= "\r\n";

            return fwrite($handler, $message)
                ? $handler
                : false;
        }

        /**
         * @return bool
         */
        public function disconnect()
        {
            if ($this->handler) {

                return @fclose($this->handler);
            }

            return false;
        }

        /**
         * @param string $url
         *
         * @return string[]
         */
        private function parseUrl($url)
        {
            $parts = parse_url($url);

            $host = isset($parts['host']) ? $parts['host'] : '';

            $port = isset($parts['port']) ? (int) $parts['port'] : -1;
            if ($port === -1) {
                $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
                $port   = $scheme === 'https' ? 443 : 80;
            }

            $uri   = isset($parts['path']) ? $parts['path'] : '';
            $query = isset($parts['query']) ? $parts['query'] : '';
            if ($query) {
                $uri .= '?' . $query;
            }

            return [$host, $port, $uri];
        }
    }

    class PackageStream
    {
        private $position = 0;
        private $content  = '';

        public function stream_open($path, $mode, $options, &$opened_path)
        {
            $this->position = 0;
            $this->content  = '';

            return true;
        }

        public function stream_read($count)
        {
            $result         = substr($this->content, $this->position, $count);
            $this->position += strlen($result);

            return $result;
        }

        public function stream_write($data)
        {
            $count = strlen($data);

            $this->content = substr($this->content, 0, $this->position)
                . $data
                . substr($this->content, $this->position);

            $this->position += $count;

            return $count;
        }

        public function stream_tell()
        {
            return $this->position;
        }

        public function stream_eof()
        {
            return $this->position >= strlen($this->content);
        }

        public function stream_seek($offset, $whence)
        {
            $count = strlen($this->content);

            switch ($whence) {
                case SEEK_SET:
                    $position = $offset;
                    break;
                case SEEK_CUR:
                    $position = $this->position + $offset;
                    break;
                case SEEK_END:
                    $position = $count + $offset;
                    break;
                default:
                    return false;
            }

            if ($position >= 0 && $position <= $count) {
                $this->position = $position;

                return true;
            }

            return false;
        }
    }

    stream_register_wrapper('package', 'XCart\SUS\PackageStream');

    class DataReader
    {
        /**
         * @var ConnectionProvider
         */
        private $provider;

        /**
         * @var array
         */
        private $headers = [];

        /**
         * @var int
         */
        private $code;

        /**
         * @var string
         */
        private $data;

        /**
         * @var array
         */
        private $headerRestriction;

        /**
         * @param ConnectionProvider $provider
         * @param array              $headerRestriction
         */
        public function __construct(ConnectionProvider $provider, array $headerRestriction = [])
        {
            $this->provider          = $provider;
            $this->headerRestriction = $headerRestriction;
        }

        /**
         * @return string
         * @throws Exception
         */
        public function getData()
        {
            if ($this->data === null) {
                $this->fetchData();

                if ($this->headerRestriction
                    && $this->headerRestriction !== array_intersect_key($this->headers, $this->headerRestriction)
                ) {
                    throw new Exception('Wrong content received', [
                        'headers' => $this->headers,
                    ]);
                }
            }

            return $this->data;
        }

        /**
         * @return int
         */
        public function getCode()
        {
            $this->getData();

            return $this->code;
        }

        /**
         * @throws Exception
         */
        private function fetchData()
        {
            $handler = $this->provider->getHandler();

            if (!$handler) {

                throw new Exception('Wrong resource');
            }

            $this->headers = [];

            do {
                $line = fgets($handler);

                if (preg_match('/^(\S+):\s+(.+)$/', $line, $matches)) {
                    $this->headers[strtolower($matches[1])] = trim($matches[2]);
                }

                if (preg_match('/^HTTP\/\d+\.\d+\s+(\d{3})/', $line, $matches)) {
                    $this->code = (int) $matches[1];
                }

            } while (trim($line) !== '' && !feof($handler));

            $this->data = stream_get_contents($handler);
        }
    }

    class Package
    {
        /**
         * @var FileManager
         */
        private $fileManager;

        /**
         * @var string
         */
        private $file;

        /**
         * @var \PharData
         */
        private $package;

        /**
         * @var array
         */
        private $checkResult;

        /**
         * @param string $source
         * @param string $packageFile
         *
         * @return string
         * @throws Exception
         */
        public static function buildFromPath($source, $packageFile)
        {
            try {
                $fileManager = new FileManager();

                $fileManager->remove($packageFile);
                $fileManager->mkdir(dirname($packageFile));

                $package = new \PharData($packageFile);

                $sourceList = new SourceList(new \RecursiveDirectoryIterator($source));
                foreach ($sourceList->getList() as $alias => $file) {
                    $package->addFile($file, $alias);
                }

                $package->setMetadata($sourceList->getHash());

                return true;

            } catch (FileManagerException $e) {

                throw new Exception($e->getMessage(), $e->getData(), $e->getCode(), $e);

            } catch (\Exception $e) {

                throw new Exception($e->getMessage(), [], $e->getCode(), $e);
            }
        }

        /**
         * @param string      $data
         * @param FileManager $fileManager
         *
         * @throws Exception
         */
        public function __construct($data, FileManager $fileManager)
        {
            $this->fileManager = $fileManager;

            try {
                $this->file = tempnam(sys_get_temp_dir(), 'package') . '.tar';
                file_put_contents($this->file, $data);

                $this->package = new \PharData(
                    $this->file,
                    \Phar::CURRENT_AS_FILEINFO | \Phar::KEY_AS_FILENAME,
                    'package.tar'
                );
            } catch (FileManagerException $e) {

                throw new Exception($e->getMessage(), $e->getData(), $e->getCode(), $e);

            } catch (\Exception $e) {

                throw new Exception($e->getMessage(), [], $e->getCode(), $e);
            }
        }

        public function __destruct()
        {
            try {
                unset($this->package);
                \Phar::unlinkArchive($this->file);
            } catch (\Exception $e) {
            }
        }

        /**
         * @return bool
         * @throws Exception
         */
        public function check()
        {
            try {
                if ($this->package) {
                    $hash     = (new SourceList($this->package))->getHash();
                    $metadata = $this->package->getMetadata();

                    $missingFiles   = array_keys(array_diff_key($hash, $metadata));
                    $extraFiles     = array_keys(array_diff_key($metadata, $hash));
                    $differentFiles = array_keys(array_diff(
                        array_intersect_key($hash, $metadata),
                        array_intersect_key($metadata, $hash)
                    ));

                    $this->checkResult = [
                        'missingFiles'   => $missingFiles,
                        'extraFiles'     => $extraFiles,
                        'differentFiles' => $differentFiles,
                    ];

                    return !$missingFiles && !$extraFiles && !$differentFiles;
                }

                return false;

            } catch (\Exception $e) {

                throw new Exception($e->getMessage(), [], $e->getCode(), $e);
            }
        }

        /**
         * @param string $path
         * @param bool   $emulate
         * @param bool   $strictRemove
         *
         * @return bool
         * @throws Exception
         */
        public function extractTo($path, $emulate = false, $strictRemove = false)
        {
            $sourceList      = (new SourceList($this->package))->getList();
            $destinationList = (new SourceList(new \RecursiveDirectoryIterator($path)))->getList();

            $delete = array_diff_key($destinationList, $sourceList);

            foreach ($delete as $file) {
                try {
                    $this->fileManager->remove($file, $emulate);

                } catch (FileManagerException $e) {
                    if ($strictRemove) {

                        throw new Exception($e->getMessage(), $e->getData(), $e->getCode(), $e);
                    }
                }
            }

            foreach ($sourceList as $shortPath => $sourcePath) {
                if (!$this->fileManager->copyFrom($sourcePath, $path . \DIRECTORY_SEPARATOR . $shortPath, $emulate)) {

                    throw new Exception('Unable to copy file', [
                        'source'      => $sourcePath,
                        'destination' => $path,
                    ]);
                }
            }

            return true;
        }
    }

    /**
     * @todo: test
     */
    class SourceList
    {
        /**
         * @var \RecursiveDirectoryIterator
         */
        private $source;

        /**
         * @var string
         */
        private $path;

        /**
         * @var array
         */
        private $list;

        /**
         * @var array
         */
        private $hash;

        /**
         * @var array
         */
        private $excludeDirs  = ['spa'];
        private $excludeFiles = ['.DS_Store', 'sus_generator.php'];

        /**
         * SourceList constructor.
         *
         * @param \RecursiveDirectoryIterator $source
         */
        public function __construct(\RecursiveDirectoryIterator $source)
        {
            $source->setFlags(\RecursiveDirectoryIterator::SKIP_DOTS);
            $source->rewind();

            $this->path   = dirname($source->getPathname());
            $this->source = $source;
        }

        /**
         * @return array
         */
        public function getHash()
        {
            if ($this->hash === null) {
                $this->hash = array_map(function ($item) {
                    return md5_file($item);
                }, $this->getList());
            }

            return $this->hash;
        }

        /**
         * @return array|null
         */
        public function getList()
        {
            if ($this->list === null) {
                $this->list = [];

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveCallbackFilterIterator(
                        $this->source,
                        $this->getIteratorFilter()
                    )
                );

                $rootPathLength = strlen(rtrim($this->path, \DIRECTORY_SEPARATOR));

                foreach ($iterator as $fileInfo) {
                    $path = (string) $fileInfo->getPathName();
                    $key  = (0 === strpos($path, $this->path) ? substr($path, $rootPathLength + 1) : $path);

                    $this->list[$key] = $path;
                }
            }

            return $this->list;
        }

        /**
         * @return \Closure
         */
        private function getIteratorFilter()
        {
            /**
             * @param \SplFileInfo                $file
             * @param string                      $key
             * @param \RecursiveDirectoryIterator $iterator
             *
             * @return bool
             */
            return function ($file, $key, $iterator) {
                if ($iterator->hasChildren() && !in_array($file->getFilename(), $this->excludeDirs, true)) {
                    return true;
                }

                return $iterator->isFile() && !in_array($file->getFilename(), $this->excludeFiles, true);
            };
        }
    }

    class FileManagerException extends Exception
    {
    }

    class FileManager
    {
        /**
         * @param string $path
         *
         * @return string
         */
        public function preparePath($path = '')
        {
            return str_replace('/', \DIRECTORY_SEPARATOR, $path);
        }

        /**
         * @param string $path
         * @param bool   $emulate
         *
         * @return bool
         */
        public function mkdir($path, $emulate = false)
        {
            if (is_dir($path)) {
                return is_writable($path);
            }

            $parent = dirname($path);
            if ($parent !== $path) {
                return $this->mkdir($parent, $emulate) && ($emulate || (mkdir($path) && is_dir($path)));
            }

            return true;
        }

        /**
         * @param string $path
         * @param bool   $emulate
         *
         * @return bool
         * @throws FileManagerException
         */
        public function remove($path = '', $emulate = false)
        {
            if (is_file($path)) {
                if ($emulate ? is_writable(dirname($path)) : @unlink($path)) {

                    return true;
                }

                throw new FileManagerException('Remove file failed', [
                    'file' => $path,
                ]);
            }

            if (!is_dir($path)) {

                return true;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            /** @var \SplFileInfo $item */
            foreach ($iterator as $item) {
                $realPath = $item->getRealPath();
                if ($item->isFile()
                    && !($emulate ? is_writable(dirname($realPath)) : @unlink($realPath))
                ) {

                    throw new FileManagerException('Remove file failed', [
                        'file' => $realPath,
                    ]);
                }

                if ($item->isDir()
                    && !($emulate ? is_writable(dirname($realPath)) : @rmdir($realPath))
                ) {

                    throw new FileManagerException('Remove directory failed', [
                        'directory' => $realPath,
                    ]);
                }
            }

            if ($emulate ? is_writable(dirname($path)) : @rmdir($path)) {

                return true;

            }

            throw new FileManagerException('Remove directory failed', [
                'directory' => $path,
            ]);
        }

        /**
         * @param string $source
         * @param string $destination
         * @param bool   $emulate
         *
         * @return bool
         */
        public function copyFrom($source, $destination, $emulate = false)
        {
            if (is_file($source)) {

                return $this->mkdir(dirname($destination), $emulate)
                    && ($emulate
                        ? (!file_exists($destination) || is_writable($destination))
                        : (@copy($source, $destination))
                    );
            }

            return false;
        }

        /**
         * @param string $file
         * @param string $content
         *
         * @return bool
         */
        public function filePutContents($file, $content)
        {
            $this->mkdir(dirname($file));

            return (bool) @file_put_contents($file, $content);
        }
    }
}
