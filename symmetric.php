<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

class Encryption
{
	const CIPHER = MCRYPT_RIJNDAEL_128; // Rijndael-128 is AES
	const MODE   = MCRYPT_MODE_CBC;

	/* Cryptographic key of length 16, 24 or 32. NOT a password! */
	private $key;
	public function __construct($key) {
		$this->key = $key;
	}

	public function encrypt($plaintext) {
		$ivSize = mcrypt_get_iv_size(self::CIPHER, self::MODE);
		$iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_URANDOM);
		$ciphertext = mcrypt_encrypt(self::CIPHER, $this->key, $plaintext, self::MODE, $iv);
		return base64_encode($iv.$ciphertext);
	}

	public function decrypt($ciphertext) {
		$ciphertext = base64_decode($ciphertext);
		$ivSize = mcrypt_get_iv_size(self::CIPHER, self::MODE);
		if (strlen($ciphertext) < $ivSize) {
			throw new Exception('Missing initialization vector');
		}

		$iv = substr($ciphertext, 0, $ivSize);
		$ciphertext = substr($ciphertext, $ivSize);
		$plaintext = mcrypt_decrypt(self::CIPHER, $this->key, $ciphertext, self::MODE, $iv);
		return rtrim($plaintext, "\0");
	}
}









$init = 0;
$how_many_per_block = 1024; //chars; //every block will be a line
$key = 'my password';

$file_to_encrypt = 'file_to_encrypt_big.txt';;
$encrypted_file='file_to_encrypt_encrypted.txt';
$encrypted_decrypted_file = 'file_to_encrypt_encrypted_decrypted.txt';


// LET's encrypt

$data = file_get_contents($file_to_encrypt);;
if(!$data) die('no data');

if(!file_exists($file_to_encrypt)) die('no file to encrypt');
if(file_exists($encrypted_file)) unlink($encrypted_file);

$final_encrypted_string ="";
$total = strlen($data);
$crypt = new Encryption($key);
do{
	$partial_encrypted="";
	$last_one = false;
	if($init+$how_many_per_block>$total){
		$how_many_per_block = $total-$init;
		$last_one = true;
	} 	
	$partial_string = substr($data,$init,$how_many_per_block);	
	$partial_encrypted = $crypt->encrypt(base64_encode($partial_string));
	file_put_contents($encrypted_file, ($partial_encrypted.PHP_EOL),FILE_APPEND);	
	$final_encrypted_string.=$partial_encrypted.'\n';
	$init+=$how_many_per_block;
}while(!$last_one);





//Lets decrypt


if(file_exists($encrypted_decrypted_file)) unlink($encrypted_decrypted_file);
if(!file_exists($encrypted_file)) die('No file to decrypt');
$file = fopen($encrypted_file, "r");
if ($file) {
	while(!feof($file)){
		$line = fgets($file);		
		if(!$line) continue;
		$crypt = new Encryption($key);
		$decrypted = $crypt->decrypt($line);
		$decrypted = base64_decode($decrypted);
		file_put_contents($encrypted_decrypted_file, $decrypted,FILE_APPEND);

	}

	fclose($file);
} else {
	// error opening the file.
} 

