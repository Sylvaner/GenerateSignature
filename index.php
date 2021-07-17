<?php
require ('conf.php');

class MicrosoftUser {
    public string $email = '';
    public string $displayName = '';
    public string $title = '';
    public string $phone = '';
    public string $mobile = '';
    public string $department = '';
    public string $address = '';
    public string $city = '';
    public string $zipcode = '';
}

class LdapException extends Exception {}

class LdapMicrosoft {
    private $ldapConnection;
    private bool $connected = false;
    private string $baseDn;
    private array $dataToTransform;

    public function __construct(string $baseDn, array $dataToTransform) {
        $this->baseDn = $baseDn;
        $this->dataToTransform = $dataToTransform;
    }

    public function connect(string $ldapServer, string $ldapUser, string $ldapPassword): void {
        $this->connectToServer($ldapServer);
        $this->connectToLdap($ldapUser, $ldapPassword);
    }

    private function connectToServer(string $ldapServer): void {
        $this->ldapConnection = ldap_connect($ldapServer);
        if ($this->ldapConnection === false) {
            throw new LdapException('Impossible de se connecter au serveur ' . $ldapServer);
        }
        $this->connected = true;
    }

    private function connectToLdap(string $ldapUser, string $ldapPassword): void {
        // Version 3 du protocol LDAP
        ldap_set_option($this->ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        // Permettre la recherche
        ldap_set_option($this->ldapConnection, LDAP_OPT_REFERRALS, 0);
        // Timeout si le serveur ne répond pas (5 secondes)
        ldap_set_option($this->ldapConnection, LDAP_OPT_NETWORK_TIMEOUT, 5);
        ldap_set_option($this->ldapConnection, LDAP_OPT_TIMELIMIT, 5);
        ldap_set_option($this->ldapConnection, LDAP_OPT_TIMEOUT, 5);

        if (ldap_bind($this->ldapConnection, $ldapUser, $ldapPassword) !== true) {
            throw new LdapException('Impossible de se connecter au serveur LDAP avec l\'utilisateur ' . $ldapUser);
        }
    }    

    public function disconnect(): void {
        if ($this->connected) {
            ldap_unbind($this->ldapConnection);
            $this->connected = false;
        }
    }

    public function searchByEmail(string $userEmail): ?MicrosoftUser {
        $result = $this->getSearchResult("(&(mail=$userEmail))");
        if ($result !== false) {
            $userRawData = $this->getUserDataFromResult($result);
            if ($userRawData !== false) {
                $userData = $this->parseUserRawData($userRawData);
                return $this->transformUserData($userData);
            }
        }
        return false;
    }

    private function getSearchResult($searchFilter) {
        return ldap_search($this->ldapConnection, $this->baseDn, $searchFilter);
    }

    private function getUserDataFromResult($ldapResults): ?array {
        $entries = ldap_get_entries($this->ldapConnection, $ldapResults);
        if (isset($entries['count']) && $entries['count'] > 0) {
            $userRawData = $entries[0];
            return $userRawData;
        }
        return false;
    }

    private function parseUserRawData(array $userRawData): MicrosoftUser {
        $user = new MicrosoftUser();
        $user->email = strtolower($this->extractData($userRawData, 'userprincipalname'));
        $user->displayName = $this->extractData($userRawData, 'displayname');
        $user->title = $this->extractData($userRawData, 'title');
        $user->phone = $this->extractData($userRawData, 'telephonenumber');
        $user->mobile = $this->extractData($userRawData, 'mobile');
        $user->department = $this->extractData($userRawData, 'department');
        $user->address = $this->extractData($userRawData, 'streetaddress');
        $user->city = $this->extractData($userRawData, 'l');
        $user->zipcode = $this->extractData($userRawData, 'postalcode');
        return $user;
    }
    
    private function extractData(array $userRawData, string $targetData): string {
        if (isset($userRawData[$targetData]) && $userRawData[$targetData]['count'] > 0) {
            return $userRawData[$targetData][0];
        }
        return '';
    }

    private function transformUserData(MicrosoftUser $userData): MicrosoftUser {
        foreach ($this->dataToTransform as $targetData => $listOfTransforms) {
            if (array_key_exists($userData->$targetData, $listOfTransforms)) {
                $userData->$targetData = $listOfTransforms[$userData->$targetData];
            }
        }
        return $userData;
    }
}

try {
    $ldap = new LdapMicrosoft($ldapBaseDn, $dataToTransform);
    $ldap->connect($ldapServer, $ldapUser, $ldapPassword);
    $user = $ldap->searchByEmail('sylvain.dangin@creps-idf.fr');
    var_dump($user);
    $user = $ldap->searchByEmail('veronique.cotteaux@creps-idf.fr');
    var_dump($user);
} catch(LdapException $exception) {
    echo $exception->getMessage();
} finally {
    $ldap->disconnect();
}
