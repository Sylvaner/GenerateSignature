<?php 
        global $template;
        global $userData;

        $userDataList = get_object_vars($userData);
        // Remplace les données trouvées dans le template
        foreach ($userDataList as $userDataKey => $userDataValue) {
            if ($userDataValue !== '') {
                $template = str_replace("#$userDataKey#", $userDataValue, $template);
                $template = preg_replace('/#(end)?if ' . $userDataKey . '#/', '', $template);
            } else {
                $template = preg_replace('/#if ' . $userDataKey . '#.*#endif ' . $userDataKey . '#/', '', $template);
            }
        }
?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>Signature</title>
    <style><?php global $cssTemplate; echo $cssTemplate; ?></style>
    <style>
        #signature {
            display: block;
            margin: 2rem auto;
        }
    </style>
    <link rel="stylesheet" href="global.css" />
</head>
<body>
    <div id="signature"><?php echo $template; ?></div>
    <div id="toast">Test message</div>
    <button id="copy-button" onclick="copySignature()">Copier dans le presse-papier</button>
    <script type="text/javascript" src="global.js"></script>
    <script type="text/javascript">
        function copySignature() {
            const signatureDiv = document.getElementById('signature');
            copyHtmlInClipboard(signatureDiv);
            showToast('Le texte a été copié dans le presse-papier');
        }

        function copyHtmlInClipboard(htmlElement) {
            const selectionRange = document.createRange();
            selectionRange.selectNode(htmlElement);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(selectionRange);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
        };
    </script>
</body>
</html>