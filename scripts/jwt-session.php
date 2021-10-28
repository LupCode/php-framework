<?php

    define('JWT_SUPPORTED_ALGORITHMS', array(
        'ES384' => array('openssl', 'SHA384'),
        'ES256' => array('openssl', 'SHA256'),
        'HS256' => array('hash_hmac', 'SHA256'),
        'HS384' => array('hash_hmac', 'SHA384'),
        'HS512' => array('hash_hmac', 'SHA512'),
        'RS256' => array('openssl', 'SHA256'),
        'RS384' => array('openssl', 'SHA384'),
        'RS512' => array('openssl', 'SHA512'),
        'EdDSA' => array('sodium_crypto', 'EdDSA'),
    ));

    define('JWT_ASN1_SEQUENCE', 0x10);
    define('JWT_ASN1_INTEGER', 0x02);
    define('JWT_ASN1_BIT_STRING', 0x03);

    function _jwt_decode($base64, $parseJson=true){
        $add = strlen($base64) % 4;
        if($add != 0) $base64 .= str_repeat("=", 4 - $add);
        $base64 = base64_decode(str_replace(array("-", "_"), array("+", "/"), $base64));
        return $parseJson ? json_decode($base64, false, 512, JSON_BIGINT_AS_STRING) : $base64;
    }

    function _jwt_encode($str, $isJsonObj=true){
        if($isJsonObj){
            $str = json_encode($str);
            if(json_last_error() != JSON_ERROR_NONE) throw new InvalidArgumentException(json_last_error_msg());
        }
        return str_replace(array("=", "+", "/"), array("", "-", "_", ), base64_encode($str));
    }

    function _jwt_read_der($der, $offset=0){
        $pos = $offset;
        $size = strlen($der);
        $constructed = (ord($der[$pos]) >> 5) & 0x01;
        $type = ord($der[$pos++]) & 0x1f;
        $len = ord($der[$pos++]);
        if($len & 0x80){
            $n = $len & 0x1f;
            $len = 0;
            while($n-- && $pos < $size){
                $len = ($len << 8) | ord($der[$pos++]);
            }
        }
        if($type == JWT_ASN1_BIT_STRING){
            $pos++; // Skip the first contents octet (padding indicator)
            $data = substr($der, $pos, $len - 1);
            $pos += $len - 1;
        } else if(!$constructed){
            $data = substr($der, $pos, $len);
            $pos += $len;
        } else $data = null;

        return array($pos, $data);
    }

    function _jwt_encode_der($type, $value){
        return chr($type | ($type === JWT_ASN1_SEQUENCE ? 0x20 : 0)) . chr(strlen($value));
    }

    function _jwt_decode_der($sig, $keySize){
        // OpenSSL returns the ECDSA signatures as a binary ASN.1 DER SEQUENCE
        list($off, $_) = _jwt_read_der($sig);
        list($off, $r) = _jwt_read_der($der, $off);
        list($off, $s) = _jwt_read_der($der, $off);

        // Convert r-value and s-value from signed two's compliment to unsigned big-endian integers
        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        // Pad out r and s so that they are $keySize bits long
        return str_pad($r, $keySize / 8, "\x00", STR_PAD_LEFT) . str_pad($s, $keySize / 8, "\x00", STR_PAD_LEFT);
    }

    function _jwt_strlen($str){
        return function_exists('mb_strlen') ? mb_strlen($str, '8bit') : strlen($str);
    }


    /** Verifies the integrity of a given message and signature
     * @param String $msg Message that should be verified
     * @param String $sig Signature of the message needed to prove the integrity
     * @param String $secretKey
     */
    function jwt_verify($msg, $sig, $secretKey=null, $alg='HS256', $algorithms=null){
        if(empty($secretKey)){
            if(!isset($_ENV['JWT_SECRET_KEY']) || empty($_ENV['JWT_SECRET_KEY']))
                throw new InvalidArgumentException("Secret key cannot be null if \$_ENV['JWT_SECRET_KEY'] is not defined");
            $secretKey = $_ENV['JWT_SECRET_KEY'];
        }
        $algorithms = !empty($algorithms) ? (is_array($algorithms) ? $algorithms : array($algorithms)) : JWT_SUPPORTED_ALGORITHMS;
        if(empty($alg) || !isset($algorithms[$alg])) return false;

        list($func, $alg) = $algorithms[$alg];
        switch ($func) {
            case 'openssl':
                $success = openssl_verify($msg, $sig, $secretKey, $alg);
                if($success === 1) return true;
                else if($success === 0) return false;
                else throw new ErrorException(openssl_error_string());
            case 'sodium_crypto':
              if(!function_exists('sodium_crypto_sign_verify_detached')) throw new ErrorException('libsodium is not available');
              try {
                  // The last non-empty line is used as the key.
                  $lines = array_filter(explode("\n", $secretKey));
                  $key = base64_decode(end($lines));
                  return sodium_crypto_sign_verify_detached($sig, $msg, $secretKey);
              } catch (Exception $e) {
                  throw new ErrorException($e->getMessage(), 0, $e);
              }
            case 'hash_hmac':
            default:
                $hash = hash_hmac($alg, $msg, $secretKey, true);
                if (function_exists('hash_equals')) return hash_equals($sig, $hash);
                $sigLen = _jwt_strlen($sig);
                $hashLen = _jwt_strlen($hash);
                $len = min($sigLen, $hashLen);
                $status = 0;
                for($i=0; $i < $len; $i++) $status |= (ord($sig[$i]) ^ ord($hash[$i]));
                $status |= ($sigLen ^ $hashLen);
                return ($status === 0);
        }
    }


    /** 
     * Decodes a JWT token
     * @param String $jwt JWT token that should be parsed
     * @param String $secretKey Private key to verify integrity of JWT (if null then $_ENV['JWT_SECRET_KEY'])
     * @param Int $currentTime Current UTC time seconds (optional, can be used for unit tests)
     * @param Array $algorithms Map of allowed algorithms (optional, if null or empty then JWT_SUPPORTED_ALGORITHMS will be used)
     * @return Object JSON object that was stored in the JWT or false if JWT invalid or expired
     */
    function jwt_decode($jwt, $secretKey=null, $currentTime=null, $algorithms=null){
        if(empty($secretKey)){
            if(!isset($_ENV['JWT_SECRET_KEY']) || empty($_ENV['JWT_SECRET_KEY']))
                throw new InvalidArgumentException("Secret key cannot be null if \$_ENV['JWT_SECRET_KEY'] is not defined");
            $secretKey = $_ENV['JWT_SECRET_KEY'];
        }
        $currentTime = $currentTime ? $currentTime : time();
        $algorithms = !empty($algorithms) ? (is_array($algorithms) ? $algorithms : array($algorithms)) : JWT_SUPPORTED_ALGORITHMS;

        $parts = explode(".", $jwt);
        if(count($parts) != 3) return array();

        list($head64, $payload64, $sig64) = $parts;
        if(!($header = _jwt_decode($head64)) || !($payload = _jwt_decode($payload64)) || !($sig = _jwt_decode($sig64, false)))
            return array();

        if(empty($header->alg) || !isset($algorithms[$header->alg]))
            return array();
        
        if ($header->alg === 'ES256' || $header->alg === 'ES384') {
            // OpenSSL expects an ASN.1 DER sequence for ES256/ES384 signatures
            list($r, $s) = str_split($sig, strlen($sig)/2);
            $r = ltrim($r, "\x00");
            $s = ltrim($s, "\x00");
            $r = _jwt_encode_der(JWT_ASN1_INTEGER, ord[$r[0]] > 0x7f ? "\x00".$r : $r);
            $s = _jwt_encode_der(JWT_ASN1_INTEGER, ord[$s[0]] > 0x7f ? "\x00".$s : $s);
            $sig = _jwt_encode_der(JWT_ASN1_SEQUENCE, $r.$s);
        }

        // Check the signature
        if(!jwt_verify($head64.".".$payload64, $sig, $secretKey, $header->alg)) return array();

        // Check if token can already be used (if set)
        $leeway = isset($_ENV['JWT_LEEWAY_SEC']) ? intval($_ENV['JWT_LEEWAY_SEC']) : 0;
        if(isset($payload->nbf) && $payload->nbf > ($timestamp + $leeway)) return false;

        // Check that token has been created before 'now'
        if(isset($payload->iat) && $payload->iat > ($timestamp + $leeway)) return false;

        // Check if this token has expired.
        if(isset($payload->exp) && ($timestamp - $leeway) >= $payload->exp) return false;

        return $payload;
    }



    /** Creates a signature for a given string
     * @param String $msg Message the signature should be generated for
     * @param String $secretKey Private key to sign message (if null then $_ENV['JWT_SECRET_KEY'])
     * @param String $alg Algorithm that should be used to sign the string
     * @return String Signed message
     */
    function jwt_sign($msg, $secretKey=null, $alg='HS256'){
        if(empty($secretKey)){
            if(!isset($_ENV['JWT_SECRET_KEY']) || empty($_ENV['JWT_SECRET_KEY']))
                throw new InvalidArgumentException("Secret key cannot be null if \$_ENV['JWT_SECRET_KEY'] is not defined");
            $secretKey = $_ENV['JWT_SECRET_KEY'];
        }
        if(empty($alg) || !isset(JWT_SUPPORTED_ALGORITHMS[$alg])) throw new InvalidArgumentException('Algorithm not supported');
        list($func, $alg) = JWT_SUPPORTED_ALGORITHMS[$alg];
        switch ($func) {
            case 'openssl':
                $sig = '';
                $success = openssl_sign($msg, $signature, $secretKey, $alg);
                if(!$success) throw new ErrorException("OpenSSL unable to sign data");
                if($alg === 'ES256')
                    $sig = _jwt_decode_der($signature, 256);
                else if($alg === 'ES384')
                    $sig = _jwt_decode_der($signature, 384);
                return $sig;
            case 'sodium_crypto':
                if(!function_exists('sodium_crypto_sign_detached')) throw new ErrorException('libsodium is not available');
                try {
                    // The last non-empty line is used as the key.
                    $lines = array_filter(explode("\n", $secretKey));
                    $key = base64_decode(end($lines));
                    return sodium_crypto_sign_detached($msg, $ksecretKeyey);
                } catch (Exception $e) {
                    throw new ErrorException($e->getMessage(), 0, $e);
                }
            case 'hash_hmac':
            default:
                return hash_hmac($alg, $msg, $secretKey, true);
        }
    }


    /** Creates a JWT
     * @param Array $payload JSON object into which custom data can be stored. Spezial functional values are:
     * "nbf": <UtcSec> to defined that token is valid after a (future) timestamp
     * "
     * @param String $secretKey Private key to sign JWT (if null then $_ENV['JWT_SECRET_KEY'])
     * @param String $alg Algorithm that should be used for signing (optional)
     * @param String $keyId ID of the key (optional)
     * @param Array $head Additional JWT header fields (optional)
     * @return String JWT token
     */
    function jwt_encode($payload, $secretKey=null, $alg='HS256', $keyId=null, $head=null){
        $header = array('typ' => 'JWT', 'alg' => $alg);
        if($keyId !== null) $header['kid'] = $keyId;
        if(isset($head) && is_array($head)) $header = array_merge($head, $header);
        $jwt = _jwt_encode($header) .".". _jwt_encode($payload);
        return $jwt .".". _jwt_encode(jwt_sign($jwt, $secretKey, $alg), false);
    }



    /** Loads the user session from a cookie
     * @param String $cookieName Name of the cookie in which the session is stored (default 'jwt')
     * @param String $secretKey Private key to verify integrity of JWT (if null then $_ENV['JWT_SECRET_KEY'])
     * @param Int $currentTime UTC timestamp in seconds (optional, can be used for unit tests)
     * @return Array containing loaded session values or empty array if no valid session
     */
    function jwt_session_load($cookieName="jwt", $secretKey=null, $currentTime=null){
        if(!isset($_COOKIE[$cookieName])) return array();
        return jwt_decode($_COOKIE[$cookieName]);
    }


    /** Stores/updates the session in a cookie
     * @param Object $jsonObj JSON object containing the custom data that should be stored in the session (if null then $_SESSION will be used)
     * @param String $cookieName Name of the cookie in which the session will be stored (default 'jwt')
     * @param Int $cookieExpire Expire seconds for how long the session should be valid (0 = until tab/browser gets closed, default '0')
     * @param Boolean $cookieSecure True if session should only be sent if HTTPs is used, if null then $_ENV['JWT_HTTPS_ONLY'] will be used (default null)
     * @param Boolean $cookieHttpOnly If the cookie should not be readble for JavaScript or other applications (default true)
     * @param Array $cookieOptions Other cookie options that should be set like 'path', 'domain', 'samesite', etc. (optional)
     */
    function jwt_session_store($jsonObj=null, $cookieName="jwt", $cookieExpire=0, $cookieSecure=null, $cookieHttpOnly=true, $cookieOptions=array()){
        if(is_null($jsonObj)) $jsonObj = $_SESSION;
        $jwt = jwt_encode($jsonObj);
        if(!$jwt) return;
        $cookieOptions['expires'] = $cookieExpire;
        $cookieOptions['secure'] = is_null($cookieSecure) ? $_ENV['JWT_HTTPS_ONLY'] : $cookieSecure;
        $cookieOptions['httponly'] = $cookieHttpOnly;
        setcookie($cookieName, $jwt, $cookieOptions);
    }


    /** Deletes the session
     * @param String $cookieName Name of the cookie in which the session is stored
     */
    function jwt_session_destroy($cookieName="jwt"){
        unset($_COOKIE[$cookieName]);
        setcookie($cookieName, null, -1, '/');
    }
?>