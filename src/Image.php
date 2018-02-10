<?php
namespace Dframe\FileStorage;
use League\Flysystem\MountManager;
use Dframe\Config;
use Dframe\View;
use Dframe\Router;

class Image
{

    public $stylist = 'orginal';
    public $stylists = array(
        'orginal' => \Dframe\Dframe\FileStorage\Stylist\SimpleStylist::class 
    );
    public $size;

    public function __construct($image, $default = false, $storage)
    {
        $configFileStorage = Config::load('fileStorage');

        $adapters = $configFileStorage->get('adapters', array());
        $this->cache = $configFileStorage->get('cache', array('life' => 600));
        $this->manager = new MountManager($adapters);
        $this->router = new Router();
        $this->orginalImage = $image;
        $this->defaultImage = $default;
        $this->storage = $storage;

    }
  
    public function stylist($stylist = false)
    {
        $this->stylist = $stylist;
        return $this;
    }

    public function size($size)
    {
        $this->size = $size;
        return $this;
    }

    public function display($adapter = 'local')
    {
        $get = $this->cache($adapter, $this->orginalImage);
        return $this->router->makeUrl('filestorage/images/:params?params='.$get['cache']);

    }

    public function get($adapter = 'local')
    {
        $data = $this->cache($adapter, $this->orginalImage);
        if(!empty($this->storage->driver)){
            $data = $this->storage->driver->get($adapter, $this->orginalImage, $get['cache'], $mimetype, $readStream);
        }
        return $data;
    }

    public function cache($adapter, $orginalImage, $default = false)
    {

        $output = array();
        $output['stylist'] = $this->stylist;
        $output['size'] = $this->size;

        $ext = substr($orginalImage, strrpos($orginalImage, "."));
    
        $stylist = $output['stylist'];

        if (isset($output['size']) AND !empty($output['size'])) {
            $stylist .= '-'.$this->size;
        }
        
        $cachePath = array();
        $cachePath[0] = substr(md5($orginalImage), 0, 6);
        $cachePath[1] = substr(md5($orginalImage), 6, 6);
        $cachePath[2] = substr(md5($stylist.'+'.$orginalImage), 0, 6);
        $cachePath[3] = $stylist;
        
        $cache = $cachePath[0].'-'.$cachePath[1].'-'.$cachePath[2].'-'.$cachePath[3].$ext;
        $cache = str_replace(basename($orginalImage, $ext).$ext, $cache, $orginalImage);

        $cacheAdapter = 'cache://'.$cache; 

        $sourceAdapter = $adapter.'://'.$orginalImage;

        $has = $this->manager->has($cacheAdapter);
        if ($has == false OR ($has == true AND $this->manager->getTimestamp($cacheAdapter) < strtotime("-".$this->cache['life']." seconds"))) {

            if ($has == true) { // zrobić update zamiast delete 
                $this->manager->delete($cacheAdapter);
            }
            
            if ($this->manager->has($sourceAdapter)) {
                $mimetype = $this->manager->getMimetype($sourceAdapter);

                $readStream = $this->manager->readStream($sourceAdapter);
         
                if (!empty($output)) {
                    $getStylist = $this->getStylist($output['stylist']);
                    $readStream = $getStylist->stylize($readStream, null, $getStylist, $output);
                }

                if (!empty($this->storage)) {
                    if(!empty($this->storage->driver)){
                        $this->storage->driver->cache($adapter, $orginalImage, $cache, $mimetype, $readStream);
                    }
                    $this->manager->putStream($cacheAdapter, $readStream);
       
                } else {
                    return false;
                }

            } elseif (!empty($this->defaultImage)) {
                if(!empty($this->storage->driver)){
                    $get = $this->storage->driver->get($adapter, $orginalImage, true);
                    if ($get['return'] == true) {
                        foreach ($get['cache'] as $key => $value) {
                            if ($this->manager->has('cache://'.$value['file_cache_path'])) {
                                $this->manager->delete('cache://'.$value['file_cache_path']);
                            }
                        }
                        //$this->storage->driver->drop($orginalImage);
                    }
                }
                if($default == false){
                    return $this->cache($adapter, $this->defaultImage, true); //zwracać bład
                }
                
            } 
        }

        $this->cache = $cache;

        return array(
            'cache' => $cache
        );
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
        
        return Response::render($contents)->header(array('Content-type' => $getMimetype));
    }

    public function addStylist($stylists)
    {
        $this->stylists = array_merge($this->stylists, $stylists);
    }


    /**
     * Zwraca obiekt stylisty o wskazanej nazwie
     * Tylko do uzytku wewnatrz klasy!
     *
     * @param  string $stylist
     * @return Dframe/Libs/Stylist
     */
    protected function getStylist($stylist = 'orginal')
    {

        $className = $this->stylists[$stylist];
        if (!class_exists($className) OR !method_exists($className, 'stylize')) {
            throw new \Exception('Requested stylist "'.$stylist.'" was not found or is incorrect');
        }

        return new $className();

    }

}
