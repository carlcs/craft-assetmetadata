<?php
/**
 * GetId3() by James Heinrich <info@getid3.org>
 * available at http://getid3.sourceforge.net
 * or http://www.getid3.org
 *
 * Please see readme.txt for more information
 */
namespace GetId3\Handler;

use GetId3\Lib\Helper;
use GetId3\GetId3Core;
use GetId3\Exception\DefaultException;

/**
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
abstract class BaseHandler
{
    /**
     * pointer
     *
     * @var GetId3\GetId3
     */
    protected $getid3;

    /**
     * analyzing filepointer or string
     *
     * @var boolean
     */
    protected $data_string_flag = false;

    /**
     * string to analyze
     *
     * @var string
     */
    protected $data_string = '';

    /**
     * seek position in string
     *
     * @var integer
     */
    protected $data_string_position = 0;

    /**
     * string length
     *
     * @var integer
     */
    protected $data_string_length = 0;

    /**
     *
     * @var type
     */
    private $dependency_to;

    /**
     *
     * @param GetId3\GetId3 $getid3
     * @param type          $call_module
     */
    public function __construct(GetId3Core $getid3, $call_module = null)
    {
        $this->setGetId3($getid3);

        if (null !== $call_module) {
            $this->dependency_to = $call_module;
        }
    }

    /**
     * Analyze from file pointer
     */
    abstract public function analyze();

    /**
     * Analyze from string instead
     */
    public function AnalyzeString(&$string)
    {
        // Enter string mode
        $this->setDataStringFlag(true);
        $this->setDataString($string);

        // Save info
        $saved_avdataoffset = $this->getGetId3()->info['avdataoffset'];
        $saved_avdataend = $this->getGetId3()->info['avdataend'];
        $saved_filesize = (isset($this->getGetId3()->info['filesize']) ? $this->getGetId3()->info['filesize'] : null); // may be not set if called as dependency without openfile() call
        // Reset some info
        $this->getid3->info['avdataoffset'] = 0;

        $this->setDataStringLength($this->getid3->info['avdataend'] = $this->getid3->info['filesize'] = strlen($string));

        // Analyze
        $this->analyze();

        // Restore some info
        $this->getid3->info['avdataoffset'] = $saved_avdataoffset;
        $this->getid3->info['avdataend'] = $saved_avdataend;
        $this->getid3->info['filesize'] = $saved_filesize;

        // Exit string mode
        $this->setDataStringFlag(false);
    }

    /**
     *
     * @return type
     */
    protected function ftell()
    {
        if ($this->getDataStringFlag()) {
            return $this->getDataStringPosition();
        }

        return ftell($this->getGetId3()->getFp());
    }

    /**
     *
     * @param  type $bytes
     * @return type
     */
    protected function fread($bytes)
    {
        if ($this->getDataStringFlag()) {

            $this->setDataStringPosition($this->getDataStringPosition() + $bytes);

            return substr($this->getDataString(),
                          $this->getDataStringPosition() - $bytes, $bytes);
        }

        return fread($this->getGetId3()->getFp(), $bytes);
    }

    /**
     *
     * @param  type $bytes
     * @param  type $whence
     * @return int
     */
    protected function fseek($bytes, $whence = SEEK_SET)
    {
        if ($this->getDataStringFlag()) {
            switch ($whence) {
                case SEEK_SET:
                    $this->setDataStringPosition($bytes);
                    break;

                case SEEK_CUR:
                    $this->setDataStringPosition($this->getDataStringPosition() + $bytes);
                    break;

                case SEEK_END:
                    $this->setDataStringPosition($this->getDataStringLength() + $bytes);
                    break;
            }

            return 0;
        }

        return fseek($this->getGetId3()->getFp(), $bytes, $whence);
    }

    /**
     *
     * @return type
     */
    protected function feof()
    {
        if ($this->getDataStringFlag()) {
            return $this->getDataStringPosition() >= $this->getDataStringLength();
        }

        return feof($this->getGetId3()->getFp());
    }

    /**
     *
     * @param  type $module
     * @return type
     */
    final protected function isDependencyFor($module)
    {
        return $this->dependency_to == $module;
    }

    /**
     *
     * @param  string  $text
     * @return boolean
     */
    protected function error($text)
    {
        $this->getGetId3()->info['error'][] = $text;

        return false;
    }

    /**
     *
     * @param  string  $text
     * @return boolean
     */
    protected function warning($text)
    {
        return $this->getGetId3()->warning($text);
    }

    /**
     *
     * @param  type      $ThisFileInfoIndex
     * @param  type      $filename
     * @param  type      $offset
     * @param  type      $length
     * @return boolean
     * @throws Exception
     */
    public function saveAttachment(&$ThisFileInfoIndex, $filename, $offset,
                                   $length)
    {
        try {
            if (!Helper::intValueSupported($offset + $length)) {
                throw new DefaultException('it extends beyond the ' . round(PHP_INT_MAX / 1073741824) . 'GB limit');
            }

            if ($this->getGetId3()->getOptionSaveAttachments() === GetId3Core::ATTACHMENTS_NONE) {
                // do not extract at all
                unset($ThisFileInfoIndex); // do not set any
            } elseif ($this->getGetId3()->getOptionSaveAttachments() === GetId3Core::ATTACHMENTS_INLINE) {
                // extract to return array

                // get whole data in one pass, till it is anyway stored in memory
                $this->fseek($offset);
                $ThisFileInfoIndex = $this->fread($length);
                if ($ThisFileInfoIndex === false || strlen($ThisFileInfoIndex) != $length) {
                    // verify
                    throw new DefaultException('failed to read attachment data');
                }
            } else {
                // assume directory path is given

                // set up destination path
                $dir = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR,
                                         $this->getGetId3()->getOptionSaveAttachments()),
                                         DIRECTORY_SEPARATOR);
                if (!is_dir($dir) || !is_writable($dir)) {
                    // check supplied directory
                    throw new DefaultException('supplied path (' . $dir . ') does not exist, or is not writable');
                }
                $dest = $dir . DIRECTORY_SEPARATOR . $filename;

                // create dest file
                if (false == ($fp_dest = fopen($dest, 'wb'))) {
                    throw new DefaultException('failed to create file ' . $dest);
                }

                // copy data
                $this->fseek($offset);
                $buffersize = ($this->getDataStringFlag() ? $length : $this->getGetId3()->fread_buffer_size());
                $bytesleft = $length;
                while ($bytesleft > 0) {
                    if (false === ($buffer = $this->fread(min($buffersize, $bytesleft))) || false === ($byteswritten = fwrite($fp_dest,
                                                                                                                    $buffer))) {
                        fclose($fp_dest);
                        unlink($dest);
                        throw new DefaultException(false === $buffer ? 'not enough data to read' : 'failed to write to destination file, may be not enough disk space');
                    }
                    $bytesleft -= $byteswritten;
                }

                fclose($fp_dest);
                $ThisFileInfoIndex = $dest;
            }
        } catch (DefaultException $e) {

            unset($ThisFileInfoIndex); // do not set any in case of error
            $this->warning('Failed to extract attachment ' . $filename . ': ' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @return GetId3Core
     */
    public function getGetId3()
    {
        return $this->getid3;
    }

    public function setGetId3(GetId3Core $getid3)
    {
        $this->getid3 = $getid3;
    }

    public function getDataStringFlag()
    {
        return $this->data_string_flag;
    }

    public function setDataStringFlag($data_string_flag)
    {
        $this->data_string_flag = $data_string_flag;
    }

    public function getDataString()
    {
        return $this->data_string;
    }

    public function setDataString($data_string)
    {
        $this->data_string = $data_string;
    }

    public function getDataStringPosition()
    {
        return $this->data_string_position;
    }

    public function setDataStringPosition($data_string_position)
    {
        $this->data_string_position = $data_string_position;
    }

    public function getDataStringLength()
    {
        return $this->data_string_length;
    }

    public function setDataStringLength($data_string_length)
    {
        $this->data_string_length = $data_string_length;
    }
}
