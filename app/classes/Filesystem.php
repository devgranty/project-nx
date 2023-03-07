<?php

/**
 * 
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.1.0
 * 
 * Upload class handles upload of files
 * to website directories defined in the
 * config/config.php file.
 * 
 * 
 */

namespace Classes;

class Filesystem extends Str{

    // This is just like instantiating a variable 
    // We made it private so that only methods & properties in this class can access it
    private $_errors, $_results = [];
	
    public static function getFileTmpName(string $input_name){
        if(isset($_FILES[$input_name])){
            return $_FILES[$input_name]['tmp_name'];
        }
    }

    public static function getFileExtension(string $input_name){
        if(isset($_FILES[$input_name])){
            $file_ext = explode('.', $_FILES[$input_name]['name']);
            return strtolower(end($file_ext));
        }
    }

    public static function getFileSize(string $input_name){
        if(isset($_FILES[$input_name])){
            return $file_size = $_FILES[$input_name]['size'];
        }
    }

    public static function getFileType(string $input_name){
        if(isset($_FILES[$input_name])){
            return $_FILES[$input_name]['type'];
        }
    }

    public static function useFileName(string $input_name, bool $change_name = false, int $new_name_length = 25){
        if(isset($_FILES[$input_name])){
            if($change_name == false){
                $file_name = $_FILES[$input_name]['name'];
            }else{
                $file_name = self::randomStr($new_name_length).'.'.self::getFileExtension($input_name);
            }
            return $file_name;
        }
    }

    public static function isAllowedFileExt(string $input_name, array $allowed_ext = []){
        if(isset($_FILES[$input_name])){
            $file_ext = self::getFileExtension($input_name);
            if(in_array($file_ext, $allowed_ext)) return true;
            return false;
        }
    }

    public static function isAllowedFileSize(string $input_name, int $allowed_file_size_in_bytes){
        if(isset($_FILES[$input_name])){
            $file_size = self::getFileSize($input_name);
            if($file_size <= $allowed_file_size_in_bytes) return true;
            return false;
        }
    }

    public static function checkFileSelect(string $input_name){
        if(isset($_FILES[$input_name])){
            if(!empty($_FILES[$input_name]['name'])) return true;
            return false;
        }
    }

    public static function checkFileError(string $input_name){
        if(isset($_FILES[$input_name])){
            $file_error = $_FILES[$input_name]['error'];
            if($file_error == 1) return true;
            return false;
        }
    }
	
	/**
	 * Uploader function takes:
	 *
	 * ----------------------------------------------------------------------
	 * @param String
	 * @param Bool
     * @param String
     * @param Array
     * @param Int
	 *
	 * ----------------------------------------------------------------------
	 * 
     * $upload_r = Filesystem->upload('file_input', true, 'uploads/', ['jpg', 'png'], 2097152);
     * print_r($upload_r);
	 *
	 */
    public function upload(string $input_name, bool $rename = true, string $upload_path, array $allowed_ext = [], int $allowed_file_size_in_bytes, int $new_name_length = 25){

        if(!is_bool($rename)) return exit("Parameter 2 must be a boolean value.");

        if(!is_array($allowed_ext)) return exit("Parameter 4 must be an array.");
        
        if(isset($_FILES[$input_name])){
            $file_size = self::getFileSize($input_name);

            $file_new_destination = $upload_path.self::useFileName($input_name, $rename, $new_name_length);

            // Let us check if the file already exists and give it a new name, if rename = true
            while(file_exists($file_new_destination) && $rename == true){
                $file_new_destination = $upload_path.self::useFileName($input_name, $rename, $new_name_length);
            }

            // Let us check if the file already exists, if rename = false
            if(file_exists($file_new_destination) && $rename == false){
                $this->_errors[] = "This file already exists, rename the file and try again.";
            }

            // Let us check if the user selected any file for upload, if there is no form of file validation, e.g. HTML required
            if(!self::checkFileSelect($input_name)){
                $this->_errors[] = "No selected file to upload.";
            }

            // Let us check if this file has errors
            if(self::checkFileError($input_name)){
                $this->_errors[] = "This is a problem with the file you are trying to upload.";
            }

            // Let us check for our upload max file size
            if(!self::isAllowedFileSize($input_name, $allowed_file_size_in_bytes)){
                $this->_errors[] = "File size is greater than required file size of ".self::formatBytes($allowed_file_size_in_bytes);
            }

            // Let us check if the file extension is allowed
            if(!self::isAllowedFileExt($input_name, $allowed_ext)){
                $this->_errors[] = "This file is not supported for upload.";
            }

            // Let us check if our file upload directory exists
            if(!file_exists($upload_path)){
                $this->_errors[] = "The file upload directory does not exist, \"$upload_path\".";
            }

            if(empty($this->_errors)){
                if(move_uploaded_file(self::getFileTmpName($input_name), $file_new_destination)){
					
					$this->_results = ['upload'=>true, 'message'=>['File uploaded successfully'], 'error'=>false, 'file_upload_path'=>$file_new_destination, 'file_size'=>self::formatBytes($file_size), 'file_type'=>self::getFileType($input_name)];
					
				}else{
					$this->_errors[] = "Unable to upload file, check write permissions.";
					
					$this->_results = ['upload'=>false, 'message'=>$this->_errors, 'error'=>true, 'file_upload_path'=>'', 'file_size'=>self::formatBytes($file_size), 'file_type'=>self::getFileType($input_name)];
				}
				
            }else{
                $this->_results = ['upload'=>false, 'message'=>$this->_errors, 'error'=>true, 'file_upload_path'=>'', 'file_size'=>self::formatBytes($file_size), 'file_type'=>self::getFileType($input_name)];
            }
			return $this->_results;
        }
    }

    public static function delete(string $file_path){
        if(file_exists($file_path)){
            if(unlink($file_path)) return true;
            return false;
        }else{
            return "File not found at: $file_path";
        }
    }

    public static function formatBytes($bytes, $precision = 2){
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision).''.$units[$pow];
    }
    
    public static function convertToBytes(string $from): ? int{
        $units = ['B', 'KB', 'MB', 'GB'];
        $number = substr($from, 0, -2);
        $suffix_unit = strtoupper(substr($from, -2));
    
        // Check if is a singlar suffix, e.g B or No suffix
        if(is_numeric(substr($suffix_unit, 0, 1))){
            return preg_replace('/[^\d]/', '', $from);
        }
    
        $exponent = array_flip($units)[$suffix_unit] ?? null;
        if($exponent === null){
            return null;
        }
    
        return $number*(1024**$exponent);
    }
}
