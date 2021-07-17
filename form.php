<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Outil de génération des signatures</title>
    <style>
        * {
            font-family: sans-serif;
        }

        #mail {
            display: block;
            font-size: 2rem;
            margin: 2rem auto;
            border: none;
            border-bottom: 2px solid #7AB8E1;
            width: 80%;
        }

        #mail:focus {
            outline: none;
            border-bottom: 2px solid #2875A8;
        }

        #gen-button {
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

        #gen-button::after {
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

        #gen-button:hover::after {
            opacity: 0.8;
            transform: translateY(20px);
        }
    </style>
</head>
<body>
    <input id="mail" type="text" placeholder="Votre adresse email" />
    <button id="gen-button" onclick="getSignature()">Générer votre signature</button>
    <script type="text/javascript">
        function generateUrlWithMail(mail) {
            const baseUrl = window.location.href.split('&');
            return baseUrl[0] + '?mail=' + mail;
        }

        function getSignature() {
            const mail = document.getElementById('mail').value;
            if (mail !== '') {
                const encodedEmail = encodeURIComponent(mail);
                const targetPage = generateUrlWithMail(mail);
                window.location.href = targetPage;
            } else {
                alert('L\'adresse email doit être remplie');
            }
        }
    </script>
</body>
</html>