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

    class LdapMicrosoft {
        private $ldapConnection;
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
            echo gettype($this->ldapConnection);
            if ($this->ldapConnection === false) {
                die("Impossible de se connecter au serveur $ldapServer.");
            }
        }
    
        private function connectToLdap(string $ldapUser, string $ldapPassword): void {
            // Version 3 du protocol LDAP
            ldap_set_option($this->ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
            // Permettre la recherche
            ldap_set_option($this->ldapConnection, LDAP_OPT_REFERRALS, 0);
    
            if (ldap_bind($this->ldapConnection, $ldapUser, $ldapPassword) !== true) {
                die("Impossible de se connecter au serveur LDAP.");
            }
        }    

        public function disconnect(): void {
            ldap_unbind($this->ldapConnection);
        }

        public function searchByEmail(string $userEmail) {
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

        public function getSearchResult($searchFilter) {
            return ldap_search($this->ldapConnection, $this->baseDn, $searchFilter);
        }

        public function getUserDataFromResult($ldapResults) {
            $entries = ldap_get_entries($this->ldapConnection, $ldapResults);
            if (isset($entries['count']) && $entries['count'] > 0) {
                $userRawData = $entries[0];
                return $userRawData;
            }
            return false;
        }

        private function parseUserRawData(array $userRawData) {
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
        
        private function extractData(array $userRawData, string $targetData) {
            if (isset($userRawData[$targetData]) && $userRawData[$targetData]['count'] > 0) {
                return $userRawData[$targetData][0];
            }
            return '';
        }

        private function transformUserData(MicrosoftUser $userData) {
            foreach ($this->dataToTransform as $targetData => $listOfTransforms) {
                if (array_key_exists($userData->$targetData, $listOfTransforms)) {
                    $userData->$targetData = $listOfTransforms[$userData->$targetData];
                }
            }
            return $userData;
        }
    }

    $ldap = new LdapMicrosoft($ldapBaseDn, $dataToTransform);
    $ldap->connect($ldapServer, $ldapUser, $ldapPassword);
    $user = $ldap->searchByEmail('sylvain.dangin@creps-idf.fr');
    var_dump($user);
    $user = $ldap->searchByEmail('veronique.cotteaux@creps-idf.fr');
    var_dump($user);
    $ldap->disconnect();