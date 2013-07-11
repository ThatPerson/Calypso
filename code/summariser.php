<?php
class summariser {
	public $string = "";
	public $s_array = array();
// 	public $s_words = array(array());
	public $s_importance = array();
	public $response = "";
	function setString($strings) {
		$this->string = $strings;
	}
	function getString() {
		return $this->string;
	}
	function remove_brackets() {
		//$this->string = preg_replace("/[({].+[)}]/", "", $this->string);
		//return 1;
		$first = 0;
		$last = 0;
		$deleting = 0;
		$this->string = preg_replace("/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/", "", $this->string);
		for ($i = 0; $i < strlen($this->string); $i++) {
			if ($this->string[$i] == "(" || $this->string[$i] == "{") {
				$deleting++;
			}
			if ($this->string[$i] == ")" || $this->string[$i] == "}") {
				$deleting--;
				$this->string[$i] = "";
			}
			if ($deleting != 0) {
				$this->string[$i] = "";
			}
		}
		if (strtolower(substr($this->string, 0, strlen("#redirect"))) == "#redirect") {
		
			return $this->string;
		}
		$this->string = str_replace("}", "", $this->string);
		$this->string = str_replace(")", "", $this->string);
		$this->string = str_replace('\n', "<br>", $this->string);
		$l = explode("]]", $this->string);
		for ($i = 0; $i < count($l); $i++) {
			$l[$i] = preg_replace('/\[\[.+\|/', "", $l[$i]);
		}
		$this->string = implode($l);
		$this->string = str_replace("]]", "", $this->string);
		$this->string = str_replace("[[", "", $this->string);
		$this->string = str_replace("\\","",$this->string);
		
		return $this->string;
	}
	function s_split() {
		$this->s_array = preg_split("/(?<=[^\"][.?!][ ][^a-z])/",$this->string);
		for ($i = 0; $i < count($this->s_array); $i++) {
			$this->s_array[$i] = strip_tags($this->s_array[$i]);
			if (strpos($this->s_array[$i], "*") !== false) {
				//$this->s_array[$i] = "<ul>".$this->s_array[$i]."</ul";
				$lio = explode("*", $this->s_array[$i]);
				//$lio[1] = "<ul><li>".$lio[1]."</li>";
				for ($po = 1; $po < count($lio); $po++) {
					if ($po == 1) {
						$lio[$po] = "<ul><li>".$lio[$po]."</li>";
					} else {
						$lio[$po] = "<li>".$lio[$po]."</li>";
					}
				}
				$this->s_array[$i] = implode($lio);
				$this->s_array[$i] .= "</ul>";
			}
			if (strpos($this->s_array[$i], "Category:") !== false) {
				$this->s_array[$i] = preg_replace("/Category:[^.\<]+/", "", $this->s_array[$i]);
			}
		}
		
	}
	function summarise($sent) {
		$words = array();
		$sentleng = count($this->s_array);
		if ($sentleng > 100)
			$sentleng = 100;
		for ($i = 0; $i < $sentleng; $i++) {
			$this->s_importance[$i] = 0;
			$words[$i] = str_word_count($this->s_array[$i], 1);
			for ($p = 0; $p < count($words[$i]); $p++) {
				preg_replace("/[.!?]/", "", $words[$i][$p]);
			}
		}
		
		for ($i = 0; $i < $sentleng; $i++) {
			for ($p = 0; $p < $sentleng; $p++) {
				for ($li = 0; $li < count($words[$p]); $li++) {
				    
					if (strpos($this->s_array[$i], $words[$p][$li]) !== FALSE) {
						$this->s_importance[$i]++;
						
					}
				}
			}
		}
		if ($sent > count($this->s_array)) 
			$sent = count($this->s_array);
		//$temp = array();
		$tor = $this->s_array[0];
		$this->s_importance[0] = -2;
		for ($i = 1; $i < $sent; $i++) {
			$max = array_keys($this->s_importance, max($this->s_importance));
			if($this->s_importance[$max[0]] != -2)
				$tor .= "<br><br><br>".$this->s_array[$max[0]]." ";
			$this->s_importance[$max[0]] = -2;
		}
		$this->s_array = $temp;//$maxs = array_keys($array, max($array))*/
		$tor = preg_replace("/=.+=/", "", $tor);
		$tor = preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $tor);
		$this->response = $tor;
		return $tor;
	}
	function isRedirect() {
		if (strtolower(substr($this->string, 0, strlen("#redirect"))) == "#redirect") {
			$l = array();
			preg_match_all("/[[.+]]/", $this->string, $l);
			return $l[0];
		} else {
			return False;
		}
	}
}

class networkobject {
	public $url = "";
	public $headers = "Cappu, a Wikipedia summariser. ben_tatman@cappu.co.uk";
	public $response = "";
	function run() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $this->headers);
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
		//$this->response = curl_exec($ch);
		if (($l = curl_exec($ch)) === false) {
			curl_close($ch);
			return -1;
		} else {
			$this->response = curl_exec($ch);
		}
			
		curl_close($ch);
		return 1;
	}
	function splice($pre, $aft) {
		  
		$start = strpos($this->response, $pre);
		$end = strpos($this->response, $aft);

		$this->response = substr($this->response, $start+strlen($pre), $end-($start+strlen($pre)));
	}
}



?>
