<?php
namespace Libs\Plugins\FileStorage;

/**
 * Example Class
 */

class MetadataFile
{
    
    public function __construct($mime, $stream)
    {
        
        //$putStream = tmpfile();
        //fwrite($putStream, stream_get_contents($readStream));
        //rewind($putStream);
        //fclose($putStream);
        
    }

    public function get($metadata = false)
    {

        if ($metadata != false) {
            return 'Example #1';
        }

        return 'Example #2';
        
    }
}
