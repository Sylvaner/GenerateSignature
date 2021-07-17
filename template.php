<?php 
        global $template;
        global $userData;

        $userDataList = get_object_vars($userData);
        // Remplace les données trouvées dans le template
        foreach ($userDataList as $userDataKey => $userDataValue) {
            $template = str_replace("#$userDataKey#", $userDataValue, $template);
        }
?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>Signature</title>
    <style><?php global $cssTemplate; echo $cssTemplate; ?></style>
    <style>
        #copy-button {
            display: block;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            color: white;
            background-color: #3498DC;
            font-size: 2rem;
            padding: 10px;
            margin: 0 auto;
        }

        #copy-button::after {
            content: "";
            display: block;
            width: 100%;
            height: 4px;
            border-radius: 50%;
            background-color: black;
            opacity: 0.4;
            transform: translateY(18px);
            transition: all 0.2s ease-in-out;
        }

        #copy-button:hover::after {
            opacity: 0.8;
            transform: translateY(20px);
        }
    </style>
</head>
<body>
    <div id="signature"><?php echo $template; ?></div>
    <button id="copy-button" onclick="copySignatureToClipboard()">Copier dans le presse-papier</button>
    <script type="text/javascript">
    function copySignatureToClipboard() {
        const signature = document.getElementById('signature');
        const selectionRange = document.createRange();
        selectionRange.selectNode(signature);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(selectionRange);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
    };
    </script>
</body>
</html>