<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotografenes Hjem</title>
    <style>

        /* nav bar og kroppen til nettsiden*/

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            overflow: hidden;
            color: #fff;
        }

        header {
            background: linear-gradient(90deg, #4b0082, #8b008b); /* Mørk lilla til rosa gradient */
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        header .logo {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 16px;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #ff69b4; /* Rosa hover-effekt */
            color: #1a1a1a;
        }

        /* Mobilmeny */
        .menu-toggle {
            display: none;
            font-size: 24px;
            color: #fff;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            nav ul {
                display: none;
                flex-direction: column;
                background-color: #4b0082; /* Lilla bakgrunn for mobilmeny */
                position: absolute;
                top: 60px;
                right: 20px;
                width: 200px;
                height: 200px;
                justify-content: center;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            }

            nav ul.show {
                display: flex;
            }

            nav ul li {
                text-align: center;
            }

            .menu-toggle {
                display: block;
            }
        }

        /* Hjemme siden til min fortograf nettside */

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background: radial-gradient(circle, #4b0082, #1a1a1a, #8b008b);
            background-size: 200% 200%;
            animation: moveSmoke 700s infinite;
        }

        @keyframes moveSmoke {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 700% 1400%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 92vh;
            padding: 20px;
            z-index: -20;
        }

        .left {
            flex: 1;
            max-width: 500px;
            text-align: left;
            margin-right: 100px;
            z-index: 2;
        }

        .left h1 {
            font-size: 44px;
            color: #ff69b4; /* Rosa */
            margin-bottom: 20px;
        }

        .left p {
            font-size: 17px;
            margin-bottom: 30px;
            color: #ddd;
        }

        .left .btn {
            display: inline-block;
            background: #8b008b; /* Lilla */
            color: #fff;
            padding: 15px 25px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .left .btn:hover {
            background: #ff69b4; /* Rosa hover */
            color: #1a1a1a;
        }

        .right {
            flex: 1;
            max-width: 450px;
            text-align: center;
            margin-right: -100px;
            z-index: 2;
        }

        .right img {
            max-width: 100%;
            border-radius: 10px;
        }

        /* Responsivitet */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                text-align: center;
            }

            .left, .right {
                max-width: 100%;
            }

            .left h1 {
                font-size: 36px;
            }
            
            .right img {
                display: none;
            }
        }
 
    </style>
</head>
<body>
    <header>
        <nav>
            <span class="logo">photoshare</span>
            <span class="menu-toggle" onclick="toggleMenu()">☰</span>
            <ul>
                <li><a href="index.php">Hjem</a></li>
                <li><a href="about.php">Om Meg</a></li>
                <li><a href="gallery.php">Galleri</a></li>
                <li><a href="login.php">Logg Inn</a></li>
            </ul>
        </nav>
    </header>
    <script>
        function toggleMenu() {
            const menu = document.querySelector('nav ul');
            menu.classList.toggle('show');
        }
    </script>

<div class="background"></div>

<div class="container">
    <div class="left">
        <h1>Velkommen til PhotoShare</h1>
        <p>Oppdag fantastiske bilder og se øyeblikkene som betyr mest. Registrer deg nå for å låse opp alle funksjoner og oppleve vårt komplette galleri.</p>
        <a href="register.php" class="btn">Registrer Deg Nå!</a>
    </div>
    <div class="right">
            <img src="the-fotografer-better.png" alt="Fotograf bilde">
    </div>
</div>

</body>
</html>
