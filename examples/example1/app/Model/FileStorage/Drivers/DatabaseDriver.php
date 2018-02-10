<?php
namespace Model\FileStorage\Drivers;
use Dframe\FileStorage\Drivers\DatabaseDriverInterface;

class DatabaseDriverModel extends \Model\Model implements DatabaseDriverInterface
{

    public function get($adapter, $path, $cache = false)
    {
        if (!isset($path) OR empty($path) OR empty($adapter)) {
            return $this->methodResult(false);
        }

        $row = $this->baseClass->db->select('files', '*', array('file_path' => $path, 'file_adapter' => $adapter))->result();
        if (empty($row['file_id'])) {
            return $this->methodResult(false);
        }

        if ($cache != false) { 

            if ($cache === true) { 
                $row['cache'] = $this->baseClass->db->select('files_cache', '*', array('file_id' => $row['file_id']))->results();
            } else { 
                $row['cache'] = $this->baseClass->db->select('files_cache', '*', array('file_cache_path' => $cache))->result();
            } 

        }

        return $this->methodResult(true, $row);
    }

    public function put($adapter, $path, $mime, $stream = false)
    {
        $get = $this->get($adapter, $path);
        if ($get['return'] == true) {
            return $this->methodResult(false, array('response' => 'Taki obraz już istnieje'));
        }

        $getLastInsertId = $this->baseClass->db->pdoQuery('INSERT INTO `files` (`file_adapter`, `file_path`, `file_mime`) VALUES (?,?,?)', array($adapter, $path, $mime))->getLastInsertId();        
        return $this->methodResult(true, array('lastInsertId' => $getLastInsertId));
    }
    
    public function cache($adapter, $orginalPath, $cachePath, $mime, $stream = false)
    {

        $row = $this->baseClass->db->select('files', '*', array('file_path' => $orginalPath))->result();
        if (empty($row['file_id'])) {
            $put = $this->put($adapter, $orginalPath, $mime);
            $row['file_id'] = $put['lastInsertId'];
        }

        $cache = $this->baseClass->db->select('files_cache', '*', array('file_cache_path' => $cachePath))->result();
        if (empty($cache['id']) AND !empty($row['file_id'])) {

            $data = array(
                'file_id' => $row['file_id'], 
                'file_cache_path' => $cachePath, 
                'file_cache_mime' => $mime
            );

            // if($stream != false){
            //     $metadata = new \Libs\Plugins\FileStorage\MetadataFile($mime, $stream);
            //     $data['file_cache_metadata'] = json_encode($metadata->get());
            // }

            $getLastInsertId = $this->baseClass->db->insert('files_cache', $data);
            return $this->methodResult(true, array('lastInsertId' => $getLastInsertId));
        } 

        return $this->methodResult(false);
    }

    public function drop($adapter, $path) 
    {

        try {
            
            $this->baseClass->db->start();
            $row = $this->baseClass->db->pdoQuery('SELECT * FROM files WHERE `file_path` = ?', array($path))->result();

            $affectedRows = $this->baseClass->db->delete('files_cache', array('file_id' => $row['file_id']))->affectedRows();
            $affectedRows = $this->baseClass->db->delete('files', array('file_id' => $row['file_id']))->affectedRows();

            $this->baseClass->db->end();
        } catch (Exception $e) {
            
            $this->baseClass->db->back();
            return $this->methodResult(false, array('response' => $e->getMessages()));
        }

        return $this->methodResult(true);
    }

}
