<?php
require ('conf.php');

/**
 * Classe de données d'un utilisateur
 */
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

/**
 * Classe de connexion à la base de données LDAP Microsoft
 */
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
            throw new LdapException("Impossible de se connecter au serveur $ldapServer.");
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
            throw new LdapException("Impossible de se connecter au serveur LDAP avec l'utilisateur $ldapUser.");
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
            if ($userRawData !== null) {
                $userData = $this->parseUserRawData($userRawData);
                return $this->transformUserData($userData);
            }
        }
        return null;
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
        return null;
    }

    private function createUserWithDefaultValues(): MicrosoftUser {
        global $defaultAddress;
        global $defaultCity;
        global $defaultZipcode;

        $user = new MicrosoftUser();
        $user->city = $defaultCity;
        $user->address = $defaultAddress;
        $user->zipcode = $defaultZipcode;
        return $user;
    }

    private function parseUserRawData(array $userRawData): MicrosoftUser {
        $user = $this->createUserWithDefaultValues();
        $user->email = strtolower($this->extractData($userRawData, 'userprincipalname'));
        $user->displayName = $this->extractData($userRawData, 'displayname');
        $user->title = $this->extractData($userRawData, 'title');
        $user->phone = $this->extractData($userRawData, 'telephonenumber');
        $user->mobile = $this->extractData($userRawData, 'mobile');
        $user->department = $this->extractData($userRawData, 'department');
        $address = $this->extractData($userRawData, 'streetaddress');
        if ($address !== '') {
            $user->address = str_replace("\n", '<br/>', $address);
        }
        $city = $this->extractData($userRawData, 'l');
        if ($city !== '') {
            $user->city = $city;
        }
        $zipcode = $this->extractData($userRawData, 'postalcode');
        if ($zipcode !== '') {
            $user->zipcode = $zipcode;
        }
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

function getUser($email): ?MicrosoftUser {
    global $ldapBaseDn;
    global $dataToTransform;
    global $ldapServer;
    global $ldapUser;
    global $ldapPassword;

    $result = null;
    try {
        $ldap = new LdapMicrosoft($ldapBaseDn, $dataToTransform);
        $ldap->connect($ldapServer, $ldapUser, $ldapPassword);
        $result = $ldap->searchByEmail($email);
    } catch(LdapException $exception) {
        echo $exception->getMessage();
    } finally {
        $ldap->disconnect();
    }
    return $result;
}

function isValidMailAddress($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function showError($msg) {
    echo '<!doctype html><html><body><p style="width: 100%; text-align: center">' . $msg . '</p><a href="/">Revenir au formulaire</a></body></html>';
}

function showSignature($email) {
    if (isValidMailAddress($email)) {
        global $userData;
        $userData = getUser($email);
        if ($userData !== null) {
            // Le fichier template se charge de l'affichage de la signature
            require_once('template.php');
        } else {
            showError("L'adresse email $email n'a pas été trouvée.");
        }
    } else {
        showError("L'adresse email $email n'est pas valide.");
    }
}

function showForm() {
    require_once('form.php');
}

if (isset($_GET['email'])) {
    showSignature($_GET['email']);
} else {
    showForm();
}

