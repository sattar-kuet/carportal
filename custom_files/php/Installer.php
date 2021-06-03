<?php
$myfile = fopen('install_files/php/Installer.php', 'r');
$content = fread($myfile,filesize('install_files/php/Installer.php'));
fclose($myfile);
$content = str_replace("return $"."this->requestServerData('plugin/popular');",'$plugins = array();$custom_plugins = explode(",",constant("PLUGINS"));foreach($custom_plugins as $plugin){$plugins[] = $this->requestServerData("plugin/detail", array("name" => $plugin));}return $plugins;',$content);
$content = str_replace("return $"."this->requestServerData('theme/popular');",'$themes = array();$custom_themes = explode(",",constant("THEMES"));foreach($custom_themes as $theme){$themes[] = $this->requestServerData("theme/detail", array("name" => $theme));}return $themes;',$content);

$content = str_replace('demo','spotlayer',$content);
$content = str_replace('if ($tables > 0) {','$conn = new mysqli($this->post("db_host"), $this->post("db_user"), $this->post("db_pass"), $this->post("db_name"));if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}$sql = file_get_contents("db.sql");if($conn->multi_query($sql)){$database = file_get_contents("database.php");$fp = fopen("database.php", "w");fwrite($fp, str_replace(array("database_host","database_name","database_user","database_pass"),array($this->post("db_host"),$this->post("db_name"),$this->post("db_user"),$this->post("db_pass")),$database));fclose($fp);
rename("database.php", "config/database.php");rename("htaccessfile.txt", ".htaccess");};$conn->close();if ($tables > 100000) {',$content);
$content = str_replace("$"."this->buildConfigFile();","$"."this->rewriter->toFile("."$"."this->configDirectory . '/database.php', "."$"."this->getDatabaseConfigValues());"."$"."this->buildConfigFile();",$content);

eval('?'.'>' . $content);
