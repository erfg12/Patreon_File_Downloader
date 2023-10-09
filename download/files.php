<?PHP
$root = './files';

function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
		$newpath = str_replace('/(ABSOLUTE_PATH_TO_FILES_DIR_HERE)/files/','',$path);
        if(is_file($path)) {
			if (!strstr($path,".htaccess"))
			echo str_replace('/','\\',$newpath).','.date ("n/d/Y g:j:s A", filemtime($path)).';';
        } else if($value != "." && $value != "..")
            getDirContents($path, $results);
    }
}

getDirContents($root);
?>