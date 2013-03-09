<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 13-3-7
 * Time: 下午6:13
 * To change this template use File | Settings | File Templates.
 */


class File {
	/****************************   高层接口 *********************************/
	//读取目录列表
	static function show_dir($path, &$dir, &$file, $deep = 0) {
		if (substr($path, -1) != '/') {
			$path .= '/';
		}
		if (is_dir($path)) {
			self::myList($path, $dir, $file, $deep);
			sort($dir);
			sort($file);
			for ($i = 0; $i < count($dir); $i++) {
				$dir[$i] = str_replace($path, '', $dir[$i]);
			}
			for ($i = 0; $i < count($file); $i++) {
				$file[$i] = str_replace($path, '', $file[$i]);
			}
		}
	}

	//修改文件访问权限
	static function chmod($path, $chmod, &$info, &$err) {
		$dirs = $files = $err['dirs'] = $err['files'] = array();
		$info['dirs'] = $info['files'] = 0;
		if (!is_dir($path)) {
			return false;
		}
		self::myList($path, $dirs, $files, -1);
		foreach ($files as $file) {
			if (self::setChmod($file, $chmod)) {
				$info['files']++;
			} else {
				$err['files'][] = $file;
			}
		}
		foreach ($dirs as $dir) {
			if (self::setChmod($dir, $chmod)) {
				$info['dirs']++;
			} else {
				$err['dirs'][] = $f;
			}
		}
		if (self::setChmod($path, $chmod)) {
			$info['dirs']++;
		}
		return $info['dirs'];
	}

	//删除文件夹及其子目录
	static function del_dir($path, &$info, &$err) {
		$dirs = $files = $err['dirs'] = $err['files'] = array();
		$info['dirs'] = $info['files'] = 0;
		if (!is_dir($path)) {
			return false;
		}
		self::myList($path, $dirs, $files, -1);
		rsort($dirs);
		rsort($files);
		foreach ($files as $file) {
			if (unlink($file)) {
				$info['files']++;
			} else {
				$err['files'][] = $file;
			}
		}
		foreach ($dirs as $dir) {
			if (rmdir($dir)) {
				$info['dirs']++;
			} else {
				$err['dirs'][] = $dir;
			}
		}
		if (rmdir($path)) {
			$info['dirs']++;
		}
		return $info['dirs'];
	}

	static function rename($from, $to) {
		rename($from, $to);
	}

	//移动复制目录及其子目录
	static function copy($from, $to, $cover = false, $cut = false, &$coverfiles, &$info) {
		$info['dirs'] = $info['files'] = $info['size'] = 0;
		if (is_array($from) && is_array($to)) {
			if (count($from) != count($to)) {
				return false;
			}
			$all = true;
			for ($i = 0; $i < count($from); $i++) {
				if (!file_exists($from[$i])) {
					continue;
				}
				self::move0($from[$i], $to[$i], $cover, $cut, $coverfiles, $info) or $all = false;
			}
			return $all;
		} else {
			return false;
		}
	}


	//文件、目录复制分流
	static function move0($from, $to, $cover, $cut, &$coverfiles, &$info) {
		//self::setChmod($from,0755);
		if (is_dir($from)) {
			return self::move1($from, $to, $cover, $cut, $coverfiles, $info);
		} elseif (is_file($from)) {
			return self::move2($from, $to, $cover, $cut, $coverfiles, $info);
		} else {
			return false;
		}
	}

	//移动目录
	static function move1($from, $to, $cover, $cut, &$coverfiles, &$info) {
		if (!is_dir($to)) {
			mk_dir($to, 0755);
			$info['dirs']++;
		}
		$dirs = $files = array();
		self::myList($from, $dirs, $files, 0);
		foreach ($dirs as $dir) {
			self::move1($dir . '/', str_replace($from, $to, $dir) . '/', $cover, $cut, $coverfiles, $info);
		}
		foreach ($files as $file) {
			self::move2($file, str_replace($from, $to, $file), $cover, $cut, $coverfiles, $info);
		}
		//递归返回后删除此目录
		if ($cut && rmdir($from)) {
		}
		return true;
	}

	//移动文件
	static function move2($from, $to, $cover, $cut, &$coverfiles, &$info) {
		if (file_exists($from)) {
			$info['size'] += filesize($from);
			if (file_exists($to) && false == $cover) {
				$coverfiles[] = $to;
			} elseif (file_exists($to) && $cover) {
				$cut && unlink($to);
				if (self::move3($from, $to, $cut)) {
					$info['files']++;
				}
			} else {
				if (self::move3($from, $to, $cut)) {
					$info['files']++;
				}
			}
		}
	}

	static function move3($from, $to, $cut) {
		return ($cut) ? rename($from, $to) : copy($from, $to);
	}

	//获取目录详细属性
	static function getProperty($path) {
		$dirs = $files = $info = array();
		self::myList($path, $dirs, $files, -1);
		$info['dir'] = count($dirs);
		$info['file'] = count($files);
		$info['writable'] = is_writable($path);
		$info['readable'] = is_readable($path);
		$info['chmod'] = substr(sprintf('%o', @fileperms($path)), -4);
		$info['size'] = 0;
		foreach ($files as $f) {
			$info['size'] += filesize($f);
		}
		return $info;
	}


	/****************************   底层接口 *********************************/
	//循环列出文件及目录
	static function myList($path, &$dir, &$file, $deepest = -1, $deep = 0) {
		if (true === is_readable($path) && false !== $handle = opendir($path)) {
			while (false !== ($val = readdir($handle))) {
				if ($val == '.' || $val == '..') {
					continue;
				}
				$value = strval($path . $val);
				if (is_file($value)) {
					$file[] = $value;
				} elseif (is_dir($value)) {
					$dir[] = $value;
					if ($deep < $deepest || $deepest == -1) {
						self::myList($value . '/', $dir, $file, $deepest, $deep + 1);
					}
				}
			}
			closedir($handle);
		}
	}

	//获取扩展名
	static function getExt($filename) {
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}

	//获取目录及文件权限
	static function getPerm($path) {
		return substr(sprintf('%o', fileperms($path)), -4);
	}

	//修改文件权限
	static function setChmod($file, $mode = 0755) {
		if (file_exists($file)) {
			return chmod($file, $mode);
		} else {
			return false;
		}
	}


}