<?php

namespace Libs\Plugins\FileStorage;

/**
 * Example Class
 */

class MetadataFile
{
    /**
     * MetadataFile constructor.
     *
     * @param $mime
     * @param $stream
     */
    public function __construct($mime, $stream)
    {

        //$putStream = tmpfile();
        //fwrite($putStream, stream_get_contents($readStream));
        //rewind($putStream);
        //fclose($putStream);
    }

    /**
     * @param bool $metadata
     *
     * @return string
     */
    public function get($metadata = false)
    {
        if ($metadata != false) {
            return 'Example #1';
        }

        return 'Example #2';
    }
}
