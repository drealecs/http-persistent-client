<?php
namespace HttpPersistent;

class Client {
    
    private $cookieId;
    
    private $persistCookie;
    
    public function __construct($uniqueSession = null)
    {
        if (isset($uniqueSession)) {
            $this->cookieId = md5($uniqueSession);
            $this->persistCookie = true;
        } else {
            $this->cookieId = md5(uniqid(microtime(), true));
            $this->persistCookie = false;
        }
    }
    
    private function initCurlSettings()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->getJarFilename());
        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->getJarFilename());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36');
        
        return $curl;
    }
    
    public function call($url, $post = array())
    {
        $curl = $this->initCurlSettings();

        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($post)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        $body = curl_exec($curl);
        curl_close($curl);

        return $body;
    }
    
    private function getJarFilename()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'HttpPersistent-' . $this->cookieId . '.jar.cookie';
    }
    
    public function __destruct()
    {
        if (!$this->persistCookie) {
            $this->destroySession();
        }
    }
    
    private function destroySession()
    {
        unlink($this->getJarFilename());
    }
} 
