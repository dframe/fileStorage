Dframe/FileStorage
===================

    #composer require dframe/filestorage

----------


Code
-------------
You can create own driver in example usage is mysql driver. 

POST from
```php 
<?php
namespace Controller;
/*
 * Dframe Route: myFileSystem/index | index.php?task=myFileSystem&action=index
 * 
 */

class myFileSystem extends \Dframe\Controller 
{

    public function index(){
        $fileStorage = new \Dframe\fileStorage\Storage($this->loadModel('fileStorage/drivers/databaseDriver'));
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'POST':
                //Aktualizacja avatara
                $imagename = $_FILES['file']['name'];
                $size = $_FILES['file']['size'];
            
                $extension = strtolower(pathinfo($imagename, PATHINFO_EXTENSION)); //Walidacja Rozszerzenia
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);  //Walidacja Mine
                finfo_close($finfo);
                if(!in_array($mime, array('image/jpeg', 'image/png', 'image/gif')) OR !in_array($extension,     array    ('jpeg', 'jpg', 'png', 'gif')));
                    exit('Wrong extension');
            
                $put = $fileStorage->put('local', $_FILES['file']['tmp_name'], 'images/path/name.'.$extension);
                if($put['return'] == true)
                    exit(json_encode(array('return' => '1', 'response' => 'File Upload OK')));
        
                exit(json_encode(array('return' => '0', 'response' => 'Error')));
        
                break;
        
            case 'DELETE':
        
                $drop = $fileStorage->drop('local', 'images/path/name.jpg'); // Filename+Extension
                if($drop['return'] == true)
                    exit(json_encode(array('return' => '1', 'response' => 'File Deleted')));
                
                exit(json_encode(array('return' => '0', 'response' => $drop['response'])));
        
                break;
            
            default:
        
                echo 'Upload Form <br>
                    <br>
                    <form action="" method="POST" enctype="multipart/form-data">
                        Select image to upload:
                        <input type="file" name="name" id="name">
                        <input type="submit" value="Upload Image" name="submit">
                    </form>';
        
                break;
        }
}

```



Mysql
------------

```mysql
CREATE TABLE `files` (
  `file_id` int(11) NOT NULL,
  `file_adapter` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_mime` varchar(127) NOT NULL,
  `last_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `files_cache` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `file_cache_path` varchar(255) NOT NULL,
  `file_cache_mime` varchar(127) NOT NULL,
  `last_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `files`
  ADD UNIQUE KEY `file_path` (`file_path`),
  ADD UNIQUE KEY `file_adapter` (`file_adapter`,`file_path`,`file_mime`),
  ADD KEY `id` (`file_id`);
  
ALTER TABLE `files_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_cache_path` (`file_cache_path`,`file_cache_mime`),
  ADD KEY `file_id` (`file_id`);
  
ALTER TABLE `files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
ALTER TABLE `files_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
ALTER TABLE `files_cache`
  ADD CONSTRAINT `files_cache_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE;
```
