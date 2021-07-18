<?php
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
    ';
    $cssTemplate = '
    #signature p { font-family: sans-serif; }
    p.displayName { font-weight: bold; }
    ';
