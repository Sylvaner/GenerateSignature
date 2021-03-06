# GenerateSignature

Génère des signatures au format HTML à partir des données contenues dans l'Active Directory en fonction de l'utilisateur.

# Champs de l'utilisateur utilisables
* email
* displayName
* title
* phone
* mobile
* department
* address
* city
* zipcode


# Configuration

```php
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
  $template = '
    <p class="displayName">#displayName#</p>
    <p>#address#</p>
    <p>#city#</p>
    #if phone#<p>Tél. : #phone#</p>#endif phone#
  ';
  $cssTemplate = '
  #signature p { font-family: sans-serif; }
  p.displayName { font-weight: bold; }
  ';
```

## $dataToTransform

Cette variable est un tableau associatif nécessaire pour renommer certaines parties des données de l'utilisateur.
Dans l'exemple ci-dessus, les personnes du service IT (attribut department) auront pour service 'Service Informatique'.
Ceci peut s'appliquer à l'ensemble des champs de l'utilisateur.

## $template

Code HTML de la signature, chacun des champs doit être entouré du sympbole __#__.

Il est possible de ne pas afficher un bloc si une information manque (Téléphone dans l'exemple ci-dessus). Pour cela, il faut entourer le bloc par #if NOM_DE_L_ATTRIBUT# Bloc de données avec l'attribut #endif NOM_DE_L_ATTRIBUT#.

## $cssTemplate

Code CSS qui sera appliqué à la signature.
Le container de la signature à l'identifiant __signature__

# Lancement

```
docker compose up -d
```
