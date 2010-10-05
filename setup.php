<?php

$version = "2.0.2";

# require the YAML loader
require("lib/spyc-0.4.5/spyc.php");

# functions to DRY out the setup script
function copy_gen_config($gen_config) {
	$install_path = $_ENV["PWD"];
	$data = file_get_contents("config.gen.copy.inc.php");
	$data = str_replace("/path/to/install/", $install_path."/", $data);
	$fp = fopen($gen_config, "w");
	fwrite($fp, $data);
	fclose($fp);
	system("chmod 664 ".$gen_config);
	echo("Default configuration in ".$gen_config." copied...\n");
}

function copy_db($sqlite_db) {
	system("cp db/development.copy.sqlite3 ".$sqlite_db);
	system("chmod 664 ".$sqlite_db);
	echo("Default SQLite database copied...\n");
}

function copy_section($dir,$filepath,$name) {
	system("cp web/".$dir."/data/data.copy.inc.php ".$filepath);
	system("chmod 664 ".$filepath);
	echo($name.": initial data loaded...\n");
}

# start the general install
echo("Starting install of Mobile Web OSP v".$version."...\n");

# copy the general config file
echo("Setting up the general config...\n");
$gen_config = "config.gen.inc.php";
if (file_exists($gen_config)) {
	echo($gen_config." already exists. Overwrite it? Y/n \n");
	$handle = fopen("php://stdin","r");
	$line = fgets($handle);
	if (strtolower(trim($line)) != "y") {
		echo("Skipping ".$gen_config."...\n");
	} else {
		copy_gen_config($gen_config);
	}
} else {
	copy_gen_config($gen_config);
}		

# copy the SQLite database assuming a user wants to use it
echo("Are you going to use SQLite for the database? Y/n \n");
$handle = fopen("php://stdin","r");
$line = fgets($handle);
if (strtolower(trim($line)) == 'y') {
	$sqlite_db = "db/development.sqlite3";
	if (file_exists($sqlite_db)) {
		echo($sqlite_db." already exists. Overwrite it? Y/n \n");
		$handle = fopen("php://stdin","r");
		$line = fgets($handle);
		if (strtolower(trim($line)) != 'y') {
			echo("Skipping ".$sqlite_db."...\n");
		} else {
			copy_db($sqlite_db);
		}
	} else {
		copy_db($sqlite_db);
	}
} else {
	echo("You will need to manually update config.gen.inc.php to support MySQL...\n");
}

# set-up individual sections based on availability of setup.yml files
echo("Setting up individual sections...\n");
$messages = array();
$base_dir = $_ENV["PWD"]."/web/";
$files = scandir($base_dir);
foreach ($files as $file) {
	if (is_dir($base_dir.$file)) {
		$config_file = $base_dir.$file."/setup.yml";
		if (file_exists($config_file)) {
			$config = Spyc::YAMLLoad($config_file);
			if ($config["data"] == true) {
				$filepath =  $base_dir.$file."/data/data.inc.php";
				if (file_exists($filepath)) {
					echo($config["name"].": data file already exists. Overwrite it? Y/n \n");
					$handle = fopen("php://stdin","r");
					$line = fgets($handle);
					if(strtolower(trim($line)) != 'y') {
						echo($config['name'].": skipping data file...\n");
					} else {
						copy_section($file,$filepath,$config["name"]);
					}
				} else {
					copy_section($file,$filepath,$config["name"]);
				}
			}
			$messages = $config["messages"]; $i = 0;
			while ($i < count($messages)) {
				$messages[] = $config["name"].": ".$messages[$i];
				$i++;
			}
		}
	}
}

echo("Some things you should look at after installing Mobile Web OSP: \n");
foreach ($messages as $message) {
	echo(" - ".$message."\n");
}

echo("Installation complete!\n");

?>