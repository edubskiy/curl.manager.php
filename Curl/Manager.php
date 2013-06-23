<?php
/**
 * @author Evgeniy Dubskiy
 * Date: 27/12/2011
 * Time: 12:02
 * Uses FileManager to Save Debug Information
 * Curl_Manager::Create()
 *  ->SetHeaders($headers)
 *  ->ExecPost($postDataFields)
 */

class Curl_Manager
{
    public $headers = array();
    protected $url = '';
    protected $timeout = 60;
    protected $usePostMethod = 0;
    protected $postDataFields = '';
    protected $enabledReturnData = 1;
    protected $response;
    protected $debug;
    protected $debugPath = "logs/";
    protected $debugFileExt = ".log";
    protected $enabledSaveDebug = true;

    public function __construct() {}

    /**
     * @static
     * @return Curl_Manager
     */
    public static function Create()
    {
        return new self();
    }

    public function Init()
    {
        $cURL = curl_init();

        curl_setopt($cURL, CURLOPT_URL, $this->url);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, $this->enabledReturnData);
        curl_setopt($cURL, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($cURL, CURLOPT_POST, $this->usePostMethod);
        curl_setopt($cURL, CURLOPT_POSTFIELDS, $this->postDataFields);

        return $cURL;
    }

    protected function OutputDebug()
    {
        if (isset($_GET['debug']))
        {
            echo $this->debug;
            return true;
        }
        return false;
    }

    public function Exec()
    {
        $cURL = $this->Init();

        if (empty($cURL))
        {
            return false;
        }

        $this->response = curl_exec($cURL);

        if(curl_errno($cURL))
        {
            $this->Debug("Error: ".curl_error($cURL));
            return false;
        }

        curl_close($cURL);

        $this->SaveDebug();
        $this->OutputDebug();

        return $this->response;
    }

    public function ExecPost($postDataFields)
    {
        $this->SetUsePostMethod(1);
        $this->SetPostDataFields($postDataFields);

        return $this->Exec();
    }

    public function GetLastResponse()
    {
        return $this->response;
    }

    protected function Debug($message)
    {
        if (is_array($message))
        {
            $message = var_export($message, true);
        }

        $this->debug .= $message . "\n";

        return true;
    }

    protected function SaveDebug()
    {
        if ( ! class_exists("FileManager"))
        {
            return false;
        }

        if (empty($this->enabledSaveDebug))
        {
            return false;
        }

        $filePath = $this->debugPath . date("Y-m-d-H") . $this->debugFileExt;

        return FileManager::Create($filePath)
                ->Save($this->debug);
    }

    public function SetHeaders($headers)
    {
        $this->Debug("set headers:");
        $this->Debug($headers);

        $this->headers = $headers;
        return $this;
    }

    public function AddHeaders($headers)
    {
        $this->Debug("add headers:");
        $this->Debug($headers);

        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    public function SetPostDataFields($postDataFields)
    {
        $this->Debug("Post Data Fields");
        $this->Debug($postDataFields);

        $this->postDataFields = $postDataFields;

        return $this;
    }

    public function SetUsePostMethod($usePostMethod)
    {
        $this->Debug("set usePostMethod = $usePostMethod");

        $this->usePostMethod = $usePostMethod;
        return $this;
    }

    public function SetEnabledReturnData($enabledReturnData)
    {
        $this->Debug("set enabledReturnData = $enabledReturnData");

        $this->enabledReturnData = $enabledReturnData;
        return $this;
    }

    public function SetEnabledSaveDebug($enabledSaveDebug)
    {
        $this->Debug("set enabledSaveDebug = $enabledSaveDebug");

        $this->enabledSaveDebug = $enabledSaveDebug;
        return $this;
    }

    public function SetURL($url)
    {
        $this->Debug("set url = $url");

        $this->url = $url;
        return $this;
    }
};