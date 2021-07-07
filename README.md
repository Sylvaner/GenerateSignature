# generate-signature

Génère des signatures à partir des données contenues dans l'Active Directory

# Configuration

```
	$ldapUser = 'utilisateur@domain.tld';
  $ldapPassword = 'user_password';
  $ldapServer = '192.168.1.1';
  $ldapBaseDn = 'OU=Users,DC=domain,DC=ld';
  $dataToTransform = [
      'department' => [
        'IT' => 'Service Informatique',
        'ACC' => 'Accueil'
      ]
  ];
```

## $dataToTransform

Cette variable est un tableau associatif nécessaire pour renommer certaines parties des données de l'utilisateur.
Dans l'exemple, les personnes du service IT auront pour service 'Service Informatique'.
Ceci peut s'appliquer à l'ensemble des champs de l'utilisateur :

```
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
```
