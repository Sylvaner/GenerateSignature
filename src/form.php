<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Outil de génération des signatures</title>
    <style type="text/css">
        * {
            font-family: sans-serif;
        }

        #email {
            display: block;
            font-size: 2rem;
            margin: 2rem auto;
            border: none;
            border-bottom: 2px solid #7AB8E1;
            width: 80%;
        }

        #email:focus {
            outline: none;
            border-bottom: 2px solid #2875A8;
        }
    </style>
    <link rel="stylesheet" href="global.css" />
</head>
<body>
    <input id="email" type="text" placeholder="Indiquez votre adresse email" />
    <button onclick="generateSignature()">Générer votre signature</button>
    <div id="toast"></div>
    <script type="text/javascript" src="global.js"></script>
    <script type="text/javascript">
        function generateSignature() {
            const email = document.getElementById('email').value;
            if (isValidemail(email)) {
                goToTemplatePage(email);
            }
        }

        function isValidemail(email) {
            if (email === '') {
                showToast('L\'adresse email doit être remplie');
                return false;
            }
            if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(email)) {
                showToast('L\'adresse email n\'est pas valide');
                return false;
            }
            return true;
        }

        function goToTemplatePage(email) {
            const encodedemail = encodeURIComponent(email);
            const targetPage = generateUrlWithEmail(encodedemail);
            window.location.href = targetPage;
        }

        function generateUrlWithEmail(email) {
            const baseUrl = window.location.href.split('?');
            return baseUrl[0] + '?email=' + email;
        }
    </script>
</body>
</html>