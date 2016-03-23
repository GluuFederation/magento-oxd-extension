<?php
/**
 * Created Vlad Karapetyan
 */

abstract class GluuOxd_Openid_Helper_ClientOXDRP extends Mage_Core_Helper_Abstract{

    protected $data = array();
    protected $command;
    protected $params = array();
    protected $response_json;
    protected $response_object;
    protected $response_data = array();
    protected static $socket = null;
    protected $oxd_config;
    protected $oxd_host_port;


    /**
     * abstract Client_oxd constructor.
     */
    public function __construct()
    {
        $this->oxd_config = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));

        $this->setCommand();

    }

    /**
     * @return mixed
     */
    public function getOxdHostPort()
    {
        return $this->oxd_host_port;
    }

    /**
     * @param mixed $oxd_host_port
     */
    public function setOxdHostPort($oxd_host_port)
    {
        $this->oxd_host_port = $oxd_host_port;
    }

    /**
     * request to oxd socket
     **/
    public function oxd_socket_request($data, $char_count = 8192){
        $oxd_host_port = '';
        if($this->getOxdHostPort()){
            $oxd_host_port = $this->getOxdHostPort();
        }else{
            $oxd_host_port = $this->oxd_config['oxd_host_port'];
        }
        self::$socket = stream_socket_client( $this->oxd_config['oxd_host_ip'] . ':' . $oxd_host_port, $errno, $errstr, STREAM_CLIENT_PERSISTENT);
        if (!self::$socket) {
            return 'Can not connect to oxd server';
        }else{
            fwrite(self::$socket, $data);
            $result = fread(self::$socket, $char_count);
            fclose(self::$socket);
            return $result;
        }

    }
    /**
     * send function sends the command to the oxD server.
     *
     * Args:
     * command (dict) - Dict representation of the JSON command string
     **/
    public function request()
    {
        $this->setParams();

        $jsondata = json_encode($this->getData(), JSON_UNESCAPED_SLASHES);

        $lenght = strlen($jsondata);
        if($lenght<=0){
            return array('status'=> false, 'message'=> 'Sorry .Problem with oxd.');
        }else{
            $lenght = $lenght <= 999 ? "0" . $lenght : $lenght;
        }

        $this->response_json =  $this->oxd_socket_request(utf8_encode($lenght . $jsondata));

        if($this->response_json !='Can not connect to oxd server'){
            $this->response_json = str_replace(substr($this->response_json, 0, 4), "", $this->response_json);
            if ($this->response_json) {
                $object = json_decode($this->response_json);
                if ($object->status == 'error') {
                    return array('status'=> false, 'message'=> $object->data->error . ' : ' . $object->data->error_description);
                } elseif ($object->status == 'ok') {
                    $this->response_object = json_decode($this->response_json);
                    return array('status'=> true);
                }
            }
        }else{
            return array('status'=> false, 'message'=> 'Can not connect to oxd server. Please look file oxd-config.json  configuration in your oxd server.');
        }

    }

    /**
     * @return mixed
     */
    public function getResponseData()
    {
        if (!$this->getResponseObject()) {
            $this->response_data = 'Data is empty';
            return ;
        } else {
            $this->response_data = $this->getResponseObject()->data;
        }
        return $this->response_data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $this->data = array('command' => $this->getCommand(), 'params' => $this->getParams());
        return $this->data;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    abstract function setCommand();

    /**
     * getResult function geting result from oxD server.
     * Return: response_object - The JSON response parsing to object
     **/
    public function getResponseObject()
    {
        return $this->response_object;
    }

    /**
     * function getting result from oxD server.
     * return: response_json - The JSON response from the oxD Server
     **/
    public function getResponseJSON()
    {
        return $this->response_json;
    }

    /**
     * @param array $params
     */
    abstract function setParams();

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }


}