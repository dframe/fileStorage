<?php
namespace Dframe\FileStorage;
use League\Flysystem\MountManager;
use Dframe\Config;
use Dframe\View;
use Dframe\Router;
use Imagecraft\ImageBuilder;
use Dframe\FileStorage\Image;


// UserFile
class Storage
{

    public function __construct($driver = false)
    {
        $this->driver = $driver;

        //Default
        //$configFileStorage = Config::load('config', 'app/Libs/Plugins/fileStorage');
        $configFileStorage = Config::load('fileStorage');

        $adapters = $configFileStorage->get('adapters', array());
        $this->manager = new MountManager($adapters);
        $this->router = new Router();

    }

    public function image($image, $default = false){
        return new Image($image, $default, $this);

    }

    // public function makeUrl($file){
    //     $file = str_replace('?', '&', $file);
    //     parse_str('file='.$file, $output);

    //     if($this->get($output['file'], $output)){
    //        $router = new Router();
    //        return $router->makeUrl('page/file').'?file='.$file;
    //     }

    //     return false;
    // }


    public function getFile($file)
    {

        $sourceAdapter = 'local://'.$file;
        if($this->manager->has($sourceAdapter)) {

            // Retrieve a read-stream
            $stream = $filesystem->readStream($sourceAdapter);
            $contents = stream_get_contents($stream);
            fclose($stream);
                 
        }else {
            return false;
        }

        return $this->router->makeUrl('filestorage/file').'?file='.$file;
    }


    public function renderFile($file, $adapter = 'local')
    {


        $fileAdapter = $adapter.'://'.$file;
        // Retrieve a read-stream
        if(!$this->manager->has($fileAdapter)) {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 Not Found</h1>";
            echo "The page that you have requested could not be found.";
            exit();
        }

        $getMimetype = $this->manager->getMimetype($fileAdapter);
        $stream = $this->manager->readStream($fileAdapter);
        $contents = stream_get_contents($stream);
        fclose($stream);
        
        header('Content-type: '.$getMimetype);
        echo $contents;
        exit();

    }

    public function drop($adapter, $file)
    {
        $get = $this->driver->get($adapter, $file, true);
        if($get['return'] == true) {
            if(!empty($get['cache'])) {
                foreach ($get['cache'] as $key => $value) {
                    if($this->manager->has($adapter.'://'.$value['file_cache_path'])) {
                        $this->manager->delete($adapter.'://'.$value['file_cache_path']);
                    }
                }
            }

            if($this->manager->has($adapter.'://'.$get['file_path'])) {
                $this->manager->delete($adapter.'://'.$get['file_path']);
            }

            $drop = $this->driver->drop($adapter, $file);
            if($drop['return'] != true) {
                return array('return' => false, 'response' => $drop['response']);
            }
                
            return array('return' => true, 'response' => 'Pomyślnie usunięto');
        }
        
        return array('return' => false, 'response' => 'Brak pliku');
    }


    public function put($adapter, $tmp_name, $pathImage, $forced = false)
    {

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp_name);
        finfo_close($finfo);

        if($this->manager->has($adapter.'://'.$pathImage)) {
            if($forced == false) {
                return array('return' => false, 'response' => 'File Exist');
            }
            
            $this->manager->delete($adapter.'://'.$pathImage);
        }

        $stream = fopen($tmp_name, 'r+');            
        $this->manager->writeStream($adapter.'://'.$pathImage, $stream);
        fclose($stream);
            
        $put = $this->driver->put($adapter, $pathImage, $mime);
        if($put['return'] != false) {
            return array('return' => true, 'fileId' => $put['lastInsertId']);
        }else{
            $get = $this->driver->get($adapter.'local', $pathImage);
            return array('return' => true, 'fileId' => $get['file_id']);
        }


        return array('return' => false, 'response' => 'Bład');
    }
    
}
