<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: amitahire
 * Date: 23/5/17
 * Time: 4:35 PM
 */

Class FileHandler
{
    /**
     * Type of file
     * @var string
     */
    private $type;

    /**
     * Name
     * @var string
     */
    private $name;

    /**
     * @var TOKEN
     */
    public $token;

    /**
     * @var Data
     */
    private $data;

    private $base64_string;

    public $upload_type;

    /**
     * Upload constructor.
     */
    public function __construct($file = NULL)
    {
        //$this->load->config('special_config');
        if(isset($file['tmp_name'])){
            $this->name = $file['name'];
            $this->type = $file['type'];
            $this->tmp_name = $file['tmp_name'];
            $this->size = $file['size'];
            $this->extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        }
        else{
            $this->name = $file['name'];
            $this->base64_string = $file['base64'];
            $this->type = $this->_get_base64_mime_type();
            $this->size = $this->_get_base64_file_size();
            $this->extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $this->tmp_name = $this->_convert_base64_to_file();

        }

        $this->allowed_size = array(
            'image' => IMAGE_MAX_SIZE,
            'profile' => IMAGE_MAX_SIZE,
            'video' => VIDEO_MAX_SIZE,
            'document' => DOC_MAX_SIZE,
            'city' => IMAGE_MAX_SIZE,
            'icon' => IMAGE_MAX_SIZE,
        );

        $this->allowed_types = array(
            'image' => $this->config->item('allowed_image'),
            'profile' => $this->config->item('allowed_image'),
            'video' => $this->config->item('allowed_video'),
            'document' => $this->config->item('allowed_doc'),
            'city' => $this->config->item('allowed_image'),
            'icon' => $this->config->item('allowed_image')
        );

        $this->allowed_extensions = array(
            'image' => $this->config->item('allowed_image_ext'),
            'profile' => $this->config->item('allowed_image_ext'),
            'video' => $this->config->item('allowed_video_ext'),
            'document' => $this->config->item('allowed_doc_ext'),
            'city' => $this->config->item('allowed_image_ext'),
            'icon' => $this->config->item('allowed_image_ext')
        );

    }

    public function __destruct()
    {
        if(isset($this->base64_string)){
            unlink($this->tmp_name);
        }
    }

    private function _convert_base64_to_file(){
        $data = explode(',', $this->base64_string);
        file_put_contents('/tmp/'.$this->name, base64_decode($data[1]));
        return '/tmp/'.$this->name;

    }

    private function _get_base64_file_size(){
        return (int) (strlen(rtrim($this->base64_string, '=')) * 3 / 4);
    }

    private function _get_base64_mime_type(){
        preg_match("/^data:(.*);base64/",$this->base64_string, $match);
        return $match[1];
    }

    public function uploadFile(){
        if($this->_checkFileType()){
            //file type supported
            if($this->_checkFileSize()){
                //File size supported
                if($path = $this->_uploader()){
                //if($path = $this->_upload_to_S3($this->tmp_name, $this->_create_path())){
                    return ['status' => 1, 'key' => 'success', 'path' => $path];
                }
                else{
                    return ['status' => 4, 'key' => 'fail'];
                }
            }
            else{
                //File size exceed
                return ['status' => 3, 'key' => 'file_size_exceed'];
            }
        }
        else{
            //File type not supported
            return ['status' => 2, 'key' => 'file_type_not_support'];
        }

    }

    public function _checkFileType(){

        $types = explode("|", $this->allowed_types[$this->upload_type]);
        $ext = explode("|", $this->allowed_extensions[$this->upload_type]);

        if(in_array($this->type, $types)){
           return TRUE;
        }
        elseif(in_array($this->type, $ext)){
            return TRUE;
        }

        return FALSE;
    }

    public function _checkFileSize(){
        $size = $this->allowed_size[$this->upload_type] * 1000 * 1000;

        if($this->size <= $size){
            return TRUE;
        }
        return FALSE;
    }

    private function _create_path(){
        $this->load->helper('string');
        $path = $this->upload_type."s/";
        if($this->upload_type === 'city')
            $path = 'cities/';
        $path .= $this->api_user->id . "_";
        $path .= random_string('alnum', 15);
        $path .= ".".$this->extension;
        return $path;

    }


    private function _upload_to_S3($file, $new_path, $base64 = FALSE){
        $this->load->library('S3');
        if(!$base64){
            $file = S3::inputFile($file);
        }
        if(S3::putObject($file, S3BUCKETNAME, $new_path, S3::ACL_PUBLIC_READ)){
            return S3BUCKETURL.$new_path;
        }
        return "";
    }

    public function _delete_from_S3($path){
        $this->load->library('S3');
        $object = str_replace(S3BUCKETURL, '', $path);
        return S3::deleteObject(S3BUCKETNAME, $object);
    }

    private function _uploader(){
        $thumbnail = NULL;
        $thumbnail = $this->_create_thumbnail();
        $path = $this->_create_path();
        if($thumbnail){
            $this->_upload_to_S3($thumbnail, "thumbnails/{$path}", TRUE);
        }
        return $this->_upload_to_S3($this->tmp_name, "original/{$path}");
    }

    private function _create_thumbnail(){
        if($this->upload_type != 'image' && $this->upload_type != 'profile'){
            return false;
        }
        return $this->_compress_image(NULL, THUMBNAIL_WIDTH, THUMBNAIL_HEIGHT);

    }

    private function _compress_image($percent = NULL,$width = NULL, $height = NULL){
        $imagick = new Imagick();
        try{
            $imagick->readImage($this->tmp_name);
            if($percent){
                $imagick->setImageCompressionQuality($percent);
            }
            elseif($width && $height){
                $imagick->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
            }
        }
        catch(Exception $e){
            log_message('dev', '-------------IMAGE COMPRESSION ISSUE------------');
            log_message('dev', print_r($e, true));
            return FALSE;

        }

        $blob = $imagick->getImageBlob();
        $imagick->clear();
        return $blob;
    }



    /**
     * Enables the use of CI super-global without having to define an extra variable.
     * I can't remember where I first saw this, so thank you if you are the original author.
     *
     * Borrowed from the Ion Auth library (http://benedmunds.com/ion_auth/)
     *
     * @param $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }


}
