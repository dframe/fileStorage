<?php
namespace Dframe\FileStorage;
use League\Flysystem\MountManager;
use Dframe\Config;
use Dframe\View;
use Dframe\Router;
use Dframe\FileStorage\Image;
use Dframe\Router\Response;

// UserFile
class Storage
{

    public function __construct($driver = null)
    {
        $this->driver = $driver;

        //Default
        //$configFileStorage = Config::load('config', 'app/Libs/Plugins/fileStorage');
        $configFileStorage = Config::load('fileStorage');

        $adapters = $configFileStorage->get('adapters', array());
        $this->manager = new MountManager($adapters);
        $this->router = new Router();

    }

    public function image($image, $default = false)
    {
        $image = new Image($image, $default, $this);
        $image->addStylist($this->settings['stylists']); 
        return $image;

    }

    public function settings($settings)
    {
        $this->settings['stylists'] = $settings['stylists'];
    }


    public function getFile($file)
    {

        $sourceAdapter = 'local://'.$file;
        if ($this->manager->has($sourceAdapter)) {

            // Retrieve a read-stream
            $stream = $this->manager->readStream($sourceAdapter);
            $contents = stream_get_contents($stream);
            fclose($stream);
                 
        } else {
            return false;
        }

        return $this->router->makeUrl('filestorage/file').'?file='.$file;
    }


    public function renderFile($file, $adapter = 'local')
    {


        $fileAdapter = $adapter.'://'.$file;
        // Retrieve a read-stream
        if (!$this->manager->has($fileAdapter)) {

            $body = "<h1>404 Not Found</h1> \n\r".
                    "The page that you have requested could not be found.";
            
            return Response::render($body)->status(404);
        }

        $getMimetype = $this->manager->getMimetype($fileAdapter);
        $stream = $this->manager->readStream($fileAdapter);
        $contents = stream_get_contents($stream);
        fclose($stream);
        
        return Response::render($contents)->headers(array('Content-type' => $getMimetype));

    }

    public function drop($adapter, $file)
    {
        $get = $this->driver->get($adapter, $file, true);
        if ($get['return'] == true) {
            if (!empty($get['cache'])) {
                foreach ($get['cache'] as $key => $value) {
                    if ($this->manager->has($adapter.'://'.$value['file_cache_path'])) {
                        $this->manager->delete($adapter.'://'.$value['file_cache_path']);
                    }
                }
            }

            if ($this->manager->has($adapter.'://'.$get['file_path'])) {
                $this->manager->delete($adapter.'://'.$get['file_path']);
            }

            $drop = $this->driver->drop($adapter, $file);
            if ($drop['return'] != true) {
                return array('return' => false, 'response' => $drop['response']);
            }
                
            return array('return' => true, 'response' => 'Pomyślnie usunięto');
        }
        
        return array('return' => false, 'response' => 'Brak pliku');
    }


    public function put($adapter, $tmp_name, $pathImage, $forced = false)
    {

        try {


            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
    
            if ($this->manager->has($adapter.'://'.$pathImage)) {
                if ($forced == false) {
                    throw new Exception('File Allredy Exist');
                }
                
                $this->manager->delete($adapter.'://'.$pathImage);
            }
            
            $stream = fopen($tmp_name, 'r+');
            if (!$stream) {
                throw new Exception('Failed to open uploaded file');
            }

            $this->manager->writeStream($adapter.'://'.$pathImage, $stream);
            $put = $this->driver->put($adapter, $pathImage, $mime, $stream);
            fclose($stream);
            
        } catch (Exception $e) {
            return array('return' => false, 'response' => $e->getMessage());
        }


        if ($put['return'] != false) {
            return array('return' => true, 'fileId' => $put['lastInsertId']);
        } else {
            $get = $this->driver->get($adapter.'local', $pathImage);
            return array('return' => true, 'fileId' => $get['file_id']);
        }


        return array('return' => false, 'response' => 'Bład');
    }
    
}
