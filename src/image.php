<?php
namespace Dframe\fileStorage;
use League\Flysystem\MountManager;
use Dframe\Config;
use Dframe\View;
use Dframe\Router;
use Imagecraft\ImageBuilder;

#UserFile
class image{
    public $stylist = 'orginal';
    public $size;

    public function __construct($image, $default = false, $storage){
        $configFileStorage = Config::load('fileStorage');

        $adapters = $configFileStorage->get('adapters', array());
        $this->manager = new MountManager($adapters);
        $this->router = new Router();
        $this->orginalImage = $image;
        $this->defaultImage = $default;
        $this->storage = $storage;

    }
  
    public function stylist($stylist = false){
        $this->stylist = $stylist;
        return $this;
    }

    public function size($size){
        $this->size = $size;
        return $this;
    }

    private function displayDefault(){
        $orginalImage = $this->defaultImage;

        $output = array();
        $output['stylist'] = $this->stylist;
        $output['size'] = $this->size;

        $ext = substr($orginalImage, strrpos($orginalImage, "."));
    
        $path = $output['stylist'];
        if(isset($output['size']) AND !empty($output['size']));
           $path .= '-'.$this->size;
        $cache = basename($orginalImage, $ext).'-'.$path.'-'.md5('+'.$path.'+'.$orginalImage.'+').$ext;
        $cache = str_replace(basename($orginalImage, $ext).$ext, $cache, $orginalImage);

        $cacheAdapter = 'cache://'.$cache;
        $sourceAdapter = 'web://'.$orginalImage;

        $has = $this->manager->has($cacheAdapter);

        if($has == false OR ($has == true AND $this->manager->getTimestamp($cacheAdapter) < strtotime("-1 minute"))){

            if($has == true) // zrobić update zamiast delete 
                $this->manager->delete($cacheAdapter);

            if($this->manager->has($sourceAdapter)){
                $mimetype = $this->manager->getMimetype($sourceAdapter);
                if(!empty($output)){

                    $stylistObj = $this->getStylist($output['stylist'].'Stylist');
                    $readStream = $this->manager->readStream($sourceAdapter);
                    $putStream = $this->stylize($readStream, null, $stylistObj, $output);

                }else{
                    // Retrieve a read-stream
                    $stream = $this->manager->readStream($sourceAdapter);
                    $contents = stream_get_contents($stream);
                    fclose($stream);
                    
                    // Create or overwrite using a stream.
                    $putStream = tmpfile();
                    fwrite($putStream, $contents);
                    rewind($putStream);
                }

                $this->manager->putStream($cacheAdapter, $putStream);

                if(!empty($this->storage->driver)){
                    $this->storage->driver->cache('web', $orginalImage, $cache, $mimetype);
                }
                
                if (is_resource($putStream))
                    fclose($putStream);
        
            }else
                return false;
        }

        $this->cache = $cache;

        return $this->router->makeUrl('filestorage/images/:params?params='.$cache);
    }

    public function display($adapter = 'local'){
        $orginalImage = $this->orginalImage;

        $output = array();
        $output['stylist'] = $this->stylist;
        $output['size'] = $this->size;

        $ext = substr($orginalImage, strrpos($orginalImage, "."));
    
        $path = $output['stylist'];
        if(isset($output['size']) AND !empty($output['size']));
           $path .= '-'.$this->size;
    
        $cache = basename($orginalImage, $ext).'-'.$path.'-'.md5('+'.$path.'+'.$orginalImage.'+').$ext;
        $cache = str_replace(basename($orginalImage, $ext).$ext, $cache, $orginalImage);

        $cacheAdapter = 'cache://'.$cache; 
        $sourceAdapter = $adapter.'://'.$orginalImage;

        $has = $this->manager->has($cacheAdapter);
        if($has == false OR ($has == true AND $this->manager->getTimestamp($cacheAdapter) < strtotime("-1 minute"))){

            if($has == true) // zrobić update zamiast delete 
                $this->manager->delete($cacheAdapter);
            
            if($this->manager->has($sourceAdapter)){
              $mimetype = $this->manager->getMimetype($sourceAdapter);

                if(!empty($output)){
                    $stylistObj = $this->getStylist($output['stylist'].'Stylist');
                    $readStream = $this->manager->readStream($sourceAdapter);
                    $putStream = $this->stylize($readStream, null, $stylistObj, $output);

                }else{
                    // Retrieve a read-stream
                    $stream = $this->manager->readStream($sourceAdapter);
                    $contents = stream_get_contents($stream);
                    fclose($stream);
                    
                    // Create or overwrite using a stream.
                    $putStream = tmpfile();
                    fwrite($putStream, $contents);
                    rewind($putStream);
                }


                if(!empty($this->storage)){
                    $this->storage->driver->cache($adapter, $orginalImage, $cache, $mimetype);
                    $this->manager->putStream($cacheAdapter, $putStream);
       
                }else
                    return false;
                
                if (is_resource($putStream))
                    fclose($putStream);
               

            }elseif(!empty($this->defaultImage)){
                $get = $this->storage->driver->get($adapter, $orginalImage, true);
                if($get['return'] == true){
                    foreach ($get['cache'] as $key => $value) {
                        if($this->manager->has('cache://'.$value['file_cache_path']))
                            $this->manager->delete('cache://'.$value['file_cache_path']);
                    }
                    //$this->storage->driver->drop($orginalImage);
                }
                return $this->displayDefault(); //zwracać bład
                
            }else
                return false;
        }

        $this->cache = $cache;

        return $this->router->makeUrl('filestorage/images/:params?params='.$cache);
    }


    public function renderFile($file, $adapter = 'local'){

        $fileAdapter = $adapter.'://'.$file;
        // Retrieve a read-stream
        if(!$this->manager->has($fileAdapter)){
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

    /**
     * Zasadniczy mechanizm stylizowania obrazu
     * Tylko do uzytku wewnatrz klasy!!!
     * @param resource $originStream
     * @param string $extension
     * @param Dframe/Libs/Stylist $stylist
     * @param array $stylistParam
     * @return resource
     */
    protected function stylize($originStream, $extension, $stylistObj, $stylistParam){

        $options = ['engine' => 'php_gd', 'locale' => 'pl_PL'];
        $builder = new ImageBuilder($options);

        $layer = $builder->addBackgroundLayer();
        $contents = stream_get_contents($originStream);
        $layer->contents($contents);
        
        fclose($originStream);

        $stylistObj->stylize($layer, $stylistParam);
        $image = $builder->save();
        
        $tmpFile = tmpfile();
        if ($image->isValid()) {
            fwrite($tmpFile, $image->getContents());
        } else {
            throw new \Exception($image->getMessage()); //echo $image->getMessage().PHP_EOL;
        }

        rewind($tmpFile);
        return $tmpFile;
    }


    /**
     * Zwraca obiekt stylisty o wskazanej nazwie
     * Tylko do uzytku wewnatrz klasy!
     * @param string $stylist
     * @return Dframe/Libs/Stylist
     */
    protected function getStylist($stylist){



    	if(empty($stylist) OR $stylist == 'simpleStylist'){

            require_once appDir.'/simpleStylist.php';
            $className = '\\Dframe\\fileStorage\\stylist\\simpleStylist';
            if(!class_exists($className) OR !method_exists($className, 'stylize')){
                throw new \Exception('Requested stylist was not found or is incorrect');
                return NULL;
            }
    
            return new $className();
    	}

        require_once appDir.'../app/Libs/Plugins/Stylist/'.$stylist.'.php';
        $className = '\\Libs\\Plugins\\Stylist\\'.$stylist;
        if(!class_exists($className) OR !method_exists($className, 'stylize')){
            throw new \Exception('Requested stylist was not found or is incorrect');
            return NULL;
        }

    return new $className();
    }

}