<?php 
defined( 'ABSPATH' ) || exit();

if ( ! class_exists('EL_Zip_Archive') ) {

	class EL_Zip_Archive {

		protected $zipFile;

		public function __construct(){
			$this->zipFile = new ZipArchive;
		}

        public function add_zip_file( $file_name, $files_add = array() ){
        	if ( is_array( $files_add ) && count( $files_add ) > 1 ) {
        		if ( $this->zipFile->open($file_name, ZipArchive::CREATE) === TRUE ) {
        			foreach ( $files_add as $key => $file ) {
        				if ( file_exists( $file ) ) {
							$this->zipFile->addFile( $file, './'. basename( $file ));
	        			}
        			}
	        		$this->zipFile->close();
	        	}
        	}
        }
	}
}