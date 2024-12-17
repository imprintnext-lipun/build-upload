<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] == 'readjson') {
    readJson();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] == 'changejson') {
    changeJson();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] == 'login') {
    login();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] == 'unzip') {
    unzip();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] == 'fetchapps') {
    fetchApps();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] == 'apps') {
    selectApps();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] == 'license') {
    uploadLicense();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] == 'backup') {
    takeBackup();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] == 'transfer') {
    transferFiles();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] == 'terminate') {
    if (file_exists(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json')) {
        unlink(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json');
    }
}

/**
 * User Login 
 */
function login(){
    $response = ['status'=>0, 'msg'=>'Something went wrong'];
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $base_path = dirname(getcwd());
    $config_file = file_get_contents($base_path . DIRECTORY_SEPARATOR . 'config.xml');
    $db_host = extractContent($config_file, '/<host>(.*?)<\/host>/');
    $db_user = extractContent($config_file, '/<dbuser>(.*?)<\/dbuser>/');
    $db_pass = extractContent($config_file, '/<dbpass><!\[CDATA\[(.*?)\]\]><\/dbpass>/');
    $db_name = extractContent($config_file, '/<dbname><!\[CDATA\[(.*?)\]\]><\/dbname>/');
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn -> connect_errno) {
        $response['msg'] = $conn -> connect_error;
        echo json_encode($response);
        exit();
    }
    $stmt = $conn->prepare('SELECT `email`, `password` FROM `admin_users` WHERE `email` = ?');
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if(!empty($result) && isset($result['email']) && isset($result['password'])){
        if ($result['email'] == $email && password_verify($password, $result['password'])) {
            $response['status'] = 1;
            $response['msg'] = 'Login Successful';
            try {
                exec("chmod -R 777 ".$base_path);
            } catch (Exception $e) {
                $response['msg'] = 'Unable to change permission. Do it manually';
            }
        }else {
            $response['status'] = 0;
            $response['msg'] = 'Invalid Credential';
        }
    }else {
        $response['status'] = 0;
        $response['msg'] = 'Invalid Credential';
    }
    echo json_encode($response);
}
function extractContent($input, $pattern) {
    if (preg_match($pattern, $input, $matches)) {
        return $matches[1];
    } 
}

/**
 * Unzip the Build Zip and store into a temporary folder
 * Also store the license file in the temporary folder
 */
function unzip(){
    $base_path = dirname(getcwd());
    $valid_files = ['admin', 'mobile', 'quotation', 'api', 'designer'];
    $response = ['status'=>0, 'msg'=>''];
    $apps = [];
    if (isset($_FILES['zip-file']) && $_FILES['zip-file']['error'] === UPLOAD_ERR_OK) {
        $tempDir = getcwd() . DIRECTORY_SEPARATOR . 'uploads';
        $uploadedFile = $_FILES['zip-file']['tmp_name'];
        $textFile = $_FILES['txt-file']['tmp_name'];
        if (pathinfo($_FILES['zip-file']['name'], PATHINFO_EXTENSION) !== 'zip') {
            $response['msg'] = 'Upload a zip file';
            echo json_encode($response);
            exit;
        }
        if (pathinfo($_FILES['txt-file']['name'], PATHINFO_EXTENSION) !== 'txt') {
            $response['msg'] = 'Upload text file';
            echo json_encode($response);
            exit;
        }
        if(is_dir($tempDir)) {
            deleteDirectory($tempDir);
        }
        // Create a temporary directory
        if (!mkdir($tempDir) && !is_dir($tempDir)) {
            $response['msg'] = 'Failed to Create Temporary Directory';
            echo json_encode($response);
            exit;
        }

        $fileName = basename($_FILES['txt-file']['name']);
        $destination = $tempDir . DIRECTORY_SEPARATOR . $fileName;
        move_uploaded_file($textFile, $destination);
        
        // Extract the ZIP file
        $zip = new ZipArchive();
        if ($zip->open($uploadedFile) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close();
            // List extracted files
            $files = array_diff(scandir($tempDir), ['.', '..']);
            foreach ($files as $file) {
                if (preg_match('/\.txt$/i', $file)) {
                    $licFile = file_get_contents($tempDir . DIRECTORY_SEPARATOR . $file);
                    if ((str_contains($licFile, 'license_Key') && str_contains($licFile, 'pathLc') && str_contains($licFile, 'pathSc')) == false) {
                        $response['status'] = 0;
                        $response['msg'] = 'Invalid License File';
                        deleteDirectory($tempDir);
                        echo json_encode($response);
                        exit;
                    }
                }
                if ($file == 'index.html' || $file == 'static') {
                    continue;
                }
                $apps[] = $file;
            }
            if (in_array('static', $files) && in_array('index.html', $files)) {
                $apps[] = 'designer';
            }
            if (count(array_intersect($valid_files, $apps)) == 0) {
                deleteDirectory($tempDir);
                $response['status'] = 0;
                $response['msg'] = 'Invalid Zip Format';
                echo json_encode($response);
                exit;
            }
            $response['status'] = 1;
            $response['msg'] = 'Zip Extracted Successfully';
            $response += ['location'=>$tempDir, 'apps'=>$apps];
            echo json_encode($response, JSON_UNESCAPED_SLASHES);
        } else {
            $response['msg'] = 'Failed to open the ZIP file';
            echo json_encode($response);
        }
    } else {
        $response['msg'] = 'No file uploaded or an error occurred';
        echo json_encode($response);
    }
}

/**
 * Delete all the files except the selected ones and the license file
 */
function selectApps(){
    $dir = $_POST['dir'];
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..' || preg_match('/\.txt$/i', $item)) {
            continue;
        }
        if(!array_key_exists($item, $_POST)){
            if($item == 'index.html' && array_key_exists('designer', $_POST)) {
                continue;
            }
            if($item == 'static' && array_key_exists('designer', $_POST)) {
                continue;
            }
            deleteDirectory($dir. DIRECTORY_SEPARATOR .$item);
        }
    }
}

/**
 * Function for delete Directory
 */
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

/**
 * Fetch and upload the license keys
 */
function uploadLicense(){
    $dir = $_GET['dir'];
    $config_file_path = dirname(getcwd()) . DIRECTORY_SEPARATOR .'config.xml' ;
    $file_contents = file_get_contents($config_file_path);
    preg_match_all('/https:.*?designer\//', $file_contents, $matches);
    $base_url = '';
    foreach ($matches[0] as $match) {
        $base_url = $match;
    }
    $base_url = substr($base_url, 0, -1);
    // fetch the license file
    $license_file = '';
    $license_key = '';
    $pathLc = '';
    $pathSc = '';
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (preg_match('/\.txt$/i', $item)) {
            $license_file = $dir. DIRECTORY_SEPARATOR .$item;
        }
    }
    // fetch the licenses
    $file = fopen($license_file, 'r');
    $content = '';
    while(!feof($file)){
        $content .= trim(fgets($file));
    }
    $key_arr = explode(',', $content);
    $license_keys = [];
    foreach ($key_arr as $value) {
        $arr = [];
        $arr = explode('=', $value, 2);
        $license_keys[$arr[0]] = str_replace('"', '', $arr[1]);
    }
    $license_key = $license_keys['license_Key'] ?? '';
    $pathLc = $license_keys['pathLc'] ?? '';
    $pathSc = $license_keys['pathSc'] ?? '';
    $base_path = dirname(getcwd());
    foreach(scandir($dir) as $item){
        if ($item == '.' || $item == '..' || preg_match('/\.txt$/i', $item) || $item == 'api') {
            continue;
        }
        if ($item == 'admin') {
            foreach (scandir($dir. DIRECTORY_SEPARATOR . $item) as $adminFiles) {
                if (preg_match('/^main/', $adminFiles)) {
                    $filepath = $dir. DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $adminFiles;
                    $fileContents = file_get_contents($filepath);
                    if (strpos($fileContents, "licence_key") !== false) {
                        $oldLcKey = strtok(substr($fileContents, strpos($fileContents, "licence_key:")), ',');
                        $fileContents = str_replace($oldLcKey, 'licence_key:"' . $license_key . '"', $fileContents);
                    }
                    if (strpos($fileContents, "pathLc:") !== false) {
                        $oldPathLc = strtok(substr($fileContents, strpos($fileContents, "pathLc:")), ',');
                        $fileContents = str_replace($oldPathLc, 'pathLc:"' . $pathLc . '"', $fileContents);
                    }
                    if (strpos($fileContents, "pathSc:") !== false) {
                        $oldPathSc =  strtok(substr($fileContents, strpos($fileContents, "pathSc:")), ',');
                        $fileContents = str_replace($oldPathSc, 'pathSc:"' . $pathSc . '"', $fileContents);
                    }
                    $fileContents = str_replace('BASEURL', $base_url, $fileContents);
                    file_put_contents($filepath, $fileContents);
                }
            }
        }
        if ($item == 'quotation') {
            foreach (scandir($dir. DIRECTORY_SEPARATOR . $item) as $adorquot) {
                if (preg_match('/^main/', $adorquot)) {
                    $filepath = $dir. DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $adorquot;
                    $fileContents = file_get_contents($filepath);

                    $oldLcKey = strtok(substr($fileContents, strpos($fileContents, "licence_key:")), ',');
                    $fileContents = str_replace($oldLcKey, 'licence_key:"' . $license_key . '"', $fileContents);

                    file_put_contents($filepath, $fileContents);
                }
            }
        }
        if ($item == 'mobile') {
            foreach (scandir($dir. DIRECTORY_SEPARATOR . $item) as $inFolder) {
                if ($inFolder != 'static' || $inFolder == '.' || $inFolder == '..') {
                    continue;
                }
                foreach (scandir($dir. DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $inFolder . DIRECTORY_SEPARATOR . 'js') as $jsFile) {
                    if (preg_match('/^main/', $jsFile)) {
                        $filepath = $dir. DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $inFolder . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $jsFile;
                        $fileContents = file_get_contents($filepath);
                        if (strpos($fileContents, "'pathLc':") !== false) {
                            $oldPathLc = strtok(substr($fileContents, strpos($fileContents, "'pathLc':")), ',');
                            preg_match('#\[(.*?)\]#', $oldPathLc, $match);
                            if (!empty($match)) {
                                $oldPathLc = strtok(substr($fileContents, strpos($fileContents, "$match[1]")), ',');
                                $fileContents = str_replace($oldPathLc, "$match[1]:'" . $pathLc . "'", $fileContents);
                            } else {
                                $fileContents = str_replace($oldPathLc, "'pathLc':'" . $pathLc . "'", $fileContents);
                            }
                        }
                        if (strpos($fileContents, "'pathSc':") !== false) {
                            $oldPathSc =  strtok(substr($fileContents, strpos($fileContents, "'pathSc':")), ',');
                            preg_match('#\[(.*?)\]#', $oldPathSc, $match);
                            if (!empty($match)) {
                                $oldPathSc = strtok(substr($fileContents, strpos($fileContents, "$match[1]")), ',');
                                $fileContents = str_replace($oldPathSc, "$match[1]:'" . $pathSc . "'", $fileContents);
                            } else {
                                $fileContents = str_replace($oldPathSc, "'pathSc':'" . $pathSc . "'", $fileContents);
                            }                           
                        }
                        if (strpos($fileContents, "'license_key':") !== false){
                            $oldLicenseKey = strtok(substr($fileContents, strpos($fileContents, "'license_key':")), ',');
                            preg_match('#\[(.*?)\]#', $oldLicenseKey, $match);
                            if (!empty($match)) {
                                $oldLicenseKey = strtok(substr($fileContents, strpos($fileContents, "$match[1]")), ',');
                                $fileContents = str_replace($oldLicenseKey, "$match[1]:'" . $license_key . "'", $fileContents);
                            } else {
                                $fileContents = str_replace($oldLicenseKey, "'license_key':'" . $license_key . "'", $fileContents);
                            }
                        }
                        file_put_contents($filepath, $fileContents);
                    }
                }
            }
        }
        if ($item == 'static') {
            foreach (scandir($dir. DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . 'js') as $jsFile) {
                if (preg_match('/^main/', $jsFile)) {
                    $filepath = $dir. DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $jsFile;
                    $fileContents = file_get_contents($filepath);
                    if (strpos($fileContents, "'pathLc':") !== false) {
                        $oldPathLc = strtok(substr($fileContents, strpos($fileContents, "'pathLc':")), ',');
                        preg_match('#\[(.*?)\]#', $oldPathLc, $match);
                        if (!empty($match)) {
                            $oldPathLc = strtok(substr($fileContents, strpos($fileContents, "$match[1]")), ',');
                            $fileContents = str_replace($oldPathLc, "$match[1]:'" . $pathLc . "'", $fileContents);
                        } else {
                            $fileContents = str_replace($oldPathLc, "'pathLc':'" . $pathLc . "'", $fileContents);
                        }
                    }
                    if (strpos($fileContents, "'pathSc':") !== false) {
                        $oldPathSc =  strtok(substr($fileContents, strpos($fileContents, "'pathSc':")), ',');
                        preg_match('#\[(.*?)\]#', $oldPathSc, $match);
                        if (!empty($match)) {
                            $oldPathSc = strtok(substr($fileContents, strpos($fileContents, "$match[1]")), ',');
                            $fileContents = str_replace($oldPathSc, "$match[1]:'" . $pathSc . "'", $fileContents);
                        } else {
                            $fileContents = str_replace($oldPathSc, "'pathSc':'" . $pathSc . "'", $fileContents);
                        }                           
                    }
                    if (strpos($fileContents, "'license_key':") !== false){
                        $oldLicenseKey = strtok(substr($fileContents, strpos($fileContents, "'license_key':")), ',');
                        preg_match('#\[(.*?)\]#', $oldLicenseKey, $match);
                        if (!empty($match)) {
                            $oldLicenseKey = strtok(substr($fileContents, strpos($fileContents, "$match[1]")), ',');
                            $fileContents = str_replace($oldLicenseKey, "$match[1]:'" . $license_key . "'", $fileContents);
                        } else {
                            $fileContents = str_replace($oldLicenseKey, "'license_key':'" . $license_key . "'", $fileContents);
                        }
                    }
                    file_put_contents($filepath, $fileContents);
                }
            }
        }
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (preg_match('/\.txt$/i', $item)) {
            deleteDirectory($dir . DIRECTORY_SEPARATOR . $item);
        }
    }
}

/**
 * Take Backup of files in designer folder
 */
function takeBackup(){
    $response = ['status'=>0, 'msg'=>''];
    $temp_dir = $_GET['dir'];
    $current_dir = getcwd();
    $parent_dir = dirname($current_dir);
    $temp_files = [];
    foreach (scandir($temp_dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        $temp_files[] = $item;
    }
    try {
        foreach (scandir($parent_dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (in_array($item, $temp_files)){
                rename($parent_dir. DIRECTORY_SEPARATOR . $item, $parent_dir. DIRECTORY_SEPARATOR . $item . '_bak_' . date('d-m-y-H:i:sa'));
            }
        }
    } catch (Exception $e) {
        $response['msg'] = 'Manually take Backup';
        $response['files'] = $temp_files;
        echo json_encode($response);exit;
    }
    $response['status'] = 1;
    $response['msg'] = 'Successfully Backedup';
    echo json_encode($response);
}

/**
 * Transfer Files from temporary folder to designer folder
 */
function transferFiles(){
    $response = ['status'=>0, 'msg'=>'Error Occured'];
    $temp_dir = $_GET['dir'];
    $current_dir = getcwd();
    $parent_dir = dirname($current_dir);
    foreach (scandir($temp_dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
    }

    $dir = $temp_dir;
    $zip = new ZipArchive();
    $zip_name = "build.zip"; 
    $zip->open($zip_name, ZipArchive::CREATE);

    // Create recursive directory iterator
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::LEAVES_ONLY);

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($dir) + 1);

            $zip->addFile($filePath, $relativePath);
        }
    }
    $zip->close();

    $zipExtract = new ZipArchive();
    if ($zipExtract->open($zip_name) == true) {
        if ($zipExtract->extractTo($parent_dir)){
            $response['status'] = 1;
            $response['msg'] = 'Upload Successful';
        }
    }
    $zipExtract->close();
    deleteDirectory($temp_dir);
    if (file_exists(getcwd() . DIRECTORY_SEPARATOR . 'build.zip')) {
        unlink(getcwd() . DIRECTORY_SEPARATOR . 'build.zip');
    }
    if (file_exists(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json')) {
        unlink(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json');
    }
    try {
        exec("chmod -R 755 ".$parent_dir);
    } catch (Exception $e) {
        $response['msg'] = 'Unable to change permission. Do it manually';
    }
    echo json_encode($response);
}

/**
 * Read JSON File
 */
function readJson() {
    if (file_exists(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json')) {
        $contents = json_decode(file_get_contents(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json'));
        echo json_encode($contents);
    } else {
        $arr = [];
        $arr['step'] = 0;
        $arr['tempdir'] = 'n';
        file_put_contents(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json', json_encode($arr, JSON_UNESCAPED_SLASHES));
        $contents = json_decode(file_get_contents(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json'));
        echo json_encode($contents);
    }
}

function changeJson() {
    $temp_dir = $_GET['tempdir'];
    $step = $_GET['step'];
    $arr = [];
    $arr['step'] = $step;
    $arr['tempdir'] = $temp_dir;
    file_put_contents(getcwd(). DIRECTORY_SEPARATOR . 'stepinfo.json', json_encode($arr, JSON_UNESCAPED_SLASHES));
}

function fetchApps() {
    $temp_dir = $_GET['tempdir'];
    $files = array_diff(scandir($temp_dir), ['.', '..']);
    $apps = [];
    foreach ($files as $file) {
        if ($file == 'index.html' || $file == 'static') {
            continue;
        }
        $apps[] = $file;
    }
    if (in_array('static', $files) && in_array('index.html', $files)) {
        $apps[] = 'designer';
    }
    echo json_encode(['apps'=>$apps]);
}