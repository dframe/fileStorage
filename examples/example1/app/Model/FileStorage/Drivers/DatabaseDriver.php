<?php
namespace Model\FileStorage\Drivers;

use Dframe\FileStorage\Drivers\DatabaseDriverInterface;

/**
 * Class DatabaseDriverModel
 *
 * @package Model\FileStorage\Drivers
 */
class DatabaseDriverModel extends \Model\Model implements DatabaseDriverInterface
{
    /**
     * @param      $adapter
     * @param      $path
     * @param bool $cache
     *
     * @return mixed
     */
    public function get($adapter, $path, $cache = false)
    {
        if (!isset($path) or empty($path) or empty($adapter)) {
            return $this->methodResult(false);
        }

        $row = $this->db->select('files', '*', ['file_path' => $path, 'file_adapter' => $adapter])->result();
        if (empty($row['file_id'])) {
            return $this->methodResult(false);
        }

        if ($cache != false) {
            if ($cache === true) {
                $row['cache'] = $this->db->select('file_cache', '*', ['file_id' => $row['file_id']])->results();
            } else {
                $row['cache'] = $this->db->select('file_cache', '*', ['file_cache_path' => $cache])->result();
            }
        }

        return $this->methodResult(true, $row);
    }

    /**
     * @param      $adapter
     * @param      $path
     * @param      $mime
     * @param bool $stream
     *
     * @return mixed
     */
    public function put($adapter, $path, $mime, $stream = false)
    {
        $get = $this->get($adapter, $path);
        if ($get['return'] == true) {
            return $this->methodResult(false, ['response' => 'Taki obraz już istnieje']);
        }

        $getLastInsertId = $this->db->pdoQuery('INSERT INTO `files` (`file_adapter`, `file_path`, `file_mime`) VALUES (?,?,?)', [$adapter, $path, $mime])->getLastInsertId();
        return $this->methodResult(true, ['lastInsertId' => $getLastInsertId]);
    }

    /**
     * @param      $adapter
     * @param      $originalPath
     * @param      $cachePath
     * @param      $mime
     * @param bool $stream
     *
     * @return mixed
     */
    public function cache($adapter, $originalPath, $cachePath, $mime, $stream = false)
    {
        $row = $this->db->select('files', '*', ['file_path' => $originalPath])->result();
        if (empty($row['file_id'])) {
            $put = $this->put($adapter, $originalPath, $mime, $stream);
            $row['file_id'] = $put['lastInsertId'];
        }

        $cache = $this->db->select('file_cache', '*', ['file_cache_path' => $cachePath])->result();
        if (empty($cache['id']) and !empty($row['file_id'])) {
            $data = [
                'file_id' => $row['file_id'],
                'file_cache_path' => $cachePath,
                'file_cache_mime' => $mime
            ];

            // if($stream != false){
            //     $metadata = new \Libs\Plugins\FileStorage\MetadataFile($mime, $stream);
            //     $data['file_cache_metadata'] = json_encode($metadata->get());
            // }

            $getLastInsertId = $this->db->insert('file_cache', $data);
            return $this->methodResult(true, ['lastInsertId' => $getLastInsertId]);
        }

        return $this->methodResult(false);
    }

    /**
     * @param $adapter
     * @param $path
     *
     * @return mixed
     */
    public function drop($adapter, $path)
    {
        try {
            $this->db->start();
            $row = $this->db->pdoQuery('SELECT * FROM files WHERE `file_path` = ?', [$path])->result();

            $affectedRows = $this->db->delete('file_cache', ['file_id' => $row['file_id']])->affectedRows();
            $affectedRows = $this->db->delete('files', ['file_id' => $row['file_id']])->affectedRows();

            $this->db->end();
        } catch (\Exception $e) {
            $this->db->back();
            return $this->methodResult(false, ['response' => $e->getMessages()]);
        }

        return $this->methodResult(true);
    }
}
