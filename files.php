<?php
error_reporting(0);
$lang = 'en';
$use_auth = true;
$auth_users = array(
    'root' => '827ccb0eea8a706c4c34a16891f84e7b', //12345
    'user1' => '827ccb0eea8a706c4c34a16891f84e7b', //12345
);
$readonly_users = array(
    'user'
);
$show_hidden_files = false;
$use_highlightjs = true;
$highlightjs_style = 'vs';
$edit_files = true;
$send_mail = false;
$toMailId = ""; //yourmailid@mail.com
$default_timezone = 'Asia/Kolkata'; // UTC
$root_path = $_SERVER['DOCUMENT_ROOT'];
$root_url = '';
$http_host = $_SERVER['HTTP_HOST'];
$iconv_input_encoding = 'UTF-8';
$datetime_format = 'd.m.y H:i';
$upload_extensions = ''; // 'gif,png,jpg'
$show_tree_view = true;
$GLOBALS['exclude_folders'] = array(
);
if (defined('FM_CONFIG') && is_file(FM_CONFIG) ) {
    include(FM_CONFIG);
}
session_start();
if(isset($_GET['toggleTree'])) {
    if ($_SESSION['treeHide'] == 'false') {
        $_SESSION['treeHide'] = 'true';
    } else {
        $_SESSION['treeHide'] = 'false';
    }
}
if (defined('FM_EMBED')) {
    $use_auth = false;
} else {
    @set_time_limit(600);

    date_default_timezone_set($default_timezone);

    ini_set('default_charset', 'UTF-8');
    if (version_compare(PHP_VERSION, '5.6.0', '<') && function_exists('mb_internal_encoding')) {
        mb_internal_encoding('UTF-8');
    }
    if (function_exists('mb_regex_encoding')) {
        mb_regex_encoding('UTF-8');
    }

    session_cache_limiter('');
    session_name('filemanager');
}


if (empty($auth_users)) {
    $use_auth = false;
}

$is_https = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
    || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
$root_path = rtrim($root_path, '\\/');
$root_path = str_replace('\\', '/', $root_path);
if (!@is_dir($root_path)) {
    echo "<h1>Root path \"{$root_path}\" not found!</h1>";
    exit;
}
$root_url = fm_clean_path($root_url);
defined('FM_SHOW_HIDDEN') || define('FM_SHOW_HIDDEN', $show_hidden_files);
defined('FM_ROOT_PATH') || define('FM_ROOT_PATH', $root_path);
defined('FM_ROOT_URL') || define('FM_ROOT_URL', ($is_https ? 'https' : 'http') . '://' . $http_host . (!empty($root_url) ? '/' . $root_url : ''));
defined('FM_SELF_URL') || define('FM_SELF_URL', ($is_https ? 'https' : 'http') . '://' . $http_host . $_SERVER['PHP_SELF']);
if (isset($_GET['logout'])) {
    unset($_SESSION['logged']);
    fm_redirect(FM_SELF_URL);
}

if (isset($_GET['img'])) {
    fm_show_image($_GET['img']);
}

if ($use_auth) {
    if (isset($_SESSION['logged'], $auth_users[$_SESSION['logged']])) {

    } elseif (isset($_POST['fm_usr'], $_POST['fm_pwd'])) {

        sleep(1);
        if (isset($auth_users[$_POST['fm_usr']]) && md5($_POST['fm_pwd']) === $auth_users[$_POST['fm_usr']]) {
            $_SESSION['logged'] = $_POST['fm_usr'];
            fm_set_msg('You are logged in');
            fm_redirect(FM_SELF_URL . '?p=');
        } else {
            unset($_SESSION['logged']);
            fm_set_msg('Invalid Username / Password', 'error');
            fm_redirect(FM_SELF_URL);
        }
    } else {

        unset($_SESSION['logged']);
        fm_show_header_login();
        ?>

        <div class="container" style="max-width: 600px">
            <form class="mt-5" action="" method="post" autocomplete="off">
                <center><img src="blank.png" alt="File manager" class="img-fluid"></center>
                <hr>
                <div class="form-group mt-5">
                    <?php
                    fm_show_message();
                    ?>
                    <label><i class="fa fa-user"></i> Username</label>
                    <input name="fm_usr" id="fm_usr" type="text" class="form-control" placeholder="Enter Username" required autocomplete="false">
                    <div class="invalid-feedback">
                        Username is required.
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-lock"></i> Password</label>
                    <input name="fm_pwd" id="fm_pwd" type="password" class="form-control" placeholder="Enter Password" required>
                    <div class="invalid-feedback">
                        Password is required.
                    </div>
                </div>
                <button type="submit" class="btn btn-info py-2 px-4""><i class="fa fa-sign-in"></i> Log In</button>


            </form>
        </div>
        <?php
        fm_show_footer_login();
        exit;
    }
}

defined('FM_LANG') || define('FM_LANG', $lang);
defined('FM_EXTENSION') || define('FM_EXTENSION', $upload_extensions);
defined('FM_TREEVIEW') || define('FM_TREEVIEW', $show_tree_view);
define('FM_READONLY', $use_auth && !empty($readonly_users) && isset($_SESSION['logged']) && in_array($_SESSION['logged'], $readonly_users));
define('FM_IS_WIN', DIRECTORY_SEPARATOR == '\\');


if (!isset($_GET['p'])) {
    fm_redirect(FM_SELF_URL . '?p=');
}
$p = isset($_GET['p']) ? $_GET['p'] : (isset($_POST['p']) ? $_POST['p'] : '');


$p = fm_clean_path($p);


define('FM_PATH', $p);
define('FM_USE_AUTH', $use_auth);
define('FM_EDIT_FILE', $edit_files);
defined('FM_ICONV_INPUT_ENC') || define('FM_ICONV_INPUT_ENC', $iconv_input_encoding);
defined('FM_USE_HIGHLIGHTJS') || define('FM_USE_HIGHLIGHTJS', $use_highlightjs);
defined('FM_HIGHLIGHTJS_STYLE') || define('FM_HIGHLIGHTJS_STYLE', $highlightjs_style);
defined('FM_DATETIME_FORMAT') || define('FM_DATETIME_FORMAT', $datetime_format);

unset($p, $use_auth, $iconv_input_encoding, $use_highlightjs, $highlightjs_style);



if (isset($_POST['ajax']) && !FM_READONLY) {

    if(isset($_POST['type']) && $_POST['type']=="search") {
        $dir = $_POST['path'];
        $response = scan($dir);
        echo json_encode($response);
    }

    if (isset($_POST['type']) && $_POST['type']=="mail") {

    }


    if(isset($_POST['type']) && $_POST['type']=="backup") {
        $file = $_POST['file'];
        $path = $_POST['path'];
        $date = date("dMy-His");
        $newFile = $file.'-'.$date.'.bak';
        copy($path.'/'.$file, $path.'/'.$newFile) or die("Unable to backup");
        echo "Backup $newFile Created";
    }

    exit;
}

// Delete file / folder
if (isset($_GET['del']) && !FM_READONLY) {
    $del = $_GET['del'];
    $del = fm_clean_path($del);
    $del = str_replace('/', '', $del);
    if ($del != '' && $del != '..' && $del != '.') {
        $path = FM_ROOT_PATH;
        if (FM_PATH != '') {
            $path .= '/' . FM_PATH;
        }
        $is_dir = is_dir($path . '/' . $del);
        if (fm_rdelete($path . '/' . $del)) {
            $msg = $is_dir ? 'Folder <b>%s</b> deleted' : 'File <b>%s</b> deleted';
            fm_set_msg(sprintf($msg, fm_enc($del)));
        } else {
            $msg = $is_dir ? 'Folder <b>%s</b> not deleted' : 'File <b>%s</b> not deleted';
            fm_set_msg(sprintf($msg, fm_enc($del)), 'error');
        }
    } else {
        fm_set_msg('Wrong file or folder name', 'error');
    }
    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}

if (isset($_GET['new']) && isset($_GET['type']) && !FM_READONLY) {
    $new = strip_tags($_GET['new']);
    $type = $_GET['type'];
    $new = fm_clean_path($new);
    $new = str_replace('/', '', $new);
    if ($new != '' && $new != '..' && $new != '.') {
        $path = FM_ROOT_PATH;
        if (FM_PATH != '') {
            $path .= '/' . FM_PATH;
        }
        if($_GET['type']=="file") {
            if(!file_exists($path . '/' . $new)) {
                @fopen($path . '/' . $new, 'w') or die('Cannot open file:  '.$new);
                fm_set_msg(sprintf('File <b>%s</b> created', fm_enc($new)));
            } else {
                fm_set_msg(sprintf('File <b>%s</b> already exists', fm_enc($new)), 'alert');
            }
        } else {
            if (fm_mkdir($path . '/' . $new, false) === true) {
                fm_set_msg(sprintf('Folder <b>%s</b> created', $new));
            } elseif (fm_mkdir($path . '/' . $new, false) === $path . '/' . $new) {
                fm_set_msg(sprintf('Folder <b>%s</b> already exists', fm_enc($new)), 'alert');
            } else {
                fm_set_msg(sprintf('Folder <b>%s</b> not created', fm_enc($new)), 'error');
            }
        }
    } else {
        fm_set_msg('Wrong folder name', 'error');
    }
    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}

if (isset($_GET['copy'], $_GET['finish']) && !FM_READONLY) {
 
    $copy = $_GET['copy'];
    $copy = fm_clean_path($copy);

    if ($copy == '') {
        fm_set_msg('Source path not defined', 'error');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }
   
    $from = FM_ROOT_PATH . '/' . $copy;
   
    $dest = FM_ROOT_PATH;
    if (FM_PATH != '') {
        $dest .= '/' . FM_PATH;
    }
    $dest .= '/' . basename($from);
    
    $move = isset($_GET['move']);
   
    if ($from != $dest) {
        $msg_from = trim(FM_PATH . '/' . basename($from), '/');
        if ($move) {
            $rename = fm_rename($from, $dest);
            if ($rename) {
                fm_set_msg(sprintf('Moved from <b>%s</b> to <b>%s</b>', fm_enc($copy), fm_enc($msg_from)));
            } elseif ($rename === null) {
                fm_set_msg('File or folder with this path already exists', 'alert');
            } else {
                fm_set_msg(sprintf('Error while moving from <b>%s</b> to <b>%s</b>', fm_enc($copy), fm_enc($msg_from)), 'error');
            }
        } else {
            if (fm_rcopy($from, $dest)) {
                fm_set_msg(sprintf('Copyied from <b>%s</b> to <b>%s</b>', fm_enc($copy), fm_enc($msg_from)));
            } else {
                fm_set_msg(sprintf('Error while copying from <b>%s</b> to <b>%s</b>', fm_enc($copy), fm_enc($msg_from)), 'error');
            }
        }
    } else {
        fm_set_msg('Paths must be not equal', 'alert');
    }
    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}

if (isset($_POST['file'], $_POST['copy_to'], $_POST['finish']) && !FM_READONLY) {

    $path = FM_ROOT_PATH;
    if (FM_PATH != '') {
        $path .= '/' . FM_PATH;
    }

    $copy_to_path = FM_ROOT_PATH;
    $copy_to = fm_clean_path($_POST['copy_to']);
    if ($copy_to != '') {
        $copy_to_path .= '/' . $copy_to;
    }
    if ($path == $copy_to_path) {
        fm_set_msg('Paths must be not equal', 'alert');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }
    if (!is_dir($copy_to_path)) {
        if (!fm_mkdir($copy_to_path, true)) {
            fm_set_msg('Unable to create destination folder', 'error');
            fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
        }
    }
    
    $move = isset($_POST['move']);
   
    $errors = 0;
    $files = $_POST['file'];
    if (is_array($files) && count($files)) {
        foreach ($files as $f) {
            if ($f != '') {
               
                $from = $path . '/' . $f;
                
                $dest = $copy_to_path . '/' . $f;
               
                if ($move) {
                    $rename = fm_rename($from, $dest);
                    if ($rename === false) {
                        $errors++;
                    }
                } else {
                    if (!fm_rcopy($from, $dest)) {
                        $errors++;
                    }
                }
            }
        }
        if ($errors == 0) {
            $msg = $move ? 'Selected files and folders moved' : 'Selected files and folders copied';
            fm_set_msg($msg);
        } else {
            $msg = $move ? 'Error while moving items' : 'Error while copying items';
            fm_set_msg($msg, 'error');
        }
    } else {
        fm_set_msg('Nothing selected', 'alert');
    }
    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}


if (isset($_GET['ren'], $_GET['to']) && !FM_READONLY) {

    $old = $_GET['ren'];
    $old = fm_clean_path($old);
    $old = str_replace('/', '', $old);

    $new = $_GET['to'];
    $new = fm_clean_path($new);
    $new = str_replace('/', '', $new);

    $path = FM_ROOT_PATH;
    if (FM_PATH != '') {
        $path .= '/' . FM_PATH;
    }

    if ($old != '' && $new != '') {
        if (fm_rename($path . '/' . $old, $path . '/' . $new)) {
            fm_set_msg(sprintf('Renamed from <b>%s</b> to <b>%s</b>', fm_enc($old), fm_enc($new)));
        } else {
            fm_set_msg(sprintf('Error while renaming from <b>%s</b> to <b>%s</b>', fm_enc($old), fm_enc($new)), 'error');
        }
    } else {
        fm_set_msg('Names not set', 'error');
    }
    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}

if (isset($_GET['dl'])) {
    $dl = $_GET['dl'];
    $dl = fm_clean_path($dl);
    $dl = str_replace('/', '', $dl);
    $path = FM_ROOT_PATH;
    if (FM_PATH != '') {
        $path .= '/' . FM_PATH;
    }
    if ($dl != '' && is_file($path . '/' . $dl)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path . '/' . $dl) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path . '/' . $dl));
        readfile($path . '/' . $dl);
        exit;
    } else {
        fm_set_msg('File not found', 'error');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }
}


if (isset($_POST['upl']) && !FM_READONLY) {
    $path = FM_ROOT_PATH;
    if (FM_PATH != '') {
        $path .= '/' . FM_PATH;
    }

    $errors = 0;
    $uploads = 0;
    $total = count($_FILES['upload']['name']);
    $allowed = (FM_EXTENSION) ? explode(',', FM_EXTENSION) : false;

    for ($i = 0; $i < $total; $i++) {
        $filename = $_FILES['upload']['name'][$i];
        $tmp_name = $_FILES['upload']['tmp_name'][$i];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $isFileAllowed = ($allowed) ? in_array($ext,$allowed) : true;
        if (empty($_FILES['upload']['error'][$i]) && !empty($tmp_name) && $tmp_name != 'none' && $isFileAllowed) {
            if (move_uploaded_file($tmp_name, $path . '/' . $_FILES['upload']['name'][$i])) {
                @chmod($path . '/' . $filename, 0640);
                $uploads++;
            } else {
                $errors++;
            }
        }
    }

    if ($errors == 0 && $uploads > 0) {
        fm_set_msg(sprintf('All files uploaded to <b>%s</b>', fm_enc($path)));
    } elseif ($errors == 0 && $uploads == 0) {
        fm_set_msg('Nothing uploaded', 'alert');
    } else {
        fm_set_msg(sprintf('Error while uploading files. Uploaded files: %s', $uploads), 'error');
    }
    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}


if (isset($_POST['group'], $_POST['delete']) && !FM_READONLY) {
    $path = FM_ROOT_PATH;
    if (FM_PATH != '') {
        $path .= '/' . FM_PATH;
    }

    $errors = 0;
    $files = $_POST['file'];
    if (is_array($files) && count($files)) {
        foreach ($files as $f) {
            if ($f != '') {
                $new_path = $path . '/' . $f;
                if (!fm_rdelete($new_path)) {
                    $errors++;
                }
            }
        }
        if ($errors == 0) {
            fm_set_msg('Selected files and folder deleted');
        } else {
            fm_set_msg('Error while deleting items', 'error');
        }
    } else {
        fm_set_msg('Nothing selected', 'alert');
    }

    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}


if (isset($_POST['group'], $_POST['zip']) && !FM_READONLY) {
    $path = FM_ROOT_PATH;
    if (FM_PATH != '') {
        $path .= '/' . FM_PATH;
    }

    if (!class_exists('ZipArchive')) {
        fm_set_msg('Operations with archives are not available', 'error');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }

    $files = $_POST['file'];
    if (!empty($files)) {
        chdir($path);

        if (count($files) == 1) {
            $one_file = reset($files);
            $one_file = basename($one_file);
            $zipname = $one_file . '_' . date('ymd_His') . '.zip';
        } else {
            $zipname = 'archive_' . date('ymd_His') . '.zip';
        }

        $zipper = new FM_Zipper();
        $res = $zipper->create($zipname, $files);

        if ($res) {
            fm_set_msg(sprintf('Archive <b>%s</b> created', fm_enc($zipname)));
        } else {
            fm_set_msg('Archive not created', 'error');
        }
    } else {
        fm_set_msg('Nothing selected', 'alert');
    }

    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}

if (isset($_GET['unzip']) && !FM_READONLY) {
    $unzip = $_GET['unzip'];
    $unzip = fm_clean_path($unzip);
    $unzip = str_replace('/', '', $unzip);

    $path = FM_ROOT_PATH;
    if (FM_PATH != '') {
        $path .= '/' . FM_PATH;
    }

    if (!class_exists('ZipArchive')) {
        fm_set_msg('Operations with archives are not available', 'error');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }

    if ($unzip != '' && is_file($path . '/' . $unzip)) {
        $zip_path = $path . '/' . $unzip;

        //to folder
        $tofolder = '';
        if (isset($_GET['tofolder'])) {
            $tofolder = pathinfo($zip_path, PATHINFO_FILENAME);
            if (fm_mkdir($path . '/' . $tofolder, true)) {
                $path .= '/' . $tofolder;
            }
        }

        $zipper = new FM_Zipper();
        $res = $zipper->unzip($zip_path, $path);

        if ($res) {
            fm_set_msg('Archive unpacked');
        } else {
            fm_set_msg('Archive not unpacked', 'error');
        }

    } else {
        fm_set_msg('File not found', 'error');
    }
    fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
}

$path = FM_ROOT_PATH;
if (FM_PATH != '') {
    $path .= '/' . FM_PATH;
}

if (!is_dir($path)) {
    fm_redirect(FM_SELF_URL . '?p=');
}

$parent = fm_get_parent_path(FM_PATH);

$objects = is_readable($path) ? scandir($path) : array();
$folders = array();
$files = array();
if (is_array($objects)) {
    foreach ($objects as $file) {
        if ($file == '.' || $file == '..' && in_array($file, $GLOBALS['exclude_folders'])) {
            continue;
        }
        if (!FM_SHOW_HIDDEN && substr($file, 0, 1) === '.') {
            continue;
        }
        $new_path = $path . '/' . $file;
        if (is_file($new_path)) {
            $files[] = $file;
        } elseif (is_dir($new_path) && $file != '.' && $file != '..' && !in_array($file, $GLOBALS['exclude_folders'])) {
            $folders[] = $file;
        }
    }
}

if (!empty($files)) {
    natcasesort($files);
}
if (!empty($folders)) {
    natcasesort($folders);
}

if (isset($_GET['upload']) && !FM_READONLY) {
    fm_show_header(); 
    fm_show_nav_path(FM_PATH); 
    ?>
    <style>
        #hide input[type=file] {
            display:none;
            margin:10px;
        }
        #hide input[type=file] + label {
            display:inline-block;
            margin:20px;
            padding: 4px 32px;
            background-color: #FFFFFF;
            border:solid 1px #666F77;
            border-radius: 6px;
            color:#666F77;
        }
        #hide input[type=file]:active + label {
            background-image: none;
            background-color:#2D6C7A;
            color:#FFFFFF;
        }
    </style>
    <div class="container mt-3">
        <p><h4><b>Uploading files</b></h4></p>
        <p class="break-word">Destination folder: <?php echo fm_enc(fm_convert_win(FM_ROOT_PATH . '/' . FM_PATH)) ?></p>
        <hr>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="p" value="<?php echo fm_enc(FM_PATH) ?>">
            <input type="hidden" name="upl" value="1">

            <div class="p-2" style="background: white">
            <input type="file" class="mt-2" name="upload[]"><br>
            <input type="file" class="mt-2" name="upload[]"><br>
            <input type="file" class="mt-2" name="upload[]"><br>
            <input type="file" class="mt-2" name="upload[]"><br>
            <input type="file" class="mt-2" name="upload[]"><br>
            </div>
            <br>
            <p>
                <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload</button> &nbsp;
                <b><a href="?p=<?php echo urlencode(FM_PATH) ?>" class="text-secondary"><i class="fa fa-times-circle"></i> Cancel</a></b>
            </p>
        </form>
    </div>
    <?php
    fm_show_footer();
    exit;
}

if (isset($_POST['copy']) && !FM_READONLY) {
    $copy_files = $_POST['file'];
    if (!is_array($copy_files) || empty($copy_files)) {
        fm_set_msg('Nothing selected', 'alert');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }

    fm_show_header(); 
    fm_show_nav_path(FM_PATH); 
    ?>
    <div class="container mt-3">
        <p><h4><b>Copying</b></h4></p>
        <form action="" method="post">
            <input type="hidden" name="p" value="<?php echo fm_enc(FM_PATH) ?>">
            <input type="hidden" name="finish" value="1">
            <?php
            foreach ($copy_files as $cf) {
                echo '<input type="hidden" name="file[]" value="' . fm_enc($cf) . '">' . PHP_EOL;
            }
            ?>
            <p class="break-word">Files: <b><?php echo implode('</b>, <b>', $copy_files) ?></b></p><hr>
            <p class="break-word">Source folder: <?php echo fm_enc(fm_convert_win(FM_ROOT_PATH . '/' . FM_PATH)) ?><br>
                <div class="form-inline"><label for="inp_copy_to">Destination folder:</label>
                <?php echo FM_ROOT_PATH ?>/<input type="text" class="form-control" name="copy_to" id="inp_copy_to" value="<?php echo fm_enc(FM_PATH) ?>">
            </div>
            </p>
            <p>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="move" value="1" class="custom-control-input" id="customCheck1">
                <label class="custom-control-label" for="customCheck1">Move</label>
            </div>
            </p>
            <hr>
            <p>
                <button type="submit" class="btn btn-primary"><i class="fa fa-copy"></i> Copy </button> &nbsp;
                <b><a href="?p=<?php echo urlencode(FM_PATH) ?>" class="text-secondary"><i class="fa fa-times-circle"></i> Cancel</a></b>
            </p>
        </form>
    </div>
    <?php
    fm_show_footer();
    exit;
}

if (isset($_GET['copy']) && !isset($_GET['finish']) && !FM_READONLY) {
    $copy = $_GET['copy'];
    $copy = fm_clean_path($copy);
    if ($copy == '' || !file_exists(FM_ROOT_PATH . '/' . $copy)) {
        fm_set_msg('File not found', 'error');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }

    fm_show_header(); 
    fm_show_nav_path(FM_PATH); 
    ?>
    <div class="container mt-3">
        <p><h4><b>Copying</b></h4></p>
        <p class="break-word">
            Source path: <?php echo fm_enc(fm_convert_win(FM_ROOT_PATH . '/' . $copy)) ?><br>
            Destination folder: <?php echo fm_enc(fm_convert_win(FM_ROOT_PATH . '/' . FM_PATH)) ?>
        </p><hr>
        <p>
            <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;copy=<?php echo urlencode($copy) ?>&amp;finish=1"><i class="fa fa-check-circle"></i> Copy</a></b> &nbsp;
            <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;copy=<?php echo urlencode($copy) ?>&amp;finish=1&amp;move=1"><i class="fa fa-check-circle"></i> Move</a></b> &nbsp;
            <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(FM_PATH) ?>"><i class="fa fa-times-circle"></i> Cancel</a></b>
        </p>
        <p><i>Select folder</i></p>
        <ul class="folders break-word">
            <?php
            if ($parent !== false) {
                ?>
                <li><a href="?p=<?php echo urlencode($parent) ?>&amp;copy=<?php echo urlencode($copy) ?>"><i class="fa fa-chevron-circle-left"></i> ..</a></li>
                <?php
            }
            foreach ($folders as $f) {
                ?>
                <li><a href="?p=<?php echo urlencode(trim(FM_PATH . '/' . $f, '/')) ?>&amp;copy=<?php echo urlencode($copy) ?>"><i class="fa fa-folder-o"></i> <?php echo fm_convert_win($f) ?></a></li>
                <?php
            }
            ?>
        </ul>
    </div>
    <?php
    fm_show_footer();
    exit;
}
if (isset($_GET['view'])) {
    $file = $_GET['view'];
    $file = fm_clean_path($file);
    $file = str_replace('/', '', $file);
    if ($file == '' || !is_file($path . '/' . $file)) {
        fm_set_msg('File not found', 'error');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }

    fm_show_header(); 
    fm_show_nav_path(FM_PATH); 

    $file_url = FM_ROOT_URL . fm_convert_win((FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $file);
    $file_path = $path . '/' . $file;

    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $mime_type = fm_get_mime_type($file_path);
    $filesize = filesize($file_path);

    $is_zip = false;
    $is_image = false;
    $is_audio = false;
    $is_video = false;
    $is_text = false;

    $view_title = 'File';
    $filenames = false; 
    $content = ''; 
    if ($ext == 'zip') {
        $is_zip = true;
        $view_title = 'Archive';
        $filenames = fm_get_zif_info($file_path);
    } elseif (in_array($ext, fm_get_image_exts())) {
        $is_image = true;
        $view_title = 'Image';
    } elseif (in_array($ext, fm_get_audio_exts())) {
        $is_audio = true;
        $view_title = 'Audio';
    } elseif (in_array($ext, fm_get_video_exts())) {
        $is_video = true;
        $view_title = 'Video';
    } elseif (in_array($ext, fm_get_text_exts()) || substr($mime_type, 0, 4) == 'text' || in_array($mime_type, fm_get_text_mimes())) {
        $is_text = true;
        $content = file_get_contents($file_path);
    }

    ?>
    <div class="path pt-3">
        <p class="break-word"><b><?php echo $view_title ?> "<?php echo fm_enc(fm_convert_win($file)) ?>"</b></p>
        <p class="break-word">
            Full path: <?php echo fm_enc(fm_convert_win($file_path)) ?><br>
            File size: <?php echo fm_get_filesize($filesize) ?><?php if ($filesize >= 1000): ?> (<?php echo sprintf('%s bytes', $filesize) ?>)<?php endif; ?><br>
            MIME-type: <?php echo $mime_type ?><br>
            <?php
            if ($is_zip && $filenames !== false) {
                $total_files = 0;
                $total_comp = 0;
                $total_uncomp = 0;
                foreach ($filenames as $fn) {
                    if (!$fn['folder']) {
                        $total_files++;
                    }
                    $total_comp += $fn['compressed_size'];
                    $total_uncomp += $fn['filesize'];
                }
                ?>
                Files in archive: <?php echo $total_files ?><br>
                Total size: <?php echo fm_get_filesize($total_uncomp) ?><br>
                Size in archive: <?php echo fm_get_filesize($total_comp) ?><br>
                Compression: <?php echo round(($total_comp / $total_uncomp) * 100) ?>%<br>
                <?php
            }
            if ($is_image) {
                $image_size = getimagesize($file_path);
                echo 'Image sizes: ' . (isset($image_size[0]) ? $image_size[0] : '0') . ' x ' . (isset($image_size[1]) ? $image_size[1] : '0') . '<br>';
            }
            if ($is_text) {
                $is_utf8 = fm_is_utf8($content);
                if (function_exists('iconv')) {
                    if (!$is_utf8) {
                        $content = iconv(FM_ICONV_INPUT_ENC, 'UTF-8//IGNORE', $content);
                    }
                }
                echo 'Charset: ' . ($is_utf8 ? 'utf-8' : '8 bit') . '<br>';
            }
            ?>
        </p>
        <p>
            <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;dl=<?php echo urlencode($file) ?>"><i class="fa fa-cloud-download"></i> Download</a></b> &nbsp;
            <b><a class="btn-outline-primary btn-sm btn" href="<?php echo fm_enc($file_url) ?>" target="_blank"><i class="fa fa-external-link-square"></i> Open</a></b> &nbsp;
            <?php
            if (!FM_READONLY && $is_zip && $filenames !== false) {
                $zip_name = pathinfo($file_path, PATHINFO_FILENAME);
                ?>
                <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;unzip=<?php echo urlencode($file) ?>"><i class="fa fa-check-circle"></i> UnZip</a></b> &nbsp;
                <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;unzip=<?php echo urlencode($file) ?>&amp;tofolder=1" title="UnZip to <?php echo fm_enc($zip_name) ?>"><i class="fa fa-check-circle"></i>
                        UnZip to folder</a></b> &nbsp;
                <?php
            }
            if($is_text && !FM_READONLY) {
                ?>
                <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(trim(FM_PATH)) ?>&amp;edit=<?php echo urlencode($file) ?>" class="edit-file"><i class="fa fa-pencil-square"></i> Edit</a></b> &nbsp;
                <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(trim(FM_PATH)) ?>&amp;edit=<?php echo urlencode($file) ?>&env=ace" class="edit-file"><i class="fa fa-pencil-square"></i> Advanced Edit</a></b> &nbsp;
            <?php }
            if($send_mail && !FM_READONLY) {
                ?>
                <b><a class="btn-outline-primary btn-sm btn" href="javascript:mailto('<?php echo urlencode(trim(FM_ROOT_PATH.'/'.FM_PATH)) ?>','<?php echo urlencode($file) ?>')"><i class="fa fa-pencil-square"></i> Mail</a></b> &nbsp;
            <?php } ?>
            <b><a class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(FM_PATH) ?>"><i class="fa fa-chevron-circle-left"></i> Back</a></b>
        </p>
        <?php
        if ($is_zip) {

            if ($filenames !== false) {
                echo '<code class="maxheight">';
                foreach ($filenames as $fn) {
                    if ($fn['folder']) {
                        echo '<b>' . fm_enc($fn['name']) . '</b><br>';
                    } else {
                        echo $fn['name'] . ' (' . fm_get_filesize($fn['filesize']) . ')<br>';
                    }
                }
                echo '</code>';
            } else {
                echo '<p>Error while fetching archive info</p>';
            }
        } elseif ($is_image) {

            if (in_array($ext, array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'ico'))) {
                echo '<p><img src="' . fm_enc($file_url) . '" alt="" class="preview-img"></p>';
            }
        } elseif ($is_audio) {

            echo '<p><audio src="' . fm_enc($file_url) . '" controls preload="metadata"></audio></p>';
        } elseif ($is_video) {

            echo '<div class="preview-video"><video src="' . fm_enc($file_url) . '" width="640" height="360" controls preload="metadata"></video></div>';
        } elseif ($is_text) {
            if (FM_USE_HIGHLIGHTJS) {

                $hljs_classes = array(
                    'shtml' => 'xml',
                    'htaccess' => 'apache',
                    'phtml' => 'php',
                    'lock' => 'json',
                    'svg' => 'xml',
                );
                $hljs_class = isset($hljs_classes[$ext]) ? 'lang-' . $hljs_classes[$ext] : 'lang-' . $ext;
                if (empty($ext) || in_array(strtolower($file), fm_get_text_names()) || preg_match('#\.min\.(css|js)$#i', $file)) {
                    $hljs_class = 'nohighlight';
                }
                $content = '<pre class="with-hljs"><code class="' . $hljs_class . '">' . fm_enc($content) . '</code></pre>';
            } elseif (in_array($ext, array('php', 'php4', 'php5', 'phtml', 'phps'))) {
   
                $content = highlight_string($content, true);
            } else {
                $content = '<pre>' . fm_enc($content) . '</pre>';
            }
            echo $content;
        }
        ?>
    </div>
    <?php
    fm_show_footer();
    exit;
}


if (isset($_GET['edit'])) {
    $file = $_GET['edit'];
    $file = fm_clean_path($file);
    $file = str_replace('/', '', $file);
    if ($file == '' || !is_file($path . '/' . $file)) {
        fm_set_msg('File not found', 'error');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }

    fm_show_header(); 
    fm_show_nav_path(FM_PATH); 

    $file_url = FM_ROOT_URL . fm_convert_win((FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $file);
    $file_path = $path . '/' . $file;

 
    $isNormalEditor = true;
    if(isset($_GET['env'])) {
        if($_GET['env'] == "ace") {
            $isNormalEditor = false;
        }
    }


    if(isset($_POST['savedata'])) {
        $writedata = $_POST['savedata'];
        $fd=fopen($file_path,"w");
        @fwrite($fd, $writedata);
        fclose($fd);
        fm_set_msg('File Saved Successfully', 'alert');
    }

    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $mime_type = fm_get_mime_type($file_path);
    $filesize = filesize($file_path);
    $is_text = false;
    $content = '';

    if (in_array($ext, fm_get_text_exts()) || substr($mime_type, 0, 4) == 'text' || in_array($mime_type, fm_get_text_mimes())) {
        $is_text = true;
        $content = file_get_contents($file_path);
    }

    ?>
    <div class="path pt-3">
        <div class="mb-3">
            <a title="Cancel" class="btn-outline-primary btn-sm btn" href="?p=<?php echo urlencode(trim(FM_PATH)) ?>&amp;view=<?php echo urlencode($file) ?>"><i class="fa fa-reply-all"></i> Cancel</a>
            <a title="Backup" class="btn-outline-primary btn-sm btn" href="javascript:backup('<?php echo urlencode($path) ?>','<?php echo urlencode($file) ?>')"><i class="fa fa-database"></i> Backup</a>
            <?php if($is_text) { ?>
                <?php if($isNormalEditor) { ?>
                    <a class="btn-outline-primary btn-sm btn" title="Advanced" href="?p=<?php echo urlencode(trim(FM_PATH)) ?>&amp;edit=<?php echo urlencode($file) ?>&amp;env=ace"><i class="fa fa-paper-plane"></i> Advanced Editor</a>
                    <button class="btn-outline-primary btn-sm btn" type="button" name="Save" data-url="<?php echo fm_enc($file_url) ?>" onclick="edit_save(this,'nrl')"><i class="fa fa-floppy-o"></i> Save</button>
                <?php } else { ?>
                    <a class="btn-outline-primary btn-sm btn" title="Plain Editor" href="?p=<?php echo urlencode(trim(FM_PATH)) ?>&amp;edit=<?php echo urlencode($file) ?>"><i class="fa fa-text-height"></i> Plain Editor</a>
                    <button class="btn-outline-primary btn-sm btn" type="button" name="Save" data-url="<?php echo fm_enc($file_url) ?>" onclick="edit_save(this,'ace')"><i class="fa fa-floppy-o"></i> Save</button>
                <?php } ?>
            <?php } ?>
        </div>
        <?php
        if ($is_text && $isNormalEditor) {
            echo '<textarea id="normal-editor" rows="33" cols="120" style="width: 99.5%;">'. htmlspecialchars($content) .'</textarea>';
        } elseif ($is_text) {
            echo '<div id="editor" contenteditable="true">'. htmlspecialchars($content) .'</div>';
        } else {
            fm_set_msg('FILE EXTENSION HAS NOT SUPPORTED', 'error');
        }
        ?>
    </div>
    <?php
    fm_show_footer();
    exit;
}


if (isset($_GET['chmod']) && !FM_READONLY && !FM_IS_WIN) {
    $file = $_GET['chmod'];
    $file = fm_clean_path($file);
    $file = str_replace('/', '', $file);
    if ($file == '' || (!is_file($path . '/' . $file) && !is_dir($path . '/' . $file))) {
        fm_set_msg('File not found', 'error');
        fm_redirect(FM_SELF_URL . '?p=' . urlencode(FM_PATH));
    }

    fm_show_header(); 
    fm_show_nav_path(FM_PATH); 

    $file_url = FM_ROOT_URL . (FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $file;
    $file_path = $path . '/' . $file;

    $mode = fileperms($path . '/' . $file);

    ?>
    <div class="path">
    <div class="py-3 container">
        <p><b><?php echo '<h4><b>Change Permissions</b></h4>'; ?></b></p>
        <p>
            <?php echo 'Full path:'; ?> <?php echo $file_path ?><br><hr>
        </p>
        <form action="" method="post">
            <input type="hidden" name="p" value="<?php echo fm_enc(FM_PATH) ?>">
            <input type="hidden" name="chmod" value="<?php echo fm_enc($file) ?>">

            <table class="compact-table">
                <tr>
                    <td></td>
                    <td><b>Owner</b></td>
                    <td><b>Group</b></td>
                    <td><b>Other</b></td>
                </tr>
                <tr>
                    <td style="text-align: right"><b>Read</b></td>
                    <td><label><input type="checkbox" name="ur" value="1"<?php echo ($mode & 00400) ? ' checked' : '' ?>></label></td>
                    <td><label><input type="checkbox" name="gr" value="1"<?php echo ($mode & 00040) ? ' checked' : '' ?>></label></td>
                    <td><label><input type="checkbox" name="or" value="1"<?php echo ($mode & 00004) ? ' checked' : '' ?>></label></td>
                </tr>
                <tr>
                    <td style="text-align: right"><b>Write</b></td>
                    <td><label><input type="checkbox" name="uw" value="1"<?php echo ($mode & 00200) ? ' checked' : '' ?>></label></td>
                    <td><label><input type="checkbox" name="gw" value="1"<?php echo ($mode & 00020) ? ' checked' : '' ?>></label></td>
                    <td><label><input type="checkbox" name="ow" value="1"<?php echo ($mode & 00002) ? ' checked' : '' ?>></label></td>
                </tr>
                <tr>
                    <td style="text-align: right"><b>Execute</b></td>
                    <td><label><input type="checkbox" name="ux" value="1"<?php echo ($mode & 00100) ? ' checked' : '' ?>></label></td>
                    <td><label><input type="checkbox" name="gx" value="1"<?php echo ($mode & 00010) ? ' checked' : '' ?>></label></td>
                    <td><label><input type="checkbox" name="ox" value="1"<?php echo ($mode & 00001) ? ' checked' : '' ?>></label></td>
                </tr>
            </table>

            <p>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check-circle"></i> Change</button> &nbsp;
                <b><a href="?p=<?php echo urlencode(FM_PATH) ?>" class="text-secondary"><i class="fa fa-times-circle"></i> Cancel</a></b>
            </p>

        </form>

    </div>
    </div>
    <?php
    fm_show_footer();
    exit;
}


fm_show_header(); 
fm_show_nav_path(FM_PATH); 


fm_show_message();

$num_files = count($files);
$num_folders = count($folders);
$all_files_size = 0;
?>
<form class="mt-3 mx-3" action="" method="post">
    <input type="hidden" name="p" value="<?php echo fm_enc(FM_PATH) ?>">
    <input type="hidden" name="group" value="1">
    <?php if(FM_TREEVIEW) { ?>
        <div class="row">
            <div class="col-sm-3  <?php  echo $_SESSION['treeHide'] == 'true'?'d-none':''; ?>">
        <div class="file-tree-view border w-100" id="file-tree-view">
            <div class="tree-title"><i class="fa fa-align-left fa-fw"></i> Browse</div>
            <?php

            echo php_file_tree($root_path, "javascript:alert('You clicked on [link]');");
            ?>
        </div>
            </div>
    <?php } ?>
            <div class="col-sm-<?php  echo $_SESSION['treeHide'] == 'true'?'12':'9'; ?> mt-3 mt-md-0">
                <div class="table-responsive">
    <table class="table" id="main-table"><thead><tr>
            <?php if (!FM_READONLY): ?>
                <th style="width:3%">
                    <label>
                        <input type="checkbox" title="Invert selection" onclick="checkbox_toggle()">
                    </label>
                </th><?php endif; ?>
            <th>Name</th>
            <th style="width:10%">Size</th>
            <th style="width:12%">Modified</th>
            <?php if (!FM_IS_WIN): ?>
                <th style="width:6%">Perms</th>
                <th style="width:10%">Owner</th><?php endif; ?>
            <th style="width:<?php if (!FM_READONLY): ?>13<?php else: ?>6.5<?php endif; ?>%">Actions</th>
        </tr>
        </thead>
        <?php

        if ($parent !== false) {
            ?>
            <tr><?php if (!FM_READONLY): ?><td></td><?php endif; ?><td colspan="<?php echo !FM_IS_WIN ? '6' : '4' ?>"><a href="?p=<?php echo urlencode($parent) ?>"><i class="fa fa-chevron-circle-left"></i> ..</a></td></tr>
            <?php
        }
        foreach ($folders as $f) {
            $is_link = is_link($path . '/' . $f);
            $img = $is_link ? 'icon-link_folder' : 'fa fa-folder-o';
            $modif = date(FM_DATETIME_FORMAT, filemtime($path . '/' . $f));
            $perms = substr(decoct(fileperms($path . '/' . $f)), -4);
            if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
                $owner = posix_getpwuid(fileowner($path . '/' . $f));
                $group = posix_getgrgid(filegroup($path . '/' . $f));
            } else {
                $owner = array('name' => '?');
                $group = array('name' => '?');
            }
            ?>
            <tr>
                <?php if (!FM_READONLY): ?>
                    <td>
                    <label>
                        <input type="checkbox" name="file[]" value="<?php echo fm_enc($f) ?>">
                    </label>
                    </td><?php endif; ?>
                <td>
                    <div class="filename"><a href="?p=<?php echo urlencode(trim(FM_PATH . '/' . $f, '/')) ?>"><i
                                class="<?php echo $img ?>"></i> <?php echo fm_convert_win($f) ?>
                        </a><?php echo($is_link ? ' &rarr; <i>' . readlink($path . '/' . $f) . '</i>' : '') ?></div>
                </td>
                <td>Folder</td>
                <td><?php echo $modif ?></td>
                <?php if (!FM_IS_WIN): ?>
                    <td><?php if (!FM_READONLY): ?><a title="Change Permissions"
                                                      href="?p=<?php echo urlencode(FM_PATH) ?>&amp;chmod=<?php echo urlencode($f) ?>"><?php echo $perms ?></a><?php else: ?><?php echo $perms ?><?php endif; ?>
                    </td>
                    <td><?php echo $owner['name'] . ':' . $group['name'] ?></td>
                <?php endif; ?>
                <td class="inline-actions"><?php if (!FM_READONLY): ?>
                        <a title="Delete" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;del=<?php echo urlencode($f) ?>"
                           onclick="return confirm('Delete folder?');"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <a title="Rename" href="#"
                           onclick="rename('<?php echo fm_enc(FM_PATH) ?>', '<?php echo fm_enc($f) ?>');return false;"><i
                                class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                        <a title="Copy to..."
                           href="?p=&amp;copy=<?php echo urlencode(trim(FM_PATH . '/' . $f, '/')) ?>"><i
                                class="fa fa-files-o" aria-hidden="true"></i></a>
                    <?php endif; ?>
                    <a title="Direct link"
                       href="<?php echo fm_enc(FM_ROOT_URL . (FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $f . '/') ?>"
                       target="_blank"><i class="fa fa-link" aria-hidden="true"></i></a>
                </td>
            </tr>
            <?php
            flush();
        }

        foreach ($files as $f) {
            $is_link = is_link($path . '/' . $f);
            $img = $is_link ? 'fa fa-file-text-o' : fm_get_file_icon_class($path . '/' . $f);
            $modif = date("d.m.y H:i", filemtime($path . '/' . $f));
            $filesize_raw = filesize($path . '/' . $f);
            $filesize = fm_get_filesize($filesize_raw);
            $filelink = '?p=' . urlencode(FM_PATH) . '&amp;view=' . urlencode($f);
            $all_files_size += $filesize_raw;
            $perms = substr(decoct(fileperms($path . '/' . $f)), -4);
            if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
                $owner = posix_getpwuid(fileowner($path . '/' . $f));
                $group = posix_getgrgid(filegroup($path . '/' . $f));
            } else {
                $owner = array('name' => '?');
                $group = array('name' => '?');
            }
            ?>
            <tr>
                <?php if (!FM_READONLY): ?><td><label><input type="checkbox" name="file[]" value="<?php echo fm_enc($f) ?>"></label></td><?php endif; ?>
                <td><div class="filename"><a href="<?php echo $filelink ?>" title="File info"><i class="<?php echo $img ?>"></i> <?php echo fm_convert_win($f) ?></a><?php echo ($is_link ? ' &rarr; <i>' . readlink($path . '/' . $f) . '</i>' : '') ?></div></td>
                <td><span title="<?php printf('%s bytes', $filesize_raw) ?>"><?php echo $filesize ?></span></td>
                <td><?php echo $modif ?></td>
                <?php if (!FM_IS_WIN): ?>
                    <td><?php if (!FM_READONLY): ?><a title="<?php echo 'Change Permissions' ?>" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;chmod=<?php echo urlencode($f) ?>"><?php echo $perms ?></a><?php else: ?><?php echo $perms ?><?php endif; ?></td>
                    <td><?php echo fm_enc($owner['name'] . ':' . $group['name']) ?></td>
                <?php endif; ?>
                <td class="inline-actions">
                    <?php if (!FM_READONLY): ?>
                        <a title="Delete" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;del=<?php echo urlencode($f) ?>" onclick="return confirm('Delete file?');"><i class="fa fa-trash-o"></i></a>
                        <a title="Rename" href="#" onclick="rename('<?php echo fm_enc(FM_PATH) ?>', '<?php echo fm_enc($f) ?>');return false;"><i class="fa fa-pencil-square-o"></i></a>
                        <a title="Copy to..." href="?p=<?php echo urlencode(FM_PATH) ?>&amp;copy=<?php echo urlencode(trim(FM_PATH . '/' . $f, '/')) ?>"><i class="fa fa-files-o"></i></a>
                    <?php endif; ?>
                    <a title="Direct link" href="<?php echo fm_enc(FM_ROOT_URL . (FM_PATH != '' ? '/' . FM_PATH : '') . '/' . $f) ?>" target="_blank"><i class="fa fa-link"></i></a>
                    <a title="Download" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;dl=<?php echo urlencode($f) ?>"><i class="fa fa-download"></i></a>
                </td></tr>
            <?php
            flush();
        }

        if (empty($folders) && empty($files)) {
            ?>
            <tr><?php if (!FM_READONLY): ?><td></td><?php endif; ?><td colspan="<?php echo !FM_IS_WIN ? '6' : '4' ?>"><em><?php echo 'Folder is empty' ?></em></td></tr>
            <?php
        } else {
            ?>
            <tr><?php if (!FM_READONLY): ?><td class="gray"></td><?php endif; ?><td class="gray" colspan="<?php echo !FM_IS_WIN ? '6' : '4' ?>">
                    Full size: <span title="<?php printf('%s bytes', $all_files_size) ?>"><?php echo fm_get_filesize($all_files_size) ?></span>,
                    files: <?php echo $num_files ?>,
                    folders: <?php echo $num_folders ?>
                </td></tr>
            <?php
        }
        ?>
    </table>
                </div>
            </div>
        </div>
    <?php if (!FM_READONLY): ?>
        <div class="mb-5 row">
            <div class="col-sm-9 offset-sm-3"><a href="#/select-all" class="mt-2 mt-sm-0 btn2 btn btn-small btn-outline-primary" onclick="select_all();return false;"><i class="fa fa-check-square"></i> Select all</a> &nbsp;
            <a href="#/unselect-all" class="mt-2 mt-sm-0 btn2 btn btn-small btn-outline-primary" onclick="unselect_all();return false;"><i class="fa fa-window-close"></i> Unselect all</a> &nbsp;
            <a href="#/invert-all" class="mt-2 mt-sm-0 btn2 btn btn-small btn-outline-primary" onclick="invert_all();return false;"><i class="fa fa-th-list"></i> Invert selection</a> &nbsp;
            <input type="submit" class="hidden" name="delete" id="a-delete" value="Delete" onclick="return confirm('Delete selected files and folders?')">
            <a href="javascript:document.getElementById('a-delete').click();" class="mt-2 mt-sm-0 btn2 btn btn-small btn-outline-primary"><i class="fa fa-trash"></i> Delete </a> &nbsp;
            <input type="submit" class="hidden" name="zip" id="a-zip" value="Zip" onclick="return confirm('Create archive?')">
            <a href="javascript:document.getElementById('a-zip').click();" class="mt-2 mt-sm-0 btn2 btn btn-small btn-outline-primary"><i class="fa fa-file-archive-o"></i> Zip </a> &nbsp;
            <input type="submit" class="hidden" name="copy" id="a-copy" value="Copy">
            <a href="javascript:document.getElementById('a-copy').click();" class="mt-2 mt-sm-0 btn2 btn btn-small btn-outline-primary"><i class="fa fa-files-o"></i> Copy </a>
            </div>
        </div>
    <?php endif; ?>
</form>

<?php
fm_show_footer();

function fm_rdelete($path)
{
    if (is_link($path)) {
        return unlink($path);
    } elseif (is_dir($path)) {
        $objects = scandir($path);
        $ok = true;
        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file != '.' && $file != '..') {
                    if (!fm_rdelete($path . '/' . $file)) {
                        $ok = false;
                    }
                }
            }
        }
        return ($ok) ? rmdir($path) : false;
    } elseif (is_file($path)) {
        return unlink($path);
    }
    return false;
}


function fm_rchmod($path, $filemode, $dirmode)
{
    if (is_dir($path)) {
        if (!chmod($path, $dirmode)) {
            return false;
        }
        $objects = scandir($path);
        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file != '.' && $file != '..') {
                    if (!fm_rchmod($path . '/' . $file, $filemode, $dirmode)) {
                        return false;
                    }
                }
            }
        }
        return true;
    } elseif (is_link($path)) {
        return true;
    } elseif (is_file($path)) {
        return chmod($path, $filemode);
    }
    return false;
}


function fm_rename($old, $new)
{
    return (!file_exists($new) && file_exists($old)) ? rename($old, $new) : null;
}


function fm_rcopy($path, $dest, $upd = true, $force = true)
{
    if (is_dir($path)) {
        if (!fm_mkdir($dest, $force)) {
            return false;
        }
        $objects = scandir($path);
        $ok = true;
        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file != '.' && $file != '..') {
                    if (!fm_rcopy($path . '/' . $file, $dest . '/' . $file)) {
                        $ok = false;
                    }
                }
            }
        }
        return $ok;
    } elseif (is_file($path)) {
        return fm_copy($path, $dest, $upd);
    }
    return false;
}

function fm_mkdir($dir, $force)
{
    if (file_exists($dir)) {
        if (is_dir($dir)) {
            return $dir;
        } elseif (!$force) {
            return false;
        }
        unlink($dir);
    }
    return mkdir($dir, 0777, true);
}


function fm_copy($f1, $f2, $upd)
{
    $time1 = filemtime($f1);
    if (file_exists($f2)) {
        $time2 = filemtime($f2);
        if ($time2 >= $time1 && $upd) {
            return false;
        }
    }
    $ok = copy($f1, $f2);
    if ($ok) {
        touch($f2, $time1);
    }
    return $ok;
}

function fm_get_mime_type($file_path)
{
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        return $mime;
    } elseif (function_exists('mime_content_type')) {
        return mime_content_type($file_path);
    } elseif (!stristr(ini_get('disable_functions'), 'shell_exec')) {
        $file = escapeshellarg($file_path);
        $mime = shell_exec('file -bi ' . $file);
        return $mime;
    } else {
        return '--';
    }
}


function fm_redirect($url, $code = 302)
{
    header('Location: ' . $url, true, $code);
    exit;
}


function fm_clean_path($path)
{
    $path = trim($path);
    $path = trim($path, '\\/');
    $path = str_replace(array('../', '..\\'), '', $path);
    if ($path == '..') {
        $path = '';
    }
    return str_replace('\\', '/', $path);
}


function fm_get_parent_path($path)
{
    $path = fm_clean_path($path);
    if ($path != '') {
        $array = explode('/', $path);
        if (count($array) > 1) {
            $array = array_slice($array, 0, -1);
            return implode('/', $array);
        }
        return '';
    }
    return false;
}


function fm_get_filesize($size)
{
    if ($size < 1000) {
        return sprintf('%s B', $size);
    } elseif (($size / 1024) < 1000) {
        return sprintf('%s KiB', round(($size / 1024), 2));
    } elseif (($size / 1024 / 1024) < 1000) {
        return sprintf('%s MiB', round(($size / 1024 / 1024), 2));
    } elseif (($size / 1024 / 1024 / 1024) < 1000) {
        return sprintf('%s GiB', round(($size / 1024 / 1024 / 1024), 2));
    } else {
        return sprintf('%s TiB', round(($size / 1024 / 1024 / 1024 / 1024), 2));
    }
}


function fm_get_zif_info($path)
{
    if (function_exists('zip_open')) {
        $arch = zip_open($path);
        if ($arch) {
            $filenames = array();
            while ($zip_entry = zip_read($arch)) {
                $zip_name = zip_entry_name($zip_entry);
                $zip_folder = substr($zip_name, -1) == '/';
                $filenames[] = array(
                    'name' => $zip_name,
                    'filesize' => zip_entry_filesize($zip_entry),
                    'compressed_size' => zip_entry_compressedsize($zip_entry),
                    'folder' => $zip_folder
                    //'compression_method' => zip_entry_compressionmethod($zip_entry),
                );
            }
            zip_close($arch);
            return $filenames;
        }
    }
    return false;
}


function fm_enc($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}


function scan($dir){
    $files = array();
    $_dir = $dir;
    $dir = FM_ROOT_PATH.'/'.$dir;
    // Is there actually such a folder/file?
    if(file_exists($dir)){
        foreach(scandir($dir) as $f) {
            if(!$f || $f[0] == '.') {
                continue; // Ignore hidden files
            }

            if(is_dir($dir . '/' . $f)) {
                // The path is a folder
                $files[] = array(
                    "name" => $f,
                    "type" => "folder",
                    "path" => $_dir.'/'.$f,
                    "items" => scan($dir . '/' . $f), // Recursively get the contents of the folder
                );
            } else {
                // It is a file
                $files[] = array(
                    "name" => $f,
                    "type" => "file",
                    "path" => $_dir,
                    "size" => filesize($dir . '/' . $f) // Gets the size of this file
                );
            }
        }
    }
    return $files;
}


function php_file_tree_dir($directory, $first_call = true) {
    // Recursive function called by php_file_tree() to list directories/files

    $php_file_tree = "";
    // Get and sort directories/files
    if( function_exists("scandir") ) $file = scandir($directory);
    natcasesort($file);
    // Make directories first
    $files = $dirs = array();
    foreach($file as $this_file) {
        if( is_dir("$directory/$this_file" ) ) {
            if(!in_array($this_file, $GLOBALS['exclude_folders'])){
                $dirs[] = $this_file;
            }
        } else {
            $files[] = $this_file;
        }
    }
    $file = array_merge($dirs, $files);

    if( count($file) > 2 ) { // Use 2 instead of 0 to account for . and .. "directories"
        $php_file_tree = "<ul";
        if( $first_call ) { $php_file_tree .= " class=\"pl-2 pt-2 php-file-tree\""; $first_call = false; }
        $php_file_tree .= ">";
        foreach( $file as $this_file ) {
            if( $this_file != "." && $this_file != ".." ) {
                if( is_dir("$directory/$this_file") ) {
                    // Directory
                    $php_file_tree .= "<li class=\"pft-directory\"><i class=\"fa fa-folder-o\"></i><a href=\"#\">" . htmlspecialchars($this_file) . "</a>";
                    $php_file_tree .= php_file_tree_dir("$directory/$this_file", false);
                    $php_file_tree .= "</li>";
                } else {
                    // File
                    $ext = fm_get_file_icon_class($this_file);
                    $path = str_replace($_SERVER['DOCUMENT_ROOT'],"",$directory);
                    $link = "?p="."$path" ."&view=".urlencode($this_file);
                    $php_file_tree .= "<li class=\"pft-file\"><a href=\"$link\"> <i class=\"$ext\"></i>" . htmlspecialchars($this_file) . "</a></li>";
                }
            }
        }
        $php_file_tree .= "</ul>";
    }
    return $php_file_tree;
}


function php_file_tree($directory) {
    // Remove trailing slash
    $code = "";
    if( substr($directory, -1) == "/" ) $directory = substr($directory, 0, strlen($directory) - 1);
    if(function_exists('php_file_tree_dir')) {
        $code .= php_file_tree_dir($directory);
        return $code;
    }
}


function fm_set_msg($msg, $status = 'ok')
{
    $_SESSION['message'] = $msg;
    $_SESSION['status'] = $status;
}


function fm_is_utf8($string)
{
    return preg_match('//u', $string);
}


function fm_convert_win($filename)
{
    if (FM_IS_WIN && function_exists('iconv')) {
        $filename = iconv(FM_ICONV_INPUT_ENC, 'UTF-8//IGNORE', $filename);
    }
    return $filename;
}


function fm_get_file_icon_class($path)
{
    // get extension
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    switch ($ext) {
        case 'ico': case 'gif': case 'jpg': case 'jpeg': case 'jpc': case 'jp2':
        case 'jpx': case 'xbm': case 'wbmp': case 'png': case 'bmp': case 'tif':
        case 'tiff': case 'svg':
        $img = 'fa fa-picture-o';
        break;
        case 'passwd': case 'ftpquota': case 'sql': case 'js': case 'json': case 'sh':
        case 'config': case 'twig': case 'tpl': case 'md': case 'gitignore':
        case 'c': case 'cpp': case 'cs': case 'py': case 'map': case 'lock': case 'dtd':
        $img = 'fa fa-file-code-o';
        break;
        case 'txt': case 'ini': case 'conf': case 'log': case 'htaccess':
        $img = 'fa fa-file-text-o';
        break;
        case 'css': case 'less': case 'sass': case 'scss':
        $img = 'fa fa-css3';
        break;
        case 'zip': case 'rar': case 'gz': case 'tar': case '7z':
        $img = 'fa fa-file-archive-o';
        break;
        case 'php': case 'php4': case 'php5': case 'phps': case 'phtml':
        $img = 'fa fa-code';
        break;
        case 'htm': case 'html': case 'shtml': case 'xhtml':
        $img = 'fa fa-html5';
        break;
        case 'xml': case 'xsl':
        $img = 'fa fa-file-excel-o';
        break;
        case 'wav': case 'mp3': case 'mp2': case 'm4a': case 'aac': case 'ogg':
        case 'oga': case 'wma': case 'mka': case 'flac': case 'ac3': case 'tds':
        $img = 'fa fa-music';
        break;
        case 'm3u': case 'm3u8': case 'pls': case 'cue':
        $img = 'fa fa-headphones';
        break;
        case 'avi': case 'mpg': case 'mpeg': case 'mp4': case 'm4v': case 'flv':
        case 'f4v': case 'ogm': case 'ogv': case 'mov': case 'mkv': case '3gp':
        case 'asf': case 'wmv':
        $img = 'fa fa-file-video-o';
        break;
        case 'eml': case 'msg':
        $img = 'fa fa-envelope-o';
        break;
        case 'xls': case 'xlsx':
        $img = 'fa fa-file-excel-o';
        break;
        case 'csv':
            $img = 'fa fa-file-text-o';
            break;
        case 'bak':
            $img = 'fa fa-clipboard';
            break;
        case 'doc': case 'docx':
        $img = 'fa fa-file-word-o';
        break;
        case 'ppt': case 'pptx':
        $img = 'fa fa-file-powerpoint-o';
        break;
        case 'ttf': case 'ttc': case 'otf': case 'woff':case 'woff2': case 'eot': case 'fon':
        $img = 'fa fa-font';
        break;
        case 'pdf':
            $img = 'fa fa-file-pdf-o';
            break;
        case 'psd': case 'ai': case 'eps': case 'fla': case 'swf':
        $img = 'fa fa-file-image-o';
        break;
        case 'exe': case 'msi':
        $img = 'fa fa-file-o';
        break;
        case 'bat':
            $img = 'fa fa-terminal';
            break;
        default:
            $img = 'fa fa-info-circle';
    }

    return $img;
}


function fm_get_image_exts()
{
    return array('ico', 'gif', 'jpg', 'jpeg', 'jpc', 'jp2', 'jpx', 'xbm', 'wbmp', 'png', 'bmp', 'tif', 'tiff', 'psd');
}


function fm_get_video_exts()
{
    return array('webm', 'mp4', 'm4v', 'ogm', 'ogv', 'mov');
}


function fm_get_audio_exts()
{
    return array('wav', 'mp3', 'ogg', 'm4a');
}


function fm_get_text_exts()
{
    return array(
        'txt', 'css', 'ini', 'conf', 'log', 'htaccess', 'passwd', 'ftpquota', 'sql', 'js', 'json', 'sh', 'config',
        'php', 'php4', 'php5', 'phps', 'phtml', 'htm', 'html', 'shtml', 'xhtml', 'xml', 'xsl', 'm3u', 'm3u8', 'pls', 'cue',
        'eml', 'msg', 'csv', 'bat', 'twig', 'tpl', 'md', 'gitignore', 'less', 'sass', 'scss', 'c', 'cpp', 'cs', 'py',
        'map', 'lock', 'dtd', 'svg',
    );
}


function fm_get_text_mimes()
{
    return array(
        'application/xml',
        'application/javascript',
        'application/x-javascript',
        'image/svg+xml',
        'message/rfc822',
    );
}


function fm_get_text_names()
{
    return array(
        'license',
        'readme',
        'authors',
        'contributors',
        'changelog',
    );
}


class FM_Zipper
{
    private $zip;

    public function __construct()
    {
        $this->zip = new ZipArchive();
    }

 
    public function create($filename, $files)
    {
        $res = $this->zip->open($filename, ZipArchive::CREATE);
        if ($res !== true) {
            return false;
        }
        if (is_array($files)) {
            foreach ($files as $f) {
                if (!$this->addFileOrDir($f)) {
                    $this->zip->close();
                    return false;
                }
            }
            $this->zip->close();
            return true;
        } else {
            if ($this->addFileOrDir($files)) {
                $this->zip->close();
                return true;
            }
            return false;
        }
    }


    public function unzip($filename, $path)
    {
        $res = $this->zip->open($filename);
        if ($res !== true) {
            return false;
        }
        if ($this->zip->extractTo($path)) {
            $this->zip->close();
            return true;
        }
        return false;
    }

    private function addFileOrDir($filename)
    {
        if (is_file($filename)) {
            return $this->zip->addFile($filename);
        } elseif (is_dir($filename)) {
            return $this->addDir($filename);
        }
        return false;
    }


    private function addDir($path)
    {
        if (!$this->zip->addEmptyDir($path)) {
            return false;
        }
        $objects = scandir($path);
        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($path . '/' . $file)) {
                        if (!$this->addDir($path . '/' . $file)) {
                            return false;
                        }
                    } elseif (is_file($path . '/' . $file)) {
                        if (!$this->zip->addFile($path . '/' . $file)) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }
}


function fm_show_nav_path($path)
{
    global $lang;
    ?>
    <nav class="navbar navbar-light bg-light navbar-expand-lg" style="background: linear-gradient(to left, #99ccff 0%, #ccffcc 100%);">

        <?php
        $path = fm_clean_path($path);
        $root_url = "<a href='?p='><i class='fa fa-home' aria-hidden='true' title='" . FM_ROOT_PATH . "'></i></a>";
        $sep = '<i class="fa fa-caret-right text-secondary"></i>';
        if ($path != '') {
            $exploded = explode('/', $path);
            $count = count($exploded);
            $array = array();
            $parent = '';
            for ($i = 0; $i < $count; $i++) {
                $parent = trim($parent . '/' . $exploded[$i], '/');
                $parent_enc = urlencode($parent);
                $array[] = "<a href='?p={$parent_enc}'>" . fm_enc(fm_convert_win($exploded[$i])) . "</a>";
            }
            $root_url .= $sep . implode($sep, $array);
        }
        echo '<div class="nav-item break-word float-left">' . $root_url . '</div>';
        ?>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
        <div class="navbar-nav float-rightd ml-auto">
            <?php if (!FM_READONLY): ?>
                <li class="nav-item"><a class="nav-link mx-1" title="Search" href="javascript:showSearch('<?php echo urlencode(FM_PATH) ?>')"><i class="fa fa-search fa-fw"></i> Search</a></li>
            <li class="nav-item"><a class="nav-link mx-1" title="Upload files" href="?p=<?php echo urlencode(FM_PATH) ?>&amp;upload"><i class="fa fa-cloud-upload fa-fw" aria-hidden="true"></i> Upload Files</a></li>
                <li class="nav-item"><a class="nav-link mx-1" title="New folder" href style="outline: none;" data-toggle="modal" data-target="#createNewItem"><i class="fa fa-plus-square fa-fw"></i> New Item</a></li>
                <li class="nav-item"><a href="?toggleTree=true" class="nav-link mx-1" title="Toggle Directories List"><i class="fa fa-eye-slash fa-fw"></i> Toggle Tree View</a></li>
            <?php endif; ?>
            <?php if (FM_USE_AUTH): ?><li class="nav-item"><a class="nav-link ml-1" title="Logout" href="?logout=1"><i class="fa fa-sign-out fa-fw" aria-hidden="true"></i> Log Out</a></li><?php endif; ?>
        </div>
        </div>
    </nav>
    <?php
}


function fm_show_message()
{
    if (isset($_SESSION['message'])) {
        $class = isset($_SESSION['status']) ? $_SESSION['status'] : 'alert-success';
        if($class == 'error'){
            $class = 'alert-danger';
        }
        if($class == 'alert'){
            $class = 'alert-info';
        }
        if($class == 'ok'){
            $class = 'alert-success';
        }

        echo '<p class="mt-2 mx-3 alert ' . $class . '">' . $_SESSION['message'] . '</p>';
        unset($_SESSION['message']);
        unset($_SESSION['status']);
    }
}


function fm_show_header_login()
{
$sprites_ver = '20160315';
header("Content-Type: text/html; charset=utf-8");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");

global $lang;
?>
    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>File Manager</title>
    <meta name="Description" CONTENT="Web Storage">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="<?php echo FM_SELF_URL ?>?img=favicon" type="image/png">
    <link rel="shortcut icon" href="<?php echo FM_SELF_URL ?>?img=favicon" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        html{
            max-height: 100% !important;
            height: 100%;
        }
    </style>
</head>
<body style="background: linear-gradient(to top left, #99ccff 0%, #ccffcc 100%);background-repeat: no-repeat;background-size: cover">


<div id="wrapper"">

    <?php
    }

    function fm_show_footer_login()
    {
    ?>
</div>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
<?php
}

/**
 * Show page header
 */
function fm_show_header()
{
$sprites_ver = '20160315';
header("Content-Type: text/html; charset=utf-8");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");

global $lang;
?>
    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>File Manager</title>
    <meta name="Description" CONTENT="Web Storage">
    <link rel="icon" href="<?php echo FM_SELF_URL ?>?img=favicon" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="<?php echo FM_SELF_URL ?>?img=favicon" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <?php if (isset($_GET['view']) && FM_USE_HIGHLIGHTJS): ?>
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/styles/<?php echo FM_HIGHLIGHTJS_STYLE ?>.min.css">
    <?php endif; ?>
    <style>
        a img, img {
            border: none
        }

        .filename, td, th {
            white-space: nowrap
        }

        .close, .close:focus, .close:hover, .php-file-tree a, a {
            text-decoration: none
        }

        body, code, div, em, form, html, img, label, li, ol, p, pre, small, span, strong, table, td, th, tr, ul {
            margin: 0;
            padding: 0;
            vertical-align: baseline;
            outline: 0;
            /*font-size: 100%;*/
            background: 0 0;
            border: none;
            text-decoration: none
        }

        p, table, ul {
            margin-bottom: 10px
        }

        html {
            overflow-y: scroll
        }

        body {
            padding: 0;
            /*font: 13px/16px Tahoma, Arial, sans-serif;*/
            color: #222;
            background: #F7F7F7;
            /*margin: 50px 30px 0*/
        }

        button, input, select, textarea {
            font-size: inherit;
            font-family: inherit
        }

        /*da {*/
            /*color: #296ea3*/
        /*}*/

        /*da:hover {*/
            /*color: #b00*/
        /*}*/

        img {
            vertical-align: middle
        }

        span {
            color: #777
        }

        small {
            font-size: 11px;
            color: #999
        }

        ul {
            list-style-type: none;
            margin-left: 0
        }

        ul li {
            padding: 3px 0
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%
        }

        .file-tree-view + #main-table {
            width: 75% !important;
            float: left
        }

        td, th {
            padding: 4px 7px;
            text-align: left;
            vertical-align: top;
            border: 1px solid #ddd;
            background: #fff
        }

        td.gray, th {
            background-color: #eee
        }

        td.gray span {
            color: #222
        }

        tr:hover td {
            background-color: #f5f5f5
        }

        tr:hover td.gray {
            background-color: #eee
        }

        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem
        }

        .table td, .table th {
            padding: .55rem;
            vertical-align: top;
            border-top: 1px solid #ddd
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #eceeef
        }

        .table tbody + tbody {
            border-top: 2px solid #eceeef
        }

        .table .table {
            background-color: #fff
        }

        code, pre {
            display: block;
            margin-bottom: 10px;
            font: 13px/16px Consolas, 'Courier New', Courier, monospace;
            border: 1px dashed #ccc;
            padding: 5px;
            overflow: auto
        }

        .hidden, .modal {
            display: none
        }

        .btn, .close {
            font-weight: 700
        }

        pre.with-hljs {
            padding: 0
        }

        pre.with-hljs code {
            margin: 0;
            border: 0;
            overflow: visible
        }

        code.maxheight, pre.maxheight {
            max-height: 512px
        }

        input[type=checkbox] {
            margin: 0;
            padding: 0
        }

        .message, .path {
            padding: 4px 7px;
            border: 1px solid #ddd;
            background-color: #fff
        }

        .fa.fa-caret-right {
            font-size: 1.2em;
            margin: 0 4px;
            vertical-align: middle;
            color: #ececec
        }

        .fa.fa-home {
            font-size: 1.2em;
            vertical-align: bottom
        }

        #wrappder {
            min-width: 400px;
            margin: 0 auto
        }

        .path {
            margin-bottom: 10px
        }

        .right {
            text-align: right
        }

        .center, .close, .login-form {
            text-align: center
        }

        .float-right {
            float: right
        }

        .float-left {
            float: left
        }

        .message.ok {
            border-color: green;
            color: green
        }

        .message.error {
            border-color: red;
            color: red
        }

        .message.alert {
            border-color: orange;
            color: orange
        }

        .btn2{
            color: #296ea3 !important;
            border-color: #296ea3 !important;
        }

        .btn2:hover{
            background-color: #296ea3 !important;
            color: white !important;
        }



        /*.btn:hover {*/
            /*color: #b00*/
        /*}*/

        .preview-img {
            max-width: 100%;
            background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAKklEQVR42mL5//8/Azbw+PFjrOJMDCSCUQ3EABZc4S0rKzsaSvTTABBgAMyfCMsY4B9iAAAAAElFTkSuQmCC)
        }

        .inline-actions > a > i {
            font-size: 1em;
            margin-left: 5px;
            background: #3785c1;
            color: #fff;
            padding: 3px;
            border-radius: 3px
        }

        .preview-video {
            position: relative;
            max-width: 100%;
            height: 0;
            padding-bottom: 62.5%;
            margin-bottom: 10px
        }

        .preview-video video {
            position: absolute;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            background: #000
        }

        .compact-table {
            border: 0;
            width: auto
        }

        .compact-table td, .compact-table th {
            width: 100px;
            border: 0;
            text-align: center
        }

        .compact-table tr:hover td {
            background-color: #fff
        }

        .filename {
            max-width: 420px;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .break-word {
            word-wrap: break-word;
        }

        .break-word.float-left a {
            color: #7d7d7d
        }

        .break-word + .float-right {
            padding-right: 30px;
            position: relative
        }

        .break-word + .float-right > a {
            color: #7d7d7d;
            font-size: 1.2em;
            margin-right: 4px
        }



        #editor, .edit-file-actions {
            position: absolute;
            right: 30px
        }



        .close:focus, .close:hover {
            color: #000;
            cursor: pointer
        }

        #editor {
            top: 230px;
            bottom: 5px;
            left: 10px;
            right: 10px;
        }

        .edit-file-actions {
            top: 0;
            background: #fff;
            margin-top: 5px
        }

        .edit-file-actions > a, .edit-file-actions > button {
            background: #fff;
            padding: 5px 15px;
            cursor: pointer;
            color: #296ea3;
            border: 1px solid #296ea3
        }

        .group-btn {
            background: #fff;
            padding: 2px 6px;
            border: 1px solid;
            cursor: pointer;
            color: #296ea3
        }

        .main-nav {
            position: fixed;
            top: 0;
            left: 0;
            padding: 10px 30px 10px 1px;
            width: 100%;
            background: #fff;
            color: #000;
            border: 0;
            box-shadow: 0 4px 5px 0 rgba(0, 0, 0, .14), 0 1px 10px 0 rgba(0, 0, 0, .12), 0 2px 4px -1px rgba(0, 0, 0, .2)
        }

        .login-form {
            width: 320px;
            margin: 0 auto;
            box-shadow: 0 8px 10px 1px rgba(0, 0, 0, .14), 0 3px 14px 2px rgba(0, 0, 0, .12), 0 5px 5px -3px rgba(0, 0, 0, .2)
        }

        .login-form label, .path.login-form input {
            padding: 8px;
            margin: 10px
        }

        .footer-links {
            background: 0 0;
            border: 0;
            clear: both
        }

        select[name=lang] {
            border: none;
            position: relative;
            text-transform: uppercase;
            left: -30%;
            top: 12px;
            color: silver
        }

        input[type=search] {
            height: 30px;
            margin: 5px;
            width: 80%;
            border: 1px solid #ccc
        }

        .path.login-form input[type=submit] {
            background-color: #4285f4;
            color: #fff;
            border: 1px solid;
            border-radius: 2px;
            font-weight: 700;
            cursor: pointer
        }

        .modalDialog {
            position: fixed;
            font-family: Arial, Helvetica, sans-serif;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: rgba(0, 0, 0, .8);
            z-index: 99999;
            opacity: 0;
            -webkit-transition: opacity .4s ease-in;
            -moz-transition: opacity .4s ease-in;
            transition: opacity .4s ease-in;
            pointer-events: none
        }

        .modalDialog:target {
            opacity: 1;
            pointer-events: auto
        }

        .modalDialog > .model-wrapper {
            max-width: 600px;
            position: relative;
            margin: 10% auto;
            padding: 15px;
            border-radius: 2px;
            background: #fff
        }

        .close {
            float: right;
            background: #fff;
            color: #000;
            line-height: 25px;
            position: absolute;
            right: 0;
            top: 0;
            width: 24px;
            border-radius: 0 5px 0 0;
            font-size: 18px
        }

        .close:hover {
            background: #e4e4e4
        }

        .modalDialog p {
            line-height: 30px
        }

        div#searchresultWrapper {
            max-height: 320px;
            overflow: auto
        }

        div#searchresultWrapper li {
            margin: 8px 0;
            list-style: none
        }

        li.file:before, li.folder:before {
            font: normal normal normal 14px/1 FontAwesome;
            content: "\f016";
            margin-right: 5px
        }

        li.folder:before {
            content: "\f114"
        }

        i.fa.fa-folder-o {
            color: #eeaf4b
        }

        i.fa.fa-picture-o {
            color: #26b99a
        }

        i.fa.fa-file-archive-o {
            color: #da7d7d
        }

        .footer-links i.fa.fa-file-archive-o {
            color: #296ea3
        }

        i.fa.fa-css3 {
            color: #f36fa0
        }

        i.fa.fa-file-code-o {
            color: #ec6630
        }

        i.fa.fa-code {
            color: #cc4b4c
        }

        i.fa.fa-file-text-o {
            color: #0096e6
        }

        i.fa.fa-html5 {
            color: #d75e72
        }

        i.fa.fa-file-excel-o {
            color: #09c55d
        }

        i.fa.fa-file-powerpoint-o {
            color: #f6712e
        }

        .file-tree-view {
            width: 24%;
            float: left;
            overflow: auto;
            border: 1px solid #ddd;
            border-right: 0;
            background: #fff
        }

        .file-tree-view .tree-title {
            background: #eee;
            padding: 9px 2px 9px 10px;
            font-weight: 700
        }

        .file-tree-view ul {
            margin-left: 15px;
            margin-bottom: 0
        }

        .file-tree-view i {
            padding-right: 3px
        }

        .php-file-tree {
            font-size: 100%;
            letter-spacing: 1px;
            line-height: 1.5;
            margin-left: 5px !important
        }

        .php-file-tree a {
            color: #296ea3
        }

        .php-file-tree A:hover {
            color: #b00
        }

        .php-file-tree .open {
            font-style: italic;
            color: #2183ce
        }

        .php-file-tree .closed {
            font-style: normal
        }

        #file-tree-view::-webkit-scrollbar {
            width: 10px;
            background-color: #F5F5F5
        }

        #file-tree-view::-webkit-scrollbar-track {
            border-radius: 10px;
            background: rgba(0, 0, 0, .1);
            border: 1px solid #ccc
        }

        #file-tree-view::-webkit-scrollbar-thumb {
            border-radius: 10px;
            background: linear-gradient(left, #fff, #e4e4e4);
            border: 1px solid #aaa
        }

        #file-tree-view::-webkit-scrollbar-thumb:hover {
            background: #fff
        }

        #file-tree-view::-webkit-scrollbar-thumb:active {
            background: linear-gradient(left, #22ADD4, #1E98BA)
        }
    </style>
</head>
<body style="background: white">
<nav class="navbar p-0 pl-2 navbar-light bg-light" style="background: linear-gradient(to left, #99ccff 0%, #ccffcc 100%);">
</nav>
<div id="wrapper">


    <div class="modal" tabindex="-1" role="dialog" id="createNewItem">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-plus-square fa-fw"></i> Create New Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>
                        <label for="newfile">Item Type &nbsp; : </label>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="customRadioInline1" name="newfile" value="file" class="custom-control-input">
                        <label class="custom-control-label" for="customRadioInline1">File</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="customRadioInline2" name="newfile" value="folder" class="custom-control-input" checked>
                        <label class="custom-control-label" for="customRadioInline2">Folder</label>
                    </div><br><br>

                        <label for="newfilename">Item Name : </label>
                        <input type="text" class="form-control" name="newfilename" id="newfilename" value="" placeholder="Enter Name">

                    </p>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="submit" value="Create Now" onclick="newfolder('<?php echo fm_enc(FM_PATH) ?>');return false;" class="btn btn-success"><i class="fa fa-plus-square fa-fw"></i> Create Now</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<!--    <div id="searchResult" class="modalDialog">-->
<!--        <div class="model-wrapper"><a href="#close" title="Close" class="close">X</a>-->
<!--            <input type="search" name="search" value="" placeholder="Find a item in current folder...">-->
<!--            <h2>Search Results</h2>-->
<!--            <div id="searchresultWrapper"></div>-->
<!--        </div>-->
<!--    </div>-->

    <div class="modal" tabindex="-1" role="dialog" id="searchResult">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-search fa-fw"></i> Search Files and Folders</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="model-wrappe">
                        <input type="search" name="search" value="" class="w-100 form-control" placeholder="Find a item in current folder...">
                        <div id="searchresultWrapper" class="mt-2 p-2"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    }


    function fm_show_footer()
    {
    ?>
</div>
<script>
    function newfolder(e) {
        var t = document.getElementById("newfilename").value,
            n = document.querySelector('input[name="newfile"]:checked').value;
        null !== t && "" !== t && n && (window.location.hash = "#", window.location.search = "p=" + encodeURIComponent(e) + "&new=" + encodeURIComponent(t) + "&type=" + encodeURIComponent(n))
    }

    function rename(e, t) {
        var n = prompt("New name", t);
        null !== n && "" !== n && n != t && (window.location.search = "p=" + encodeURIComponent(e) + "&ren=" + encodeURIComponent(t) + "&to=" + encodeURIComponent(n))
    }

    function change_checkboxes(e, t) {
        for (var n = e.length - 1; n >= 0; n--) e[n].checked = "boolean" == typeof t ? t : !e[n].checked
    }

    function get_checkboxes() {
        for (var e = document.getElementsByName("file[]"), t = [], n = e.length - 1; n >= 0; n--) (e[n].type = "checkbox") && t.push(e[n]);
        return t
    }

    function select_all() {
        change_checkboxes(get_checkboxes(), !0)
    }

    function unselect_all() {
        change_checkboxes(get_checkboxes(), !1)
    }

    function invert_all() {
        change_checkboxes(get_checkboxes())
    }

    function mailto(e, t) {
        var n = new XMLHttpRequest, a = "path=" + e + "&file=" + t + "&type=mail&ajax=true";
        n.open("POST", "", !0), n.setRequestHeader("Content-type", "application/x-www-form-urlencoded"), n.onreadystatechange = function () {
            4 == n.readyState && 200 == n.status && alert(n.responseText)
        }, n.send(a)
    }

    function showSearch(e) {
        var t = new XMLHttpRequest, n = "path=" + e + "&type=search&ajax=true";
        t.open("POST", "", !0), t.setRequestHeader("Content-type", "application/x-www-form-urlencoded"), t.onreadystatechange = function () {
            4 == t.readyState && 200 == t.status && (window.searchObj = t.responseText, document.getElementById("searchresultWrapper").innerHTML = "", window.location.hash = "#searchResult",$('#searchResult').modal('show'))
        }, t.send(n)
    }

    function getSearchResult(e, t) {
        var n = [], a = [];
        return e.forEach(function (e) {
            "folder" === e.type ? (getSearchResult(e.items, t), e.name.toLowerCase().match(t) && n.push(e)) : "file" === e.type && e.name.toLowerCase().match(t) && a.push(e)
        }), {folders: n, files: a}
    }

    function checkbox_toggle() {
        var e = get_checkboxes();
        e.push(this), change_checkboxes(e)
    }

    function backup(e, t) {
        var n = new XMLHttpRequest, a = "path=" + e + "&file=" + t + "&type=backup&ajax=true";
        return n.open("POST", "", !0), n.setRequestHeader("Content-type", "application/x-www-form-urlencoded"), n.onreadystatechange = function () {
            4 == n.readyState && 200 == n.status && alert(n.responseText)
        }, n.send(a), !1
    }

    function edit_save(e, t) {
        var n = "ace" == t ? editor.getSession().getValue() : document.getElementById("normal-editor").value;
        if (n) {
            var a = document.createElement("form");
            a.setAttribute("method", "POST"), a.setAttribute("action", "");
            var o = document.createElement("textarea");
            o.setAttribute("type", "textarea"), o.setAttribute("name", "savedata");
            var c = document.createTextNode(n);
            o.appendChild(c), a.appendChild(o), document.body.appendChild(a), a.submit()
        }
    }

    function init_php_file_tree() {
        if (document.getElementsByTagName) {
            for (var e = document.getElementsByTagName("LI"), t = 0; t < e.length; t++) {
                var n = e[t].className;
                if (n.indexOf("pft-directory") > -1) for (var a = e[t].childNodes, o = 0; o < a.length; o++) "A" == a[o].tagName && (a[o].onclick = function () {
                    for (var e = this.nextSibling; ;) {
                        if (null == e) return !1;
                        if ("UL" == e.tagName) {
                            var t = "none" == e.style.display;
                            return e.style.display = t ? "block" : "none", this.className = t ? "open" : "closed", !1
                        }
                        e = e.nextSibling
                    }
                    return !1
                }, a[o].className = n.indexOf("open") > -1 ? "open" : "closed"), "UL" == a[o].tagName && (a[o].style.display = n.indexOf("open") > -1 ? "block" : "none")
            }
            return !1
        }
    }

    var searchEl = document.querySelector("input[type=search]"), timeout = null;
    searchEl.onkeyup = function (e) {
        clearTimeout(timeout);
        var t = JSON.parse(window.searchObj), n = document.querySelector("input[type=search]").value;
        timeout = setTimeout(function () {
            if (n.length >= 2) {
                var e = getSearchResult(t, n), a = "", o = "";
                e.folders.forEach(function (e) {
                    a += '<li class="' + e.type + '"><a href="?p=' + e.path + '">' + e.name + "</a></li>"
                }), e.files.forEach(function (e) {
                    o += '<li class="' + e.type + '"><a href="?p=' + e.path + "&view=" + e.name + '">' + e.name + "</a></li>"
                }), document.getElementById("searchresultWrapper").innerHTML = '<div class="model-wrapper">'+a+o+"</div>"
            }
        }, 500)
    }, window.onload = init_php_file_tree;
    if (document.getElementById("file-tree-view")) {
        var tableViewHt = document.getElementById("main-table").offsetHeight - 2;
        document.getElementById("file-tree-view").setAttribute("style", "height:" + tableViewHt + "px")
    }
    ;

    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("/serviceworker.js").then(function(registration){
            console.log("Service Worker registered with scope:", registration);
        }).catch(function(err) {
            console.log("Service worker registration failed:", err);
        });
    }
</script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<?php if (isset($_GET['view']) && FM_USE_HIGHLIGHTJS): ?>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
<?php endif; ?>
<?php if (isset($_GET['edit']) && isset($_GET['env']) && FM_EDIT_FILE): ?>
    <script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.2.9/ace.js"></script>
    <script>var editor = ace.edit("editor");editor.getSession().setMode("ace/mode/javascript");</script>
<?php endif; ?>
</body>
</html>
<?php
}

/**
 * Show image
 * @param string $img
 */
function fm_show_image($img)
{
    $modified_time = gmdate('D, d M Y 00:00:00') . ' GMT';
    $expires_time = gmdate('D, d M Y 00:00:00', strtotime('+1 day')) . ' GMT';

    $img = trim($img);
    $images = fm_get_images();
    $image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAEElEQVR42mL4//8/A0CAAQAI/AL+26JNFgAAAABJRU5ErkJggg==';
    if (isset($images[$img])) {
        $image = $images[$img];
    }
    $image = base64_decode($image);
    if (function_exists('mb_strlen')) {
        $size = mb_strlen($image, '8bit');
    } else {
        $size = strlen($image);
    }

    if (function_exists('header_remove')) {
        header_remove('Cache-Control');
        header_remove('Pragma');
    } else {
        header('Cache-Control:');
        header('Pragma:');
    }

    header('Last-Modified: ' . $modified_time, true, 200);
    header('Expires: ' . $expires_time);
    header('Content-Length: ' . $size);
    header('Content-Type: image/png');
    echo $image;

    exit;
}

?>
