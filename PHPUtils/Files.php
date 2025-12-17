<?php

namespace PHPUtils;

/* ────────────────────────────────────────────────────────────────────────── */
/*                                  Files                                     */
/* ────────────────────────────────────────────────────────────────────────── */


 /**
  * Files
  *
  * @author Darknetzz
  * @package PHPUtils
  * @version 1.0.0
  * @since 1.0.0
  * @license MIT
  *
  * This class provides utility methods for working with files.
  */
class Files extends Base {

    /* ────────────────────────────────────────────────────────────────────────── */
    /*                               include_folder                               */
    /* ────────────────────────────────────────────────────────────────────────── */
    /**
     * include_folder
     * 
     * Include all files in a folder, except those in the $except array
     * 
     * @param  mixed $fullpath The full path to the folder
     * @param  mixed $except An array of files to exclude
     */
    public function include_folder(string $fullpath, array $except) {
        
        // if (!file_exists($fullpath)) {
        //     $this->error("Unable to include path $fullpath");
        // }

    }

    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    //                                             IS_FILE                                             #
    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    /**
     * is_file
     * 
     * Check if a file exists and is a file
     * 
     * @param  mixed $fullpath The full path to the file
     * @return bool True if the file exists and is a file, false otherwise
     */
    public function is_file(string $fullpath) {
        if (file_exists($fullpath) && is_file($fullpath)) {
            return true;
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    //                                           FILE_CLOSE                                            #
    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    /**
     * file_close
     * 
     * Close a file handle
     * 
     * @param  mixed $handle The file handle to close
     * @param  mixed $attempts The number of attempts to close the file handle
     * @return bool True if the file handle was closed, false otherwise
     */
    public function file_close(mixed $handle, int $attempts = 3) {

        if (!is_resource($handle)) {
            return false;
        }

        $try = 0;
        while (is_resource($handle) && $try <= $attempts) {
            fclose($handle);
            $try++;
        }

        if (!is_resource($handle)) {
            return true;
        }
        
        $this->debugger->throw_exception(__METHOD__.": Unable to close file handle on line ".__LINE__);
    }

    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    //                                            FILE_READ                                            #
    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    /**
     * file_read
     * 
     * Read a file
     * 
     * @param  mixed $fullpath The full path to the file
     * @return string|null The contents of the file, or null if the file is empty
     */
    public function file_read(string $fullpath) {
        if (!file_exists($fullpath) || !is_file($fullpath)) {
            $this->debugger->throw_exception(__METHOD__.": Attempted to read a file that does not exist: $fullpath");
        }

        if (!filesize($fullpath) || filesize($fullpath) < 1) {
            return null;
        }

        $f = fopen($fullpath, 'r');
        $read = fread($f, filesize($fullpath));
        $this->file_close($f);

        return $read;
    }

    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    //                                        FILE_WRITE_ACCESS                                        #
    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    /**
     * file_write_access
     * 
     * Check if a file has write access
     * 
     * @param  mixed $fullpath The full path to the file
     * @return bool True if the file has write access, false otherwise
     */
    public function file_write_access(string $fullpath) {
        $f = fopen($fullpath, 'w+');

        if (!$f) {
            return false;
        }

        $this->file_close($f);
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    //                                           FILE_WRITE                                            #
    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    /**
     * file_write
     * 
     * Write to a file
     * 
     * @param  mixed $fullpath The full path to the file
     * @param  mixed $content The content to write to the file
     * @param  mixed $create Whether to create the file if it does not exist. Defaults to true
     * @return string|bool The content written to the file, or false if the file does not exist and $create is false
     */
    public function file_write(string $fullpath, ?string $content, bool $create = true) {    
        if (!file_exists($fullpath) && $create === false) {
            return false;
        }

        if (!$this->file_write_access($fullpath)) {
            return false;
        }

        if (empty($content)) {
            return false;
        }

        touch($fullpath);
        
        $f = fopen($fullpath, 'w+');
        fwrite($f, $content);
        $this->file_close($f);
        return $content;
    }

    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    //                                           FILE_DELETE                                           #
    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    /**
     * file_delete
     * 
     * Delete a file
     * 
     * @param  mixed $fullpath The full path to the file
     * @return bool True if the file was deleted, false otherwise
     */
    public function file_delete(string $fullpath) {
        if (!file_exists($fullpath) || !is_file($fullpath)) {
            return true;
        }

        if (unlink($fullpath)) {
            return true;
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    //                                          FILE_IS_EMPTY                                          #
    // ─────────────────────────────────────────────────────────────────────────────────────────────── #
    /**
     * file_is_empty
     * 
     * Check if a file is empty
     * 
     * @param  mixed $fullpath The full path to the file
     * @return bool True if the file is empty, false otherwise
     */
    public function file_is_empty(string $fullpath) {
        $contents = $this->file_read($fullpath);

        if (!empty($contents)) {
            return false;
        }

        return true;
    }

    /* ───────────────────────────────────────────────────────────────────── */
    /*                            currentFileName                            */
    /* ───────────────────────────────────────────────────────────────────── */
    /**
     * currentFileName
     * 
     * Get the current file name
     * 
     * @param  bool $ext Whether to include the file extension. Defaults to true
     * @return string The current file name
     */
    public function currentFileName(bool $ext = True) {
        # The whole point of this function is to not have to pass or know the current file name
        # __SCRIPT__           = resolves to Files.php
        # $_SERVER['PHP_SELF'] = resolves to the PHP file requested in the URI
        $caller = $_SERVER['PHP_SELF'];

        if ($ext != True) {
            return basename($caller, '.php');
        }
        return basename($caller);
    }

    /* ───────────────────────────────────────────────────────────────────── */
    /*                             preventDirect                             */
    /* ───────────────────────────────────────────────────────────────────── */
    /**
     * preventDirect
     * 
     * Prevents direct invokation of a script - except $exceptions
     * 
     * @param  mixed $exceptions
     * @param  mixed $pagevar
     * @param  mixed $callback
     * @return void
     */
    # TODO: Fix this function at some point
    # Prevents direct invokation of a script - except $exceptions
    public function preventDirect(array $exceptions = [], mixed &$pagevar = null, ?callable $callback = null) {

        # default callback function
        if ($callback == null) {
            $callback = function() {
                http_response_code(404);
                die("404 Not found");
            };
        }

        $custom = (!empty($pagevar) ? True : False);

        # The file requested in the URI
        $page = ($custom ? $pagevar : basename($_SERVER['PHP_SELF']));

        # current page is stored in a variable passed to this method
        if ($custom && $pagevar != $page && !in_array($pagevar, $exceptions)) {
            $callback();
        }

        // if (!$custom && $page && !in_array($page, $exceptions)) {

        // }



        # The actual script running
        $currentFileName = basename($this->currentFileName());

        if (!in_array($currentFileName, $exceptions) && $currentFileName != $page) {
            $callback();
        }
    }
}