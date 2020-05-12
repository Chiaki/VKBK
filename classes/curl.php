<?php
// Curl Class for VK

class cu{
	
	var $ch;
	
    function __construct(){
		return true;
    }

	public function curl_on(){
		$this->ch = curl_init("");
	}

	public function curl_off(){
		if($this->ch){
			curl_close($this->ch);
		}
	}

	function curl_req($data){
		curl_setopt($this->ch,CURLOPT_URL,$data['uri']);
		// HTTPS
		if(substr($data['uri'],0,5)=="https"){
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		if($data['method']=='post'){
			curl_setopt($this->ch, CURLOPT_POST, true);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data['post_data']);
		}
		// options
		if($data['return']==1){
			// return web page
			curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
		} else {
			// direct output
			curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,0);
		}
		// don't return headers
		curl_setopt($this->ch,CURLOPT_HEADER,0);
		// follow redirects
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,0);
		// handle all encodings
		curl_setopt($this->ch,CURLOPT_ENCODING,"");
		// who am i
		curl_setopt($this->ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36");
		// set referer on redirect
		curl_setopt($this->ch,CURLOPT_AUTOREFERER,0);
		// timeout on connect
		curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,0);
		// timeout on response
		curl_setopt($this->ch,CURLOPT_TIMEOUT,0);
		// stop after 10 redirects
		curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);

		$out['header'] = curl_getinfo($this->ch);
		$out['err'] = curl_errno($this->ch);
		$out['errmsg'] = curl_error($this->ch);
		if($data['return'] == 1){
			$out['content'] = curl_exec($this->ch);
		}

		return $out;
	}

	public function critError($msg){
		print $msg;
		curl_off();
		exit;
	}

	public function file_save($opts,$data){
		if(!is_dir($opts['path'])){
			if($this->winlin() === false){
				mkdir($opts['path'],null,true);
			} else {
				mkdir($opts['path'],0755,true);
			}
		}
		if(!is_writable($opts['path'])){
			return false;
		} else {
			$handle = fopen($opts['path'].$opts['name'], "w+");
			// Write $somecontent to our opened file.
			if (fwrite($handle,$data) === FALSE) {
				fclose($handle);
				return false;
			} else {
				fclose($handle);
				// File saved? Check the file size.
				if(filesize($opts['path'].$opts['name']) > 1){
					return true;
				} else {
					// File 0 bytes? Delete!
					@unlink($opts['path'].$opts['name']);
					return false;
				}
			}
		}
	} // token_save end
	
	public function clean_name($name){
		// Check for `bad characters` in windows filenames
		$bad = array_merge(
        array_map('chr', range(0,31)),
        array("<", ">", ":", '"', "/", "\\", "|", "?", "*"));
		$name = str_replace($bad, "", $name);

		// Clean UTF-8 name
		// We left only letters, numbers & punctuation
		// ref http://www.php.net/manual/en/regexp.reference.unicode.php
		$name = preg_replace('/[^\p{L}\p{Nd}\p{P}\s]/u', '', $name);
		// HEX clean
		// ref http://www.utf8-chartable.de/unicode-utf8-table.pl?utf8=oct&unicodeinhtml=dec&htmlent=1
		$name = preg_replace('/[\xA1-\xBF\x23]/u', '', $name);
		// UTF-8 Trim
		$name = preg_replace("/^[\p{Z}\p{C}]+|[\p{Z}\p{C}]+$/u","",$name);
		
		return $name;
	}
	
	public function win_name($name){
		// Fow Windows encode UTF8 symbols to CP1251
		// ref http://stackoverflow.com/questions/23058449/save-filename-with-unicode-chars
		// ref http://stackoverflow.com/questions/9659600/glob-cant-find-file-names-with-multibyte-characters-on-windows
		// ref https://github.com/jbroadway/urlify
		$converted = false;
		if ($this->winlin() === false) {
			if(mb_detect_encoding($name, 'UTF-8', true)){
				$name = iconv("UTF-8","CP1251//IGNORE",$name);
				$converted = true;
			}
		}
		
		return array($converted,$name);
	}
	
	/*
	   WinLin
	   Returns OS type
	   IN:
	   OUT: false - windows, true - linux
	*/
	public function winlin(){
		$osu = strtoupper(substr(php_uname('s'), 0, 3));
		if($osu === 'WIN'){
			return false;
		} else {
			return true;
		}
	}

}

?>