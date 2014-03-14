<?php
/**
 * DokuWiki Plugin shorty (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Fernando Ribeiro <pinguim.ribeiro@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_shorty extends DokuWiki_Plugin {

var $savedir = '';

    /**
     * Constructor gets default preferences and language strings
     */
    function helper_plugin_shorty() {
        global $conf;

        $this->savedir = rtrim($conf['savedir'],"/") . "/cache";
    }

    /**
     * Return info about supported methods in this Helper Plugin
     *
     * @return array of public methods
     */
    public function getMethods() {
        return array(
            array(
                'name'   => 'getShortUrl',
                'desc'   => 'returns the short url if exists, otherwise creates the short url',
                'params' => array(
                    'ID'                 => 'string',
                    'service (optional)' => 'string'
                ),
                'return' => 'string'
            ),
            array(
                // and more supported methods...
            )
        );
    }


    /**
     * Generates a short url for the pageID using the required shortening service
     * and adds the url to the database
     *
     * @return the shorturl or false in case of error
     */
     function getShortUrl($pageID, $service='default') {

        if ($service == 'default')
            $service = $this->getconf('default_service');

        switch ($service) {
            case 'bit.ly':
            case 'bitly.com':
            case 'j.mp':
                $shortURL = $this->getBitlyURL($pageID, $service);
                break;
            case 'tinyurl':
                $shortURL = $this->getTinyurlURL($pageID);
                break;
            default:
                $shortURL = false;
        }
        return $shortURL;
    }



    /**
     * Generates a short url using the Bit.ly API
     *
     * @return the shorturl or false in case of error
     */
    function getBitlyURL($pageID, $domain) {
        // checks if the short url already exists in the database
        $url = $this->readShortUrl($pageID, $domain);
        if ($url == false ) {
            // calls the service API to generate a short url
            $longUrl = rawurlencode(wl($pageID,'',true));

            $uri = $this->getConf('bitly_oauth_api');
            $uri .= "shorten?access_token=" . $this->getConf('bitly_oauth_access_token');
            $uri .= "&format=json";
            $uri .= "&domain=" . $domain;
            $uri .= "&longUrl=" . $longUrl;

            $output = json_decode($this->getCurl($uri));

            if ($output->status_txt != "OK") {
                return false;
            } else{
                $url = $output->{'data'}->{'url'};
                // saves the new short url to the database
                $this->writeShortUrl($pageID, $url, $domain);
            }
        }
        return $url;
    }


    /**
     * Generates a short url using the Tinyurl API
     *
     * @return the shorturl or false in case of error
     */
    function getTinyurlURL($pageID) {
        // checks if the short url already exists in the database
        $url = $this->readShortUrl($pageID, 'tinyurl');
        if ($url == false ) {
            // calls the service API to generate a short url
            $longUrl = rawurlencode(wl($pageID,'',true));
            $http = new DokuHTTPClient();
            $url = $http->get('http://tinyurl.com/api-create.php?url='.$longUrl);
            // saves the new short url to the database
            $this->writeShortUrl($pageID, $url, 'tinyurl');
        }
        return $url;
    }



   /**
    * reads shortURL for pageID from file
    */
    function readShortUrl ($pageID, $file) {
        $redirects = confToHash($this->savedir.'/'.$file.'.conf');
        if (in_array($pageID, $redirects)) {
            $shortURL = array_search($pageID, $redirects);
        } else {
            $shortURL = false;
        }
        return $shortURL;
    }


   /**
    * writes shortID for pageID to file
    */
    function writeShortUrl ($pageID, $shortURL, $file) {
        $redirects = confToHash($this->savedir.'/'.$file.'.conf');
        // check for duplicates in database and select alternative shorty when needed
        $url = $output[0];
        for ($j = 0; $j < 6; $j++) {
            if ( $redirects["$url"] && $redirects["$url"] != $pageID ) {
                $url = $output[$j+1];
            }
        }
        $redirects["$shortURL"] = $pageID;
        $filecontents = "";
        foreach ( $redirects as $short => $long ) {
            $filecontents .= $short . "          " . $long . "\n";
        }
        io_saveFile($this->savedir.'/'.$file.'.conf',$filecontents);
    }


    /**
    * Make a GET call using cURL.
    *
    * from https://github.com/Falicon/BitlyPHP/blob/master/bitly.php
    *
    * @param $uri
    * URI to call.
    */
    function getCurl($uri) {
    $output = "";
       try {
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = curl_exec($ch);
       } catch (Exception $e) {
       }
    return $output;
    }

}

// vim:ts=4:sw=4:et:
