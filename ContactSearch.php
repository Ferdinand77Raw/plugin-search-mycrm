<?php
use GuzzleHttp\Client;

class PBXManager_ContactSearch_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

        if (!$permission) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
    }

    public function process(Vtiger_Request $request)
    {
        
        $response = new Vtiger_Response();
        $url = "https://devapi.endato.com/Phone/Enrich";
        $number = $request->get("phone");
        $searchType = $request->get("searchType");
        
        $ch = curl_init($url);
        $callnumber = preg_replace('/^\+1\s*/', '', $number);
        $data = ["Phone"=> $callnumber];
        $jsonData = json_encode($data);

        $headers = [
            'accept: application/json',
            'content-type: application/json',
            'galaxy-ap-name: cd8aa3bf-81a1-479c-b095-4057148a8f2e',
            'galaxy-ap-password: 634d36c551e546aabe7ea61d125cd6c2',
            'galaxy-client-type: Galaxy Client Type',
            'galaxy-search-type: ' . $searchType
        ];

        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_error($ch)) {
            $result = json_encode(["isError" => true, "error" => curl_error($ch)]);
        }

        $responseData = json_decode($result, true);
        
        curl_close($ch);
        $response->setResult($responseData);
        $response->emit();        
    }

    
}

?>