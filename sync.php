<?php

if (@$_REQUEST['operation'] == 'push') {
//	if ((($_FILES["file"]["type"] == "image/gif")
//		|| ($_FILES["file"]["type"] == "image/jpeg")
//		|| ($_FILES["file"]["type"] == "application/octet-stream"))
//		&& (true)
//	) {
		if ($_FILES["file"]["error"] > 0) {
			echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
		} else {
			echo "Upload: " . $_FILES["file"]["name"] . "<br />";
			echo "Type: " . $_FILES["file"]["type"] . "<br />";
			echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
			echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

			if (file_exists("upload/" . $_FILES["file"]["name"])) {
				echo $_FILES["file"]["name"] . " already exists. ";
			} else {
				move_uploaded_file($_FILES["file"]["tmp_name"],
					"./" . $_FILES["file"]["name"]);
				echo "Stored in: " . "./" . $_FILES["file"]["name"];
			}
		}
//	} else {
//		echo "Invalid file";
//	}
} elseif (@$_REQUEST['operation'] == 'unzip'){
	require_once 'zip.class.php';

	$unzip = new Unzip();

	$unzip->Extract('package.zip', '.');
	if($result==-1){
		echo "<br>文件 $upfile[name] 错误.<br>";
	}
	echo "<br>完成,共建立 $unzip->total_folders 个目录,$unzip->total_files 个文件.<br><br><br>";
}

?>