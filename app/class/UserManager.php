<?php
class UserManager
{
	public function getUsers () {
		try {
			$allRoom = Db::loadAll ("SELECT * FROM users");
			return $allRoom;
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public function getUser ($userName) {
		try {
			$user = Db::loadOne ("SELECT * FROM users WHERE username = ?", [$userName]);
			return $user;
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public function login ($username, $password, $rememberMe) {
		try {
			if ($user = Db::loadOne ('SELECT * FROM users WHERE LOWER(username)=LOWER(?)', array ($username))) {
				if ($user['password'] == UserManager::getHashPassword($password)) {
					if (isset($rememberMe) && $rememberMe == 'true') {
						setcookie ("rememberMe" . str_replace(".", "_", $_SERVER['HTTP_HOST']), $this->setEncryptedCookie($user['username']), time () + (30 * 24 * 60 * 60 * 1000), "/", $_SERVER['HTTP_HOST'], 1);
					}
					$_SESSION['user']['id'] = $user['user_id'];
					$page = "./index.php";
					if ($user["startPage"] == 1) {
						$page = "./dashboard.php";
					}
					unset($_POST['login']);
					return $page;
				} else {
					throw new PDOException("Heslo není správné!");
				}
			} else {
				throw new PDOException("Uživatel s tím to jménem neexistuje!");
			}
		} catch(PDOException $error) {
			echo $error->getMessage();
			die();
		}
	}

	public function isLogin () {
		if (isset ($_SESSION['user']) && isset($_SESSION['user']['id'])) {
			return true;
		} else {
			if (isset ($_COOKIE['rememberMe' . str_replace(".", "_", $_SERVER['HTTP_HOST'])])){
				if ($user = Db::loadOne ('SELECT * FROM users WHERE LOWER(username)=LOWER(?)', array ($this->getDecryptedCookie($_COOKIE['rememberMe' . str_replace(".", "_", $_SERVER['HTTP_HOST'])])))) {
					$_SESSION['user']['id'] = $user['user_id'];
					return true;
				}
			}
		}
		return false;
	}

	public function logout () {
		setcookie ("rememberMe" . str_replace(".", "_", $_SERVER['HTTP_HOST']),"", time() - (30 * 24 * 60 * 60 * 1000), "/", $_SERVER['HTTP_HOST'], 1);
		unset($_SESSION['user']);
		session_destroy();
	}

	public function setEncryptedCookie($value){
		$first_key = base64_decode(FIRSTKEY);
		$second_key = base64_decode(SECONDKEY);

		$method = "aes-256-cbc";
		$ivlen = openssl_cipher_iv_length($method);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$newvalue_raw = openssl_encrypt($value, $method, $first_key, OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $newvalue_raw, $second_key, TRUE);
		$newvalue = base64_encode ($iv.$hmac.$newvalue_raw);
		return $newvalue;
	}

	public function getDecryptedCookie($value){
		$first_key = base64_decode(FIRSTKEY);
		$second_key = base64_decode(SECONDKEY);

		$c = base64_decode($value);
		$method = "aes-256-cbc";
		$ivlen = openssl_cipher_iv_length($method);
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, 32);
		$newValue_raw = substr($c, $ivlen+32);
		$newValue = openssl_decrypt($newValue_raw, $method, $first_key, OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $newValue_raw, $second_key, TRUE);
		if (hash_equals ($hmac, $calcmac)) {
			return $newValue;
		}
		return false;
	}

	public static function getUserData ($type) {
		if (isset($_SESSION['user']['id'])) {
			$user = Db::loadOne ('SELECT ' . $type . ' FROM users WHERE user_id=?', array ($_SESSION['user']['id']));
			return $user[$type];
		}
		return "";
	}

	public function setUserData ($type, $value) {
		if (isset ($_SESSION['user']['id'])) {
			Db::command ('UPDATE users SET ' . $type . '=? WHERE user_id=?', array ($value, $_SESSION['user']['id']));
		}
	}

	public function getHashPassword ($password) {
		$salt = "s0mRIdlKvI";
		$hashPassword = hash('sha512', ($password . $salt));
		return $hashPassword;
	}

	public function ulozitObrazek ($file, $path = "", $name = "") {
		if (!@is_array (getimagesize($file['tmp_name']))) {
			throw new ChybaUzivatele("Formát obrázku ". $file['name'] ." není podporován!");
		} else {
			$extension = strtolower(strrchr($file['name'], '.'));
			switch ($extension) {
				case '.jpg':
				case '.jpeg':
				$img = @imagecreatefromjpeg($file['tmp_name']);
				break;
				case '.gif':
					$img = @imagecreatefromgif($file['tmp_name']);
					break;
					case '.png':
					$img2 = @imagecreatefrompng($file['tmp_name']);
					break;
					case '.ico':
					$img3 = @$file['tmp_name'];
					break;
					default:
					$img = false;
					break;
				}
				if($name == ""){
					$nazev = substr($file['name'], 0, strpos($file['name'], ".")) ."_". round(microtime(true) * 1000);
				}else{
					$nazev = $name;
				}
				if(!file_exists($path)){
					mkdir($path, 0777, true);
				}
				if (@$img) {
					if (!imagejpeg ($img, $path . $nazev .".jpg", 95)) {
						throw new ChybaUzivatele ("Obrázek neuložen!");
					}
					imagedestroy ($img);
				} else if (@$img2) {
					if (!imagepng ($img2, $path . $nazev .".jpg")) {
						throw new ChybaUzivatele ("Obrázek neuložen!");
					}
					imagedestroy ($img2);
				} else if (@$img3) {
					if (!copy($img3, $path . $nazev .'.ico')) {
						throw new ChybaUzivatele ("Obrázek neuložen!");
					}
				}
				return array('success' => true, 'url' => $path . $nazev .".jpg");
			}
		}

		public function atHome($userId, $atHome){
			try {
				Db::edit ('users', ['at_home' => $atHome], 'WHERE user_id = ?', array($userId));
			} catch(PDOException $error) {
				echo $error->getMessage();
				die();
			}
		}
	}
	?>
