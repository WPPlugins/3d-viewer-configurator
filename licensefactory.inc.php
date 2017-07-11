<?php
/*
=============================================================
Author: ProNego, http://www.pronego.com,
Contact: Manuel Lamotte-Schubert (mls@pronego.com)

Based on a script from Payam khaninajad (Progvig@yahoo.com).
=============================================================
*/
class License_factory
{
	private $sitestring = "visualtektur";
	
    /**
     * This is used to generate a new serial key.
     * If further serial parts are necessary, 
     * e.g. domain information, construct the parts
     * in this function like <domain_str>-<num_str>
     * using the function generate_partial_key()
     * @param $user_id The username to generate a key for.
     */
	function generate_licensekey($user_id) 
	{
		$site_str = $this->generate_partial_key($this->sitestring);
		$user_str = $this->generate_partial_key($user_id);
		
		$fullkey = md5($site_str."-".$user_str);
		
		return $fullkey;
	}
    
    /**
     * This is the main function of the license process
     * you can encode your page with base64 function 
     * php.net/manual/en/function.base64-encode.php
     * php.net/manual/en/function.base64-decode.php
     * @param $user_id The username to check the key for.
     * @param $key_to_validate License key that needs to be validated.
     * @return TRUE if 
     */
	function validate_licensekey($user_id, $key_to_validate) 
	{
		// Generate key for comparison from given user_id
		$stored_key = $this->generate_licensekey($user_id);

		if ((strcmp($stored_key, $key_to_validate)) == 0)
			return TRUE;
		else 
			return FALSE;
	}
    
    /**
     * Generate partial serial keys.
     */
	function generate_partial_key($string)
	{
		$ascii = NULL;
		$serial = NULL;
		$secret_num = 1;
		for ($i = 0; $i < strlen($string); $i++)
		{
			$ascii .= $secret_num+ ord($string[$i]);
		}
		$ascii = substr($ascii,0,20);
		for ($i = 0; $i < strlen($ascii); $i+=2)
		{
			$string = substr($ascii,$i,2);
			switch($string) 
			{
				case $string>122:
					$string-=40;
				break;
				case $string<=48:
					$string+=40;
				break;
			}
			$serial .= chr($string);
		}	
		return $serial;
	}
}
?>