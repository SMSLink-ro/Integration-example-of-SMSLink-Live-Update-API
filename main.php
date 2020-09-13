<?php 

/**
 *
 *   Live Update integration with SMSLink.ro
 *   
 *     SMSLink Live Update allows you to perform various operations on your SMSLink account, using Live Update API, such as:
 *     
 *     Blacklist Phone Number Add/Remove/Verify
 *     Contact Create/Remove/Update
 *
 *   Featured Functions of SMSLinkLiveUpdate class
 *   
 *     $liveUpdate = new SMSLinkLiveUpdate("MyLiveUpdateConnectionID", "MyLiveUpdatePassword");
 *     
 *     $liveUpdate->blacklistAdd(...)
 *         Adds a Phone Number to the Blacklist in your SMSLink account
 *     
 *     $liveUpdate->blacklistRemove(...)    
 *         Removes a Phone Number from the Blacklist in your SMSLink account     
 *     
 *     $liveUpdate->isBlacklisted(...)
 *         Checks if Phone Number is in the Blacklist in your SMSLink account
 *         
 *     $liveUpdate->createContact(...)    
 *         Creates a Contact into a Specified Group in your SMSLink account
 *     
 *     $liveUpdate->updateContact(...)
 *         Updates a Contact from a Specified Group in your SMSLink account
 *     
 *     $liveUpdate->removeContact(...)
 *         Removes a Phone Number from a Specified Group or from All Groups in your SMSLink account
 *
 *   Features
 *   
 *     Supports HTTP and HTTPS protocols
 *     Supports PHP cURL GET, PHP cURL POST and file_get_contents()
 *
 *   Usage:
 *
 *     See Usage Examples for the SMSLinkLiveUpdate() class starting on line 659
 *
 *     Get your SMSLink / SMS Marketing / Live Update Connection ID and Password from
 *         https://www.smslink.ro/get-live-update-api-key/
 *          
 *   System Requirements:
 *
 *     PHP 5 with
 *         CURL enabled or file_get_contents with allow_url_fopen to be set to 1 in php.ini
 *
 *   @version    1.0
 *   @see        https://www.smslink.ro/sms-marketing-documentatie-live-update.html
 *
 */

class SMSLinkLiveUpdate
{
    private $connection_id = null;
    private $password      = null;
    
    private $doHTTPS       = true;
    private $requestMethod = 1;
    
    protected $endpointHTTP  = "http://www.smslink.ro/sms/marketing/communicate/index.php";
    protected $endpointHTTPS = "https://secure.smslink.ro/sms/marketing/communicate/index.php";
    
    public $communicationLogs = array();
    
    protected $serviceIDs = array(
             1 => "SMS Marketing",
             2 => "Mail to SMS",
             3 => "SMS Gateway (HTTP)",
             9 => "SMS Gateway (BULK)",
            10 => "SMS Gateway (SOAP)",
            11 => "SMS Gateway (JSON)",
             4 => "SMS Alerts",
             7 => "SMS Connectors",
             5 => "2-Way SMS"                    
        );
    
    /*******************************************************************************************************************************************************************************
     *   
     *   
     *   Public-Scope Functions for Object Handling and Configuration
     *   
     *   
     ******************************************************************************************************************************************************************************/
    
    /**
     *   Initialize SMSLink - Live Update
     *
     *   Initializing Live Update will require the parameters $connection_id and $password. $connection_id and $password can be generated at
     *   https://www.smslink.ro/sms/marketing/liveupdate.php after authenticated with your account credentials.
     *
     *   @param string    $connection_id     SMSLink - Live Update - Connection ID
     *   @param string    $password          SMSLink - Live Update - Password
     *
     *   @return void
    */
    public function __construct($connection_id, $password)
    {
        if (!is_null($connection_id))
            $this->connection_id = $connection_id;
    
        if (!is_null($password))
            $this->password = $password;
         
        if ((is_null($this->connection_id)) or (is_null($this->password)))
            exit("SMS Gateway initialization failed, credentials not provided. Please see documentation.");
    
    }
    
    public function __destruct()
    {
        $this->connection_id = null;
        $this->password = null;
    
        $this->doHTTPS = true;
        $this->requestMethod = 1;
    }
    
    /**
     *   Sets the method in which the parameters are sent to SMS Gateway
     *
     *   @param int    $requestMethod     1 for cURL GET (recommended and default value)
     *                                    2 for cURL POST
     *                                    3 for file_get_contents (recommended if you do not have PHP cURL installed)
     *
     *   @return bool     true if method was set or false otherwise
     */
    public function setRequestMethod($requestMethod = 1)
    {
        if (in_array($requestMethod, array(1, 2, 3))) $this->requestMethod = $requestMethod;
        else return false;
    
        return true;
    }
    
    /**
     *   Returns the method in which the parameters are sent to SMS Gateway
     *
     *   @return int
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }
    
    /**
     *   Sets the protocol that will be used by SMS Gateway (HTTPS or HTTP).
     *
     *   @param string    $methodName     POST or GET
     *
     *   @return bool     true if method was set or false otherwise
     */
    public function setProtocol($protocolName = "HTTPS")
    {
        $protocolName = strtoupper($protocolName);
    
        if ($protocolName == "HTTPS") $this->doHTTPS = true;
        elseif ($protocolName == "HTTP") $this->doHTTPS = false;
        else return false;
    
        return true;
    }
    
    /**
     *   Returns the protocol that is used by SMS Gateway (HTTPS or HTTP)
     *
     *   @return string     GET or POST possible values
     */
    public function getProtocol()
    {
        return ($this->doHTTPS) ? "HTTPS" : "HTTP";
    }
    
    /**
     *   Returns the latest log message from communication log
     *
     *   @return string
     */
    public function getLastLogMessage()
    {
        return $this->communicationLogs[sizeof($this->communicationLogs) - 1];
    }
    
    /**
     *   Displays the communication log
     *
     *   @return string
     */
    public function displayLogMessages()
    {
        echo "<b>Communication Log:</b><br />";
        foreach ($this->communicationLogs as $key => $logMessage)
            echo $logMessage."<br />";
    }
    
    /*******************************************************************************************************************************************************************************
     *
     *
     *   Private-Scope Functions
     *
     *
     ******************************************************************************************************************************************************************************/
        
    /**
     *   Prepares the Request Response
     *
     *   @param string     $requestResponse
     *
     *   @return array
     */
    private function prepareResponse($requestResponse)
    {
        $requestResponse = explode(";", $requestResponse);

        $requestStatus = false;
        
        if (isset($requestResponse[0]))        
            if ($requestResponse[0] == "MESSAGE")
                $requestStatus = true;        
            
        return array(
                "responseStatus"   => $requestStatus,
                "responseCategory" => isset($requestResponse[0]) ? $requestResponse[0] : "ERROR",
                "responseCode"     => isset($requestResponse[1]) ? $requestResponse[1] : 0,
                "responseMessage"  => isset($requestResponse[2]) ? $requestResponse[2] : "Unknown Error",
                "responseParams"   => isset($requestResponse[3]) ? explode(",", $requestResponse[3]) : array()
            );               
    }
    
    /**
     *   Associate the Locally Generated Request Response
     *
     *   @param array     $requestResponse
     *
     *   @return array
     */
    private function assocResponse($requestResponse)
    {
        return array(
                "responseStatus"   => isset($requestResponse[0]) ? $requestResponse[0] : false,
                "responseCategory" => isset($requestResponse[1]) ? $requestResponse[1] : "ERROR",
                "responseCode"     => isset($requestResponse[2]) ? $requestResponse[2] : 0,
                "responseMessage"  => isset($requestResponse[3]) ? $requestResponse[3] : "Unknown Error",
                "responseParams"   => isset($requestResponse[4]) ? explode(",", $requestResponse[4]) : array()
            );
    }
    
    /**
     *   Prepares the Request for SMSLink
     *     
     *   @param array     $requestParameters
     *
     *   @return string
     */
    private function prepareSendRequest($requestParameters)
    {
        $requestURL = ($this->getProtocol() == "HTTPS") ? $this->endpointHTTPS : $this->endpointHTTP;
        
        $requestCommonParameters = array(
                "connection_id" => $this->connection_id,
                "password"      => $this->password
            );
        
        $requestParameters = array_merge($requestCommonParameters, $requestParameters);
        
        return $this->sendRequest($requestURL, $requestParameters);
    }
    
    /**
     *   Sends Request to SMSLink
     *
     *   @param string    $requestURL
     *   @param array     $requestParameters
     *
     *   @return string     
     */
    private function sendRequest($requestURL, $requestParameters)
    {
        $requestResult  = false;
        $returnedResult = "ERROR;0;Unknown error.";
    
        $requestMethod = $this->getRequestMethod();
    
        $logMessage = date("d-m-Y H:i:s")." - Sending Request using ";
    
        if ($requestMethod == 1)
            $logMessage = $logMessage."cURL GET";
    
        if ($requestMethod == 2)
            $logMessage = $logMessage."cURL POST";
    
        if ($requestMethod == 3)
            $logMessage = $logMessage."file_get_contents()";
    
        $serializedParameters = http_build_query($requestParameters);
    
        if (($requestMethod == 1) or ($requestMethod == 2))
        {
            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, $requestURL.(($requestMethod == 1) ? "?".$serializedParameters : ""));
    
            $logMessage = $logMessage." to URL: [".$requestURL.(($requestMethod == 1) ? "?".$serializedParameters : "")."]";
    
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
            if ($requestMethod == 2)
            {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $serializedParameters);
    
                $logMessage = $logMessage." with POST parameters: [".$serializedParameters."]";
            }
    
            if (strpos($requestURL, "https://") !== false)
            {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
    
            $requestResult = curl_exec($ch);
    
            $connectionErrorCode    = curl_errno($ch);
            $connectionErrorMessage = curl_error($ch);
            $requestStatusCode      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
            if ($connectionErrorCode == 0)
            {
                if (($requestStatusCode >= 200) and ($requestStatusCode <= 299))
                {
                    $returnedResult = $requestResult;
                }
                else
                {
                    $returnedResult = "ERROR;0;Unexpected HTTP code ".$requestStatusCode;
                }
            }
            else
            {
                $returnedResult = "ERROR;0;".$connectionErrorMessage;
            }
    
            curl_close($ch);
        }
        else
        {
            if ($requestMethod == 3)
            {
                $requestResult = file_get_contents($requestURL."?".$serializedParameters);
                $logMessage = $logMessage." to URL: [".$requestURL."?".$serializedParameters."]";
    
                if ($requestResult !== false)
                {
                    $returnedResult = $requestResult;
                }
                else
                {
                    $returnedResult = "ERROR;0;Connection failed using file_get_contents().";
                }
            }
        }
    
        $logMessage = $logMessage." => Request Result: [".$returnedResult."]";
    
        $this->communicationLogs[] = $logMessage;
    
        return $returnedResult;
    }
    
    /**
     *   Formats the Phone Number
     *     
     *   @param string     $phoneNumber
     *
     *   @return string
     */
    private function formatPhoneNumber($phoneNumber)
    {
        $phoneNumber = str_replace("+", "00", $phoneNumber);       // Converts + to 00
        $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumber); // Remove all non-numeric characters
        
        return $phoneNumber;
    }
    
    /*******************************************************************************************************************************************************************************
     *
     *
     *   Public-Scope Functions
     *
     *
     ******************************************************************************************************************************************************************************/

    /**
     *   Adds a Phone Number to the Blacklist
     *
     *   @param string     $phoneNumber              Phone Number
     *   
     *   @param array      $blacklistedServiceIDs    If the value is an empty array, the Phone Number will be blacklisted for all the SMSLink services.
     *                                               If the value is an array, the Phone Number will be blacklisted for the enumerated services from the array.
     *                                               
     *                                                   Examples:
     *                                                   
     *                                                   - if the value is array(), the Phone Number will be blacklisted for all the SMSLink services
     *                                                   - if the value is array(1, 2), the Phone Number will be blacklisted for SMS Marketing and Mail to SMS services.
     *                                                        
     *   @param bool       $forceUpdate              If the Phone Number already exists in the blacklist and if this parameter is set to true, it will force an update of the
     *                                               Phone Number from the Blacklist.
     *                                                
     *                                               If the Phone Number already exists in the blacklist and if this parameter is set to false, it will do nothing to the
     *                                               Phone Number in the Blacklist.
     *                                                
     *   @return array
     */
    public function blacklistAdd($phoneNumber, $blacklistedServiceIDs = array(), $forceUpdate = true)
    {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        if (strlen($phoneNumber) == 0)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Invalid Phone Number."));
        
        $requestParameters = array(
                  "mode"            => "blacklist-add",  
                  "receiver_number" => $phoneNumber,
                  "force_update"    => ($forceUpdate == true) ? 1 : 0
            );
        
        if (sizeof($blacklistedServiceIDs) > 0)
            $requestParameters["service_ids"] = implode(",", $blacklistedServiceIDs);                
        
        $requestResponse = $this->prepareSendRequest($requestParameters);
        $requestResponse = $this->prepareResponse($requestResponse);
        
        return $requestResponse;
    }
    
    /**
     *   Removes a Phone Number from the Blacklist
     *
     *   @param string     $phoneNumber              Phone Number
     *     
     *   @return array
     */
    public function blacklistRemove($phoneNumber)
    {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        if (strlen($phoneNumber) == 0)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Invalid Phone Number."));
        
        $requestParameters = array(
                "mode"            => "blacklist-remove",
                "receiver_number" => $phoneNumber                
            );
        
        $requestResponse = $this->prepareSendRequest($requestParameters);
        $requestResponse = $this->prepareResponse($requestResponse);
        
        return $requestResponse;
    }
    
    /**
     *   Checks if Phone Number is in the Blacklist
     *
     *   @param string     $phoneNumber              Phone Number
     *
     *   @return array(
     *           "isRequestError"   => (bool) true if an error occured occured during request or false otherwise,
     *           "isBlacklisted"    => (bool) true if Phone Number is blacklisted or false otherwise,
     *           "responseCategory" => (string) the category of the response, ERROR for errors or MESSAGE on success,
     *           "responseCode"     => (int) the code of the response, according to the documentation,
     *           "responseMessage"  => (string) the blacklist response message for the query,
     *           "responseParams"   => (array) the parameters associated with the response, according to the documentation
     *       )               
     */
    public function isBlacklisted($phoneNumber)
    {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        if (strlen($phoneNumber) == 0)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Invalid Phone Number."));
        
        $requestParameters = array(
                "mode"            => "blacklist-verify",
                "receiver_number" => $phoneNumber
            );
        
        $requestResponse = $this->prepareSendRequest($requestParameters);
        $requestResponse = $this->prepareResponse($requestResponse);
        
        $isBlacklistedResponse = array(
                "isRequestError"   => true,
                "isBlacklisted"    => false,
            );
        
        if ($requestResponse["responseStatus"] == true)
        {
            if ($requestResponse["responseCategory"] == "MESSAGE")
            {
                $isBlacklistedResponse["isRequestError"] = false;
                
                if (((int) $requestResponse["responseCode"] == 12) or ((int) $requestResponse["responseCode"] == 13)) 
                    $isBlacklistedResponse["isBlacklisted"] = true;
                
                if ((int) $requestResponse["responseCode"] == 14)
                    $isBlacklistedResponse["isBlacklisted"] = false;
            }            
        }
            
        $isBlacklistedResponse = array_merge($isBlacklistedResponse, $requestResponse);
        
        return $isBlacklistedResponse;
    }
    
    /**
     *   Creates a Contact into a Specified Group
     *
     *   @param string     $phoneNumber              Phone Number
     *   
     *   @param int        $groupId                  Group ID in which the contact will be created
     *   
     *   @param string     $contactFullName          (Optional) Full name for the contact
     *   
     *   @param array      $contactVariabiles        (Optional) Associative array with up to 25 contact variabiles. Example:
     *                                                   array(
     *                                                       "dynamic_variabile_1"  => "... value ...",
     *                                                       "dynamic_variabile_2"  => "... value ...",
     *                                                       "dynamic_variabile_3"  => "... value ...",
     *                                                       ...
     *                                                       "dynamic_variabile_25" => "... value ...",                                                                                                                                                                     
     *                                                   )
     *                                                   
     *   @param bool       $allowDuplicate           (Optional) If set to true, duplicates will be allowed. 
     *                                                          If set to false, duplicates will return an error.
     *                                                          
     *   @param int        $duplicateScope           (Optional) Scope for duplicates check.        
     *                                                          If set to 1, the check will be done inside the Group ID.
     *                                                          If set to 2, the check will be within all groups from the account.         
     *   
     *   @return array
     */
    public function createContact($phoneNumber, $groupId, $contactFullName = null, $contactVariabiles = array(), $allowDuplicate = false, $duplicateScope = 1)
    {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        if (strlen($phoneNumber) == 0)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Invalid Phone Number."));
        
        if ($groupId == 0)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Invalid Group ID."));
        
        if (sizeof($contactVariabiles) > 25)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Too many contact variabiles."));
        
        $requestParameters = array(
                "mode"            => "receiver-add",
                "receiver_number" => $phoneNumber,
                "group_id"        => $groupId,
                "duplicate"       => ($allowDuplicate == false) ? 1 : 0,
                "duplicate_scope" => $duplicateScope,
                "receiver_name"   => (!is_null($contactFullName)) ? $contactFullName : "",                
            );
        
        if (sizeof($contactVariabiles) > 0)
        {
            foreach($contactVariabiles as $contactVariabileKey => $contactVariabileValue)
                $requestParameters[$contactVariabileKey] = $contactVariabileValue;
        }
        
        $requestResponse = $this->prepareSendRequest($requestParameters);
        $requestResponse = $this->prepareResponse($requestResponse);
        
        return $requestResponse;        
    }
    
    /**
     *   Updates a Contact from a Specified Group
     *
     *   @param string     $phoneNumber              Phone Number
     *
     *   @param int        $groupId                  Group ID in which the contact will be updated.
     *                                               If set to 0, the contact will be updated within all groups.
     *
     *   @param string     $contactFullName          (Optional) Full name for the contact
     *
     *   @param array      $contactVariabiles        (Optional) Associative array with up to 25 contact variabiles. Example:
     *                                                   array(
     *                                                       "dynamic_variabile_1"  => "... value ...",
     *                                                       "dynamic_variabile_2"  => "... value ...",
     *                                                       "dynamic_variabile_3"  => "... value ...",
     *                                                       ...
     *                                                       "dynamic_variabile_25" => "... value ...",
     *                                                   )
     *
     *   @return array
     */
    public function updateContact($phoneNumber, $groupId = 0, $contactFullName = null, $contactVariabiles = array())
    {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        if (strlen($phoneNumber) == 0)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Invalid Phone Number."));
        
        if (sizeof($contactVariabiles) > 25)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Too many contact variabiles."));
        
        $requestParameters = array(
                "mode"            => "receiver-update",
                "receiver_number" => $phoneNumber,
                "group_id"        => $groupId,
                "receiver_name"   => (!is_null($contactFullName)) ? $contactFullName : "",
            );
        
        if (sizeof($contactVariabiles) > 0)
        {
            foreach($contactVariabiles as $contactVariabileKey => $contactVariabileValue)
                $requestParameters[$contactVariabileKey] = $contactVariabileValue;
        }
        
        $requestResponse = $this->prepareSendRequest($requestParameters);
        $requestResponse = $this->prepareResponse($requestResponse);
        
        return $requestResponse;        
    }
    
    /**
     *   Removes a Phone Number from a Specified Group or from All Groups
     *
     *   @param string     $phoneNumber              Phone Number
     *   @param int        $groupId                  The Group ID from which the contact will be removed. If set to 0, the contact will be removed from all groups.
     *   
     *   @return array
     */
    public function removeContact($phoneNumber, $groupId = 0)
    {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        if (strlen($phoneNumber) == 0)
            return $this->assocResponse(array(false, "ERROR", 0, "Error Thrown in ".__FUNCTION__.": Invalid Phone Number."));
        
        $requestParameters = array(
                "mode"            => "receiver-remove",
                "receiver_number" => $phoneNumber,
                "group_id"        => $groupId                
            );
        
        $requestResponse = $this->prepareSendRequest($requestParameters);
        $requestResponse = $this->prepareResponse($requestResponse);
        
        return $requestResponse;        
    }
        
}

/*
 * 
 * 
 * 
 * 
 *
 *     Usage Examples for the SMSLinkLiveUpdate() class
 * 
 *
 *
 *
 *
 *
 */

/*
 *
 *
 *     Initialize SMS Marketing Live Update
 *
 *       Get your SMSLink / SMS Gateway Connection ID and Password from
 *       https://www.smslink.ro/get-live-update-api-key/
 *
 *
 *
 */
$liveUpdate = new SMSLinkLiveUpdate("MyLiveUpdateConnectionID", "MyLiveUpdatePassword");

/*
 *     Sets the method in which the parameters are sent to Live Update
 *
 *      1 for cURL GET  (make sure you have PHP cURL installed) (default and recommended)
 *      2 for cURL POST (make sure you have PHP cURL installed)
 *      3 for file_get_contents (requires allow_url_fopen to be set to 1 in php.ini) (recommended if you do not have PHP cURL installed)
 */
$liveUpdate->setRequestMethod(1);

/*
 *     Sets the protocol that will be used by SMS Gateway (HTTPS or HTTP).
 */
$liveUpdate->setProtocol("HTTPS");

/*
 * 
 * 
 * 
 *     Request & Response Examples
 *     
 *     
 *     
 */

/*
 * 
 * 
 *     Adds a Phone Number to the Blacklist
 *     
 *     
 */
echo "<b>Example for Adding a Phone Number to the Blacklist</b><br />";

$liveUpdateResult = $liveUpdate->blacklistAdd("07xyzzzzzz");

if ($liveUpdateResult["responseStatus"] == true) 
{
    echo "-- Phone Number successfuly added to the blacklist. ".$liveUpdateResult["responseMessage"]."<br />";
}
else 
{
    echo "-- Could not add Phone Number to the blacklist. ".$liveUpdateResult["responseMessage"]."<br />";

    if ($liveUpdateResult["responseCategory"] == "ERROR")
    {
        switch ((int) $liveUpdateResult["responseCode"])
        {
            case 14:
                echo "---- Phone Number is already blacklisted.";
                break;
            default:
                echo "---- Failed to connect to the blacklist.";
                break;
        }
        
        echo "<br />";
    }
}
    
echo "<br />";

/*
 * 
 * 
 *     Checks if a Phone Number is Blacklisted
 *     
 *     
 */
echo "<b>Example for Verifying a Phone Number to the Blacklist</b><br />";

$liveUpdateResult = $liveUpdate->isBlacklisted("07xyzzzzzz");

if ($liveUpdateResult["isRequestError"] == false)
{
    if ($liveUpdateResult["isBlacklisted"] == true) 
    {
        echo "-- Phone Number found in the blacklist. (".$liveUpdateResult["responseMessage"].")<br />";
        
        if ($liveUpdateResult["responseCategory"] == "MESSAGE")
        {
            switch ((int) $liveUpdateResult["responseCode"])
            {
                case 12:
                    echo "---- Phone Number is blacklisted for all services.";
                    break;
                case 13:
                    echo "---- Phone Number is blacklisted for the following services: ";
                    break;
            }
            
            echo "<br />";
        }
    }
    else 
    {
        echo "-- Phone Number not found in the blacklist. (".$liveUpdateResult["responseMessage"].")<br />";
    }
}
else
{
    echo "-- Could not query the blacklist. (".$liveUpdateResult["responseMessage"].")<br />";
}

echo "<br />";

/*
 * 
 * 
 *     Removes a Phone Number from the Blacklist
 *     
 *     
 */
echo "<b>Example for Removing a Phone Number from the Blacklist</b><br />";

$liveUpdateResult = $liveUpdate->blacklistRemove("07xyzzzzzz");

if ($liveUpdateResult["responseStatus"] == true)
{
    echo "-- Phone Number successfuly removed from the blacklist. ".$liveUpdateResult["responseMessage"]."<br />";
}
else
{
    echo "-- Could not remove Phone Number from the blacklist. ".$liveUpdateResult["responseMessage"]."<br />";

    if ($liveUpdateResult["responseCategory"] == "ERROR")
    {
        switch ((int) $liveUpdateResult["responseCode"])
        {
            case 12:
                echo "---- Phone Number is not blacklisted.";
                break;
            default:
                echo "---- Failed to connect to the blacklist.";
                break;
        }
        
        echo "<br />";
    }
}
    
echo "<br />";

/*
 *
 *
 *     Create a Contact in a Group
 *
 *
 */
echo "<b>Example for Creating a Contact in a Group</b><br />";

$liveUpdateResult = $liveUpdate->createContact(
                        "07xyzzzzzz", 
                        54321, 
                        "Popescu Gabriel", 
                        array(
                            "dynamic_variabile_1" => "... valoare 1 ...",
                            "dynamic_variabile_2" => "... valoare 2 ...",
                            "dynamic_variabile_3" => "... valoare 3 ...",
                            "dynamic_variabile_4" => "... valoare 4 ...",
                        ), 
                        false,
                        1
                    );

if ($liveUpdateResult["responseStatus"] == true)
{
    echo "-- Contact successfuly created. ".$liveUpdateResult["responseMessage"]."<br />";
}
else
{
    echo "-- Could create contact. ".$liveUpdateResult["responseMessage"]."<br />";

    if ($liveUpdateResult["responseCategory"] == "ERROR")
    {
        switch ((int) $liveUpdateResult["responseCode"])
        {            
            case 10:
                echo "---- Permission denied to specified group.";
                break;                
            case 15:
                echo "---- Phone Number already exists in the specified group.";
                break;
            case 22:
                echo "---- Phone Number already exists in groups.";
                break;
            case 30:
                echo "---- Permission denied the specified Live Update method. Check Live Update settings on SMSLink.";
                break;
            default:
                echo "---- Failed to connect to the specified group.";
                break;
        }

        echo "<br />";
    }
}

echo "<br />";

/*
 *
 *
 *     Updates a Contact in a Group
 *
 *
 */
echo "<b>Example for Updating a Contact in a Group</b><br />";

$liveUpdateResult = $liveUpdate->updateContact(
                            "07xyzzzzzz",
                            54321,
                            "Popescu Gabriel George",
                            array(
                                "dynamic_variabile_1" => "... valoare actualizata 1 ...",
                                "dynamic_variabile_2" => "... valoare actualizata 2 ...",
                                "dynamic_variabile_3" => "... valoare actualizata 3 ...",
                                "dynamic_variabile_4" => "... valoare actualizata 4 ...",
                            )
                        );

if ($liveUpdateResult["responseStatus"] == true)
{
    echo "-- Contact successfuly updated. ".$liveUpdateResult["responseMessage"]."<br />";
}
else
{
    echo "-- Could update contact. ".$liveUpdateResult["responseMessage"]."<br />";

    if ($liveUpdateResult["responseCategory"] == "ERROR")
    {
        switch ((int) $liveUpdateResult["responseCode"])
        {
            case 24:
                echo "---- Permission denied to specified group.";
                break;
            case 25:
                echo "---- Phone Number not found.";
                break;
            case 26:
                echo "---- No associated data passed for updating.";
                break;
            case 30:
                echo "---- Permission denied the specified Live Update method. Check Live Update settings on SMSLink.";
                break;
            default:
                echo "---- Failed to connect to the specified group.";
                break;
        }

        echo "<br />";
    }
}

echo "<br />";

/*
 *
 *
 *     Removes a Contact from a Group
 *
 *
 */
echo "<b>Example for Removing a Contact in a Group</b><br />";

$liveUpdateResult = $liveUpdate->removeContact(
                            "07xyzzzzzz",
                            54321  
                        );

if ($liveUpdateResult["responseStatus"] == true)
{
    echo "-- Contact successfuly removed. ".$liveUpdateResult["responseMessage"]."<br />";
}
else
{
    echo "-- Could remove contact. ".$liveUpdateResult["responseMessage"]."<br />";

    if ($liveUpdateResult["responseCategory"] == "ERROR")
    {
        switch ((int) $liveUpdateResult["responseCode"])
        {
            case 13:
                echo "---- Permission denied to specified group.";
                break;
            case 17:
                echo "---- Invalid specified group.";
                break;
            case 30:
                echo "---- Permission denied the specified Live Update method. Check Live Update settings on SMSLink.";
                break;
            case 32:
            case 33:
                echo "---- Phone Number not found.";
                break;            
            default:
                echo "---- Failed to connect to the specified group.";
                break;
        }

        echo "<br />";
    }
}

echo "<br />";

?>