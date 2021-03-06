<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2015, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace PHPCensor\ProcessControl;

/**
 * Control process using the POSIX extension.
 *
 * @author Adirelle <adirelle@gmail.com>
 */
class PosixProcessControl implements ProcessControlInterface
{
    /**
     *
     * @param int $pid
     * @return bool
     */
    public function isRunning($pid)
    {
        // Signal "0" is not sent to the process, but posix_kill checks the process anyway;
        return posix_kill($pid, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function kill($pid, $forcefully = false)
    {
        return posix_kill($pid, $forcefully ? 9 : 15);
    }

    /**
     * Check whether this posix_kill is available.
     *
     * @return bool
     *
     * @internal
     */
    public static function isAvailable()
    {
        return function_exists('posix_kill');
    }
}
