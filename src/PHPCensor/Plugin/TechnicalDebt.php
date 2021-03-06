<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2014, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace PHPCensor\Plugin;

use PHPCensor;
use PHPCensor\Builder;
use PHPCensor\Model\Build;
use PHPCensor\Plugin;
use PHPCensor\ZeroConfigPluginInterface;

/**
 * Technical Debt Plugin - Checks for existence of "TODO", "FIXME", etc.
 *
 * @author       James Inman <james@jamesinman.co.uk>
 * @package      PHPCI
 * @subpackage   Plugins
 */
class TechnicalDebt extends Plugin implements ZeroConfigPluginInterface
{
    /**
     * @var array
     */
    protected $suffixes;

    /**
     * @var string
     */
    protected $directory;

    /**
     * @var int
     */
    protected $allowed_errors;

    /**
     * @var string, based on the assumption the root may not hold the code to be
     * tested, extends the base path
     */
    protected $path;

    /**
     * @var array - paths to ignore
     */
    protected $ignore;

    /**
     * @var array - terms to search for
     */
    protected $searches;

    /**
     * @return string
     */
    public static function pluginName()
    {
        return 'technical_debt';
    }
    
    /**
     * {@inheritdoc}
     */
    public function __construct(Builder $builder, Build $build, array $options = [])
    {
        parent::__construct($builder, $build, $options);

        $this->suffixes       = ['php'];
        $this->directory      = $this->builder->buildPath;
        $this->path           = '';
        $this->ignore         = $this->builder->ignore;
        $this->allowed_errors = 0;
        $this->searches       = ['TODO', 'FIXME', 'TO DO', 'FIX ME'];

        if (isset($options['searches']) && is_array($options['searches'])) {
            $this->searches = $options['searches'];
        }

        if (isset($options['zero_config']) && $options['zero_config']) {
            $this->allowed_errors = -1;
        }
    }

    /**
     * Check if this plugin can be executed.
     *
     * @param $stage
     * @param Builder $builder
     * @param Build $build
     * @return bool
     */
    public static function canExecute($stage, Builder $builder, Build $build)
    {
        if ($stage == 'test') {
            return true;
        }

        return false;
    }

    /**
    * Runs the plugin
    */
    public function execute()
    {
        $success = true;
        $this->builder->logExecOutput(false);

        $errorCount = $this->getErrorList();

        $this->builder->log("Found $errorCount instances of " . implode(', ', $this->searches));

        $this->build->storeMeta('technical_debt-warnings', $errorCount);

        if ($this->allowed_errors != -1 && $errorCount > $this->allowed_errors) {
            $success = false;
        }

        return $success;
    }

    /**
     * Gets the number and list of errors returned from the search
     *
     * @return array
     */
    public function getErrorList()
    {
        $dirIterator = new \RecursiveDirectoryIterator($this->directory);
        $iterator    = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);
        $files       = [];

        $ignores   = $this->ignore;
        $ignores[] = '.php-censor.yml';
        $ignores[] = 'phpci.yml';
        $ignores[] = '.phpci.yml';

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $skipFile = false;
            foreach ($ignores as $ignore) {
                if (stripos($filePath, $ignore) !== false) {
                    $skipFile = true;
                    break;
                }
            }

            // Ignore hidden files, else .git, .sass_cache, etc. all get looped over
            if (stripos($filePath, DIRECTORY_SEPARATOR . '.') !== false) {
                $skipFile = true;
            }

            if ($skipFile === false) {
                $files[] = $file->getRealPath();
            }
        }

        $files = array_filter(array_unique($files));
        $errorCount = 0;

        foreach ($files as $file) {
            foreach ($this->searches as $search) {
                $fileContent = file_get_contents($file);
                $allLines = explode(PHP_EOL, $fileContent);
                $beforeString = strstr($fileContent, $search, true);

                if (false !== $beforeString) {
                    $lines = explode(PHP_EOL, $beforeString);
                    $lineNumber = count($lines);
                    $content = trim($allLines[$lineNumber - 1]);

                    $errorCount++;

                    $fileName = str_replace($this->directory, '', $file);

                    $this->build->reportError(
                        $this->builder,
                        'technical_debt',
                        $content,
                        PHPCensor\Model\BuildError::SEVERITY_LOW,
                        $fileName,
                        $lineNumber
                    );
                }
            }
        }

        return $errorCount;
    }
}
